<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\PaymentModel;
use App\Models\ContributionModel;
use App\Models\PaymentRequestModel;
use App\Models\PaymentMethodModel;
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
            ->select('payments.*, payers.payer_id as payer_student_id, payers.payer_name, payers.contact_number, payers.email_address, payers.profile_picture, contributions.title as contribution_title, contributions.id as contrib_id')
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
                // Get the payment record to get the receipt number
                $paymentModel = new PaymentModel();
                $paymentRecord = $paymentModel->find($paymentId);
                $receiptNumber = $paymentRecord['receipt_number'] ?? 'N/A';
                
                // Mark payment request as approved
                $result = $paymentRequestModel->approveRequest($requestId, $processedBy, $adminNotes);
                
                if ($result) {
                    // Log activity for payer notification (using ActivityLogger)
                    $activityLogger = new ActivityLogger();
                    $activityLogger->logPaymentRequest('approved', $request);
                    
                    // Log activity for admin dashboard (user_activities table)
                    $this->logUserActivity('approved', 'payment_request', $requestId, "Payment request approved and payment recorded (Receipt Number: {$receiptNumber})");
                    
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
            
            $result = $paymentRequestModel->rejectRequest($requestId, $processedBy, $adminNotes);
            
            if ($result) {
                // Log activity for payer notification (using ActivityLogger)
                $activityLogger = new ActivityLogger();
                $activityLogger->logPaymentRequest('rejected', $request);
                
                // Log activity for admin dashboard (user_activities table)
                $this->logUserActivity('rejected', 'payment_request', $requestId, "Payment request rejected" . ($adminNotes ? " - Reason: {$adminNotes}" : ""));
                
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
                // Mark payment request as processed
                $paymentRequestModel->processRequest($requestId, $processedBy, $adminNotes);
                
                // Log activity for payer notification (using ActivityLogger)
                $activityLogger = new ActivityLogger();
                $activityLogger->logPaymentRequest('processed', $request);
                
                // Get the payment record to get the receipt number
                $paymentModel = new PaymentModel();
                $paymentRecord = $paymentModel->find($paymentId);
                $receiptNumber = $paymentRecord['receipt_number'] ?? 'N/A';
                
                // Log activity for admin dashboard (user_activities table)
                $this->logUserActivity('processed', 'payment_request', $requestId, "Payment request processed and payment recorded (Receipt Number: {$receiptNumber})");
                
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
        $contributionAmount = $contribution ? $contribution['amount'] : 0;
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
            'is_partial_payment' => $isPartial ? 1 : 0,
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
}
