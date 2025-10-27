<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\PaymentModel;
use App\Models\ContributionModel;
use App\Models\PaymentRequestModel;
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
            ->orderBy('payments.payment_date', 'DESC')
            ->limit(6)
            ->findAll(); // last 6 payments

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
        
        // Get all payment requests with details
        $paymentRequests = $paymentRequestModel->getRequestsWithDetails();
        
        // Get stats
        $pendingCount = $paymentRequestModel->where('status', 'pending')->countAllResults();
        $approvedCount = $paymentRequestModel->where('status', 'approved')->countAllResults();
        $rejectedCount = $paymentRequestModel->where('status', 'rejected')->countAllResults();
        $processedCount = $paymentRequestModel->where('status', 'processed')->countAllResults();

        $data = [
            'title' => 'Payment Requests',
            'pageTitle' => 'Payment Requests Management',
            'pageSubtitle' => 'Manage online payment requests from payers',
            'paymentRequests' => $paymentRequests,
            'stats' => [
                'pending' => $pendingCount,
                'approved' => $approvedCount,
                'rejected' => $rejectedCount,
                'processed' => $processedCount,
                'total' => count($paymentRequests)
            ]
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

            // Create actual payment record
            $paymentModel = new PaymentModel();
            $paymentData = [
                'payer_id' => $request['payer_id'],
                'contribution_id' => $request['contribution_id'],
                'amount_paid' => $request['requested_amount'],
                'payment_method' => $request['payment_method'],
                'payment_status' => 'fully paid',
                'reference_number' => 'REQ-' . date('Ymd') . '-' . strtoupper(substr(md5($requestId), 0, 12)),
                'receipt_number' => 'RCPT-' . date('Ymd') . '-' . strtoupper(substr(md5($requestId . time()), 0, 12)),
                'payment_date' => date('Y-m-d H:i:s'),
                'recorded_by' => $processedBy
            ];

            $paymentId = $paymentModel->insert($paymentData);
            
            if ($paymentId) {
                // Mark payment request as approved
                $result = $paymentRequestModel->approveRequest($requestId, $processedBy, $adminNotes);
                
                if ($result) {
                    // Log the activity for admin (user activities)
                    $activityLogger = new ActivityLogger();
                    $activityLogger->logActivity(
                        'approved',
                        'payment_request',
                        $requestId,
                        "Payment request approved and payment recorded (Receipt Number: {$paymentData['receipt_number']})",
                        $request['payer_id']
                    );
                    
                    // Log activity for payer notification (using ActivityLogModel directly)
                    $activityLogModel = new \App\Models\ActivityLogModel();
                    $activityLogModel->insert([
                        'activity_type' => 'payment_request',
                        'entity_type' => 'payment_request',
                        'entity_id' => $requestId,
                        'action' => 'approved',
                        'title' => 'Payment Request Approved',
                        'description' => "Your payment request for ₱" . number_format($request['requested_amount'], 2) . " has been approved and payment recorded. Receipt Number: {$paymentData['receipt_number']}",
                        'user_id' => $processedBy,
                        'user_type' => 'admin',
                        'payer_id' => $request['payer_id'],
                        'target_audience' => 'payers',
                        'is_read' => false,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
                    
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
                // Log the activity for admin (user activities)
                $activityLogger = new ActivityLogger();
                $activityLogger->logActivity(
                    'rejected',
                    'payment_request',
                    $requestId,
                    "Payment request rejected: {$adminNotes}",
                    $request['payer_id']
                );
                
                // Log activity for payer notification (using ActivityLogModel directly)
                $activityLogModel = new \App\Models\ActivityLogModel();
                $activityLogModel->insert([
                    'activity_type' => 'payment_request',
                    'entity_type' => 'payment_request',
                    'entity_id' => $requestId,
                    'action' => 'rejected',
                    'title' => 'Payment Request Rejected',
                    'description' => "Your payment request for ₱" . number_format($request['requested_amount'], 2) . " has been rejected. Reason: {$adminNotes}",
                    'user_id' => $processedBy,
                    'user_type' => 'admin',
                    'payer_id' => $request['payer_id'],
                    'target_audience' => 'payers',
                    'is_read' => false,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
                
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

            // Create actual payment record
            $paymentModel = new PaymentModel();
            $paymentData = [
                'payer_id' => $request['payer_id'],
                'contribution_id' => $request['contribution_id'],
                'amount_paid' => $request['requested_amount'],
                'payment_method' => $request['payment_method'],
                'payment_status' => 'fully paid',
                'reference_number' => 'REQ-' . date('Ymd') . '-' . strtoupper(substr(md5($requestId), 0, 12)),
                'receipt_number' => 'RCPT-' . date('Ymd') . '-' . strtoupper(substr(md5($requestId . time()), 0, 12)),
                'payment_date' => date('Y-m-d H:i:s'),
                'recorded_by' => $processedBy
            ];

            $paymentId = $paymentModel->insert($paymentData);
            
            if ($paymentId) {
                // Mark payment request as processed
                $paymentRequestModel->processRequest($requestId, $processedBy, $adminNotes);
                
                // Log the activity for admin (user activities)
                $activityLogger = new ActivityLogger();
                $activityLogger->logActivity(
                    'processed',
                    'payment_request',
                    $requestId,
                    "Payment request processed and payment recorded (Receipt Number: {$paymentData['receipt_number']})",
                    $request['payer_id']
                );
                
                // Log activity for payer notification (using ActivityLogModel directly)
                $activityLogModel = new \App\Models\ActivityLogModel();
                $activityLogModel->insert([
                    'activity_type' => 'payment_request',
                    'entity_type' => 'payment_request',
                    'entity_id' => $requestId,
                    'action' => 'processed',
                    'title' => 'Payment Request Processed',
                    'description' => "Your payment request for ₱" . number_format($request['requested_amount'], 2) . " has been processed and payment recorded. Receipt Number: {$paymentData['receipt_number']}",
                    'user_id' => $processedBy,
                    'user_type' => 'admin',
                    'payer_id' => $request['payer_id'],
                    'target_audience' => 'payers',
                    'is_read' => false,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
                
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
}
