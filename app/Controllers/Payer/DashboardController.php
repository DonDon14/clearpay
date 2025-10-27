<?php

namespace App\Controllers\Payer;

use App\Controllers\BaseController;
use App\Models\PayerModel;
use App\Models\PaymentModel;
use App\Models\AnnouncementModel;
use App\Models\ContributionModel;
use App\Models\ActivityLogModel;

class DashboardController extends BaseController
{
    protected $payerModel;
    protected $paymentModel;
    protected $announcementModel;
    protected $contributionModel;
    protected $activityLogModel;

    public function __construct()
    {
        $this->payerModel = new PayerModel();
        $this->paymentModel = new PaymentModel();
        $this->announcementModel = new AnnouncementModel();
        $this->contributionModel = new ContributionModel();
        $this->activityLogModel = new ActivityLogModel();
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
            users.username as recorded_by_name
        ')
        ->join('payers', 'payers.id = payments.payer_id', 'left')
        ->join('contributions', 'contributions.id = payments.contribution_id', 'left')
        ->join('users', 'users.id = payments.recorded_by', 'left')
        ->where('payments.payer_id', $payerId)
        ->orderBy('payments.payment_date', 'DESC')
        ->findAll();
        
        $data = [
            'title' => 'Payment History',
            'pageTitle' => 'Payment History',
            'pageSubtitle' => 'View all your payment transactions',
            'payments' => $payments
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
}
