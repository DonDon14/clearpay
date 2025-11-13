<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ContributionModel;
use App\Models\PaymentModel;
use App\Services\ActivityLogger;
// use App\Services\QRReceiptService; // Disabled for now

class PaymentsController extends BaseController
{
    public function index()
    {
        // Check if user is logged in
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $contributionModel = new ContributionModel();
        $paymentModel = new PaymentModel();
        
        // Get only active contributions for payment recording
        $contributions = $contributionModel->where('status', 'active')->findAll();
        
        // Get grouped payments (grouped by payer and contribution)
        $groupedPayments = $paymentModel->getGroupedPayments();
        
        if (empty($groupedPayments)) {
            // Fallback: Get individual payments if grouped query fails
            $individualPayments = $paymentModel->getRecentPayments(100);
            
            // Convert individual payments to grouped format for display
            $groupedPayments = $this->convertIndividualToGrouped($individualPayments);
        }

        $data = [
            'title' => 'Payments Management',
            'pageTitle' => 'Payments',
            'pageSubtitle' => 'Manage student payments and transactions',
            'username' => session()->get('username'),
            'contributions' => $contributions,
            'groupedPayments' => $groupedPayments,
        ];

        return view('admin/payments', $data);
    }

    public function history()
    {
        // Check if user is logged in
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $data = [
            'title' => 'Payment History',
            'pageTitle' => 'Payment History',
            'pageSubtitle' => 'View all payment transactions and records',
            'username' => session()->get('username'),
        ];

        return view('admin/payment-history', $data);
    }

    /**
     * Get individual payments for a specific payer and contribution
     */
    public function getPaymentHistory()
    {
        if (!$this->request->isAJAX() && $this->request->getMethod() !== 'GET') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid request method'
            ]);
        }

        $payerId = $this->request->getGet('payer_id');
        $contributionId = $this->request->getGet('contribution_id');
        $paymentSequence = $this->request->getGet('payment_sequence') ?? 1;

        if (!$payerId || !$contributionId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Payer ID and Contribution ID are required'
            ]);
        }

        try {
            $paymentModel = new PaymentModel();
            $payments = $paymentModel->getPaymentsByPayerAndContribution($payerId, $contributionId, $paymentSequence);

            // Add refund information for each payment
            $refundModel = new \App\Models\RefundModel();
            foreach ($payments as &$group) {
                if (isset($group['payments']) && is_array($group['payments'])) {
                    foreach ($group['payments'] as &$payment) {
                        // Get refunds for this payment
                        $refunds = $refundModel
                            ->where('payment_id', $payment['id'])
                            ->where('status', 'completed')
                            ->findAll();
                        
                        $totalRefunded = 0;
                        foreach ($refunds as $refund) {
                            $totalRefunded += (float)$refund['refund_amount'];
                        }
                        
                        $payment['total_refunded'] = $totalRefunded;
                        $payment['available_for_refund'] = (float)$payment['amount_paid'] - $totalRefunded;
                        
                        // Determine refund status
                        if ($totalRefunded >= (float)$payment['amount_paid']) {
                            $payment['refund_status'] = 'fully_refunded';
                        } elseif ($totalRefunded > 0) {
                            $payment['refund_status'] = 'partially_refunded';
                        } else {
                            $payment['refund_status'] = 'no_refund';
                        }
                    }
                }
            }

            return $this->response->setJSON([
                'success' => true,
                'payments' => $payments
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    public function analytics()
    {
        // Check if user is logged in
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $data = [
            'title' => 'Payment Analytics',
            'pageTitle' => 'Analytics & Reports',
            'pageSubtitle' => 'View payment statistics and generate reports',
            'username' => session()->get('username'),
        ];

        return view('admin/analytics', $data);
    }

    public function save()
    {
        try {
            // Debug: Log all incoming data
            log_message('info', 'Payment save request data: ' . json_encode($this->request->getPost()));
            
            // Determine if this is a new or existing payer
            $payerId = $this->request->getPost('payer_id');
            $isExistingPayer = !empty($payerId);
            
            // Get valid payment method names from database
            $paymentMethodModel = new \App\Models\PaymentMethodModel();
            $validPaymentMethods = $paymentMethodModel->getActiveMethods();
            
            // Check if payment methods exist (critical for validation)
            if (empty($validPaymentMethods)) {
                log_message('error', 'No payment methods found in database. Payment methods must be seeded!');
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Payment methods are not configured. Please run: php spark db:seed PaymentMethodSeeder',
                    'errors' => ['payment_method' => 'No payment methods found in database. Setup incomplete.']
                ]);
            }
            
            $paymentMethodNames = array_column($validPaymentMethods, 'name');
            $paymentMethodList = implode(',', $paymentMethodNames);
            
            // Debug: Log valid payment methods
            log_message('info', 'Valid payment methods: ' . $paymentMethodList);
            log_message('info', 'Received payment_method: ' . $this->request->getPost('payment_method'));
            
            // Validation rules - conditional based on payer type
            $rules = [
                'contribution_id' => 'required|integer',
                'amount_paid' => 'required|numeric',
                'payment_method' => 'required',
                'is_partial_payment' => 'required|in_list[0,1]',
                'payment_date' => 'required'
            ];

            // Only require payer name/ID if it's a new payer
            if (!$isExistingPayer) {
                $rules['payer_name'] = 'required|min_length[3]|max_length[255]';
                $rules['payer_id'] = 'required|min_length[3]|max_length[50]';
            }

            if (!$this->validate($rules)) {
                $errors = $this->validator->getErrors();
                log_message('error', 'Payment validation failed: ' . json_encode($errors));
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $errors
                ]);
            }

            // Custom validation: Check if payment method exists (case-insensitive)
            $submittedPaymentMethod = $this->request->getPost('payment_method');
            $paymentMethodExists = false;
            foreach ($paymentMethodNames as $validMethod) {
                if (strcasecmp(trim($submittedPaymentMethod), trim($validMethod)) === 0) {
                    $paymentMethodExists = true;
                    // Use the exact name from database (to handle case differences)
                    $submittedPaymentMethod = $validMethod;
                    break;
                }
            }
            
            if (!$paymentMethodExists) {
                log_message('error', 'Payment method validation failed. Submitted: "' . $submittedPaymentMethod . '", Valid methods: ' . $paymentMethodList);
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Invalid payment method. Please select a valid payment method from the list.',
                    'errors' => [
                        'payment_method' => 'The selected payment method is not valid. Available methods: ' . $paymentMethodList
                    ],
                    'debug' => [
                        'submitted' => $submittedPaymentMethod,
                        'valid_methods' => $paymentMethodNames
                    ]
                ]);
            }

            $paymentModel = new PaymentModel();
            $payerModel = new \App\Models\PayerModel();
            
            // For existing payers, fetch their details from the database
            if ($isExistingPayer) {
                $existingPayer = $payerModel->where('id', $payerId)->first();
                
                if (!$existingPayer) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Payer not found'
                    ]);
                }
                $payerDbId = $existingPayer['id']; // Get the database ID
            } else {
                // Check if payer with this ID already exists
                $existingPayer = $payerModel->where('payer_id', $payerId)->first();
                
                if ($existingPayer) {
                    // Use existing payer
                    $payerDbId = $existingPayer['id'];
                } else {
                    // Create new payer in payers table (admin-created accounts are auto-verified)
                    $payerData = [
                        'payer_id' => $payerId,
                        'payer_name' => $this->request->getPost('payer_name'),
                        'contact_number' => $this->request->getPost('contact_number'),
                        'email_address' => $this->request->getPost('email_address') ?: null,
                        'password' => password_hash($payerId, PASSWORD_DEFAULT), // Set password to payer_id for admin-created accounts
                        'email_verified' => true,
                        'verification_token' => null,
                    ];
                    $payerDbId = $payerModel->insert($payerData);
                    
                    if (!$payerDbId) {
                        return $this->response->setJSON([
                            'success' => false,
                            'message' => 'Failed to create payer record'
                        ]);
                    }
                }
            }

            // Check for existing payments to prevent duplicates
            $duplicateCheck = $this->checkForDuplicatePayments($payerDbId, $this->request->getPost('contribution_id'));
            if (!$duplicateCheck['allowed'] && !$this->request->getPost('bypass_duplicate_check')) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => $duplicateCheck['message'],
                    'requires_confirmation' => $duplicateCheck['requires_confirmation'] ?? false,
                    'existing_payments' => $duplicateCheck['existing_payments'] ?? []
                ]);
            }

            // Determine payment status and sequence for duplicate payments
            $isPartial = $this->request->getPost('is_partial_payment') == '1';
            $remainingBalance = (float) $this->request->getPost('remaining_balance');
            $isDuplicatePayment = $this->request->getPost('bypass_duplicate_check') == '1';
            
            // Get contribution amount to calculate proper status
            $contributionModel = new \App\Models\ContributionModel();
            $contribution = $contributionModel->find($this->request->getPost('contribution_id'));
            
            // Check if contribution exists
            if (!$contribution) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Contribution not found'
                ]);
            }
            
            // Check if contribution is inactive - prevent adding payments to inactive contributions
            if ($contribution['status'] === 'inactive') {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Cannot add payment to an inactive contribution. This contribution is no longer active.'
                ]);
            }
            
            $contributionAmount = $contribution['amount'];
            $amountPaid = (float) $this->request->getPost('amount_paid');
            
            // Calculate actual remaining balance
            $actualRemainingBalance = $contributionAmount - $amountPaid;
            
            // For duplicate payments, treat as separate payment group
            if ($isDuplicatePayment) {
                $paymentStatus = 'fully paid'; // Always fully paid for duplicate payments
                $remainingBalance = 0; // No remaining balance for duplicate payments
                $isPartial = false;
                $paymentSequence = $this->getNextPaymentSequence($payerDbId, $this->request->getPost('contribution_id'));
            } else {
                // Calculate cumulative total paid including this new payment
                $existingPayments = $paymentModel
                    ->where('payer_id', $this->request->getPost('payer_id'))
                    ->where('contribution_id', $this->request->getPost('contribution_id'))
                    ->where('deleted_at', null)
                    ->findAll();
                
                $existingTotalPaid = array_sum(array_column($existingPayments, 'amount_paid'));
                $newTotalPaid = $existingTotalPaid + $amountPaid;
                
                // Determine payment sequence - ensure only one active partial group per contribution type
                if (!empty($existingPayments)) {
                    // Group payments by sequence to find active partial groups
                    $paymentGroups = [];
                    foreach ($existingPayments as $payment) {
                        $sequence = $payment['payment_sequence'] ?? 1;
                        if (!isset($paymentGroups[$sequence])) {
                            $paymentGroups[$sequence] = [];
                        }
                        $paymentGroups[$sequence][] = $payment;
                    }
                    
                    // Check each group to find if there's an active partial group
                    $activePartialGroup = null;
                    foreach ($paymentGroups as $sequence => $groupPayments) {
                        $groupTotalPaid = array_sum(array_column($groupPayments, 'amount_paid'));
                        $contributionAmount = $contribution ? $contribution['amount'] : 0;
                        
                        // Check if this group is partial (not fully paid)
                        if ($groupTotalPaid < $contributionAmount) {
                            $activePartialGroup = $sequence;
                            break; // Found an active partial group
                        }
                    }
                    
                    if ($activePartialGroup !== null) {
                        // Add to the existing partial group
                        $paymentSequence = $activePartialGroup;
                    } else {
                        // No active partial group, create new group
                        $paymentSequence = $this->getNextPaymentSequence($payerDbId, $this->request->getPost('contribution_id'));
                    }
                } else {
                    // No existing payments, start with sequence 1
                    $paymentSequence = 1;
                }
                
                // Calculate status based on cumulative total
                if ($newTotalPaid >= $contributionAmount) {
                    $paymentStatus = 'fully paid';
                    $remainingBalance = 0;
                    $isPartial = false;
                } else {
                    $paymentStatus = 'partial';
                    $remainingBalance = $contributionAmount - $newTotalPaid;
                    $isPartial = true;
                }
            }

            // Generate reference number and receipt number
            $referenceNumber = 'REF-' . date('Ymd') . '-' . strtoupper(uniqid());
            $receiptNumber = 'RCPT-' . date('Ymd') . '-' . str_pad(uniqid(), 8, '0', STR_PAD_LEFT);

            // Gather POST data for payment record (payer info is now in separate payers table)
            $data = [
                'payer_id' => $payerDbId, // This is the FK to payers.id
                'contribution_id' => $this->request->getPost('contribution_id'),
                'amount_paid' => $this->request->getPost('amount_paid'),
                'payment_method' => $submittedPaymentMethod, // Use normalized name from database
                'payment_status' => $paymentStatus,
                'is_partial_payment' => $isPartial ? true : false,
                'remaining_balance' => $remainingBalance,
                'parent_payment_id' => $this->request->getPost('parent_payment_id') ?: null,
                'payment_sequence' => $paymentSequence,
                'reference_number' => $referenceNumber,
                'receipt_number' => $receiptNumber,
                'recorded_by' => session()->get('user-id') ?: session()->get('user_id') ?: null,
                'payment_date' => $this->request->getPost('payment_date'),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            

            $id = $this->request->getPost('id');

            if ($id) {
                // Update existing payment
                $result = $paymentModel->update($id, $data);
                $message = 'Payment updated successfully.';
            } else {
                // Insert new payment
                $result = $paymentModel->insert($data);
                $message = 'Payment recorded successfully.';
            }

            if ($result) {
                // Get the payment ID (for new payments)
                $paymentId = $id ?: $paymentModel->getInsertID();
                
                // Check if this payment was added to an existing partial group
                // IMPORTANT: We need to check BEFORE the payment is inserted to see if there were existing payments
                // Since the payment was just inserted, we exclude it from the count
                $wasAddedToPartialGroup = false;
                if (!$id && !empty($paymentSequence)) {
                    // Check if there were existing payments in this group BEFORE this one was added
                    // Exclude the newly inserted payment to get accurate count of pre-existing payments
                    $existingGroupPayments = $paymentModel
                        ->where('payer_id', $payerDbId)
                        ->where('contribution_id', $this->request->getPost('contribution_id'))
                        ->where('payment_sequence', $paymentSequence)
                        ->where('payments.id !=', $paymentId) // Exclude the newly inserted payment
                        ->where('deleted_at', null)
                        ->findAll();
                    
                    $existingGroupPaymentsCount = count($existingGroupPayments);
                    
                    // If there were existing payments in this group, check if the group was partial before adding this payment
                    if ($existingGroupPaymentsCount > 0) {
                        // Calculate total paid in the group BEFORE adding this payment
                        $existingGroupTotalPaid = array_sum(array_column($existingGroupPayments, 'amount_paid'));
                        
                        // Check if the group was partial (not fully paid) before adding this payment
                        if ($existingGroupTotalPaid < $contributionAmount) {
                            $wasAddedToPartialGroup = true;
                            log_message('debug', "Payment added to existing partial group - Payment ID: {$paymentId}, Sequence: {$paymentSequence}, Existing Count: {$existingGroupPaymentsCount}, Existing Total: {$existingGroupTotalPaid}, Contribution Amount: {$contributionAmount}");
                        }
                    }
                }
                
                // Consolidate any existing multiple partial groups before processing
                $this->consolidatePartialGroups($payerDbId, $this->request->getPost('contribution_id'));
                
                // If this payment makes the total fully paid, update all previous payments in the same group
                if (!$isDuplicatePayment && isset($newTotalPaid) && $newTotalPaid >= $contributionAmount) {
                    $this->updatePaymentGroupStatus($payerDbId, $this->request->getPost('contribution_id'), $paymentSequence);
                }
                
                // Log activity using ActivityLogger
                $activityLogger = new ActivityLogger();
                
                if ($id) {
                    // Update existing payment - get old data first
                    $oldData = $paymentModel->find($id);
                    $data['id'] = $id;
                    $activityLogger->logPayment('updated', $data, $oldData);
                } else {
                    // Create new payment - use the insert result as ID
                    $data['id'] = $result;
                    $activityLogger->logPayment('created', $data);
                }
                
                // Return the full payment data so frontend can show QR receipt
                $paymentData = $paymentModel->select('
                    payments.id,
                    payments.receipt_number,
                    payments.payment_date,
                    payments.amount_paid,
                    payments.payment_method,
                    payments.payment_status,
                    payments.reference_number,
                    payments.remaining_balance,
                    payers.payer_id,
                    payers.payer_name,
                    payers.contact_number,
                    payers.email_address,
                    contributions.title as contribution_title
                ')
                ->join('payers', 'payers.id = payments.payer_id', 'left')
                ->join('contributions', 'contributions.id = payments.contribution_id', 'left')
                ->find($paymentId);
                
                // Send receipt email to payer for successful payments
                if ($paymentData && !$id) { // Only send for new payments
                    try {
                        $emailSent = $this->sendReceiptEmail($paymentData);
                        if ($emailSent) {
                            log_message('info', "Receipt email sent successfully for payment ID: {$paymentId}");
                        }
                    } catch (\Exception $e) {
                        log_message('error', 'Exception while sending receipt email (non-fatal): ' . $e->getMessage());
                    } catch (\Error $e) {
                        log_message('error', 'Error while sending receipt email (non-fatal): ' . $e->getMessage());
                    }
                }
                
                // Log to user_activities table if payment was added to partial group
                if (!$id && $wasAddedToPartialGroup && $paymentData) {
                    $payerName = $paymentData['payer_name'] ?? 'Unknown Payer';
                    $receiptNumber = $paymentData['receipt_number'] ?? $receiptNumber ?? 'N/A';
                    $contributionTitle = $paymentData['contribution_title'] ?? $contribution['title'] ?? 'Unknown Contribution';
                    
                    $description = "Payment added to partially paid contribution for {$payerName} - Receipt: {$receiptNumber} - Contribution: {$contributionTitle}";
                    
                    log_message('debug', "Logging payment added to partial group - Payer: {$payerName}, Receipt: {$receiptNumber}, Sequence: {$paymentSequence}");
                    
                    $result = $this->logUserActivity('create', 'payment', $paymentId, $description);
                    
                    if (!$result) {
                        log_message('error', "Failed to log payment added to partial group activity");
                    } else {
                        log_message('debug', "Successfully logged payment added to partial group activity - ID: {$paymentId}");
                    }
                } else {
                    log_message('debug', "Payment added check conditions - id: " . ($id ? 'true' : 'false') . ", wasAddedToPartialGroup: " . ($wasAddedToPartialGroup ? 'true' : 'false') . ", paymentData exists: " . ($paymentData ? 'true' : 'false') . ", paymentSequence: " . ($paymentSequence ?? 'null'));
                }
                
                // Generate QR receipt for new payments (disabled temporarily)
                // if (!$id) {
                //     try {
                //         $qrReceiptService = new QRReceiptService();
                //         $paymentData = $paymentModel->find($paymentId);
                //         
                //         // Get contribution details
                //         $contributionModel = new ContributionModel();
                //         $contribution = $contributionModel->find($paymentData['contribution_id']);
                //         $paymentData['contribution_title'] = $contribution['title'] ?? 'N/A';
                //         
                //         $qrResult = $qrReceiptService->generateQRReceipt($paymentData);
                //         
                //         if ($qrResult['success']) {
                //             $message .= ' QR receipt generated successfully.';
                //         }
                //     } catch (\Exception $e) {
                //         // Log error but don't fail the payment
                //         log_message('error', 'QR Receipt Generation Error: ' . $e->getMessage());
                //     }
                // }
                
                session()->setFlashdata('success', $message);
                
                return $this->response->setJSON([
                    'success' => true,
                    'message' => $message,
                    'reference_number' => $referenceNumber,
                    'payment_id' => $paymentId,
                    'payment' => $paymentData
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to save payment'
                ]);
            }

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get recent payments for QR receipt selection
     */
    public function recent()
    {
        try {
            $paymentModel = new PaymentModel();
            $payments = $paymentModel->select('
                payments.id,
                payments.receipt_number,
                payments.payment_date,
                payments.amount_paid,
                payments.payment_method,
                payments.payment_status,
                payments.contribution_id,
                payments.remaining_balance,
                payers.payer_id,
                payers.payer_name,
                payers.contact_number,
                payers.email_address,
                contributions.title as contribution_title,
                contributions.contribution_code
            ')
            ->join('payers', 'payers.id = payments.payer_id', 'left')
            ->join('contributions', 'contributions.id = payments.contribution_id', 'left')
            ->where('payments.deleted_at', null)
            ->orderBy('payments.id', 'DESC')
            ->orderBy('payments.payment_date', 'DESC')
            ->limit(100)
            ->findAll();

            return $this->response->setJSON([
                'success' => true,
                'payments' => $payments
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Search for existing payers
     */
    public function searchPayers()
    {
        try {
            $term = $this->request->getGet('q') ?: $this->request->getGet('term');
            
            if (empty($term) || strlen($term) < 2) {
                return $this->response->setJSON([
                    'success' => true,
                    'results' => []
                ]);
            }

            $payerModel = new \App\Models\PayerModel();
            
            // Search payers in the payers table
            $payers = $payerModel->select('
                id,
                payer_id,
                payer_name,
                contact_number,
                email_address
            ')
            ->groupStart()
                ->like('payer_name', $term)
                ->orLike('payer_id', $term)
            ->groupEnd()
            ->orderBy('payer_name', 'ASC')
            ->limit(10)
            ->findAll();

            return $this->response->setJSON([
                'success' => true,
                'results' => $payers
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Verify payment by receipt number
     */
    public function verify($receiptNumber)
    {
        try {
            $paymentModel = new PaymentModel();
            $refundModel = new \App\Models\RefundModel();
            
            // Find payment by receipt number
            $payment = $paymentModel->select('
                payments.id,
                payments.receipt_number,
                payments.payment_date,
                payments.amount_paid,
                payments.payment_method,
                payments.payment_status,
                payments.contribution_id,
                payments.payer_id,
                payers.payer_id as payer_id_number,
                payers.payer_name,
                payers.contact_number,
                payers.email_address,
                contributions.title as contribution_title
            ')
            ->join('payers', 'payers.id = payments.payer_id', 'left')
            ->join('contributions', 'contributions.id = payments.contribution_id', 'left')
            ->where('payments.receipt_number', $receiptNumber)
            ->first();

            if ($payment) {
                // Check if payment has been refunded
                $refundStatus = $paymentModel->getPaymentRefundStatus($payment['id']);
                $totalRefunded = $paymentModel->getPaymentTotalRefunded($payment['id']);
                
                // Get refund details if refunded
                $refunds = [];
                if ($refundStatus !== 'no_refund') {
                    $refunds = $refundModel
                        ->where('payment_id', $payment['id'])
                        ->where('status', 'completed')
                        ->orderBy('processed_at', 'DESC')
                        ->findAll();
                }
                
                // Add refund information to payment data
                $payment['refund_status'] = $refundStatus;
                $payment['total_refunded'] = $totalRefunded;
                $payment['is_refunded'] = ($refundStatus === 'fully_refunded' || $refundStatus === 'partially_refunded');
                
                // Get latest refund date if available
                if (!empty($refunds)) {
                    $payment['refunded_at'] = $refunds[0]['processed_at'] ?? null;
                    $payment['refunds'] = $refunds;
                }
                
                return $this->response->setJSON([
                    'success' => true,
                    'payment' => $payment
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Payment not found'
                ]);
            }

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get payments by contribution ID
     */
    public function byContribution($contributionId)
    {
        try {
            $paymentModel = new PaymentModel();
            
            // Find all payments for this contribution
            $payments = $paymentModel->select('
                payments.id,
                payments.receipt_number,
                payments.payment_date,
                payments.amount_paid,
                payments.payment_method,
                payments.payment_status,
                payments.remaining_balance,
                payments.contribution_id,
                payments.payment_sequence,
                payers.payer_id as payer_student_id,
                payers.payer_id as payer_id,
                payers.payer_name,
                payers.contact_number,
                payers.email_address,
                contributions.title as contribution_title,
                contributions.amount as contribution_amount
            ')
            ->join('payers', 'payers.id = payments.payer_id', 'left')
            ->join('contributions', 'contributions.id = payments.contribution_id', 'left')
            ->where('payments.contribution_id', $contributionId)
            ->orderBy('payments.payment_date', 'DESC')
            ->findAll();

            return $this->response->setJSON([
                'success' => true,
                'payments' => $payments,
                'count' => count($payments)
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Export contribution payments to PDF
     */
    public function exportContributionPaymentsPDF($contributionId)
    {
        // Check if user is logged in
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        try {
            $paymentModel = new PaymentModel();
            $contributionModel = new ContributionModel();
            
            // Get contribution details
            $contribution = $contributionModel->find($contributionId);
            if (!$contribution) {
                return redirect()->back()->with('error', 'Contribution not found');
            }
            
            // Find all payments for this contribution
            $payments = $paymentModel->select('
                payments.id,
                payments.receipt_number,
                payments.payment_date,
                payments.amount_paid,
                payments.payment_method,
                payments.payment_status,
                payments.remaining_balance,
                payments.contribution_id,
                payments.payment_sequence,
                payers.payer_id as payer_student_id,
                payers.payer_id as payer_id,
                payers.payer_name,
                payers.contact_number,
                payers.email_address,
                contributions.title as contribution_title,
                contributions.amount as contribution_amount
            ')
            ->join('payers', 'payers.id = payments.payer_id', 'left')
            ->join('contributions', 'contributions.id = payments.contribution_id', 'left')
            ->where('payments.contribution_id', $contributionId)
            ->orderBy('payments.payment_date', 'DESC')
            ->findAll();

            // Aggregate payments by payer and payment_sequence
            $payerMap = [];
            foreach ($payments as $payment) {
                $payerId = $payment['payer_id'];
                $sequence = $payment['payment_sequence'] ?? 1;
                $key = $payerId . '_' . $sequence; // Unique key for payer+sequence combination
                
                if (!isset($payerMap[$key])) {
                    $payerMap[$key] = [
                        'payer_id' => $payerId,
                        'payer_student_id' => $payment['payer_student_id'] ?? $payerId ?? 'N/A',
                        'payer_name' => $payment['payer_name'],
                        'payment_sequence' => $sequence,
                        'total_paid' => 0,
                        'contribution_amount' => $payment['contribution_amount'] ?? 0,
                        'status' => 'fully paid',
                        'last_payment_date' => null
                    ];
                }
                
                $payerMap[$key]['total_paid'] += floatval($payment['amount_paid']);
                
                // Track latest payment date
                if (!$payerMap[$key]['last_payment_date'] || 
                    strtotime($payment['payment_date']) > strtotime($payerMap[$key]['last_payment_date'])) {
                    $payerMap[$key]['last_payment_date'] = $payment['payment_date'];
                }
            }
            
            // Determine status and calculate remaining balance
            foreach ($payerMap as &$payerData) {
                if ($payerData['contribution_amount']) {
                    if ($payerData['total_paid'] >= $payerData['contribution_amount']) {
                        $payerData['status'] = 'COMPLETED';
                    } else {
                        $payerData['status'] = 'PARTIAL';
                    }
                } else {
                    $payerData['status'] = 'PARTIAL';
                }
                $payerData['remaining_balance'] = max(0, $payerData['contribution_amount'] - $payerData['total_paid']);
            }
            
            // Load TCPDF library (autoload is already loaded via bootstrap, but ensure Composer autoloader is available)
            if (defined('COMPOSER_PATH') && file_exists(COMPOSER_PATH)) {
                require_once COMPOSER_PATH;
            } elseif (defined('ROOTPATH')) {
                require_once ROOTPATH . 'vendor/autoload.php';
            }
            
            // Create new PDF document
            $pdf = new \TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);
            
            // Set document information
            $pdf->SetCreator('ClearPay System');
            $pdf->SetAuthor('ClearPay');
            $pdf->SetTitle('Contribution Payments - ' . $contribution['title']);
            $pdf->SetSubject('Payment Records');
            
            // Remove default header/footer
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);
            
            // Set margins
            $pdf->SetMargins(15, 15, 15);
            $pdf->SetAutoPageBreak(TRUE, 15);
            
            // Add a page
            $pdf->AddPage();
            
            // Set font to dejavusans which supports UTF-8 characters including ₱
            $pdf->SetFont('dejavusans', '', 10);
            
            // Header Section
            $pdf->SetFillColor(52, 152, 219);
            $pdf->Rect(0, 0, 297, 40, 'F');
            
            // Logo and Title
            $pdf->SetTextColor(255, 255, 255);
            $pdf->SetFont('dejavusans', 'B', 24);
            $pdf->SetXY(15, 8);
            $pdf->Cell(0, 10, 'ClearPay', 0, 1, 'L');
            
            $pdf->SetFont('dejavusans', '', 12);
            $pdf->SetXY(15, 18);
            $pdf->Cell(0, 8, 'Contribution Payment Report', 0, 1, 'L');
            
            $pdf->SetFont('dejavusans', '', 10);
            $pdf->SetXY(15, 27);
            $pdf->Cell(0, 6, date('F j, Y g:i A'), 0, 1, 'L');
            
            // Reset text color
            $pdf->SetTextColor(0, 0, 0);
            
            // Contribution Information Section
            $pdf->SetY(50);
            $pdf->SetFont('dejavusans', 'B', 14);
            $pdf->Cell(0, 8, $contribution['title'], 0, 1, 'L');
            
            $pdf->SetFont('dejavusans', '', 10);
            $pdf->Cell(0, 6, 'Contribution Amount: ₱' . number_format($contribution['amount'], 2), 0, 1, 'L');
            $pdf->Cell(0, 6, 'Total Payers: ' . count($payerMap), 0, 1, 'L');
            
            // Table header
            $pdf->SetY(75);
            $pdf->SetFillColor(232, 232, 232);
            $pdf->SetFont('dejavusans', 'B', 10);
            $pdf->Cell(35, 8, 'Payer ID', 1, 0, 'C', true);
            $pdf->Cell(55, 8, 'Payer Name', 1, 0, 'C', true);
            $pdf->Cell(30, 8, 'Group', 1, 0, 'C', true);
            $pdf->Cell(35, 8, 'Total Paid', 1, 0, 'C', true);
            $pdf->Cell(35, 8, 'Remaining', 1, 0, 'C', true);
            $pdf->Cell(30, 8, 'Status', 1, 0, 'C', true);
            $pdf->Cell(40, 8, 'Last Payment', 1, 1, 'C', true);
            
            // Table body
            $pdf->SetFont('dejavusans', '', 9);
            foreach ($payerMap as $payerData) {
                // Format last payment date
                $lastPaymentDate = 'N/A';
                if ($payerData['last_payment_date']) {
                    $date = new \DateTime($payerData['last_payment_date']);
                    $lastPaymentDate = $date->format('M j, Y');
                }
                
                // Status badge color
                if ($payerData['status'] === 'COMPLETED') {
                    $pdf->SetFillColor(52, 152, 219);
                } else {
                    $pdf->SetFillColor(241, 196, 15);
                }
                
                $pdf->SetTextColor(0, 0, 0);
                $pdf->Cell(35, 7, $payerData['payer_student_id'], 1, 0, 'L');
                $pdf->Cell(55, 7, $payerData['payer_name'], 1, 0, 'L');
                
                // Payment Group
                $pdf->Cell(30, 7, 'Group ' . $payerData['payment_sequence'], 1, 0, 'C');
                
                // Reset fill color
                $pdf->SetFillColor(255, 255, 255);
                
                $pdf->Cell(35, 7, '₱' . number_format($payerData['total_paid'], 2), 1, 0, 'R');
                
                // Remaining balance color
                if ($payerData['remaining_balance'] > 0) {
                    $pdf->SetTextColor(192, 57, 43);
                } else {
                    $pdf->SetTextColor(46, 204, 113);
                }
                $pdf->Cell(35, 7, '₱' . number_format($payerData['remaining_balance'], 2), 1, 0, 'R');
                
                // Reset text color
                $pdf->SetTextColor(0, 0, 0);
                $pdf->SetFont('dejavusans', 'B', 9);
                $pdf->Cell(30, 7, $payerData['status'], 1, 0, 'C');
                $pdf->SetFont('dejavusans', '', 9);
                $pdf->Cell(40, 7, $lastPaymentDate, 1, 1, 'C');
            }
            
            // Summary footer
            $pdf->SetY($pdf->GetY() + 5);
            $pdf->SetFont('dejavusans', 'B', 10);
            $pdf->SetFillColor(52, 152, 219);
            $pdf->SetTextColor(255, 255, 255);
            $pdf->Cell(0, 8, 'SUMMARY', 1, 1, 'C', true);
            
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetFillColor(255, 255, 255);
            $totalPaid = array_sum(array_column($payerMap, 'total_paid'));
            $totalPayers = count($payerMap);
            $completedPayers = count(array_filter($payerMap, function($p) { return $p['status'] === 'COMPLETED'; }));
            $partialPayers = $totalPayers - $completedPayers;
            
            $pdf->SetFont('dejavusans', '', 10);
            $pdf->Cell(93, 7, 'Total Amount Collected:', 1, 0, 'L', true);
            $pdf->Cell(93, 7, '₱' . number_format($totalPaid, 2), 1, 1, 'R', true);
            
            $pdf->Cell(93, 7, 'Total Payers:', 1, 0, 'L', true);
            $pdf->Cell(93, 7, $totalPayers, 1, 1, 'R', true);
            
            $pdf->Cell(93, 7, 'Completed Payers:', 1, 0, 'L', true);
            $pdf->SetTextColor(46, 204, 113);
            $pdf->SetFont('dejavusans', 'B', 10);
            $pdf->Cell(93, 7, $completedPayers, 1, 1, 'R', true);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetFont('dejavusans', '', 10);
            
            $pdf->Cell(93, 7, 'Partial Payers:', 1, 0, 'L', true);
            $pdf->SetTextColor(192, 57, 43);
            $pdf->SetFont('dejavusans', 'B', 10);
            $pdf->Cell(93, 7, $partialPayers, 1, 1, 'R', true);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetFont('dejavusans', '', 10);
            
            $totalRemaining = array_sum(array_column($payerMap, 'remaining_balance'));
            $pdf->Cell(93, 7, 'Total Remaining:', 1, 0, 'L', true);
            $pdf->SetTextColor(192, 57, 43);
            $pdf->SetFont('dejavusans', 'B', 10);
            $pdf->Cell(93, 7, '₱' . number_format($totalRemaining, 2), 1, 1, 'R', true);
            $pdf->SetTextColor(0, 0, 0);
            
            // Output PDF
            $filename = 'Contribution_' . preg_replace('/[^a-z0-9]/i', '_', $contribution['title']) . '_' . date('Y-m-d_H-i-s') . '.pdf';
            $pdf->Output($filename, 'D');
            
        } catch (\Exception $e) {
            log_message('error', 'Error exporting contribution payments PDF: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error generating PDF: ' . $e->getMessage());
        }
    }

    /**
     * Add payment to a partial payment
     */
    public function addToPartial()
    {
        try {
            $json = $this->request->getJSON(true);
            
            // Debug logging
            log_message('info', 'Add to Partial - Received data: ' . print_r($json, true));
            
            // Validate required fields
            if (empty($json['original_payment_id']) || empty($json['contribution_id']) || empty($json['amount_paid'])) {
                log_message('error', 'Validation failed - Missing fields: ' . print_r([
                    'original_payment_id' => $json['original_payment_id'] ?? 'MISSING',
                    'contribution_id' => $json['contribution_id'] ?? 'MISSING',
                    'amount_paid' => $json['amount_paid'] ?? 'MISSING'
                ], true));
                
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Required fields missing: ' . json_encode([
                        'original_payment_id' => $json['original_payment_id'] ?? 'missing',
                        'contribution_id' => $json['contribution_id'] ?? 'missing',
                        'amount_paid' => $json['amount_paid'] ?? 'missing'
                    ])
                ]);
            }

            $paymentModel = new PaymentModel();
            
            // Get the original payment
            $originalPayment = $paymentModel->find($json['original_payment_id']);
            if (!$originalPayment) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Original payment not found'
                ]);
            }

            // Check if contribution is inactive - prevent adding payments to inactive contributions
            $contributionModel = new \App\Models\ContributionModel();
            $contribution = $contributionModel->find($json['contribution_id']);
            
            if (!$contribution) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Contribution not found'
                ]);
            }
            
            if ($contribution['status'] === 'inactive') {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Cannot add payment to an inactive contribution. This contribution is no longer active.'
                ]);
            }

            // Calculate new remaining balance
            $newPaymentAmount = (float) $json['amount_paid'];
            $currentRemaining = (float) ($originalPayment['remaining_balance'] ?? 0);
            $newRemaining = max(0, $currentRemaining - $newPaymentAmount);

            // Generate reference number and receipt number
            $referenceNumber = 'REF-' . date('Ymd') . '-' . strtoupper(uniqid());
            $receiptNumber = 'RCPT-' . date('Ymd') . '-' . str_pad(uniqid(), 8, '0', STR_PAD_LEFT);

            // Get the correct payer_id from the original payment
            $payerId = $originalPayment['payer_id']; // This is the foreign key to payers table

            // Create new payment record
            $paymentData = [
                'payer_id' => (int) $payerId,
                'contribution_id' => (int) $json['contribution_id'],
                'amount_paid' => $newPaymentAmount,
                'payment_method' => $json['payment_method'] ?? 'cash',
                'payment_status' => $newRemaining <= 0.01 ? 'fully paid' : 'partial',
                'is_partial_payment' => $newRemaining > 0 ? true : false,
                'remaining_balance' => $newRemaining,
                'parent_payment_id' => $json['original_payment_id'],
                'payment_sequence' => 1,
                'reference_number' => $referenceNumber,
                'receipt_number' => $receiptNumber,
                'recorded_by' => session()->get('user-id') ?: session()->get('user_id') ?: null,
                'payment_date' => $json['payment_date'] ?? date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            // Insert new payment
            $newPaymentId = $paymentModel->insert($paymentData);

            if ($newPaymentId) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Payment recorded successfully',
                    'payment_id' => $newPaymentId,
                    'new_remaining_balance' => $newRemaining
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to record payment'
                ]);
            }

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Update an existing payment
     */
    public function update($paymentId)
    {
        try {
            $paymentModel = new PaymentModel();
            
            // Check if payment exists
            $payment = $paymentModel->find($paymentId);
            if (!$payment) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Payment not found'
                ]);
            }

            // Check if request has JSON content
            $jsonData = null;
            $contentType = $this->request->getHeaderLine('Content-Type');
            if (strpos($contentType, 'application/json') !== false) {
                $jsonData = $this->request->getJSON(true);
            }
            
            // Get data from JSON or POST
            if ($jsonData) {
                $contributionId = $jsonData['contribution_id'] ?? null;
                $amountPaid = $jsonData['amount_paid'] ?? null;
                $paymentMethod = $jsonData['payment_method'] ?? null;
                $paymentDate = $jsonData['payment_date'] ?? null;
                $isPartial = $jsonData['is_partial_payment'] ?? 0;
                $remainingBalance = $jsonData['remaining_balance'] ?? 0;
            } else {
                $contributionId = $this->request->getPost('contribution_id');
                $amountPaid = $this->request->getPost('amount_paid');
                $paymentMethod = $this->request->getPost('payment_method');
                $paymentDate = $this->request->getPost('payment_date');
                $isPartial = $this->request->getPost('is_partial_payment');
                $remainingBalance = $this->request->getPost('remaining_balance', 0);
            }

            // Validate required fields
            if (empty($contributionId) || empty($amountPaid) || empty($paymentMethod) || empty($paymentDate)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Validation failed: Required fields are missing'
                ]);
            }

            // Get contribution details and payment sequence for correct calculation
            $contributionModel = new \App\Models\ContributionModel();
            $contribution = $contributionModel->find($contributionId);
            $contributionAmount = $contribution ? (float) $contribution['amount'] : 0;
            
            // Get the payment's sequence to calculate based on the group total
            $paymentSequence = $payment['payment_sequence'] ?? 1;
            $payerId = $payment['payer_id'];
            
            // Get all payments in this sequence group (excluding the one being edited)
            $groupPayments = $paymentModel
                ->where('payer_id', $payerId)
                ->where('contribution_id', $contributionId)
                ->where('payment_sequence', $paymentSequence)
                ->where('payments.id !=', $paymentId) // Exclude the payment being edited
                ->where('deleted_at', null)
                ->findAll();
            
            // Calculate total paid in the group (other payments + new amount for this payment)
            $otherPaymentsTotal = array_sum(array_column($groupPayments, 'amount_paid'));
            $newGroupTotal = $otherPaymentsTotal + (float) $amountPaid;
            
            // Calculate remaining balance based on group total, not individual payment
            $calculatedRemainingBalance = max(0, $contributionAmount - $newGroupTotal);
            
            // Update payment data
            $updateData = [
                'contribution_id' => (int) $contributionId,
                'amount_paid' => (float) $amountPaid,
                'payment_method' => (string) $paymentMethod,
                'payment_date' => $paymentDate,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            // Determine payment status based on calculated remaining balance
            if ($calculatedRemainingBalance > 0.01) {
                // Still partial payment
                $updateData['payment_status'] = 'partial';
                $updateData['is_partial_payment'] = true;
                $updateData['remaining_balance'] = $calculatedRemainingBalance;
            } else {
                // Fully paid
                $updateData['payment_status'] = 'fully paid';
                $updateData['is_partial_payment'] = false;
                $updateData['remaining_balance'] = 0;
            }

            // Store old payment data for logging
            $oldPaymentData = $payment;
            
            // Update the payment (skip validation to avoid issues)
            $updated = $paymentModel->skipValidation()->update($paymentId, $updateData);

            if ($updated) {
                // Fetch updated payment with payer and contribution details for logging
                $updatedPayment = $paymentModel->select('
                    payments.id,
                    payments.receipt_number,
                    payments.amount_paid,
                    payments.payment_method,
                    payments.payment_status,
                    payments.payment_date,
                    payments.remaining_balance,
                    payers.payer_name,
                    payers.payer_id as payer_id_number,
                    contributions.title as contribution_title
                ')
                ->join('payers', 'payers.id = payments.payer_id', 'left')
                ->join('contributions', 'contributions.id = payments.contribution_id', 'left')
                ->find($paymentId);
                
                // Log activity to user_activities table
                if ($updatedPayment) {
                    $payerName = $updatedPayment['payer_name'] ?? 'Unknown Payer';
                    $receiptNumber = $updatedPayment['receipt_number'] ?? 'N/A';
                    
                    // Build description with change details
                    $changes = [];
                    
                    // Compare amount_paid (using floating point comparison)
                    $oldAmount = isset($oldPaymentData['amount_paid']) ? (float) $oldPaymentData['amount_paid'] : 0;
                    $newAmount = isset($updatedPayment['amount_paid']) ? (float) $updatedPayment['amount_paid'] : 0;
                    if (abs($oldAmount - $newAmount) > 0.01) {
                        $changes[] = "Amount: ₱" . number_format($oldAmount, 2) . " → ₱" . number_format($newAmount, 2);
                    }
                    
                    // Compare payment_method
                    $oldMethod = isset($oldPaymentData['payment_method']) ? trim((string) $oldPaymentData['payment_method']) : '';
                    $newMethod = isset($updatedPayment['payment_method']) ? trim((string) $updatedPayment['payment_method']) : '';
                    if ($oldMethod !== $newMethod && !empty($oldMethod) && !empty($newMethod)) {
                        $changes[] = "Method: {$oldMethod} → {$newMethod}";
                    }
                    
                    // Compare payment_status
                    $oldStatus = isset($oldPaymentData['payment_status']) ? trim((string) $oldPaymentData['payment_status']) : '';
                    $newStatus = isset($updatedPayment['payment_status']) ? trim((string) $updatedPayment['payment_status']) : '';
                    if ($oldStatus !== $newStatus && !empty($oldStatus)) {
                        $changes[] = "Status: {$oldStatus} → {$newStatus}";
                    }
                    
                    // Compare remaining_balance
                    $oldRemaining = isset($oldPaymentData['remaining_balance']) ? (float) $oldPaymentData['remaining_balance'] : 0;
                    $newRemaining = isset($updatedPayment['remaining_balance']) ? (float) $updatedPayment['remaining_balance'] : 0;
                    if (abs($oldRemaining - $newRemaining) > 0.01) {
                        $changes[] = "Remaining: ₱" . number_format($oldRemaining, 2) . " → ₱" . number_format($newRemaining, 2);
                    }
                    
                    // Debug logging
                    log_message('debug', "Payment edit logging - Old: " . json_encode($oldPaymentData) . ", New: " . json_encode($updatedPayment) . ", Changes: " . json_encode($changes));
                    
                    $description = "Payment edited for {$payerName} - Receipt: {$receiptNumber}";
                    if (!empty($changes)) {
                        $description .= " (" . implode(', ', $changes) . ")";
                    } else {
                        // If no specific changes detected, log a general edit message
                        $description .= " (Payment details updated)";
                    }
                    
                    log_message('debug', "Logging payment edit activity: {$description}");
                    $logResult = $this->logUserActivity('update', 'payment', $paymentId, $description);
                    if (!$logResult) {
                        log_message('error', "Failed to log payment edit activity");
                    }
                }
                
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Payment updated successfully'
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to update payment'
                ]);
            }

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get payment details for QR receipt
     */
    public function getDetails($paymentId)
    {
        try {
            // Check if user is logged in
            if (!session()->get('isLoggedIn')) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Unauthorized'
                ]);
            }

            $paymentModel = new PaymentModel();
            
            // Get payment with all related data
            $payment = $paymentModel->select('
                payments.id,
                payments.receipt_number,
                payments.payment_date,
                payments.amount_paid,
                payments.payment_method,
                payments.payment_status,
                payments.contribution_id,
                payments.payer_id,
                payers.payer_id as payer_id_number,
                payers.payer_name,
                payers.contact_number,
                payers.email_address,
                contributions.title as contribution_title,
                contributions.amount as contribution_amount,
                contributions.contribution_code
            ')
            ->join('payers', 'payers.id = payments.payer_id', 'left')
            ->join('contributions', 'contributions.id = payments.contribution_id', 'left')
            ->find($paymentId);
            
            // Calculate remaining balance based on payment sequence group total
            if ($payment) {
                $paymentSequence = $payment['payment_sequence'] ?? 1;
                $payerId = $payment['payer_id'];
                $contributionId = $payment['contribution_id'];
                $contributionAmount = (float) ($payment['contribution_amount'] ?? 0);
                
                // Get all payments in this sequence group
                $groupPayments = $paymentModel
                    ->where('payer_id', $payerId)
                    ->where('contribution_id', $contributionId)
                    ->where('payment_sequence', $paymentSequence)
                    ->where('deleted_at', null)
                    ->findAll();
                
                // Calculate total paid in the group
                $groupTotalPaid = array_sum(array_column($groupPayments, 'amount_paid'));
                
                // Calculate remaining balance for the group
                $groupRemainingBalance = max(0, $contributionAmount - $groupTotalPaid);
                
                // Add this to the payment data for frontend calculation
                $payment['payment_sequence'] = $paymentSequence;
                $payment['group_total_paid'] = $groupTotalPaid;
                $payment['group_remaining_balance'] = $groupRemainingBalance;
                $payment['other_payments_count'] = count($groupPayments) - 1; // Excluding current payment
                
                return $this->response->setJSON([
                    'success' => true,
                    'payment' => $payment
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Payment not found'
                ]);
            }

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Delete a payment (soft delete)
     * After deletion, computes and returns the updated status
     */
    public function delete($paymentId)
    {
        try {
            // Check if user is logged in
            if (!session()->get('isLoggedIn')) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Unauthorized'
                ]);
            }

            $paymentModel = new PaymentModel();
            
            // Check if payment exists (including soft-deleted)
            $payment = $paymentModel->withDeleted()->find($paymentId);
            if (!$payment) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Payment not found'
                ]);
            }

            // Soft delete the payment (sets deleted_at timestamp)
            $deleted = $paymentModel->delete($paymentId);

            if ($deleted) {
                // Get the computed status after deletion
                $payerId = $payment['payer_id'];
                $contributionId = $payment['contribution_id'] ?? null;
                $computedStatus = $paymentModel->getPaymentStatus($payerId, $contributionId);
                
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Payment deleted successfully',
                    'updated_status' => $computedStatus
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to delete payment'
                ]);
            }

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Convert individual payments to grouped format
     */
    private function convertIndividualToGrouped($individualPayments)
    {
        $grouped = [];
        
        foreach ($individualPayments as $payment) {
            $key = $payment['payer_id'] . '_' . $payment['contribution_id'];
            
            if (!isset($grouped[$key])) {
                $grouped[$key] = [
                    'payer_id' => $payment['payer_id'],
                    'contribution_id' => $payment['contribution_id'],
                    'payer_name' => $payment['payer_name'],
                    'payer_student_id' => $payment['payer_student_id'] ?? $payment['payer_id'],
                    'contact_number' => $payment['contact_number'] ?? '',
                    'email_address' => $payment['email_address'] ?? '',
                    'profile_picture' => $this->normalizeProfilePicturePath(
                        $payment['profile_picture'] ?? null, 
                        $payment['payer_id'] ?? null, 
                        null, 
                        'payer'
                    ) ?? '',
                    'contribution_title' => $payment['contribution_title'] ?? 'N/A',
                    'contribution_description' => $payment['contribution_description'] ?? '',
                    'contribution_amount' => $payment['contribution_amount'] ?? $payment['amount_paid'] ?? 0,
                    'total_paid' => 0,
                    'payment_count' => 0,
                    'last_payment_date' => null,
                    'first_payment_date' => null,
                    'computed_status' => 'unpaid',
                    'remaining_balance' => 0
                ];
            }
            
            $grouped[$key]['total_paid'] += $payment['amount_paid'];
            $grouped[$key]['payment_count']++;
            
            if (!$grouped[$key]['last_payment_date'] || $payment['payment_date'] > $grouped[$key]['last_payment_date']) {
                $grouped[$key]['last_payment_date'] = $payment['payment_date'];
            }
            
            if (!$grouped[$key]['first_payment_date'] || $payment['payment_date'] < $grouped[$key]['first_payment_date']) {
                $grouped[$key]['first_payment_date'] = $payment['payment_date'];
            }
        }
        
        // Calculate status and remaining balance
        foreach ($grouped as &$group) {
            if ($group['total_paid'] >= $group['contribution_amount']) {
                $group['computed_status'] = 'fully paid';
            } elseif ($group['total_paid'] > 0) {
                $group['computed_status'] = 'partial';
            } else {
                $group['computed_status'] = 'unpaid';
            }
            
            $group['remaining_balance'] = $group['contribution_amount'] - $group['total_paid'];
        }
        
        return array_values($grouped);
    }

    /**
     * Check for duplicate payments and validate payment rules
     */
    private function checkForDuplicatePayments($payerId, $contributionId)
    {
        $paymentModel = new PaymentModel();
        $contributionModel = new \App\Models\ContributionModel();
        
        // Get contribution details
        $contribution = $contributionModel->find($contributionId);
        if (!$contribution) {
            return [
                'allowed' => false,
                'message' => 'Contribution not found'
            ];
        }
        
        // Get all existing payments for this payer and contribution
        $existingPayments = $paymentModel
            ->where('payer_id', $payerId)
            ->where('contribution_id', $contributionId)
            ->where('deleted_at', null)
            ->findAll();
        
        if (empty($existingPayments)) {
            // No existing payments, allow new payment
            return ['allowed' => true];
        }
        
        // Group payments by payment_sequence to check each group separately
        $paymentGroups = [];
        foreach ($existingPayments as $payment) {
            $sequence = $payment['payment_sequence'] ?? 1;
            if (!isset($paymentGroups[$sequence])) {
                $paymentGroups[$sequence] = [];
            }
            $paymentGroups[$sequence][] = $payment;
        }
        
        // Check each payment group separately
        foreach ($paymentGroups as $sequence => $groupPayments) {
            $groupTotalPaid = array_sum(array_column($groupPayments, 'amount_paid'));
            $contributionAmount = $contribution['amount'];
            
            // Check if this group is partial (not fully paid)
            if ($groupTotalPaid < $contributionAmount) {
                // This is an active partial group - allow completion
                return [
                    'allowed' => true,
                    'message' => "Partial payment detected in group {$sequence}. You can complete this contribution.",
                    'is_partial_payment' => true,
                    'existing_payments' => $groupPayments,
                    'remaining_amount' => $contributionAmount - $groupTotalPaid
                ];
            }
        }
        
        // If we get here, all groups are fully paid - show confirmation for new group
        $totalPaid = array_sum(array_column($existingPayments, 'amount_paid'));
        return [
            'allowed' => false,
            'message' => "⚠️ Contribution Already Fully Paid\n\nThis payer already has a fully paid contribution group for '" . $contribution['title'] . "' (₱" . number_format($totalPaid, 2) . " total).",
            'requires_confirmation' => true,
            'existing_payments' => $existingPayments
        ];
    }
    
    /**
     * Delete an entire payment group (all payments for a payer and contribution)
     */
    public function deletePaymentGroup()
    {
        if (!$this->request->isAJAX() && $this->request->getMethod() !== 'POST') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid request method'
            ]);
        }

        $payerId = $this->request->getPost('payer_id');
        $contributionId = $this->request->getPost('contribution_id');
        $paymentSequence = $this->request->getPost('payment_sequence') ?? 1;
        
        if (!$payerId || !$contributionId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Payer ID and Contribution ID are required'
            ]);
        }

        try {
            $paymentModel = new PaymentModel();
            $payerModel = new \App\Models\PayerModel();
            $contributionModel = new \App\Models\ContributionModel();
            
            // Get payer and contribution details for logging
            $payer = $payerModel->find($payerId);
            $contribution = $contributionModel->find($contributionId);
            
            if (!$payer || !$contribution) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Payer or contribution not found'
                ]);
            }

            // Get all payments for this payer and contribution (and sequence if specified)
            $payments = $paymentModel
                ->where('payer_id', $payerId)
                ->where('contribution_id', $contributionId)
                ->where('deleted_at', null);
            
            // Handle payment sequence filtering - if sequence is 1, include both NULL and 1
            if ($paymentSequence == 1) {
                $payments->groupStart()
                    ->where('payment_sequence', 1)
                    ->orWhere('payment_sequence IS NULL')
                    ->groupEnd();
            } else {
                $payments->where('payment_sequence', $paymentSequence);
            }
            
            $payments = $payments->findAll();

            // Debug logging
            log_message('info', 'Delete Payment Group Debug:');
            log_message('info', 'Payer ID: ' . $payerId);
            log_message('info', 'Contribution ID: ' . $contributionId);
            log_message('info', 'Payment Sequence: ' . $paymentSequence);
            log_message('info', 'Found ' . count($payments) . ' payments');
            
            if (!empty($payments)) {
                foreach ($payments as $payment) {
                    log_message('info', 'Payment ID: ' . $payment['id'] . ', Sequence: ' . ($payment['payment_sequence'] ?? 'NULL'));
                }
            }

            if (empty($payments)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'No payments found for this group'
                ]);
            }

            // Soft delete all payments in the group
            $deletedCount = 0;
            foreach ($payments as $payment) {
                if ($paymentModel->delete($payment['id'])) {
                    $deletedCount++;
                }
            }

            if ($deletedCount > 0) {
                // Log the deletion activity
                $activityLogger = new \App\Services\ActivityLogger();
                $activityLogger->logActivity('delete', 'payment_group', $payerId . '_' . $contributionId . '_' . $paymentSequence, 
                    "Payment group deleted: {$deletedCount} payments for " . $payer['payer_name'] . " - " . $contribution['title'] . 
                    ($paymentSequence > 1 ? " (Group {$paymentSequence})" : ""));

                return $this->response->setJSON([
                    'success' => true,
                    'message' => "Successfully deleted {$deletedCount} payment(s) from the group",
                    'deleted_count' => $deletedCount
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to delete any payments'
                ]);
            }

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Delete a payment record
     */
    public function deletePayment()
    {
        if (!$this->request->isAJAX() && $this->request->getMethod() !== 'POST') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid request method'
            ]);
        }

        $paymentId = $this->request->getPost('payment_id');
        
        if (!$paymentId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Payment ID is required'
            ]);
        }

        try {
            $paymentModel = new PaymentModel();
            
            // Get payment details before deletion for logging
            $payment = $paymentModel->select('
                payments.*,
                payers.payer_name,
                contributions.title as contribution_title
            ')
            ->join('payers', 'payers.id = payments.payer_id', 'left')
            ->join('contributions', 'contributions.id = payments.contribution_id', 'left')
            ->where('payments.id', $paymentId)
            ->first();

            if (!$payment) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Payment not found'
                ]);
            }

            // Soft delete the payment
            $result = $paymentModel->delete($paymentId);

            if ($result) {
                // Log the deletion activity
                $activityLogger = new \App\Services\ActivityLogger();
                $activityLogger->logActivity('delete', 'payment', $paymentId, 
                    "Payment deleted: ₱" . number_format($payment['amount_paid'], 2) . 
                    " for " . $payment['payer_name'] . " - " . $payment['contribution_title']);

                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Payment deleted successfully'
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to delete payment'
                ]);
            }

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get other unpaid contributions for a payer
     */
    private function getOtherUnpaidContributions($payerId, $excludeContributionId)
    {
        $paymentModel = new PaymentModel();
        $contributionModel = new \App\Models\ContributionModel();
        
        // Get only active contributions except the one being paid (if specified)
        // We don't want to show warnings for inactive contributions
        $builder = $contributionModel->where('status', 'active');
        if ($excludeContributionId) {
            $builder->where('id !=', $excludeContributionId);
        }
        $allContributions = $builder->findAll();
        
        $unpaidContributions = [];
        
        foreach ($allContributions as $contribution) {
            // Get payments for this contribution
            $payments = $paymentModel
                ->where('payer_id', $payerId)
                ->where('contribution_id', $contribution['id'])
                ->where('deleted_at', null)
                ->findAll();
            
            if (!empty($payments)) {
                // Only show contributions that have been started but not completed
                $totalPaid = array_sum(array_column($payments, 'amount_paid'));
                if ($totalPaid < $contribution['amount']) {
                    $unpaidContributions[] = [
                        'id' => $contribution['id'],
                        'title' => $contribution['title'],
                        'amount' => $contribution['amount'],
                        'remaining_amount' => $contribution['amount'] - $totalPaid,
                        'total_paid' => $totalPaid
                    ];
                }
            }
            // If no payments exist, don't show it as "unpaid" - it's just not started yet
        }
        
        return $unpaidContributions;
    }

    /**
     * Save payment with confirmation (for duplicate payments)
     */
    public function saveWithConfirmation()
    {
        // Debug: Log confirmation request
        log_message('info', 'Save with confirmation request data: ' . json_encode($this->request->getPost()));
        
        // Add a flag to bypass duplicate check
        $this->request->setGlobal('post', array_merge($this->request->getPost(), ['bypass_duplicate_check' => true]));
        
        // Call the regular save method
        return $this->save();
    }

    /**
     * Check unpaid contributions for a payer
     */
    public function checkUnpaidContributions()
    {
        if (!$this->request->isAJAX() && $this->request->getMethod() !== 'GET') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid request method'
            ]);
        }

        $payerId = $this->request->getGet('payer_id');

        if (!$payerId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Payer ID is required'
            ]);
        }

        try {
            $unpaidContributions = $this->getOtherUnpaidContributions($payerId, null);
            
            return $this->response->setJSON([
                'success' => true,
                'unpaid_contributions' => $unpaidContributions
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Check fully paid contributions for a payer
     */
    public function checkFullyPaidContributions()
    {
        if (!$this->request->isAJAX() && $this->request->getMethod() !== 'GET') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid request method'
            ]);
        }

        $payerId = $this->request->getGet('payer_id');

        if (!$payerId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Payer ID is required'
            ]);
        }

        try {
            $fullyPaidContributions = $this->getFullyPaidContributions($payerId);
            
            return $this->response->setJSON([
                'success' => true,
                'fully_paid_contributions' => $fullyPaidContributions
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Check status of a specific contribution for a payer
     */
    public function checkContributionStatus()
    {
        if ($this->request->getMethod() !== 'GET') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid request method'
            ]);
        }

        $payerId = $this->request->getGet('payer_id');
        $contributionId = $this->request->getGet('contribution_id');

        if (!$payerId || !$contributionId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Payer ID and Contribution ID are required'
            ]);
        }

        try {
            $paymentModel = new PaymentModel();
            $contributionModel = new \App\Models\ContributionModel();
            
            // Get contribution details
            $contribution = $contributionModel->find($contributionId);
            if (!$contribution) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Contribution not found'
                ]);
            }
            
            // Get all payments for this payer and contribution
            $payments = $paymentModel
                ->where('payer_id', $payerId)
                ->where('contribution_id', $contributionId)
                ->where('deleted_at', null)
                ->findAll();
            
            if (empty($payments)) {
                // No payments exist for this contribution
                return $this->response->setJSON([
                    'success' => true,
                    'status' => 'none',
                    'message' => 'No payments found'
                ]);
            }
            
            // Group payments by payment_sequence
            $paymentGroups = [];
            foreach ($payments as $payment) {
                $sequence = $payment['payment_sequence'] ?? 1;
                if (!isset($paymentGroups[$sequence])) {
                    $paymentGroups[$sequence] = [];
                }
                $paymentGroups[$sequence][] = $payment;
            }
            
            // Check each payment group
            foreach ($paymentGroups as $sequence => $groupPayments) {
                $groupTotalPaid = array_sum(array_column($groupPayments, 'amount_paid'));
                
                if ($groupTotalPaid < $contribution['amount']) {
                    // This group is unpaid/partial
                    return $this->response->setJSON([
                        'success' => true,
                        'status' => 'unpaid',
                        'remaining_amount' => $contribution['amount'] - $groupTotalPaid,
                        'total_paid' => $groupTotalPaid,
                        'payment_sequence' => $sequence
                    ]);
                }
            }
            
            // If we get here, all groups are fully paid
            $totalPaid = array_sum(array_column($payments, 'amount_paid'));
            return $this->response->setJSON([
                'success' => true,
                'status' => 'fully_paid',
                'total_paid' => $totalPaid,
                'message' => 'Contribution is fully paid'
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get fully paid contributions for a payer
     */
    private function getFullyPaidContributions($payerId)
    {
        $paymentModel = new PaymentModel();
        $contributionModel = new \App\Models\ContributionModel();
        
        // Get all contributions
        $allContributions = $contributionModel->where('status', 'active')->findAll();
        $fullyPaidContributions = [];
        
        foreach ($allContributions as $contribution) {
            // Get all payments for this payer and contribution
            $payments = $paymentModel
                ->where('payer_id', $payerId)
                ->where('contribution_id', $contribution['id'])
                ->where('deleted_at', null)
                ->findAll();
            
            if (!empty($payments)) {
                // Group payments by payment_sequence
                $paymentGroups = [];
                foreach ($payments as $payment) {
                    $sequence = $payment['payment_sequence'] ?? 1;
                    if (!isset($paymentGroups[$sequence])) {
                        $paymentGroups[$sequence] = [];
                    }
                    $paymentGroups[$sequence][] = $payment;
                }
                
                // Check if any group is fully paid
                foreach ($paymentGroups as $sequence => $groupPayments) {
                    $groupTotalPaid = array_sum(array_column($groupPayments, 'amount_paid'));
                    if ($groupTotalPaid >= $contribution['amount']) {
                        $fullyPaidContributions[] = [
                            'id' => $contribution['id'],
                            'title' => $contribution['title'],
                            'amount' => $contribution['amount'],
                            'total_paid' => $groupTotalPaid,
                            'payment_sequence' => $sequence
                        ];
                        break; // Found a fully paid group for this contribution
                    }
                }
            }
        }
        
        return $fullyPaidContributions;
    }

    /**
     * Get payment details for QR receipt
     */
    public function getPaymentDetails()
    {
        // Allow both AJAX and regular GET requests
        if ($this->request->getMethod() !== 'GET') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid request method'
            ]);
        }

        $paymentId = $this->request->getGet('payment_id');

        if (!$paymentId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Payment ID is required'
            ]);
        }

        try {
            $paymentModel = new PaymentModel();
            
            $payment = $paymentModel->select('
                payments.*,
                payers.payer_name,
                payers.payer_id as payer_student_id,
                payers.contact_number,
                payers.email_address,
                payers.profile_picture,
                contributions.title as contribution_title,
                contributions.description as contribution_description,
                contributions.amount as contribution_amount,
                contributions.contribution_code,
                users.username as recorded_by_name
            ')
            ->join('payers', 'payers.id = payments.payer_id', 'left')
            ->join('contributions', 'contributions.id = payments.contribution_id', 'left')
            ->join('users', 'users.id = payments.recorded_by', 'left')
            ->where('payments.id', $paymentId)
            ->where('payments.deleted_at', null)
            ->first();
            
            if (!$payment) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Payment not found'
                ]);
            }
            
            // Store the database payer_id for queries
            $payerDbId = $payment['payer_id'];
            
            // Map payer_student_id to payer_id for frontend compatibility (student ID, not DB ID)
            if (isset($payment['payer_student_id'])) {
                $payment['payer_id'] = $payment['payer_student_id'];
            }
            
            // Calculate computed status based on total paid vs contribution amount
            if (!empty($payment['contribution_amount'])) {
                $contributionId = $payment['contribution_id'];
                
                // Get all payments for this payer and contribution using database ID
                $allPayments = $paymentModel
                    ->where('payer_id', $payerDbId)
                    ->where('contribution_id', $contributionId)
                    ->where('deleted_at', null)
                    ->findAll();
                
                $totalPaid = array_sum(array_column($allPayments, 'amount_paid'));
                $contributionAmount = (float) $payment['contribution_amount'];
                
                if ($totalPaid >= $contributionAmount) {
                    $payment['computed_status'] = 'fully paid';
                    $payment['remaining_balance'] = 0;
                } elseif ($totalPaid > 0) {
                    $payment['computed_status'] = 'partial';
                    $payment['remaining_balance'] = $contributionAmount - $totalPaid;
                } else {
                    $payment['computed_status'] = 'unpaid';
                    $payment['remaining_balance'] = $contributionAmount;
                }
            }

            // Log success for debugging
            log_message('info', 'getPaymentDetails success for payment_id: ' . $paymentId);
            
            return $this->response->setJSON([
                'success' => true,
                'payment' => $payment
            ]);

        } catch (\Exception $e) {
            log_message('error', 'getPaymentDetails Exception: ' . $e->getMessage());
            log_message('error', 'getPaymentDetails Trace: ' . $e->getTraceAsString());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        } catch (\Error $e) {
            log_message('error', 'getPaymentDetails Fatal Error: ' . $e->getMessage());
            log_message('error', 'getPaymentDetails Trace: ' . $e->getTraceAsString());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Fatal Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get next payment sequence for duplicate payments
     */
    private function getNextPaymentSequence($payerId, $contributionId)
    {
        $paymentModel = new PaymentModel();
        
        // Get the highest sequence number for this payer and contribution
        $maxSequence = $paymentModel
            ->select('MAX(payment_sequence) as max_seq')
            ->where('payer_id', $payerId)
            ->where('contribution_id', $contributionId)
            ->where('deleted_at', null)
            ->first();
        
        return ($maxSequence['max_seq'] ?? 0) + 1;
    }
    
    /**
     * Consolidate multiple partial payment groups into one
     * This method helps clean up existing data where there might be multiple partial groups
     */
    private function consolidatePartialGroups($payerId, $contributionId)
    {
        $paymentModel = new PaymentModel();
        $contributionModel = new \App\Models\ContributionModel();
        
        // Get all existing payments for this payer and contribution
        $existingPayments = $paymentModel
            ->where('payer_id', $payerId)
            ->where('contribution_id', $contributionId)
            ->where('deleted_at', null)
            ->findAll();
        
        if (empty($existingPayments)) {
            return;
        }
        
        // Get contribution amount
        $contribution = $contributionModel->find($contributionId);
        $contributionAmount = $contribution ? $contribution['amount'] : 0;
        
        // Group payments by sequence
        $paymentGroups = [];
        foreach ($existingPayments as $payment) {
            $sequence = $payment['payment_sequence'] ?? 1;
            if (!isset($paymentGroups[$sequence])) {
                $paymentGroups[$sequence] = [];
            }
            $paymentGroups[$sequence][] = $payment;
        }
        
        // Find partial groups
        $partialGroups = [];
        foreach ($paymentGroups as $sequence => $groupPayments) {
            $groupTotalPaid = array_sum(array_column($groupPayments, 'amount_paid'));
            if ($groupTotalPaid < $contributionAmount) {
                $partialGroups[$sequence] = $groupPayments;
            }
        }
        
        // If there are multiple partial groups, consolidate them
        if (count($partialGroups) > 1) {
            $targetSequence = min(array_keys($partialGroups)); // Use the lowest sequence number
            
            foreach ($partialGroups as $sequence => $groupPayments) {
                if ($sequence !== $targetSequence) {
                    // Move payments from this group to the target group
                    foreach ($groupPayments as $payment) {
                        $paymentModel->update($payment['id'], [
                            'payment_sequence' => $targetSequence
                        ]);
                    }
                }
            }
        }
    }

    /**
     * Update payment group status when total becomes fully paid
     */
    private function updatePaymentGroupStatus($payerId, $contributionId, $paymentSequence)
    {
        $paymentModel = new PaymentModel();
        
        // Update all payments in the same group to fully paid
        $paymentModel
            ->where('payer_id', $payerId)
            ->where('contribution_id', $contributionId)
            ->where('payment_sequence', $paymentSequence)
            ->where('deleted_at', null)
            ->set([
                'payment_status' => 'fully paid',
                'remaining_balance' => 0,
                'is_partial_payment' => false
            ])
            ->update();
    }

    /**
     * Get contribution warning data for modal
     * This method provides all the data needed for warning messages in the add payment modal
     */
    public function getContributionWarningData()
    {
        if ($this->request->getMethod() !== 'GET') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid request method'
            ]);
        }

        $payerId = $this->request->getGet('payer_id');
        $contributionId = $this->request->getGet('contribution_id');

        if (!$payerId || !$contributionId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Payer ID and Contribution ID are required'
            ]);
        }

        try {
            $paymentModel = new PaymentModel();
            $contributionModel = new \App\Models\ContributionModel();
            
            // Get contribution details
            $contribution = $contributionModel->find($contributionId);
            if (!$contribution) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Contribution not found'
                ]);
            }
            
            // Get all payments for this payer and contribution
            $payments = $paymentModel
                ->where('payer_id', $payerId)
                ->where('contribution_id', $contributionId)
                ->where('deleted_at', null)
                ->findAll();
            
            if (empty($payments)) {
                // No payments exist for this contribution
                return $this->response->setJSON([
                    'success' => true,
                    'status' => 'none',
                    'message' => 'No payments found',
                    'contribution' => [
                        'id' => $contribution['id'],
                        'title' => $contribution['title'],
                        'amount' => $contribution['amount']
                    ]
                ]);
            }
            
            // Group payments by payment_sequence
            $paymentGroups = [];
            foreach ($payments as $payment) {
                $sequence = $payment['payment_sequence'] ?? 1;
                if (!isset($paymentGroups[$sequence])) {
                    $paymentGroups[$sequence] = [];
                }
                $paymentGroups[$sequence][] = $payment;
            }
            
            // Check each payment group
            $hasUnpaidGroup = false;
            $hasFullyPaidGroup = false;
            $unpaidGroupData = null;
            $fullyPaidGroupData = null;
            
            foreach ($paymentGroups as $sequence => $groupPayments) {
                $groupTotalPaid = array_sum(array_column($groupPayments, 'amount_paid'));
                
                if ($groupTotalPaid >= $contribution['amount']) {
                    // This group is fully paid
                    $hasFullyPaidGroup = true;
                    $fullyPaidGroupData = [
                        'sequence' => $sequence,
                        'total_paid' => $groupTotalPaid,
                        'payment_count' => count($groupPayments),
                        'first_payment_date' => min(array_column($groupPayments, 'payment_date')),
                        'last_payment_date' => max(array_column($groupPayments, 'payment_date'))
                    ];
                } else {
                    // This group is partially paid
                    $hasUnpaidGroup = true;
                    $unpaidGroupData = [
                        'sequence' => $sequence,
                        'total_paid' => $groupTotalPaid,
                        'remaining_amount' => $contribution['amount'] - $groupTotalPaid,
                        'payment_count' => count($groupPayments),
                        'first_payment_date' => min(array_column($groupPayments, 'payment_date')),
                        'last_payment_date' => max(array_column($groupPayments, 'payment_date'))
                    ];
                }
            }
            
            // Determine overall status and return appropriate data
            if ($hasUnpaidGroup && $hasFullyPaidGroup) {
                // Has both unpaid and fully paid groups - show unpaid warning
                return $this->response->setJSON([
                    'success' => true,
                    'status' => 'unpaid',
                    'message' => 'Incomplete contribution found',
                    'contribution' => [
                        'id' => $contribution['id'],
                        'title' => $contribution['title'],
                        'amount' => $contribution['amount']
                    ],
                    'unpaid_group' => $unpaidGroupData,
                    'fully_paid_groups' => $fullyPaidGroupData ? [$fullyPaidGroupData] : []
                ]);
            } elseif ($hasUnpaidGroup) {
                // Only has unpaid groups
                return $this->response->setJSON([
                    'success' => true,
                    'status' => 'unpaid',
                    'message' => 'Incomplete contribution found',
                    'contribution' => [
                        'id' => $contribution['id'],
                        'title' => $contribution['title'],
                        'amount' => $contribution['amount']
                    ],
                    'unpaid_group' => $unpaidGroupData
                ]);
            } elseif ($hasFullyPaidGroup) {
                // Only has fully paid groups
                return $this->response->setJSON([
                    'success' => true,
                    'status' => 'fully_paid',
                    'message' => 'Contribution already fully paid',
                    'contribution' => [
                        'id' => $contribution['id'],
                        'title' => $contribution['title'],
                        'amount' => $contribution['amount']
                    ],
                    'fully_paid_groups' => $fullyPaidGroupData ? [$fullyPaidGroupData] : []
                ]);
            } else {
                // No groups found (shouldn't happen)
                return $this->response->setJSON([
                    'success' => true,
                    'status' => 'none',
                    'message' => 'No payment groups found'
                ]);
            }
            
        } catch (\Exception $e) {
            log_message('error', 'Error in getContributionWarningData: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'An error occurred while checking contribution status'
            ]);
        }
    }

    /**
     * Log user activity to user_activities table for admin dashboard
     */
    private function logUserActivity($action, $entityType, $entityId, $description)
    {
        try {
            $db = \Config\Database::connect();
            
            $userId = session()->get('user-id') ?? 1;
            
            $data = [
                'user_id' => $userId,
                'activity_type' => $action, // Use 'update' for payment edits
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'description' => $description,
                'ip_address' => $this->request->getIPAddress(),
                'user_agent' => $this->request->getUserAgent(),
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $result = $db->table('user_activities')->insert($data);
            
            if (!$result) {
                log_message('error', 'Failed to insert user activity for payment edit');
            }
            
            return $result;
        } catch (\Exception $e) {
            // Log error but don't fail the main operation
            log_message('error', 'Failed to log user activity: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send payment receipt email to payer
     */
    private function sendReceiptEmail($paymentData)
    {
        try {
            // Check if payer has an email address
            if (empty($paymentData['email_address'])) {
                log_message('info', 'No email address for payer, skipping receipt email');
                return false;
            }

            // Get email settings from database or config
            $emailConfig = $this->getEmailConfig();
            
            // Validate SMTP credentials
            if (empty($emailConfig['SMTPUser']) || empty($emailConfig['SMTPPass']) || empty($emailConfig['SMTPHost'])) {
                log_message('error', 'SMTP configuration incomplete for receipt email');
                return false;
            }
            
            // Get a fresh email service instance
            $emailService = \Config\Services::email();
            
            // Clear any previous configuration
            $emailService->clear();
            
            // Manually configure SMTP settings to ensure they're current
            $smtpConfig = [
                'protocol' => $emailConfig['protocol'] ?? 'smtp',
                'SMTPHost' => trim($emailConfig['SMTPHost'] ?? ''),
                'SMTPUser' => trim($emailConfig['SMTPUser'] ?? ''),
                'SMTPPass' => $emailConfig['SMTPPass'] ?? '', // Don't trim password - may contain spaces
                'SMTPPort' => (int)($emailConfig['SMTPPort'] ?? 587),
                'SMTPCrypto' => $emailConfig['SMTPCrypto'] ?? 'tls',
                'SMTPTimeout' => (int)($emailConfig['SMTPTimeout'] ?? 30),
                'mailType' => $emailConfig['mailType'] ?? 'html',
                'mailtype' => $emailConfig['mailType'] ?? 'html', // CodeIgniter uses lowercase
                'charset' => $emailConfig['charset'] ?? 'UTF-8',
                'newline' => "\r\n", // Required for SMTP
                'CRLF' => "\r\n", // Required for SMTP
                'wordWrap' => true,
                'validate' => false, // Don't validate email addresses
            ];
            
            // Validate configuration before initializing
            if (empty($smtpConfig['SMTPHost']) || empty($smtpConfig['SMTPUser']) || empty($smtpConfig['SMTPPass'])) {
                log_message('error', 'SMTP configuration validation failed for receipt email - Host: ' . ($smtpConfig['SMTPHost'] ? 'SET' : 'EMPTY') . ', User: ' . ($smtpConfig['SMTPUser'] ? 'SET' : 'EMPTY') . ', Pass: ' . ($smtpConfig['SMTPPass'] ? 'SET' : 'EMPTY'));
                return false;
            }
            
            $emailService->initialize($smtpConfig);
            
            $emailService->setFrom($emailConfig['fromEmail'], $emailConfig['fromName']);
            $emailService->setTo($paymentData['email_address']);
            $emailService->setSubject('Payment Receipt - ' . ($paymentData['receipt_number'] ?? 'ClearPay'));
            
            // Format payment method
            $paymentMethod = ucwords(str_replace('_', ' ', $paymentData['payment_method']));
            
            // Format status
            $status = $paymentData['computed_status'] ?? $paymentData['payment_status'] ?? 'pending';
            $statusText = 'COMPLETED';
            $statusBadgeClass = 'badge-success';
            
            if ($status === 'partial') {
                $statusText = 'PARTIAL PAYMENT';
                $statusBadgeClass = 'badge-warning';
            } elseif ($status === 'fully paid') {
                $statusText = 'COMPLETED';
                $statusBadgeClass = 'badge-success';
            }
            
            // Format date
            $paymentDate = $paymentData['payment_date'] ?? date('Y-m-d H:i:s');
            $formattedDate = date('F j, Y \a\t g:i A', strtotime($paymentDate));
            
            // Build email message
            $message = view('emails/receipt', [
                'payerName' => $paymentData['payer_name'] ?? 'Valued Payer',
                'receiptNumber' => $paymentData['receipt_number'] ?? 'N/A',
                'referenceNumber' => $paymentData['reference_number'] ?? 'N/A',
                'paymentDate' => $formattedDate,
                'payerId' => $paymentData['payer_id'] ?? 'N/A',
                'contactNumber' => $paymentData['contact_number'] ?? '',
                'contributionTitle' => $paymentData['contribution_title'] ?? 'N/A',
                'contributionCode' => $paymentData['contribution_code'] ?? null,
                'paymentMethod' => $paymentMethod,
                'amountPaid' => $paymentData['amount_paid'] ?? 0,
                'remainingBalance' => $paymentData['remaining_balance'] ?? null,
                'statusText' => $statusText,
                'statusBadgeClass' => $statusBadgeClass
            ]);
            
            $emailService->setMessage($message);
            
            // Log email attempt
            log_message('info', "Attempting to send receipt email to: {$paymentData['email_address']} using SMTP: {$emailConfig['SMTPHost']}:{$emailConfig['SMTPPort']}");
            
            // Send email
            $result = $emailService->send();
            
            if ($result) {
                log_message('info', "Receipt email sent successfully to: {$paymentData['email_address']}");
                return true;
            } else {
                $error = $emailService->printDebugger(['headers', 'subject']);
                log_message('error', "Failed to send receipt email: {$error}");
                return false;
            }
        } catch (\Exception $e) {
            // Log error but don't fail the payment
            log_message('error', 'Failed to send receipt email: ' . $e->getMessage());
            log_message('error', 'Exception details: ' . $e->getTraceAsString());
            return false;
        } catch (\Error $e) {
            log_message('error', 'Failed to send receipt email (Error): ' . $e->getMessage());
            log_message('error', 'Exception details: ' . $e->getTraceAsString());
            return false;
        }
    }
    
    /**
     * Get email configuration from database or fallback to config/environment
     */
    private function getEmailConfig()
    {
        try {
            $db = \Config\Database::connect();
            
            // Try to load from database first
            if ($db->tableExists('email_settings')) {
                $settings = $db->table('email_settings')
                    ->where('is_active', true)
                    ->orderBy('id', 'DESC')
                    ->limit(1)
                    ->get()
                    ->getRowArray();
                
                if ($settings) {
                    return [
                        'fromEmail' => $settings['from_email'] ?? '',
                        'fromName' => $settings['from_name'] ?? 'ClearPay',
                        'protocol' => $settings['protocol'] ?? 'smtp',
                        'SMTPHost' => $settings['smtp_host'] ?? '',
                        'SMTPUser' => $settings['smtp_user'] ?? '',
                        'SMTPPass' => $settings['smtp_pass'] ?? '',
                        'SMTPPort' => (int)($settings['smtp_port'] ?? 587),
                        'SMTPCrypto' => $settings['smtp_crypto'] ?? 'tls',
                        'SMTPTimeout' => (int)($settings['smtp_timeout'] ?? 30),
                        'mailType' => $settings['mail_type'] ?? 'html',
                        'charset' => $settings['charset'] ?? 'UTF-8',
                    ];
                }
            }
        } catch (\Exception $e) {
            log_message('debug', 'Email settings table not found, using config: ' . $e->getMessage());
        }
        
        // Fallback to config
        $config = config('Email');
        return [
            'fromEmail' => $config->fromEmail,
            'fromName' => $config->fromName,
            'protocol' => $config->protocol,
            'SMTPHost' => $config->SMTPHost,
            'SMTPUser' => $config->SMTPUser,
            'SMTPPass' => $config->SMTPPass,
            'SMTPPort' => $config->SMTPPort,
            'SMTPCrypto' => $config->SMTPCrypto,
            'SMTPTimeout' => $config->SMTPTimeout,
            'mailType' => $config->mailType,
            'charset' => $config->charset,
        ];
    }
}