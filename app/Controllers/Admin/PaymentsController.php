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
        
        // Get all contributions
        $contributions = $contributionModel->findAll();
        
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

        if (!$payerId || !$contributionId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Payer ID and Contribution ID are required'
            ]);
        }

        try {
            $paymentModel = new PaymentModel();
            $payments = $paymentModel->getPaymentsByPayerAndContribution($payerId, $contributionId);

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
            // Determine if this is a new or existing payer
            $existingPayerId = $this->request->getPost('existing_payer_id');
            $isExistingPayer = !empty($existingPayerId);
            
            // Validation rules - conditional based on payer type
            $rules = [
                'contribution_id' => 'required|integer',
                'amount_paid' => 'required|numeric',
                'payment_method' => 'required|in_list[cash,online,check,bank]',
                'is_partial_payment' => 'required|in_list[0,1]',
                'payment_date' => 'required'
            ];

            // Only require payer name/ID if it's a new payer
            if (!$isExistingPayer) {
                $rules['payer_name'] = 'required|min_length[3]|max_length[255]';
                $rules['payer_id'] = 'required|min_length[3]|max_length[50]';
            }

            if (!$this->validate($rules)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $this->validator->getErrors()
                ]);
            }

            $paymentModel = new PaymentModel();
            $payerModel = new \App\Models\PayerModel();
            
            // For existing payers, fetch their details from the database
            if ($isExistingPayer) {
                $existingPayer = $payerModel->where('payer_id', $existingPayerId)->first();
                
                if (!$existingPayer) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Payer not found'
                    ]);
                }
                $payerDbId = $existingPayer['id']; // Get the database ID
            } else {
                // Check if payer with this ID already exists
                $payerId = $this->request->getPost('payer_id');
                $existingPayer = $payerModel->where('payer_id', $payerId)->first();
                
                if ($existingPayer) {
                    // Use existing payer
                    $payerDbId = $existingPayer['id'];
                } else {
                    // Create new payer in payers table
                    $payerData = [
                        'payer_id' => $payerId,
                        'payer_name' => $this->request->getPost('payer_name'),
                        'contact_number' => $this->request->getPost('contact_number'),
                        'email_address' => $this->request->getPost('email_address'),
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

            // Determine payment status
            $isPartial = $this->request->getPost('is_partial_payment') == '1';
            $remainingBalance = (float) $this->request->getPost('remaining_balance');
            $paymentStatus = ($isPartial && $remainingBalance > 0) ? 'partial' : 'fully paid';

            // Generate reference number and receipt number
            $referenceNumber = 'REF-' . date('Ymd') . '-' . strtoupper(uniqid());
            $receiptNumber = 'RCPT-' . date('Ymd') . '-' . str_pad(uniqid(), 8, '0', STR_PAD_LEFT);

            // Gather POST data for payment record (payer info is now in separate payers table)
            $data = [
                'payer_id' => $payerDbId, // This is the FK to payers.id
                'contribution_id' => $this->request->getPost('contribution_id'),
                'amount_paid' => $this->request->getPost('amount_paid'),
                'payment_method' => $this->request->getPost('payment_method'),
                'payment_status' => $paymentStatus,
                'is_partial_payment' => $isPartial ? 1 : 0,
                'remaining_balance' => $remainingBalance,
                'parent_payment_id' => $this->request->getPost('parent_payment_id') ?: null,
                'payment_sequence' => 1, // Default to 1, can be enhanced later
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
                
                // Return the full payment data so frontend can show QR receipt
                $paymentData = $paymentModel->select('
                    payments.id,
                    payments.receipt_number,
                    payments.payment_date,
                    payments.amount_paid,
                    payments.payment_method,
                    payments.payment_status,
                    payers.payer_id,
                    payers.payer_name,
                    payers.contact_number,
                    payers.email_address,
                    contributions.title as contribution_title
                ')
                ->join('payers', 'payers.id = payments.payer_id', 'left')
                ->join('contributions', 'contributions.id = payments.contribution_id', 'left')
                ->find($paymentId);
                
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
                contributions.title as contribution_title
            ')
            ->join('payers', 'payers.id = payments.payer_id', 'left')
            ->join('contributions', 'contributions.id = payments.contribution_id', 'left')
            ->orderBy('payments.payment_date', 'DESC')
            ->limit(20)
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
            $term = $this->request->getGet('term');
            
            if (empty($term) || strlen($term) < 2) {
                return $this->response->setJSON([
                    'success' => true,
                    'payers' => []
                ]);
            }

            $payerModel = new \App\Models\PayerModel();
            
            // Search payers in the payers table
            $payers = $payerModel->select('
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
                'payers' => $payers
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
                'is_partial_payment' => $newRemaining > 0 ? 1 : 0,
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

            // Update payment data
            $updateData = [
                'contribution_id' => (int) $contributionId,
                'amount_paid' => (float) $amountPaid,
                'payment_method' => (string) $paymentMethod,
                'payment_date' => $paymentDate,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            // Handle partial payment status
            $isPartialValue = ($isPartial == '1' || $isPartial === 1 || $isPartial === true);
            $remainingBalanceValue = (float) $remainingBalance;
            
            if ($isPartialValue && $remainingBalanceValue > 0) {
                $updateData['payment_status'] = 'partial';
                $updateData['is_partial_payment'] = 1;
                $updateData['remaining_balance'] = $remainingBalanceValue;
            } else {
                $updateData['payment_status'] = 'fully paid';
                $updateData['is_partial_payment'] = 0;
                $updateData['remaining_balance'] = 0;
            }

            // Update the payment (skip validation to avoid issues)
            $updated = $paymentModel->skipValidation()->update($paymentId, $updateData);

            if ($updated) {
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
                contributions.title as contribution_title
            ')
            ->join('payers', 'payers.id = payments.payer_id', 'left')
            ->join('contributions', 'contributions.id = payments.contribution_id', 'left')
            ->find($paymentId);

            if ($payment) {
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
                    'profile_picture' => $payment['profile_picture'] ?? '',
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
}