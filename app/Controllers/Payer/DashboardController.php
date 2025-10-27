<?php

namespace App\Controllers\Payer;

use App\Controllers\BaseController;
use App\Models\PayerModel;
use App\Models\PaymentModel;
use App\Models\AnnouncementModel;
use App\Models\ContributionModel;
use App\Models\ActivityLogModel;
use App\Models\PaymentRequestModel;

class DashboardController extends BaseController
{
    protected $payerModel;
    protected $paymentModel;
    protected $announcementModel;
    protected $contributionModel;
    protected $activityLogModel;
    protected $paymentRequestModel;

    public function __construct()
    {
        $this->payerModel = new PayerModel();
        $this->paymentModel = new PaymentModel();
        $this->announcementModel = new AnnouncementModel();
        $this->contributionModel = new ContributionModel();
        $this->activityLogModel = new ActivityLogModel();
        $this->paymentRequestModel = new PaymentRequestModel();
    }

    public function index()
    {
        $payerId = session('payer_id');
        
        // Get payer data
        $payer = $this->payerModel->find($payerId);
        
        // Get recent payments
        $recentPayments = $this->paymentModel->where('payer_id', $payerId)
            ->orderBy('payment_date', 'DESC')
            ->limit(5)
            ->findAll();
        
        // Get total paid amount
        $totalPaid = $this->paymentModel->where('payer_id', $payerId)
            ->selectSum('amount_paid')
            ->first();
        
        // Get published announcements for payers
        $announcements = $this->announcementModel->where('status', 'published')
            ->where("(target_audience = 'payers' OR target_audience = 'both' OR target_audience = 'all')")
            ->orderBy('created_at', 'DESC')
            ->limit(3)
            ->findAll();
        
        $data = [
            'title' => 'Dashboard',
            'pageTitle' => 'Dashboard',
            'pageSubtitle' => 'Welcome back, ' . ($payer['payer_name'] ?? 'Payer'),
            'payer' => $payer,
            'recentPayments' => $recentPayments,
            'totalPaid' => $totalPaid['amount_paid'] ?? 0,
            'announcements' => $announcements
        ];
        
        return view('payer/dashboard', $data);
    }

    public function myData()
    {
        $payerId = session('payer_id');
        $payer = $this->payerModel->find($payerId);
        
        $data = [
            'title' => 'My Data',
            'pageTitle' => 'My Data',
            'pageSubtitle' => 'View your personal information',
            'payer' => $payer,
            'payerData' => $payer // Pass to layout for header
        ];
        
        return view('payer/my-data', $data);
    }

    public function updateProfile()
    {
        $payerId = session('payer_id');
        
        if (!$payerId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Payer not authenticated'
            ]);
        }

        $validation = \Config\Services::validation();
        $validation->setRules([
            'email_address' => 'required|valid_email',
            'contact_number' => 'required|min_length[10]|max_length[15]'
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validation->getErrors()
            ]);
        }

        $data = [
            'email_address' => $this->request->getPost('email_address'),
            'contact_number' => $this->request->getPost('contact_number')
        ];

        $result = $this->payerModel->update($payerId, $data);

        if ($result) {
            // Update session data
            session()->set([
                'payer_email' => $data['email_address']
            ]);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Profile updated successfully'
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to update profile'
            ]);
        }
    }

    public function uploadProfilePicture()
    {
        $payerId = session('payer_id');
        
        if (!$payerId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Payer not authenticated'
            ]);
        }

        $file = $this->request->getFile('profile_picture');
        
        if (!$file || !$file->isValid()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No valid file uploaded'
            ]);
        }

        // Validate file type
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        if (!in_array($file->getMimeType(), $allowedTypes)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid file type. Only JPEG, PNG, and GIF are allowed.'
            ]);
        }

        // Validate file size (max 2MB)
        if ($file->getSize() > 2 * 1024 * 1024) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'File size too large. Maximum 2MB allowed.'
            ]);
        }

        // Create upload directory if it doesn't exist
        $uploadPath = FCPATH . 'uploads/profile/';
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        // Generate unique filename
        $newName = 'payer_' . $payerId . '_' . time() . '.' . $file->getExtension();
        
                if ($file->move($uploadPath, $newName)) {
                    // Get current payer data to delete old profile picture
                    $payer = $this->payerModel->find($payerId);
                    $oldProfilePicture = $payer['profile_picture'] ?? null;
                    
                    // Update database with new profile picture path
                    $profilePicturePath = 'uploads/profile/' . $newName;
                    $this->payerModel->update($payerId, ['profile_picture' => $profilePicturePath]);
                    
                    // Update session with new profile picture path
                    session()->set('payer_profile_picture', $profilePicturePath);
                    
                    // Delete old profile picture if it exists
                    if ($oldProfilePicture && file_exists(FCPATH . $oldProfilePicture)) {
                        unlink(FCPATH . $oldProfilePicture);
                    }

                    return $this->response->setJSON([
                        'success' => true,
                        'message' => 'Profile picture uploaded successfully',
                        'profile_picture' => base_url($profilePicturePath)
                    ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to upload profile picture'
            ]);
        }
    }

    public function announcements()
    {
        // Get published announcements for payers
        $announcements = $this->announcementModel->where('status', 'published')
            ->where("(target_audience = 'payers' OR target_audience = 'both' OR target_audience = 'all')")
            ->orderBy('created_at', 'DESC')
            ->findAll();
        
        $data = [
            'title' => 'Announcements',
            'pageTitle' => 'Announcements',
            'pageSubtitle' => 'Stay updated with the latest news',
            'announcements' => $announcements
        ];
        
        return view('payer/announcements', $data);
    }

    public function paymentHistory()
    {
        $payerId = session('payer_id');
        
        // Get payments with all necessary fields for QR receipt
        $payments = $this->paymentModel->select('
            payments.id,
            payments.payer_id,
            payments.contribution_id,
            payments.amount_paid,
            payments.payment_method,
            payments.payment_status,
            payments.reference_number,
            payments.receipt_number,
            payments.qr_receipt_path,
            payments.payment_date,
            payments.created_at,
            payments.updated_at,
            payers.payer_name,
            payers.contact_number,
            payers.email_address,
            contributions.title as contribution_title,
            contributions.description as contribution_description,
            contributions.amount as contribution_amount,
            users.username as recorded_by_name
        ')
        ->join('payers', 'payers.id = payments.payer_id', 'left')
        ->join('contributions', 'contributions.id = payments.contribution_id', 'left')
        ->join('users', 'users.id = payments.recorded_by', 'left')
        ->where('payments.payer_id', $payerId)
        ->orderBy('contributions.title', 'ASC')
        ->orderBy('payments.payment_date', 'DESC')
        ->findAll();
        
        // Group payments by contribution
        $contributionsWithPayments = [];
        foreach ($payments as $payment) {
            $contributionId = $payment['contribution_id'];
            $contributionTitle = $payment['contribution_title'];
            
            if (!isset($contributionsWithPayments[$contributionId])) {
                $contributionsWithPayments[$contributionId] = [
                    'id' => $contributionId,
                    'title' => $contributionTitle,
                    'description' => $payment['contribution_description'],
                    'amount' => $payment['contribution_amount'],
                    'payments' => []
                ];
            }
            
            $contributionsWithPayments[$contributionId]['payments'][] = $payment;
        }
        
        // Calculate total paid for each contribution
        foreach ($contributionsWithPayments as &$contribution) {
            $totalPaid = 0;
            foreach ($contribution['payments'] as $payment) {
                $totalPaid += $payment['amount_paid'];
            }
            $contribution['total_paid'] = $totalPaid;
            $contribution['remaining_amount'] = $contribution['amount'] - $totalPaid;
            $contribution['is_fully_paid'] = $totalPaid >= $contribution['amount'];
            $contribution['is_partially_paid'] = $totalPaid > 0 && $totalPaid < $contribution['amount'];
        }
        
        $data = [
            'title' => 'Payment History',
            'pageTitle' => 'Payment History',
            'pageSubtitle' => 'View all your payment transactions',
            'contributions' => $contributionsWithPayments
        ];
        
        return view('payer/payment-history', $data);
    }

    public function contributions()
    {
        $payerId = session('payer_id');
        
        // Get active contributions
        $contributions = $this->contributionModel->where('status', 'active')
            ->orderBy('created_at', 'DESC')
            ->findAll();
        
        // Get payment data for each contribution (let JavaScript calculate status)
        foreach ($contributions as &$contribution) {
            // Get total paid for this contribution
            $totalPaid = $this->paymentModel->where('payer_id', $payerId)
                ->where('contribution_id', $contribution['id'])
                ->selectSum('amount_paid')
                ->first();
            $contribution['total_paid'] = $totalPaid['amount_paid'] ?? 0;
            $contribution['remaining_balance'] = max(0, $contribution['amount'] - $contribution['total_paid']);
            
            // Don't set payment_status here - let JavaScript calculate it dynamically
            // based on total_paid vs amount comparison
        }
        
        $data = [
            'title' => 'Contributions',
            'pageTitle' => 'Contributions',
            'pageSubtitle' => 'View active contributions and payment status',
            'contributions' => $contributions
        ];
        
        return view('payer/contributions', $data);
    }

    public function getContributionPayments($contributionId)
    {
        $payerId = session('payer_id');
        
        // Get payments for this specific contribution and payer with all necessary fields
        $payments = $this->paymentModel->select('
            payments.id,
            payments.payer_id,
            payments.contribution_id,
            payments.amount_paid,
            payments.payment_method,
            payments.payment_status,
            payments.reference_number,
            payments.receipt_number,
            payments.qr_receipt_path,
            payments.payment_date,
            payments.created_at,
            payments.updated_at,
            payers.payer_name,
            payers.contact_number,
            payers.email_address,
            contributions.title as contribution_title,
            users.username as recorded_by_name
        ')
        ->join('payers', 'payers.id = payments.payer_id', 'left')
        ->join('contributions', 'contributions.id = payments.contribution_id', 'left')
        ->join('users', 'users.id = payments.recorded_by', 'left')
        ->where('payments.payer_id', $payerId)
        ->where('payments.contribution_id', $contributionId)
        ->orderBy('payments.payment_date', 'DESC')
        ->findAll();
        
        // Debug: Log the payments data to see what's being returned
        log_message('info', 'Payer payments for contribution ' . $contributionId . ': ' . json_encode($payments));
        
        return $this->response->setJSON([
            'success' => true,
            'payments' => $payments
        ]);
    }

    public function checkNewActivities()
    {
        // Get the current payer ID from session
        $payerId = session('payer_id');
        
        // Get the last activity ID that was shown to this payer
        $lastShownId = $this->request->getGet('last_shown_id') ?: 0;
        
        // Debug logging
        log_message('info', "Checking for new activities for payer {$payerId}. Last shown ID: {$lastShownId}");
        
        // Get recent activities for this specific payer (limit to 5 for faster loading)
        $activities = $this->activityLogModel->getRecentForPayers(5, $payerId);
        
        log_message('info', "Found " . count($activities) . " activities for payer {$payerId}");
        
        if (!empty($activities)) {
            // Format activities for frontend
            foreach ($activities as &$activity) {
                // Format the created_at time for Philippines timezone (UTC+8)
                $createdAt = new \DateTime($activity['created_at'], new \DateTimeZone('UTC'));
                $createdAt->setTimezone(new \DateTimeZone('Asia/Manila'));
                $activity['created_at_formatted'] = $createdAt->format('Y-m-d H:i:s');
                $activity['created_at_time'] = $createdAt->format('g:i A');
                $activity['created_at_date'] = $createdAt->format('M d, Y');
                
                // Format activity data for frontend
                $activity['activity_icon'] = $this->getActivityIcon($activity['activity_type'], $activity['action']);
                $activity['activity_color'] = $this->getActivityColor($activity['activity_type'], $activity['action']);
            }
            
            // Check if there are new activities (greater than last shown ID)
            $newActivities = array_filter($activities, function($activity) use ($lastShownId) {
                return $activity['id'] > $lastShownId;
            });
            
            log_message('info', "Found " . count($newActivities) . " new activities for payer {$payerId}");
            
            return $this->response->setJSON([
                'success' => true,
                'activities' => $activities,
                'newActivities' => array_values($newActivities),
                'hasNew' => !empty($newActivities)
            ]);
        }
        
        log_message('info', "No activities found for payer {$payerId}");
        
        return $this->response->setJSON([
            'success' => false,
            'message' => 'No activities',
            'activities' => [],
            'newActivities' => [],
            'hasNew' => false
        ]);
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
                'deleted' => 'fas fa-trash'
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
                'approved' => 'fas fa-check-circle',
                'rejected' => 'fas fa-times-circle',
                'processed' => 'fas fa-cog'
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
                'deleted' => 'danger'
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
                'approved' => 'success',
                'rejected' => 'danger',
                'processed' => 'info'
            ]
        ];
        
        return $colors[$activityType][$action] ?? 'info';
    }

    public function markActivityAsRead($activityId)
    {
        try {
            $payerId = session('payer_id');
            
            if (!$payerId) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Payer not authenticated'
                ]);
            }
            
            $result = $this->activityLogModel->markAsRead($activityId, $payerId);
            
            if ($result) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Activity marked as read'
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to mark activity as read'
                ]);
            }
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    public function getAllActivities()
    {
        try {
            // Get the current payer ID from session
            $payerId = session('payer_id');
            
            // Get all activities for this specific payer
            $activities = $this->activityLogModel->getRecentForPayers(50, $payerId); // Get more for the full modal
            
            if (!empty($activities)) {
                // Format activities for frontend
                foreach ($activities as &$activity) {
                    // Format the created_at time for Philippines timezone (UTC+8)
                    $createdAt = new \DateTime($activity['created_at'], new \DateTimeZone('UTC'));
                    $createdAt->setTimezone(new \DateTimeZone('Asia/Manila'));
                    $activity['created_at_formatted'] = $createdAt->format('Y-m-d H:i:s');
                    $activity['created_at_time'] = $createdAt->format('g:i A');
                    $activity['created_at_date'] = $createdAt->format('M d, Y');
                    
                    // Format activity data for frontend
                    $activity['activity_icon'] = $this->getActivityIcon($activity['activity_type'], $activity['action']);
                    $activity['activity_color'] = $this->getActivityColor($activity['activity_type'], $activity['action']);
                }
            }
            
            return $this->response->setJSON([
                'success' => true,
                'activities' => $activities
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    public function paymentRequests()
    {
        $payerId = session('payer_id');
        
        // Get active contributions for the dropdown
        $contributions = $this->contributionModel->where('status', 'active')
            ->orderBy('title', 'ASC')
            ->findAll();
        
        // Get payer's payment requests
        $paymentRequests = $this->paymentRequestModel->getRequestsByPayer($payerId);
        
        $data = [
            'title' => 'Payment Requests',
            'pageTitle' => 'Payment Requests',
            'pageSubtitle' => 'Submit online payment requests',
            'contributions' => $contributions,
            'paymentRequests' => $paymentRequests
        ];
        
        return view('payer/payment-requests', $data);
    }

    public function submitPaymentRequest()
    {
        // Check if it's an AJAX request or if it's a POST request (more flexible)
        if (!$this->request->isAJAX() && $this->request->getMethod() !== 'POST') {
            log_message('error', 'Invalid request method for payment request submission');
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid request method'
            ]);
        }

        $payerId = session('payer_id');
        
        if (!$payerId) {
            log_message('error', 'No payer ID in session for payment request submission');
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Please log in to submit payment requests'
            ]);
        }

        $validation = \Config\Services::validation();
        $validation->setRules([
            'contribution_id' => 'required|integer',
            'requested_amount' => 'required|decimal|greater_than[0]',
            'payment_method' => 'required|in_list[online,bank_transfer,gcash,paymaya]',
            'notes' => 'permit_empty|max_length[500]'
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validation->getErrors()
            ]);
        }

        try {
            // Get contribution details
            $contribution = $this->contributionModel->find($this->request->getPost('contribution_id'));
            if (!$contribution) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Contribution not found'
                ]);
            }

            // Check if amount is valid
            $requestedAmount = (float)$this->request->getPost('requested_amount');
            if ($requestedAmount > $contribution['amount']) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Requested amount cannot exceed contribution amount (₱' . number_format($contribution['amount'], 2) . ')'
                ]);
            }

            // Handle file upload for proof of payment
            $proofOfPaymentPath = null;
            $file = $this->request->getFile('proof_of_payment');
            
            if ($file && $file->isValid() && !$file->hasMoved()) {
                $uploadPath = FCPATH . 'uploads/payment_proofs/';
                
                // Create directory if it doesn't exist
                if (!is_dir($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
                }
                
                $newName = 'proof_' . $payerId . '_' . time() . '.' . $file->getExtension();
                $file->move($uploadPath, $newName);
                $proofOfPaymentPath = 'uploads/payment_proofs/' . $newName;
            }

            // Create payment request
            $requestData = [
                'payer_id' => $payerId,
                'contribution_id' => $this->request->getPost('contribution_id'),
                'requested_amount' => $requestedAmount,
                'payment_method' => $this->request->getPost('payment_method'),
                'proof_of_payment_path' => $proofOfPaymentPath,
                'notes' => $this->request->getPost('notes'),
                'status' => 'pending'
            ];

            $requestId = $this->paymentRequestModel->insert($requestData);
            
            if ($requestId) {
                // Log activity
                $activityLogger = new \App\Services\ActivityLogger();
                $activityLogger->logActivity(
                    'create',
                    'payment_request',
                    $requestId,
                    "Submitted payment request for {$contribution['title']} - ₱" . number_format($requestedAmount, 2),
                    $payerId
                );

                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Payment request submitted successfully! Reference: REQ-' . date('Ymd') . '-' . strtoupper(substr(md5($requestId), 0, 12)),
                    'request_id' => $requestId
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to submit payment request. Please try again.'
                ]);
            }

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    public function getContributionDetails()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid request method'
            ]);
        }

        $contributionId = $this->request->getGet('contribution_id');
        
        if (!$contributionId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Contribution ID is required'
            ]);
        }

        try {
            $contribution = $this->contributionModel->find($contributionId);
            
            if (!$contribution) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Contribution not found'
                ]);
            }

            // Get payer's current payments for this contribution
            $payerId = session('payer_id');
            $totalPaid = $this->paymentModel->where('payer_id', $payerId)
                ->where('contribution_id', $contributionId)
                ->selectSum('amount_paid')
                ->first();

            $totalPaidAmount = $totalPaid['amount_paid'] ?? 0;
            $remainingAmount = $contribution['amount'] - $totalPaidAmount;

            return $this->response->setJSON([
                'success' => true,
                'contribution' => [
                    'id' => $contribution['id'],
                    'title' => $contribution['title'],
                    'description' => $contribution['description'],
                    'amount' => $contribution['amount'],
                    'total_paid' => $totalPaidAmount,
                    'remaining_amount' => $remainingAmount,
                    'is_fully_paid' => $remainingAmount <= 0
                ]
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }
}
