<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\PaymentModel;
use App\Models\ContributionModel;
use App\Models\PaymentRequestModel;
use App\Models\PaymentMethodModel;
use App\Models\ActivityLogModel;
use App\Services\ActivityLogger;

class DashboardController extends BaseController
{
    public function index()
    {
        // Check if user is logged in
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/admin/login');
        }

        $paymentModel = new PaymentModel();
        $allPayments = $paymentModel
            ->select('payments.*, payers.payer_id as payer_student_id, payers.payer_name, payers.contact_number, payers.email_address, payers.profile_picture, contributions.title as contribution_title')
            ->join('payers', 'payers.id = payments.payer_id', 'left')
            ->join('contributions', 'contributions.id = payments.contribution_id', 'left')
            ->where('payments.deleted_at', null)
            ->orderBy('payments.id', 'DESC')
            ->orderBy('payments.payment_date', 'DESC')
            ->findAll();

        // Get User Information
        $userModel = new UserModel();
        $user = $userModel->select('username')
                          ->where('id', session()->get('user-id'))
                          ->first();

        // --- Fetch Total Collections ---
        $totalModel = new PaymentModel();
        $totalCollectionsRow = $totalModel
            ->selectSum('amount_paid')
            ->where('payment_status', 'fully paid')
            ->first();

        $totalCollections = 0.0;
        if (!empty($totalCollectionsRow)) {
            $totalCollections = isset($totalCollectionsRow['amount_paid'])
                                ? (float)$totalCollectionsRow['amount_paid']
                                : (float)array_values($totalCollectionsRow)[0];
        }
        $totalCollections = number_format($totalCollections, 2);

        // --- Fetch Other Stats ---
        $paymentModel = new PaymentModel();
        $verifiedPayments = $paymentModel->where('payment_status', 'fully paid')->countAllResults();

        $paymentModel = new PaymentModel();
        $partialPayments  = $paymentModel->where('LOWER(payment_status)', 'partial')->countAllResults();

        $paymentModel = new PaymentModel();
        $todayPayments    = $paymentModel->where('DATE(payment_date)', date('Y-m-d'))->countAllResults();

        // --- Fetch Recent Payments ---
        $recentPayments = $paymentModel
            ->select('payments.*, payers.payer_id as payer_student_id, payers.payer_name, payers.contact_number, payers.email_address, payers.profile_picture, contributions.title as contribution_title, contributions.contribution_code, contributions.id as contrib_id')
            ->join('payers', 'payers.id = payments.payer_id', 'left')
            ->join('contributions', 'contributions.id = payments.contribution_id', 'left')
            ->where('payments.deleted_at', null)
            ->orderBy('payments.id', 'DESC')
            ->orderBy('payments.payment_date', 'DESC')
            ->limit(7)
            ->findAll(); // last 7 payments

        // Add computed status to each payment
        foreach ($recentPayments as &$payment) {
            $payerId = $payment['payer_id'];
            $contributionId = $payment['contrib_id'] ?? $payment['contribution_id'] ?? null;
            $payment['computed_status'] = $paymentModel->getPaymentStatus($payerId, $contributionId);
        }

        // Add computed status to all payments as well
        foreach ($allPayments as &$payment) {
            $payerId = $payment['payer_id'];
            $contributionId = $payment['contribution_id'] ?? null;
            $payment['computed_status'] = $paymentModel->getPaymentStatus($payerId, $contributionId);
        }

        // --- Fetch Contributions for Modal ---
        $contributionModel = new ContributionModel();
        $contributions = $contributionModel->where('status', 'active')->findAll();

        // --- Fetch User Activities ---
        $db = \Config\Database::connect();
        $userActivities = $db->table('user_activities ua')
            ->select('ua.*, u.name as user_name, u.username, u.role, u.profile_picture')
            ->join('users u', 'u.id = ua.user_id', 'left')
            ->orderBy('ua.created_at', 'DESC')
            ->limit(5)
            ->get()
            ->getResultArray();
        
        // Fetch all user activities for the modal
        $allUserActivities = $db->table('user_activities ua')
            ->select('ua.*, u.name as user_name, u.username, u.role, u.profile_picture')
            ->join('users u', 'u.id = ua.user_id', 'left')
            ->orderBy('ua.created_at', 'DESC')
            ->get()
            ->getResultArray();

        // --- Fetch Payment Requests Count ---
        $paymentRequestModel = new PaymentRequestModel();
        $pendingPaymentRequests = $paymentRequestModel->getPendingCount();

        // --- Fetch Payment Methods ---
        $paymentMethodModel = new PaymentMethodModel();
        $paymentMethods = $paymentMethodModel->orderBy('name', 'ASC')->findAll();

        // --- Prepare Data for View ---
        $data = [
            'totalCollections' => $totalCollections,
            'verifiedPayments'  => $verifiedPayments,
            'partialPayments'   => $partialPayments,
            'todayPayments'     => $todayPayments,
            'pendingPaymentRequests' => $pendingPaymentRequests,
            'recentPayments'    => $recentPayments,
            'allPayments'       => $allPayments,
            'contributions'     => $contributions,
            'userActivities'    => $userActivities,
            'allUserActivities' => $allUserActivities,
            'paymentMethods'    => $paymentMethods,
            'title'             => 'Admin Dashboard',
            'pageTitle'         => 'Dashboard',
            'pageSubtitle'      => 'Welcome back ' . ucwords($user['username'] ?? 'User') . '!',
            'username'          => $user['username'] ?? 'User', 
        ];

        return view('admin/dashboard', $data);
    }

    /**
     * Global search functionality
     * Searches across payments, contributions, and payers
     */
    public function search()
    {
        try {
            $query = $this->request->getGet('q') ?? '';
            
            // Log the search query
            log_message('debug', 'Search query: ' . $query);
            
            if (empty($query) || strlen($query) < 2) {
                return $this->response->setJSON([
                    'success' => true,
                    'results' => [],
                    'message' => 'Query too short'
                ]);
            }

            $results = [];
        
        // Search payments
        $paymentModel = new PaymentModel();
        $payments = $paymentModel
            ->select('payments.*, payers.payer_id as payer_student_id, payers.payer_name, contributions.title as contribution_title')
            ->join('payers', 'payers.id = payments.payer_id', 'left')
            ->join('contributions', 'contributions.id = payments.contribution_id', 'left')
            ->groupStart()
                ->like('payers.payer_name', $query)
                ->orLike('payers.payer_id', $query)
                ->orLike('payments.receipt_number', $query)
                ->orLike('contributions.title', $query)
            ->groupEnd()
            ->limit(5)
            ->findAll();

        foreach ($payments as $payment) {
            $results[] = [
                'type' => 'payment',
                'id' => $payment['id'],
                'title' => $payment['payer_name'] . ' - ' . $payment['contribution_title'],
                'subtitle' => '₱' . number_format($payment['amount_paid'], 2) . ' • ' . date('M d, Y', strtotime($payment['payment_date'])),
                'icon' => 'fa-money-bill-wave',
                'url' => base_url('payments') . '#payment-' . $payment['id'],
                'data' => $payment
            ];
        }
        
        // Search contributions
        $contributionModel = new ContributionModel();
        $contributions = $contributionModel
            ->like('title', $query)
            ->orLike('description', $query)
            ->limit(5)
            ->findAll();

        foreach ($contributions as $contribution) {
            $results[] = [
                'type' => 'contribution',
                'id' => $contribution['id'],
                'title' => $contribution['title'],
                'subtitle' => '₱' . number_format($contribution['amount'], 2) . ' • ' . ucfirst($contribution['status']),
                'icon' => 'fa-tag',
                'url' => base_url('contributions') . '#contribution-' . $contribution['id'],
                'data' => $contribution
            ];
        }
        
        // Search payers
        $payerModel = new \App\Models\PayerModel();
        $payers = $payerModel
            ->like('payer_name', $query)
            ->orLike('payer_id', $query)
            ->orLike('email_address', $query)
            ->limit(5)
            ->findAll();

        foreach ($payers as $payer) {
            $results[] = [
                'type' => 'payer',
                'id' => $payer['id'],
                'title' => $payer['payer_name'],
                'subtitle' => $payer['payer_id'] . ($payer['email_address'] ? ' • ' . $payer['email_address'] : ''),
                'icon' => 'fa-user',
                'url' => base_url('payers') . '#payer-' . $payer['id'],
                'data' => $payer
            ];
        }

            return $this->response->setJSON([
                'success' => true,
                'results' => array_slice($results, 0, 10) // Limit to 10 total results
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Search error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error performing search: ' . $e->getMessage(),
                'results' => []
            ]);
        }
    }

    public function clearSidebarFlag()
    {
        session()->remove('forceSidebarExpanded');
        return $this->response->setJSON(['success' => true]);
    }

    public function paymentRequests()
    {
        // Check if user is logged in
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/admin/login');
        }

        $paymentRequestModel = new PaymentRequestModel();
        
        // Get payment requests by status
        $pendingRequests = $paymentRequestModel->getRequestsWithDetails('pending');
        $approvedRequests = $paymentRequestModel->getRequestsWithDetails('approved');
        $rejectedRequests = $paymentRequestModel->getRequestsWithDetails('rejected');
        
        // Fix image paths for all requests
        $fixImagePath = function(&$request) {
            if (!empty($request['proof_of_payment_path'])) {
                $path = $request['proof_of_payment_path'];
                $path = preg_replace('#^uploads/payment_proofs/#', '', $path);
                $path = preg_replace('#^payment_proofs/#', '', $path);
                $filename = basename($path);
                
                // Verify file exists before setting path
                $filePath = FCPATH . 'uploads/payment_proofs/' . $filename;
                if (file_exists($filePath)) {
                    $request['proof_of_payment_path'] = base_url('uploads/payment_proofs/' . $filename);
                } else {
                    log_message('warning', 'Proof of payment image not found: ' . $filePath);
                    $request['proof_of_payment_path'] = null;
                }
            }
            if (!empty($request['profile_picture'])) {
                $path = $request['profile_picture'];
                $path = preg_replace('#^uploads/profile/#', '', $path);
                $path = preg_replace('#^profile/#', '', $path);
                $filename = basename($path);
                
                // Verify file exists before setting path
                $filePath = FCPATH . 'uploads/profile/' . $filename;
                if (file_exists($filePath)) {
                    $request['profile_picture'] = base_url('uploads/profile/' . $filename);
                } else {
                    log_message('warning', 'Profile picture not found: ' . $filePath);
                    $request['profile_picture'] = null;
                }
            }
        };
        
        foreach ($pendingRequests as &$req) $fixImagePath($req);
        foreach ($approvedRequests as &$req) $fixImagePath($req);
        foreach ($rejectedRequests as &$req) $fixImagePath($req);
        
        // Get stats
        $stats = [
            'pending' => count($pendingRequests),
            'approved' => count($approvedRequests),
            'rejected' => count($rejectedRequests),
            'processed' => $paymentRequestModel->where('status', 'processed')->countAllResults(),
            'total' => count($pendingRequests) + count($approvedRequests) + count($rejectedRequests)
        ];

        $data = [
            'title' => 'Payment Requests',
            'pageTitle' => 'Payment Requests Management',
            'pageSubtitle' => 'Manage online payment requests from payers',
            'pendingRequests' => $pendingRequests,
            'approvedRequests' => $approvedRequests,
            'rejectedRequests' => $rejectedRequests,
            'stats' => $stats
        ];

        return view('admin/payment-requests', $data);
    }

    public function approvePaymentRequest()
    {
        if (!$this->request->isAJAX() && $this->request->getMethod() !== 'POST') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid request method'
            ]);
        }

        $requestId = $this->request->getPost('request_id');
        $adminNotes = $this->request->getPost('admin_notes');
        $processedBy = session()->get('user-id');

        if (!$requestId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Request ID is required'
            ]);
        }

        try {
            $paymentRequestModel = new PaymentRequestModel();
            
            // Get the payment request details
            $request = $paymentRequestModel->find($requestId);
            if (!$request) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Payment request not found'
                ]);
            }

            // Create actual payment record using proper grouping logic
            $paymentId = $this->createPaymentFromRequest($request, $processedBy);
            
            if ($paymentId) {
                // Get the payment record with full details including contribution amount
                $paymentModel = new PaymentModel();
                $paymentRecord = $paymentModel->select('
                    payments.*,
                    payers.payer_id,
                    payers.payer_name,
                    payers.contact_number,
                    payers.email_address,
                    contributions.title as contribution_title,
                    contributions.amount as contribution_amount
                ')
                ->join('payers', 'payers.id = payments.payer_id', 'left')
                ->join('contributions', 'contributions.id = payments.contribution_id', 'left')
                ->find($paymentId);
                
                $receiptNumber = $paymentRecord['receipt_number'] ?? 'N/A';
                
                // Get payer information for activity log
                $payerModel = new \App\Models\PayerModel();
                $payer = $payerModel->find($request['payer_id']);
                $payerName = $payer ? $payer['payer_name'] : ($paymentRecord['payer_name'] ?? 'Unknown Payer');
                $contributionTitle = $paymentRecord['contribution_title'] ?? 'Unknown Contribution';
                
                // Check if payment was added to a partially paid contribution
                $wasAddedToPartialGroup = false;
                if ($paymentRecord && !empty($paymentRecord['payment_sequence'])) {
                    // Check if there were existing payments in this group BEFORE this one was added
                    // Exclude the newly inserted payment to get accurate count
                    $existingGroupPayments = $paymentModel
                        ->where('payer_id', $request['payer_id'])
                        ->where('contribution_id', $request['contribution_id'])
                        ->where('payment_sequence', $paymentRecord['payment_sequence'])
                        ->where('payments.id !=', $paymentId) // Exclude the newly inserted payment
                        ->where('deleted_at', null)
                        ->findAll();
                    
                    $existingGroupPaymentsCount = count($existingGroupPayments);
                    
                    // If there were existing payments in this group, check if the group was partial before adding this payment
                    if ($existingGroupPaymentsCount > 0) {
                        // Calculate total paid in the group BEFORE adding this payment
                        $existingGroupTotalPaid = array_sum(array_column($existingGroupPayments, 'amount_paid'));
                        $contributionAmount = (float) ($paymentRecord['contribution_amount'] ?? 0);
                        
                        // Check if the group was partial (not fully paid) before adding this payment
                        if ($existingGroupTotalPaid < $contributionAmount) {
                            $wasAddedToPartialGroup = true;
                        }
                    }
                }
                
                // Mark payment request as approved
                $result = $paymentRequestModel->approveRequest($requestId, $processedBy, $adminNotes);
                
                if ($result) {
                    // Log activity for payer notification (using ActivityLogger)
                    $activityLogger = new ActivityLogger();
                    $activityLogger->logPaymentRequest('approved', $request);
                    
                    // Log activity for admin dashboard (user_activities table)
                    // Include both admin and payer names
                    $userModel = new \App\Models\UserModel();
                    $admin = $userModel->find($processedBy);
                    $adminName = $admin['name'] ?? $admin['username'] ?? 'Admin';
                    $this->logUserActivity('approved', 'payment_request', $requestId, "Payment request approved by {$adminName} for {$payerName} - Receipt: {$receiptNumber}");
                    
                    // Log if payment was added to partially paid contribution
                    if ($wasAddedToPartialGroup) {
                        $userModel = new \App\Models\UserModel();
                        $admin = $userModel->find($processedBy);
                        $adminName = $admin['name'] ?? $admin['username'] ?? 'Admin';
                        $description = "Payment added to partially paid contribution by {$adminName} for {$payerName} - Receipt: {$receiptNumber} - Contribution: {$contributionTitle}";
                        $this->logUserActivity('create', 'payment', $paymentId, $description);
                    }
                    
                    // Send receipt email to payer
                    if ($paymentRecord) {
                        try {
                            $emailSent = $this->sendReceiptEmail($paymentRecord);
                            if ($emailSent) {
                                log_message('info', "Receipt email sent successfully for payment ID: {$paymentId}");
                            }
                        } catch (\Exception $e) {
                            log_message('error', 'Exception while sending receipt email (non-fatal): ' . $e->getMessage());
                        } catch (\Error $e) {
                            log_message('error', 'Error while sending receipt email (non-fatal): ' . $e->getMessage());
                        }
                    }
                    
                    return $this->response->setJSON([
                        'success' => true,
                        'message' => 'Payment request approved and payment recorded successfully',
                        'payment_id' => $paymentId
                    ]);
                } else {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Failed to approve payment request'
                    ]);
                }
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to create payment record'
                ]);
            }
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    public function rejectPaymentRequest()
    {
        if (!$this->request->isAJAX() && $this->request->getMethod() !== 'POST') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid request method'
            ]);
        }

        $requestId = $this->request->getPost('request_id');
        $adminNotes = $this->request->getPost('admin_notes');
        $processedBy = session()->get('user-id');

        if (!$requestId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Request ID is required'
            ]);
        }

        try {
            $paymentRequestModel = new PaymentRequestModel();
            
            // Get the payment request details for logging
            $request = $paymentRequestModel->find($requestId);
            if (!$request) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Payment request not found'
                ]);
            }
            
            // Get payer information for activity log
            $payerModel = new \App\Models\PayerModel();
            $payer = $payerModel->find($request['payer_id']);
            $payerName = $payer ? $payer['payer_name'] : 'Unknown Payer';
            
            $result = $paymentRequestModel->rejectRequest($requestId, $processedBy, $adminNotes);
            
            if ($result) {
                // Log activity for payer notification (using ActivityLogger)
                $activityLogger = new ActivityLogger();
                $activityLogger->logPaymentRequest('rejected', $request);
                
                // Log activity for admin dashboard (user_activities table)
                // Include both admin and payer names
                $userModel = new \App\Models\UserModel();
                $admin = $userModel->find($processedBy);
                $adminName = $admin['name'] ?? $admin['username'] ?? 'Admin';
                $this->logUserActivity('rejected', 'payment_request', $requestId, "Payment request rejected by {$adminName} for {$payerName}" . ($adminNotes ? " - Reason: {$adminNotes}" : ""));
                
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Payment request rejected'
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to reject payment request'
                ]);
            }
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    public function processPaymentRequest()
    {
        if (!$this->request->isAJAX() && $this->request->getMethod() !== 'POST') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid request method'
            ]);
        }

        $requestId = $this->request->getPost('request_id');
        $adminNotes = $this->request->getPost('admin_notes');
        $processedBy = session()->get('user-id');

        if (!$requestId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Request ID is required'
            ]);
        }

        try {
            $paymentRequestModel = new PaymentRequestModel();
            
            // Get the payment request details
            $request = $paymentRequestModel->find($requestId);
            if (!$request) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Payment request not found'
                ]);
            }

            // Create actual payment record using proper grouping logic (same as approve)
            $paymentId = $this->createPaymentFromRequest($request, $processedBy);
            
            if ($paymentId) {
                // Get the payment record with full details including contribution amount
                $paymentModel = new PaymentModel();
                $paymentRecord = $paymentModel->select('
                    payments.*,
                    payers.payer_id,
                    payers.payer_name,
                    payers.contact_number,
                    payers.email_address,
                    contributions.title as contribution_title,
                    contributions.amount as contribution_amount
                ')
                ->join('payers', 'payers.id = payments.payer_id', 'left')
                ->join('contributions', 'contributions.id = payments.contribution_id', 'left')
                ->find($paymentId);
                
                $receiptNumber = $paymentRecord['receipt_number'] ?? 'N/A';
                
                // Get payer information for activity log
                $payerModel = new \App\Models\PayerModel();
                $payer = $payerModel->find($request['payer_id']);
                $payerName = $payer ? $payer['payer_name'] : ($paymentRecord['payer_name'] ?? 'Unknown Payer');
                $contributionTitle = $paymentRecord['contribution_title'] ?? 'Unknown Contribution';
                
                // Check if payment was added to a partially paid contribution
                $wasAddedToPartialGroup = false;
                if ($paymentRecord && !empty($paymentRecord['payment_sequence'])) {
                    // Check if there were existing payments in this group BEFORE this one was added
                    // Exclude the newly inserted payment to get accurate count
                    $existingGroupPayments = $paymentModel
                        ->where('payer_id', $request['payer_id'])
                        ->where('contribution_id', $request['contribution_id'])
                        ->where('payment_sequence', $paymentRecord['payment_sequence'])
                        ->where('payments.id !=', $paymentId) // Exclude the newly inserted payment
                        ->where('deleted_at', null)
                        ->findAll();
                    
                    $existingGroupPaymentsCount = count($existingGroupPayments);
                    
                    // If there were existing payments in this group, check if the group was partial before adding this payment
                    if ($existingGroupPaymentsCount > 0) {
                        // Calculate total paid in the group BEFORE adding this payment
                        $existingGroupTotalPaid = array_sum(array_column($existingGroupPayments, 'amount_paid'));
                        $contributionAmount = (float) ($paymentRecord['contribution_amount'] ?? 0);
                        
                        // Check if the group was partial (not fully paid) before adding this payment
                        if ($existingGroupTotalPaid < $contributionAmount) {
                            $wasAddedToPartialGroup = true;
                        }
                    }
                }
                
                // Mark payment request as processed
                $paymentRequestModel->processRequest($requestId, $processedBy, $adminNotes);
                
                // Log activity for payer notification (using ActivityLogger)
                $activityLogger = new ActivityLogger();
                $activityLogger->logPaymentRequest('processed', $request);
                
                // Log activity for admin dashboard (user_activities table)
                // Include both admin and payer names
                $userModel = new \App\Models\UserModel();
                $admin = $userModel->find($processedBy);
                $adminName = $admin['name'] ?? $admin['username'] ?? 'Admin';
                $this->logUserActivity('processed', 'payment_request', $requestId, "Payment request processed by {$adminName} for {$payerName} - Receipt: {$receiptNumber}");
                
                // Log if payment was added to partially paid contribution
                if ($wasAddedToPartialGroup) {
                    $userModel = new \App\Models\UserModel();
                    $admin = $userModel->find($processedBy);
                    $adminName = $admin['name'] ?? $admin['username'] ?? 'Admin';
                    $description = "Payment added to partially paid contribution by {$adminName} for {$payerName} - Receipt: {$receiptNumber} - Contribution: {$contributionTitle}";
                    $this->logUserActivity('create', 'payment', $paymentId, $description);
                }
                
                // Send receipt email to payer
                if ($paymentRecord) {
                    try {
                        $emailSent = $this->sendReceiptEmail($paymentRecord);
                        if ($emailSent) {
                            log_message('info', "Receipt email sent successfully for payment ID: {$paymentId}");
                        }
                    } catch (\Exception $e) {
                        log_message('error', 'Exception while sending receipt email (non-fatal): ' . $e->getMessage());
                    } catch (\Error $e) {
                        log_message('error', 'Error while sending receipt email (non-fatal): ' . $e->getMessage());
                    }
                }
                
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Payment request processed and payment recorded successfully',
                    'payment_id' => $paymentId
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to create payment record'
                ]);
            }
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    public function getPaymentRequestDetails()
    {
        if (!$this->request->isAJAX() && $this->request->getMethod() !== 'GET') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid request method'
            ]);
        }

        $requestId = $this->request->getGet('request_id');
        
        if (!$requestId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Request ID is required'
            ]);
        }

        try {
            $paymentRequestModel = new PaymentRequestModel();
            
            // Get the specific request by ID with all details
            $request = $paymentRequestModel->select('
                payment_requests.*,
                payers.payer_name,
                payers.contact_number,
                payers.email_address,
                payers.profile_picture,
                contributions.title as contribution_title,
                contributions.description as contribution_description,
                contributions.amount as contribution_amount,
                contributions.contribution_code,
                users.username as processed_by_name
            ')
            ->join('payers', 'payers.id = payment_requests.payer_id', 'left')
            ->join('contributions', 'contributions.id = payment_requests.contribution_id', 'left')
            ->join('users', 'users.id = payment_requests.processed_by', 'left')
            ->where('payment_requests.id', $requestId)
            ->first();
            
            if (!$request) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Payment request not found'
                ]);
            }

            // Add base_url to proof of payment path and profile picture
            if (!empty($request['proof_of_payment_path'])) {
                // Extract filename from path, handling various formats
                $path = $request['proof_of_payment_path'];
                $path = preg_replace('#^uploads/payment_proofs/#', '', $path);
                $path = preg_replace('#^payment_proofs/#', '', $path);
                $filename = basename($path);
                $request['proof_of_payment_path'] = base_url('uploads/payment_proofs/' . $filename);
            }
            if (!empty($request['profile_picture'])) {
                // Extract filename from path, handling various formats
                $path = $request['profile_picture'];
                $path = preg_replace('#^uploads/profile/#', '', $path);
                $path = preg_replace('#^profile/#', '', $path);
                $filename = basename($path);
                $request['profile_picture'] = base_url('uploads/profile/' . $filename);
            }

            return $this->response->setJSON([
                'success' => true,
                'request' => $request
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Delete a payment request
     */
    public function deletePaymentRequest()
    {
        if (!$this->request->isAJAX() && $this->request->getMethod() !== 'POST') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid request method'
            ]);
        }

        $requestId = $this->request->getPost('request_id');

        if (!$requestId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Request ID is required'
            ]);
        }

        try {
            $paymentRequestModel = new PaymentRequestModel();
            
            // Get the payment request details for logging
            $request = $paymentRequestModel->find($requestId);
            if (!$request) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Payment request not found'
                ]);
            }

            // Soft delete the payment request
            $deleted = $paymentRequestModel->delete($requestId);
            
            if ($deleted) {
                // Log activity for admin dashboard
                $this->logUserActivity('deleted', 'payment_request', $requestId, "Payment request deleted (Reference: {$request['reference_number']})");
                
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Payment request deleted successfully'
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to delete payment request'
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
     * Create payment record from approved payment request using proper grouping logic
     */
    private function createPaymentFromRequest($request, $processedBy)
    {
        $paymentModel = new PaymentModel();
        $contributionModel = new \App\Models\ContributionModel();
        
        // Get contribution details
        $contribution = $contributionModel->find($request['contribution_id']);
        
        // Check if contribution exists and is active
        if (!$contribution) {
            throw new \Exception('Contribution not found');
        }
        
        // Check if contribution is inactive - prevent processing payment requests for inactive contributions
        if ($contribution['status'] === 'inactive') {
            throw new \Exception('Cannot process payment request for an inactive contribution. This contribution is no longer active.');
        }
        
        $contributionAmount = $contribution['amount'];
        $amountPaid = (float) $request['requested_amount'];
        
        // Get existing payments to determine proper grouping and status
        $existingPayments = $paymentModel
            ->where('payer_id', $request['payer_id'])
            ->where('contribution_id', $request['contribution_id'])
            ->where('deleted_at', null)
            ->findAll();
        
        // Determine payment sequence
        $paymentSequence = null;
        
        // If payment request explicitly has a payment_sequence, use it (payer intended to add to existing group)
        if (!empty($request['payment_sequence']) && is_numeric($request['payment_sequence'])) {
            $paymentSequence = (int) $request['payment_sequence'];
        } else {
            // No payment_sequence specified means this is a NEW payment cycle/group
            // Create a new payment sequence (next available sequence number)
            $paymentSequence = $this->getNextPaymentSequence($request['payer_id'], $request['contribution_id']);
        }
        
        // Get existing payments for THIS specific payment_sequence group
        $groupPayments = array_filter($existingPayments, function($payment) use ($paymentSequence) {
            $seq = $payment['payment_sequence'] ?? 1;
            return $seq == $paymentSequence;
        });
        
        // Calculate total paid for THIS specific group (not all payments combined)
        $groupTotalPaid = array_sum(array_column($groupPayments, 'amount_paid'));
        $newGroupTotalPaid = $groupTotalPaid + $amountPaid;
        
        // Calculate status based on THIS group's total, not all payments combined
        if ($newGroupTotalPaid >= $contributionAmount) {
            $paymentStatus = 'fully paid';
            $remainingBalance = 0;
            $isPartial = false;
        } else {
            $paymentStatus = 'partial';
            $remainingBalance = $contributionAmount - $newGroupTotalPaid;
            $isPartial = true;
        }
        
        $paymentData = [
            'payer_id' => $request['payer_id'],
            'contribution_id' => $request['contribution_id'],
            'amount_paid' => $request['requested_amount'],
            'payment_method' => $request['payment_method'],
            'payment_status' => $paymentStatus,
            'is_partial_payment' => $isPartial ? true : false,
            'remaining_balance' => $remainingBalance,
            'payment_sequence' => $paymentSequence,
            'reference_number' => 'REQ-' . date('Ymd') . '-' . strtoupper(substr(md5($request['id']), 0, 12)),
            'receipt_number' => 'RCPT-' . date('Ymd') . '-' . strtoupper(substr(md5($request['id'] . time()), 0, 12)),
            'payment_date' => date('Y-m-d H:i:s'),
            'recorded_by' => $processedBy,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        return $paymentModel->insert($paymentData);
    }

    /**
     * Get next payment sequence for a payer and contribution
     */
    private function getNextPaymentSequence($payerId, $contributionId)
    {
        $paymentModel = new PaymentModel();
        
        $maxSequence = $paymentModel
            ->select('MAX(payment_sequence) as max_seq')
            ->where('payer_id', $payerId)
            ->where('contribution_id', $contributionId)
            ->where('deleted_at', null)
            ->first();
        
        return ($maxSequence['max_seq'] ?? 0) + 1;
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
                'activity_type' => 'update', // Use valid enum value
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'description' => $description,
                'ip_address' => $this->request->getIPAddress(),
                'user_agent' => $this->request->getUserAgent(),
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            // Debug logging
            log_message('info', 'Logging user activity: ' . json_encode($data));
            
            $result = $db->table('user_activities')->insert($data);
            
            if ($result) {
                log_message('info', 'User activity logged successfully');
            } else {
                log_message('error', 'Failed to insert user activity');
            }
            
        } catch (\Exception $e) {
            // Log error but don't break the main flow
            log_message('error', 'Failed to log user activity: ' . $e->getMessage());
        }
    }

    /**
     * Get pending payment requests count for notification badge
     */
    public function getPendingPaymentRequestsCount()
    {
        try {
            // Check if user is logged in
            if (!session()->get('isLoggedIn')) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Unauthorized'
                ]);
            }

            $paymentRequestModel = new PaymentRequestModel();
            $count = $paymentRequestModel->getPendingCount();

            return $this->response->setJSON([
                'success' => true,
                'count' => $count
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'count' => 0
            ]);
        }
    }

    /**
     * Get pending refund requests count for notification badge
     */
    public function getPendingRefundRequestsCount()
    {
        try {
            // Check if user is logged in
            if (!session()->get('isLoggedIn')) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Unauthorized'
                ]);
            }

            $refundModel = new \App\Models\RefundModel();
            $count = $refundModel->getPendingCount();

            return $this->response->setJSON([
                'success' => true,
                'count' => $count
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'count' => 0
            ]);
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
            
            // Initialize email service with fresh config
            $emailService = \Config\Services::email();
            
            // Manually configure SMTP settings to ensure they're current
            $smtpConfig = [
                'protocol' => $emailConfig['protocol'] ?? 'smtp',
                'SMTPHost' => trim($emailConfig['SMTPHost'] ?? ''),
                'SMTPUser' => trim($emailConfig['SMTPUser'] ?? ''),
                'SMTPPass' => $emailConfig['SMTPPass'] ?? '', // Don't trim password
                'SMTPPort' => (int)($emailConfig['SMTPPort'] ?? 587),
                'SMTPCrypto' => $emailConfig['SMTPCrypto'] ?? 'tls',
                'SMTPTimeout' => (int)($emailConfig['SMTPTimeout'] ?? 30),
                'mailType' => $emailConfig['mailType'] ?? 'html',
                'mailtype' => $emailConfig['mailType'] ?? 'html',
                'charset' => $emailConfig['charset'] ?? 'UTF-8',
                'newline' => "\r\n", // Required for SMTP
                'CRLF' => "\r\n", // Required for SMTP
            ];
            
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

    /**
     * Check for new admin notifications
     */
    public function checkNewActivities()
    {
        // Get the current admin user ID from session
        $userId = session()->get('user-id');
        
        if (!$userId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'User not authenticated',
                'activities' => [],
                'newActivities' => [],
                'hasNew' => false
            ]);
        }

        // Get the last activity ID that was shown to this admin
        $lastShownId = $this->request->getGet('last_shown_id') ?: 0;
        
        // Debug logging
        log_message('info', "Checking for new activities for admin user {$userId}. Last shown ID: {$lastShownId}");
        
        // Get recent activities for admin users (limit to 10 for better UX)
        $activityLogModel = new ActivityLogModel();
        $activities = $activityLogModel->getRecentForAdmins(10, $userId);
        
        log_message('info', "Found " . count($activities) . " activities for admin user {$userId}");
        
        if (!empty($activities)) {
            // Format activities for frontend
            foreach ($activities as &$activity) {
                // Ensure activity_type is explicitly set (debugging)
                if (!isset($activity['activity_type']) || empty($activity['activity_type'])) {
                    log_message('warning', 'Activity missing activity_type: ' . json_encode($activity));
                }
                
                // Format the created_at time for Philippines timezone (UTC+8)
                $createdAt = new \DateTime($activity['created_at'], new \DateTimeZone('UTC'));
                $createdAt->setTimezone(new \DateTimeZone('Asia/Manila'));
                $activity['created_at_formatted'] = $createdAt->format('Y-m-d H:i:s');
                $activity['created_at_time'] = $createdAt->format('g:i A');
                $activity['created_at_date'] = $createdAt->format('M d, Y');
                
                // Check if activity is read by this admin
                $activity['is_read'] = $activity['is_read_by_admin'] ?? 0;
                
                // Ensure activity_type is present for frontend (explicit check)
                if (!isset($activity['activity_type']) || empty($activity['activity_type'])) {
                    $activity['activity_type'] = $activity['entity_type'] ?? 'unknown';
                    log_message('warning', 'Using entity_type as fallback for activity_type: ' . $activity['activity_type']);
                }
                
                // Format activity data for frontend
                $activity['activity_icon'] = $this->getActivityIcon($activity['activity_type'] ?? 'unknown', $activity['action'] ?? '');
                $activity['activity_color'] = $this->getActivityColor($activity['activity_type'] ?? 'unknown', $activity['action'] ?? '');
            }
            
            // Check if there are new activities (greater than last shown ID OR unread on first load)
            $newActivities = array_filter($activities, function($activity) use ($lastShownId) {
                $isReadByAdmin = ($activity['is_read_by_admin'] ?? 0) == 1;
                // If lastShownId is 0 (first load), include all unread activities
                if ($lastShownId == 0) {
                    return !$isReadByAdmin;
                }
                // Otherwise, include activities that are new (greater than last shown) and unread
                return $activity['id'] > $lastShownId && !$isReadByAdmin;
            });
            
            // Get unread count
            $unreadCount = $activityLogModel->getUnreadCountForAdmin($userId);
            
            log_message('info', "Found " . count($newActivities) . " new activities for admin user {$userId}");
            
            return $this->response->setJSON([
                'success' => true,
                'activities' => $activities,
                'newActivities' => array_values($newActivities),
                'hasNew' => !empty($newActivities),
                'unreadCount' => $unreadCount
            ]);
        }
        
        log_message('info', "No activities found for admin user {$userId}");
        
        return $this->response->setJSON([
            'success' => false,
            'message' => 'No activities',
            'activities' => [],
            'newActivities' => [],
            'hasNew' => false,
            'unreadCount' => 0
        ]);
    }

    /**
     * Mark activity as read for admin
     */
    public function markActivityAsRead($activityId)
    {
        try {
            $userId = session()->get('user-id');
            
            if (!$userId) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'User not authenticated'
                ]);
            }

            $activityLogModel = new ActivityLogModel();
            $result = $activityLogModel->markAsReadByAdmin($activityId, $userId);

            if ($result) {
                // Get updated unread count
                $unreadCount = $activityLogModel->getUnreadCountForAdmin($userId);
                
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Activity marked as read',
                    'unreadCount' => $unreadCount
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to mark activity as read'
                ]);
            }
        } catch (\Exception $e) {
            log_message('error', 'Error marking activity as read: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Mark all activities as read for admin
     */
    public function markAllAsRead()
    {
        try {
            $userId = session()->get('user-id');
            
            if (!$userId) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'User not authenticated'
                ]);
            }

            $activityLogModel = new ActivityLogModel();
            
            // Get all unread admin activities
            $activities = $activityLogModel->getRecentForAdmins(1000, $userId); // Get large number to mark all
            
            $unreadActivityIds = [];
            foreach ($activities as $activity) {
                if (!($activity['is_read_by_admin'] ?? 0)) {
                    $unreadActivityIds[] = $activity['id'];
                }
            }

            // Mark all as read
            $adminReadStatusModel = new \App\Models\AdminReadStatusModel();
            if (!empty($unreadActivityIds)) {
                $adminReadStatusModel->markMultipleAsRead($unreadActivityIds, $userId);
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => 'All activities marked as read',
                'unreadCount' => 0
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error marking all activities as read: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get all activities for admin
     */
    public function getAllActivities()
    {
        try {
            $userId = session()->get('user-id');
            
            if (!$userId) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'User not authenticated'
                ]);
            }

            $limit = $this->request->getGet('limit') ?? 50;
            $offset = $this->request->getGet('offset') ?? 0;

            $activityLogModel = new ActivityLogModel();
            $activities = $activityLogModel->getRecentForAdmins((int)$limit + (int)$offset, $userId);
            
            // Apply offset
            $activities = array_slice($activities, (int)$offset, (int)$limit);

            // Format activities for frontend
            foreach ($activities as &$activity) {
                // Ensure activity_type is explicitly set
                if (!isset($activity['activity_type']) || empty($activity['activity_type'])) {
                    $activity['activity_type'] = $activity['entity_type'] ?? 'unknown';
                }
                
                // Format the created_at time for Philippines timezone (UTC+8)
                $createdAt = new \DateTime($activity['created_at'], new \DateTimeZone('UTC'));
                $createdAt->setTimezone(new \DateTimeZone('Asia/Manila'));
                $activity['created_at_formatted'] = $createdAt->format('Y-m-d H:i:s');
                $activity['created_at_time'] = $createdAt->format('g:i A');
                $activity['created_at_date'] = $createdAt->format('M d, Y');
                
                // Check if activity is read by this admin
                $activity['is_read'] = $activity['is_read_by_admin'] ?? 0;
                
                // Format activity data for frontend
                $activity['activity_icon'] = $this->getActivityIcon($activity['activity_type'] ?? 'unknown', $activity['action'] ?? '');
                $activity['activity_color'] = $this->getActivityColor($activity['activity_type'] ?? 'unknown', $activity['action'] ?? '');
            }

            return $this->response->setJSON([
                'success' => true,
                'activities' => $activities,
                'count' => count($activities)
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error getting all activities: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage(),
                'activities' => []
            ]);
        }
    }

    /**
     * Get unread count for admin
     */
    public function getUnreadCount()
    {
        try {
            $userId = session()->get('user-id');
            
            if (!$userId) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'User not authenticated',
                    'unreadCount' => 0
                ]);
            }

            $activityLogModel = new ActivityLogModel();
            $unreadCount = $activityLogModel->getUnreadCountForAdmin($userId);

            return $this->response->setJSON([
                'success' => true,
                'unreadCount' => $unreadCount
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error getting unread count: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage(),
                'unreadCount' => 0
            ]);
        }
    }

    /**
     * Get icon for activity type and action
     */
    private function getActivityIcon($activityType, $action)
    {
        $icons = [
            'announcement' => [
                'created' => 'fas fa-bullhorn',
                'updated' => 'fas fa-edit',
                'published' => 'fas fa-check-circle',
                'unpublished' => 'fas fa-times-circle'
            ],
            'contribution' => [
                'created' => 'fas fa-plus-circle',
                'updated' => 'fas fa-edit',
                'deleted' => 'fas fa-trash'
            ],
            'payment' => [
                'created' => 'fas fa-money-bill-wave',
                'updated' => 'fas fa-edit',
                'deleted' => 'fas fa-trash',
                'processed' => 'fas fa-check-circle'
            ],
            'payer' => [
                'created' => 'fas fa-user-plus',
                'updated' => 'fas fa-user-edit',
                'deleted' => 'fas fa-user-times'
            ],
            'user' => [
                'created' => 'fas fa-user-plus',
                'updated' => 'fas fa-user-edit',
                'deleted' => 'fas fa-user-times'
            ],
            'payment_request' => [
                'created' => 'fas fa-file-invoice-dollar',
                'approved' => 'fas fa-check-circle',
                'rejected' => 'fas fa-times-circle',
                'processed' => 'fas fa-cog'
            ],
            'refund' => [
                'requested' => 'fas fa-undo',
                'approved' => 'fas fa-check-circle',
                'rejected' => 'fas fa-times-circle',
                'processed' => 'fas fa-cog',
                'completed' => 'fas fa-check-double'
            ]
        ];
        
        return $icons[$activityType][$action] ?? 'fas fa-info-circle';
    }

    /**
     * Get color for activity type and action
     */
    private function getActivityColor($activityType, $action)
    {
        $colors = [
            'announcement' => [
                'created' => 'primary',
                'updated' => 'warning',
                'published' => 'success',
                'unpublished' => 'danger'
            ],
            'contribution' => [
                'created' => 'success',
                'updated' => 'warning',
                'deleted' => 'danger'
            ],
            'payment' => [
                'created' => 'success',
                'updated' => 'warning',
                'deleted' => 'danger',
                'processed' => 'info'
            ],
            'payer' => [
                'created' => 'success',
                'updated' => 'warning',
                'deleted' => 'danger'
            ],
            'user' => [
                'created' => 'success',
                'updated' => 'warning',
                'deleted' => 'danger'
            ],
            'payment_request' => [
                'created' => 'info',
                'approved' => 'success',
                'rejected' => 'danger',
                'processed' => 'primary'
            ],
            'refund' => [
                'requested' => 'warning',
                'approved' => 'success',
                'rejected' => 'danger',
                'processed' => 'info',
                'completed' => 'success'
            ]
        ];
        
        return $colors[$activityType][$action] ?? 'info';
    }
}
