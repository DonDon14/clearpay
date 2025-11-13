<?php

namespace App\Controllers\Payer;

use App\Controllers\BaseController;
use App\Models\PayerModel;
use App\Models\PaymentModel;
use App\Models\AnnouncementModel;
use App\Models\ContributionModel;
use App\Models\ActivityLogModel;
use App\Models\PaymentRequestModel;
use App\Models\RefundModel;
use App\Models\RefundMethodModel;

class DashboardController extends BaseController
{
    protected $payerModel;
    protected $paymentModel;
    protected $announcementModel;
    protected $contributionModel;
    protected $activityLogModel;
    protected $paymentRequestModel;
    protected $refundModel;

    public function __construct()
    {
        $this->payerModel = new PayerModel();
        $this->paymentModel = new PaymentModel();
        $this->announcementModel = new AnnouncementModel();
        $this->contributionModel = new ContributionModel();
        $this->activityLogModel = new ActivityLogModel();
        $this->paymentRequestModel = new PaymentRequestModel();
        $this->refundModel = new RefundModel();
    }

    public function index()
    {
        // Check if payer is logged in (filter handles this, but double-check for safety)
        $payerId = session('payer_id');
        
        if (!$payerId) {
            return redirect()->to('payer/login')
                ->with('error', 'Please login to access the dashboard');
        }
        
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
        
        // Normalize profile picture path with fallback
        $payer['profile_picture'] = $this->normalizeProfilePicturePath($payer['profile_picture'] ?? null, $payer['id'] ?? null, null, 'payer');

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
        
        // Normalize profile picture path with fallback
        $payer['profile_picture'] = $this->normalizeProfilePicturePath($payer['profile_picture'] ?? null, $payer['id'] ?? null, null, 'payer');

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
        // Check if this is an API request (OPTIONS preflight or from Flutter app)
        $isApiRequest = $this->request->getMethod() === 'OPTIONS' || 
                       $this->request->getHeader('X-Requested-With') === 'XMLHttpRequest' ||
                       $this->request->getHeader('Accept') === 'application/json';
        
        // Set CORS headers for all requests (needed for Flutter web)
        $this->response->setHeader('Access-Control-Allow-Origin', '*');
        $this->response->setHeader('Access-Control-Allow-Methods', 'POST, OPTIONS');
        $this->response->setHeader('Access-Control-Allow-Headers', 'Content-Type, Accept, Authorization, X-Requested-With, Origin');
        $this->response->setHeader('Access-Control-Max-Age', '7200');
        
        // Handle OPTIONS preflight request
        if ($this->request->getMethod() === 'OPTIONS') {
            return $this->response->setStatusCode(200)->setBody('');
        }
        
        // Get payer ID - try JSON body first (for API requests), then POST, then session
        $payerId = null;
        $jsonData = null;
        
        // Try to decode JSON body first (for API requests)
        $requestBody = $this->request->getBody();
        if (!empty($requestBody)) {
            $jsonData = json_decode($requestBody, true);
            if ($jsonData && isset($jsonData['payer_id'])) {
                $payerId = $jsonData['payer_id'];
            }
        }
        
        // If not in JSON body, try POST data
        if (!$payerId) {
            $payerId = $this->request->getPost('payer_id');
        }
        
        // If still not found, try session (for web requests)
        if (!$payerId) {
            $payerId = session('payer_id');
        }
        
        if (!$payerId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Payer not authenticated'
            ]);
        }

        // Get data from JSON body (if available) or POST
        if ($jsonData && (isset($jsonData['email_address']) || isset($jsonData['contact_number']))) {
            // Data is in JSON body
            $emailAddress = $jsonData['email_address'] ?? null;
            $contactNumber = $jsonData['contact_number'] ?? null;
        } else {
            // Data is in POST
            $emailAddress = $this->request->getPost('email_address');
            $contactNumber = $this->request->getPost('contact_number');
        }
        
        // Prepare data for validation
        $validationData = [
            'email_address' => $emailAddress,
            'contact_number' => $contactNumber,
        ];
        
        $validation = \Config\Services::validation();
        $validation->setRules([
            'email_address' => 'permit_empty|valid_email',
            'contact_number' => 'permit_empty|min_length[10]|max_length[15]'
        ]);

        if (!$validation->run($validationData)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validation->getErrors()
            ]);
        }

        // Only update fields that are provided
        $data = [];
        
        if ($emailAddress !== null && $emailAddress !== '') {
            $data['email_address'] = $emailAddress;
        }
        if ($contactNumber !== null && $contactNumber !== '') {
            $data['contact_number'] = $contactNumber;
        }
        
        // If no fields to update, return success (allows profile picture-only updates)
        if (empty($data)) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'No changes to update'
            ]);
        }

        // Get old payer data for activity logging
        $oldPayerData = $this->payerModel->find($payerId);
        
        $result = $this->payerModel->update($payerId, $data);

        if ($result) {
            // Update session data if email was updated
            if (isset($data['email_address'])) {
                session()->set([
                    'payer_email' => $data['email_address']
                ]);
            }

            // Log payer profile update activity for admin notification
            try {
                $activityLogger = new \App\Services\ActivityLogger();
                $updatedPayerData = array_merge($oldPayerData, $data, ['id' => $payerId]);
                $activityLogger->logPayer('updated', $updatedPayerData, $oldPayerData);
            } catch (\Exception $e) {
                log_message('error', 'Failed to log payer profile update activity: ' . $e->getMessage());
            }

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
        // Check if this is an API request
        $isApiRequest = strpos($this->request->getUri()->getPath(), '/api/') !== false;
        
        // Set CORS headers if it's an API endpoint
        if ($isApiRequest) {
            $this->response->setHeader('Access-Control-Allow-Origin', '*');
            $this->response->setHeader('Access-Control-Allow-Methods', 'POST, OPTIONS');
            $this->response->setHeader('Access-Control-Allow-Headers', 'Content-Type, Accept, Authorization, X-Requested-With, Origin');
            $this->response->setHeader('Access-Control-Max-Age', '7200');
        }
        
        // Get payer ID from session (web) or POST data (API)
        if ($isApiRequest) {
            $payerId = $this->request->getPost('payer_id');
            if (!$payerId) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Payer ID is required'
                ]);
            }
        } else {
            $payerId = session('payer_id');
            if (!$payerId) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Payer not authenticated'
                ]);
            }
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

        // Try Cloudinary upload first (if configured), fallback to local storage
        $cloudinaryService = new \App\Services\CloudinaryService();
        $profilePicturePath = null;
        $profilePictureUrl = null;
        $oldProfilePicture = null;
        
        // Get current payer data for old profile picture deletion
        $payer = $this->payerModel->find($payerId);
        $oldProfilePicture = $payer['profile_picture'] ?? null;
        $oldPayerData = $this->payerModel->find($payerId);
        
        if ($cloudinaryService->isConfigured()) {
            // Upload to Cloudinary
            log_message('error', 'Cloudinary is configured, attempting upload for payer ID: ' . $payerId);
            log_message('error', 'File info - Name: ' . $file->getName() . ', Size: ' . $file->getSize() . ', MimeType: ' . $file->getMimeType());
            
            // Generate public ID for Cloudinary
            $publicId = 'payer_' . $payerId . '_' . time();
            log_message('error', 'Generated public ID: ' . $publicId);
            
            $cloudinaryResult = $cloudinaryService->uploadFile($file, 'profile', $publicId);
            
            log_message('error', 'Cloudinary upload result type: ' . gettype($cloudinaryResult));
            if (is_array($cloudinaryResult)) {
                log_message('error', 'Cloudinary upload result keys: ' . implode(', ', array_keys($cloudinaryResult)));
                log_message('error', 'Cloudinary upload result: ' . json_encode($cloudinaryResult));
            } else {
                log_message('error', 'Cloudinary upload result is not array: ' . var_export($cloudinaryResult, true));
            }
            
            if ($cloudinaryResult && is_array($cloudinaryResult) && isset($cloudinaryResult['url'])) {
                // Cloudinary upload successful
                $profilePictureUrl = $cloudinaryResult['url'];
                $profilePicturePath = $profilePictureUrl; // Store full Cloudinary URL in database
                
                log_message('error', 'Profile picture uploaded to Cloudinary successfully: ' . $profilePictureUrl);
                
                // Delete old profile picture from Cloudinary if it exists
                if ($oldProfilePicture && $cloudinaryService->isCloudinaryUrl($oldProfilePicture)) {
                    $oldPublicId = $cloudinaryService->extractPublicId($oldProfilePicture);
                    if ($oldPublicId) {
                        $cloudinaryService->delete($oldPublicId);
                        log_message('info', 'Deleted old Cloudinary profile picture: ' . $oldPublicId);
                    }
                }
            } else {
                // Cloudinary upload failed, fallback to local storage
                log_message('error', 'Cloudinary upload failed or returned invalid result, falling back to local storage');
                log_message('error', 'Result was: ' . var_export($cloudinaryResult, true));
                $cloudinaryService = null; // Force local storage
            }
        } else {
            log_message('error', 'Cloudinary is NOT configured - checking why...');
            log_message('error', 'Cloudinary service isConfigured() returned: false');
        }
        
        // Fallback to local storage if Cloudinary is not configured or upload failed
        if (!$profilePicturePath) {
            log_message('info', 'Using local storage for profile picture upload');
            
            // Create upload directory if it doesn't exist
            $uploadPath = FCPATH . 'uploads/profile/';
            
            // Ensure parent directory exists
            $parentDir = dirname($uploadPath);
            if (!is_dir($parentDir)) {
                if (!mkdir($parentDir, 0755, true)) {
                    log_message('error', 'Failed to create parent directory: ' . $parentDir);
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Error: Could not create parent upload directory. Please contact administrator.'
                    ]);
                }
            }
            
            if (!is_dir($uploadPath)) {
                if (!mkdir($uploadPath, 0755, true)) {
                    log_message('error', 'Failed to create profile upload directory: ' . $uploadPath);
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Error: Could not create upload directory. Please contact administrator.'
                    ]);
                }
                chmod($uploadPath, 0755);
            }
            
            // Check if directory is writable
            if (!is_writable($uploadPath)) {
                @chmod($uploadPath, 0755);
                if (!is_writable($uploadPath)) {
                    log_message('error', 'Profile upload directory is not writable: ' . $uploadPath);
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Error: Upload directory is not writable. Please contact administrator.'
                    ]);
                }
            }

            // Generate unique filename
            $newName = 'payer_' . $payerId . '_' . time() . '.' . $file->getExtension();
            
            if (!$file->move($uploadPath, $newName)) {
                $error = $file->getErrorString() ?: 'Unknown error';
                log_message('error', 'Profile picture upload failed - Error: ' . $error);
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Error: Could not save file. ' . $error
                ]);
            }
            
            // Verify file exists
            $fullFilePath = $uploadPath . $newName;
            if (!file_exists($fullFilePath)) {
                log_message('error', 'Profile picture upload failed - File not found after move: ' . $fullFilePath);
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Error: File was not saved correctly. Please try again.'
                ]);
            }
            
            $profilePicturePath = 'uploads/profile/' . $newName;
            $profilePictureUrl = base_url($profilePicturePath);
            
            // Delete old local profile picture if it exists
            if ($oldProfilePicture && strpos($oldProfilePicture, 'res.cloudinary.com') === false && file_exists(FCPATH . $oldProfilePicture)) {
                unlink(FCPATH . $oldProfilePicture);
            }
        }
        
        // Update database with profile picture path/URL
        $updateResult = $this->payerModel->update($payerId, ['profile_picture' => $profilePicturePath]);
        
        // Verify database update was successful
        if (!$updateResult) {
            log_message('error', 'Profile picture database update failed for payer ID: ' . $payerId);
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: Failed to update database. Please try again.'
            ]);
        }
        
        // Verify the path was saved correctly in database
        $updatedPayer = $this->payerModel->find($payerId);
        if (empty($updatedPayer['profile_picture'])) {
            log_message('error', 'Profile picture path not saved in database for payer ID: ' . $payerId);
        } else {
            log_message('info', 'Profile picture successfully saved and database updated for payer ID: ' . $payerId . ', Path: ' . $updatedPayer['profile_picture']);
        }
        
        // Update session with new profile picture path
        session()->set('payer_profile_picture', $profilePicturePath);

        // Log payer profile picture update activity for admin notification
        try {
            $activityLogger = new \App\Services\ActivityLogger();
            $updatedPayerData = array_merge($oldPayerData, ['profile_picture' => $profilePicturePath, 'id' => $payerId]);
            $activityLogger->logPayer('updated', $updatedPayerData, $oldPayerData);
        } catch (\Exception $e) {
            log_message('error', 'Failed to log payer profile picture update activity: ' . $e->getMessage());
        }

        // Return appropriate URL based on storage type
        $returnUrl = (strpos($profilePicturePath, 'res.cloudinary.com') !== false)
            ? $profilePicturePath 
            : base_url($profilePicturePath);
        
        return $this->response->setJSON([
            'success' => true,
            'message' => 'Profile picture uploaded successfully',
            'profile_picture' => $returnUrl // Return full URL (Cloudinary URL or local base_url)
        ]);
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
        
        // Calculate total paid and refund statuses for each contribution
        foreach ($contributionsWithPayments as &$contribution) {
            $totalPaid = 0;
            foreach ($contribution['payments'] as &$payment) {
                $totalPaid += $payment['amount_paid'];
                
                // Get refund status for each payment
                $payment['refund_status'] = $this->paymentModel->getPaymentRefundStatus($payment['id']);
                $payment['total_refunded'] = $this->paymentModel->getPaymentTotalRefunded($payment['id']);
                $payment['available_refund'] = (float)$payment['amount_paid'] - (float)$payment['total_refunded'];
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
        
        // Get all contributions (both active and inactive)
        $allContributions = $this->contributionModel
            ->orderBy('created_at', 'DESC')
            ->findAll();
        
        // Filter contributions:
        // - Show active contributions (always show)
        // - Show inactive contributions ONLY if payer has transactions for them
        $contributions = [];
        foreach ($allContributions as $contribution) {
            if ($contribution['status'] === 'active') {
                // Always show active contributions
                $contributions[] = $contribution;
            } else {
                // For inactive contributions, check if payer has transactions
                $hasTransactions = $this->paymentModel
                    ->where('payer_id', $payerId)
                    ->where('contribution_id', $contribution['id'])
                    ->where('deleted_at', null)
                    ->countAllResults() > 0;
                
                if ($hasTransactions) {
                    // Show inactive contribution only if payer has transactions
                    $contributions[] = $contribution;
                }
                // If no transactions, don't include this inactive contribution
            }
        }
        
        // Get payment data for each contribution with payment groups
        foreach ($contributions as &$contribution) {
            // Get payment groups for this contribution
            $contributionAmount = $contribution['amount'];
            $paymentGroups = $this->paymentModel->select('
                COALESCE(payment_sequence, 1) as payment_sequence,
                SUM(amount_paid) as total_paid,
                COUNT(id) as payment_count,
                MAX(payment_date) as last_payment_date,
                MIN(payment_date) as first_payment_date,
                CASE 
                    WHEN SUM(amount_paid) >= ' . $contributionAmount . ' THEN \'fully paid\'
                    WHEN SUM(amount_paid) > 0 THEN \'partial\'
                    ELSE \'unpaid\'
                END as computed_status,
                ' . $contributionAmount . ' - SUM(amount_paid) as remaining_balance
            ')
            ->where('payer_id', $payerId)
            ->where('contribution_id', $contribution['id'])
            ->where('deleted_at', null)
            ->groupBy('COALESCE(payment_sequence, 1)')
            ->orderBy('payment_sequence', 'ASC')
            ->findAll();
            
            // Add refund statuses to payment groups
            $refundModel = new \App\Models\RefundModel();
            foreach ($paymentGroups as &$group) {
                // Get all payments in this group
                $groupPayments = $this->paymentModel
                    ->where('payer_id', $payerId)
                    ->where('contribution_id', $contribution['id'])
                    ->where('COALESCE(payment_sequence, 1)', $group['payment_sequence'])
                    ->where('deleted_at', null)
                    ->findAll();
                
                // Calculate total refunded for this group
                $totalRefunded = 0;
                foreach ($groupPayments as $payment) {
                    $refunds = $refundModel
                        ->selectSum('refund_amount')
                        ->where('payment_id', $payment['id'])
                        ->where('status', 'completed')
                        ->first();
                    $totalRefunded += (float)($refunds['refund_amount'] ?? 0);
                }
                
                $group['total_refunded'] = $totalRefunded;
                
                // Determine refund status for the group
                if ($totalRefunded >= (float)$group['total_paid']) {
                    $group['refund_status'] = 'fully_refunded';
                } elseif ($totalRefunded > 0) {
                    $group['refund_status'] = 'partially_refunded';
                } else {
                    $group['refund_status'] = 'no_refund';
                }
            }
            
            $contribution['payment_groups'] = $paymentGroups;
            
            // Calculate overall totals
            $totalPaid = array_sum(array_column($paymentGroups, 'total_paid'));
            $contribution['total_paid'] = $totalPaid;
            $contribution['remaining_balance'] = max(0, $contribution['amount'] - $totalPaid);
        }
        
        $data = [
            'title' => 'Contributions',
            'pageTitle' => 'Contributions',
            'pageSubtitle' => 'View contributions and payment status',
            'contributions' => $contributions
        ];
        
        return view('payer/contributions', $data);
    }

    public function getContributionPayments($contributionId)
    {
        // Check if this is an API request
        $isApiRequest = strpos($this->request->getUri()->getPath(), '/api/') !== false;
        
        // Set CORS headers if it's an API endpoint
        if ($isApiRequest) {
            $this->response->setHeader('Access-Control-Allow-Origin', '*');
            $this->response->setHeader('Access-Control-Allow-Methods', 'GET, OPTIONS');
            $this->response->setHeader('Access-Control-Allow-Headers', 'Content-Type, Accept, Authorization, X-Requested-With, Origin');
            $this->response->setHeader('Access-Control-Max-Age', '7200');
        }
        
        // Get payer_id from query parameter for API requests, or from session for web requests
        if ($isApiRequest) {
            $payerId = $this->request->getGet('payer_id');
            if (!$payerId) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Payer ID is required'
                ]);
            }
        } else {
            $payerId = session('payer_id');
            if (!$payerId) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Not authenticated'
                ]);
            }
        }
        
        $paymentSequence = $this->request->getGet('sequence'); // Get payment sequence filter
        
        // Get payments for this specific contribution and payer with all necessary fields
        $builder = $this->paymentModel->select('
            payments.id,
            payments.payer_id,
            payments.contribution_id,
            payments.amount_paid,
            payments.payment_method,
            payments.payment_status,
            payments.payment_sequence,
            payments.reference_number,
            payments.receipt_number,
            payments.qr_receipt_path,
            payments.payment_date,
            payments.created_at,
            payments.updated_at,
            payers.payer_name,
            payers.contact_number,
            payers.email_address,
            payers.payer_id as payer_student_id,
            contributions.title as contribution_title,
            contributions.description as contribution_description,
            contributions.amount as contribution_amount,
            contributions.category as contribution_category,
            contributions.created_at as contribution_created_at,
            users.username as recorded_by_name
        ')
        ->join('payers', 'payers.id = payments.payer_id', 'left')
        ->join('contributions', 'contributions.id = payments.contribution_id', 'left')
        ->join('users', 'users.id = payments.recorded_by', 'left')
        ->where('payments.payer_id', $payerId)
        ->where('payments.contribution_id', $contributionId);
        
        // Filter by payment sequence if provided
        if ($paymentSequence) {
            $builder->where('COALESCE(payments.payment_sequence, 1)', $paymentSequence);
        }
        
        $payments = $builder->orderBy('payments.payment_date', 'DESC')->findAll();
        
        // Debug: Log the payments data to see what's being returned
        log_message('info', 'Payer payments for contribution ' . $contributionId . ': ' . json_encode($payments));
        
        return $this->response->setJSON([
            'success' => true,
            'payments' => $payments
        ]);
    }

    public function checkNewActivities()
    {
        // Check if this is an API request
        $isApiRequest = strpos($this->request->getUri()->getPath(), '/api/') !== false;
        
        // Set CORS headers if it's an API endpoint
        if ($isApiRequest) {
            $this->response->setHeader('Access-Control-Allow-Origin', '*');
            $this->response->setHeader('Access-Control-Allow-Methods', 'GET, OPTIONS');
            $this->response->setHeader('Access-Control-Allow-Headers', 'Content-Type, Accept, Authorization, X-Requested-With, Origin');
            $this->response->setHeader('Access-Control-Max-Age', '7200');
        }
        
        // Get the current payer ID from session (web) or query parameter (API)
        if ($isApiRequest) {
            $payerId = $this->request->getGet('payer_id');
            if (!$payerId) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Payer ID is required'
                ]);
            }
        } else {
            $payerId = session('payer_id');
            if (!$payerId) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Not authenticated'
                ]);
            }
        }
        
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
            'success' => true,
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
        // Check if this is an API request
        $isApiRequest = strpos($this->request->getUri()->getPath(), '/api/') !== false;
        
        // Set CORS headers if it's an API endpoint
        if ($isApiRequest) {
            $this->response->setHeader('Access-Control-Allow-Origin', '*');
            $this->response->setHeader('Access-Control-Allow-Methods', 'POST, OPTIONS');
            $this->response->setHeader('Access-Control-Allow-Headers', 'Content-Type, Accept, Authorization, X-Requested-With, Origin');
            $this->response->setHeader('Access-Control-Max-Age', '7200');
        }
        
        // Handle OPTIONS preflight request
        if ($this->request->getMethod() === 'OPTIONS') {
            return $this->response->setStatusCode(200)->setBody('');
        }
        
        try {
            // Get payer ID from session (web) or POST data (API)
            if ($isApiRequest) {
                $payerId = $this->request->getPost('payer_id');
                if (!$payerId) {
                    // Try to get from JSON body
                    $jsonData = json_decode($this->request->getBody(), true);
                    $payerId = $jsonData['payer_id'] ?? null;
                }
                if (!$payerId) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Payer ID is required'
                    ]);
                }
            } else {
                $payerId = session('payer_id');
                if (!$payerId) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Payer not authenticated'
                    ]);
                }
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
        // Check if this is an API request
        $isApiRequest = strpos($this->request->getUri()->getPath(), '/api/') !== false;
        
        // Set CORS headers if it's an API endpoint
        if ($isApiRequest) {
            $this->response->setHeader('Access-Control-Allow-Origin', '*');
            $this->response->setHeader('Access-Control-Allow-Methods', 'GET, OPTIONS');
            $this->response->setHeader('Access-Control-Allow-Headers', 'Content-Type, Accept, Authorization, X-Requested-With, Origin');
            $this->response->setHeader('Access-Control-Max-Age', '7200');
        }
        
        // Handle OPTIONS preflight request
        if ($this->request->getMethod() === 'OPTIONS') {
            return $this->response->setStatusCode(200)->setBody('');
        }
        
        try {
            // Get the current payer ID from session (web) or query parameter (API)
            if ($isApiRequest) {
                $payerId = $this->request->getGet('payer_id');
                if (!$payerId) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Payer ID is required'
                    ]);
                }
            } else {
                $payerId = session('payer_id');
                if (!$payerId) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Not authenticated'
                    ]);
                }
            }
            
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
                'activities' => $activities ?? []
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
        
        // Add base_url to proof of payment paths - use ImageController route
        foreach ($paymentRequests as &$request) {
            if (!empty($request['proof_of_payment_path'])) {
                // Extract filename from path (handles both full paths and just filenames)
                $path = $request['proof_of_payment_path'];
                // Remove any existing uploads/ prefix to avoid duplication
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
        }
        
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
        // Set CORS headers for API requests (same as other mobile endpoints)
        $isApiEndpoint = strpos($this->request->getUri()->getPath(), '/api/') !== false;
        if ($isApiEndpoint) {
            $this->response->setHeader('Access-Control-Allow-Origin', '*');
            $this->response->setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
            $this->response->setHeader('Access-Control-Allow-Headers', 'Content-Type, Accept, Authorization, X-Requested-With, Origin');
            $this->response->setHeader('Access-Control-Max-Age', '7200');
        }
        
        // Debug: Log all incoming data
        log_message('info', 'Payment request submission started');
        log_message('info', 'Request method: ' . $this->request->getMethod());
        log_message('info', 'Is AJAX: ' . ($this->request->isAJAX() ? 'Yes' : 'No'));
        log_message('info', 'Is API endpoint: ' . ($isApiEndpoint ? 'Yes' : 'No'));
        log_message('info', 'POST data: ' . json_encode($this->request->getPost()));
        
        // Check if it's an AJAX request or if it's a POST request (more flexible)
        if (!$this->request->isAJAX() && $this->request->getMethod() !== 'POST') {
            log_message('error', 'Invalid request method for payment request submission');
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid request method'
            ]);
        }

        if ($isApiEndpoint) {
            // For API endpoints, get payer_id from request body or query parameter
            $payerId = $this->request->getPost('payer_id') ?? $this->request->getGet('payer_id');
            
            // If not in POST/GET, try to get from JSON body
            if (!$payerId) {
                $jsonBody = $this->request->getJSON(true);
                if ($jsonBody && isset($jsonBody['payer_id'])) {
                    $payerId = $jsonBody['payer_id'];
                }
            }
            
            // Validate payer_id for API requests
            if (!$payerId) {
                log_message('error', 'No payer ID provided for API payment request submission');
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Payer ID is required'
                ]);
            }
        } else {
            // For web endpoints, use session
            $payerId = session('payer_id');
            
            if (!$payerId) {
                log_message('error', 'No payer ID in session for payment request submission');
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Please log in to submit payment requests'
                ]);
            }
        }
        
        log_message('info', 'Payer ID: ' . $payerId);

        // Get valid payment method names from database
        $paymentMethodModel = new \App\Models\PaymentMethodModel();
        $validPaymentMethods = $paymentMethodModel->getActiveMethods();
        $paymentMethodNames = array_column($validPaymentMethods, 'name');
        $paymentMethodList = implode(',', $paymentMethodNames);
        
        // Debug: Log valid payment methods
        log_message('info', 'Valid payment methods for payer: ' . $paymentMethodList);
        
        $validation = \Config\Services::validation();
        $validation->setRules([
            'contribution_id' => 'required|integer',
            'requested_amount' => 'required|decimal|greater_than[0]',
            'payment_method' => 'required|in_list[' . $paymentMethodList . ']',
            'notes' => 'permit_empty|max_length[500]'
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            $errors = $validation->getErrors();
            log_message('error', 'Payment request validation failed: ' . json_encode($errors));
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $errors
            ]);
        }
        
        log_message('info', 'Payment request validation passed');

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
            
            log_message('info', 'File upload check - File exists: ' . ($file ? 'Yes' : 'No'));
            if ($file) {
                log_message('info', 'File details - Name: ' . $file->getName() . ', Size: ' . $file->getSize() . ', Valid: ' . ($file->isValid() ? 'Yes' : 'No'));
            }
            
            if ($file && $file->isValid() && !$file->hasMoved()) {
                $uploadPath = FCPATH . 'uploads/payment_proofs/';
                
                // Log the actual path being used
                log_message('info', 'Payment proof upload - FCPATH: ' . FCPATH . ', Upload path: ' . $uploadPath);
                
                // Ensure parent directory exists
                $parentDir = dirname($uploadPath);
                if (!is_dir($parentDir)) {
                    if (!mkdir($parentDir, 0755, true)) {
                        log_message('error', 'Failed to create parent directory: ' . $parentDir);
                        return $this->response->setJSON([
                            'success' => false,
                            'message' => 'Error: Could not create parent upload directory. Please contact administrator.'
                        ]);
                    }
                }
                
                // Create directory if it doesn't exist
                if (!is_dir($uploadPath)) {
                    if (!mkdir($uploadPath, 0755, true)) {
                        log_message('error', 'Failed to create upload directory: ' . $uploadPath);
                        return $this->response->setJSON([
                            'success' => false,
                            'message' => 'Error: Could not create upload directory. Please contact administrator.'
                        ]);
                    }
                    // Set permissions after creation
                    chmod($uploadPath, 0755);
                }
                
                // Check if directory is writable
                if (!is_writable($uploadPath)) {
                    // Try to fix permissions
                    @chmod($uploadPath, 0755);
                    @chown($uploadPath, 'www-data');
                    
                    if (!is_writable($uploadPath)) {
                        log_message('error', 'Upload directory is not writable: ' . $uploadPath . ', Permissions: ' . substr(sprintf('%o', fileperms($uploadPath)), -4));
                        return $this->response->setJSON([
                            'success' => false,
                            'message' => 'Error: Upload directory is not writable. Please contact administrator.'
                        ]);
                    }
                }
                
                $newName = 'proof_' . $payerId . '_' . time() . '.' . $file->getExtension();
                
                // Log detailed information before move
                log_message('info', 'Attempting to move payment proof - Path: ' . $uploadPath . ', File: ' . $newName . ', Directory exists: ' . (is_dir($uploadPath) ? 'Yes' : 'No') . ', Writable: ' . (is_writable($uploadPath) ? 'Yes' : 'No'));
                
                if (!$file->move($uploadPath, $newName)) {
                    // Get error from file object
                    $error = $file->getErrorString();
                    if (empty($error)) {
                        $error = $file->getError() . ' (Error code: ' . $file->getError() . ')';
                    }
                    
                    // Get system-level error if available
                    $lastError = error_get_last();
                    $systemError = $lastError ? $lastError['message'] : 'Unknown system error';
                    
                    // Check if directory exists and is writable
                    $dirExists = is_dir($uploadPath);
                    $dirWritable = is_writable($uploadPath);
                    $parentWritable = is_writable(dirname($uploadPath));
                    
                    log_message('error', 'Payment proof upload failed - Error: ' . $error . ', System Error: ' . $systemError . ', Path: ' . $uploadPath . ', File: ' . $newName . ', Dir exists: ' . ($dirExists ? 'Yes' : 'No') . ', Dir writable: ' . ($dirWritable ? 'Yes' : 'No') . ', Parent writable: ' . ($parentWritable ? 'Yes' : 'No'));
                    
                    $errorMessage = 'Error: Could not move file "' . $file->getName() . '" to upload directory.';
                    if (!$dirExists) {
                        $errorMessage .= ' Directory does not exist.';
                    } elseif (!$dirWritable) {
                        $errorMessage .= ' Directory is not writable.';
                    } elseif (!empty($error) && $error !== '0') {
                        $errorMessage .= ' Reason: ' . $error;
                    } else {
                        $errorMessage .= ' Reason: ' . $systemError;
                    }
                    
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => $errorMessage
                    ]);
                }
                
                $proofOfPaymentPath = 'uploads/payment_proofs/' . $newName;
                log_message('info', 'File uploaded successfully: ' . $proofOfPaymentPath);
            } else {
                if ($file) {
                    log_message('info', 'File upload validation failed - Valid: ' . ($file->isValid() ? 'Yes' : 'No') . ', HasMoved: ' . ($file->hasMoved() ? 'Yes' : 'No'));
                } else {
                    log_message('info', 'No file uploaded');
                }
            }

            // Create payment request
            $requestData = [
                'payer_id' => $payerId,
                'contribution_id' => $this->request->getPost('contribution_id'),
                'payment_sequence' => $this->request->getPost('payment_sequence') ?: null,
                'requested_amount' => $requestedAmount,
                'payment_method' => $this->request->getPost('payment_method'),
                'proof_of_payment_path' => $proofOfPaymentPath,
                'notes' => $this->request->getPost('notes'),
                'status' => 'pending'
            ];

            log_message('info', 'Attempting to insert payment request with data: ' . json_encode($requestData));
            $requestId = $this->paymentRequestModel->insert($requestData);
            log_message('info', 'Payment request insert result: ' . ($requestId ? 'Success - ID: ' . $requestId : 'Failed'));
            
            if ($requestId) {
                // Add the ID to the request data for logging
                $requestData['id'] = $requestId;
                
                // Log activity
                $activityLogger = new \App\Services\ActivityLogger();
                $activityLogger->logPaymentRequest('submitted', $requestData);

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
        // Check if this is an API request
        $isApiRequest = strpos($this->request->getUri()->getPath(), '/api/') !== false;
        
        // Set CORS headers if it's an API endpoint
        if ($isApiRequest) {
            $this->response->setHeader('Access-Control-Allow-Origin', '*');
            $this->response->setHeader('Access-Control-Allow-Methods', 'GET, OPTIONS');
            $this->response->setHeader('Access-Control-Allow-Headers', 'Content-Type, Accept, Authorization, X-Requested-With, Origin');
            $this->response->setHeader('Access-Control-Allow-Credentials', 'true');
            $this->response->setHeader('Access-Control-Max-Age', '7200');
        }

        // For web requests, check if it's AJAX
        if (!$isApiRequest && !$this->request->isAJAX()) {
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

            // Get payer_id from query parameter for API requests, or from session for web requests
            if ($isApiRequest) {
                $payerId = $this->request->getGet('payer_id') ?? session('payer_id');
            } else {
                $payerId = session('payer_id');
            }

            if (!$payerId) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Payer ID is required'
                ]);
            }

            // Get payer's current payments for this contribution
            $totalPaid = $this->paymentModel->where('payer_id', $payerId)
                ->where('contribution_id', $contributionId)
                ->where('deleted_at', null)
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
                    'remaining_balance' => $remainingAmount, // Add for consistency
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

    /**
     * Refund requests page
     */
    public function refundRequests()
    {
        // Check if this is an API request
        $isApiEndpoint = strpos($this->request->getUri()->getPath(), '/api/') !== false;
        
        // Set CORS headers if it's an API endpoint
        if ($isApiEndpoint) {
            $this->response->setHeader('Access-Control-Allow-Origin', '*');
            $this->response->setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
            $this->response->setHeader('Access-Control-Allow-Headers', 'Content-Type, Accept, Authorization, X-Requested-With, Origin');
            $this->response->setHeader('Access-Control-Allow-Credentials', 'true');
            $this->response->setHeader('Access-Control-Max-Age', '7200');
        }
        
        // Get payer_id from session (web) or query parameter (API)
        $payerId = $isApiEndpoint 
            ? ($this->request->getGet('payer_id') ?? session('payer_id'))
            : session('payer_id');
        
        // Validate payer_id for API requests
        if ($isApiEndpoint && !$payerId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Payer ID is required'
            ]);
        }
        
        // Get payer's refund requests
        $refundRequests = $this->refundModel->getRequestsByPayer($payerId);
        
        // Get active refund methods for refund request form
        $refundMethodModel = new RefundMethodModel();
        $refundMethods = $refundMethodModel->getActiveMethods();
        
        // Get payments that can be refunded (completed payments with available refund amount)
        // Only get payments that have been verified/processed (not pending payment requests)
        $payments = $this->paymentModel->select('
            payments.id,
            payments.amount_paid,
            payments.payment_method,
            payments.payment_status,
            payments.receipt_number,
            payments.payment_date,
            payments.contribution_id,
            contributions.title as contribution_title
        ')
        ->join('contributions', 'contributions.id = payments.contribution_id', 'left')
        ->where('payments.payer_id', $payerId)
        ->where('payments.deleted_at', null)
        ->where('payments.amount_paid >', 0) // Only payments with amount > 0
        ->orderBy('payments.payment_date', 'DESC')
        ->findAll();
        
        // Filter payments that have available refund amount
        $refundablePayments = [];
        foreach ($payments as $payment) {
            $availableAmount = $this->getAvailableRefundAmount($payment['id']);
            // Only include payments with available refund amount > 0
            if ($availableAmount > 0) {
                $payment['available_refund'] = $availableAmount;
                $payment['refund_status'] = $this->paymentModel->getPaymentRefundStatus($payment['id']);
                $refundablePayments[] = $payment;
            }
        }
        
        // Log for debugging
        log_message('info', 'Refund requests - Payer ID: ' . $payerId . ', Total payments: ' . count($payments) . ', Refundable: ' . count($refundablePayments));
        
        // Return JSON for API requests, view for web requests
        if ($isApiEndpoint) {
            return $this->response->setJSON([
                'success' => true,
                'data' => [
                    'refund_requests' => $refundRequests,
                    'refund_methods' => $refundMethods,
                    'refundable_payments' => $refundablePayments
                ]
            ]);
        }
        
        $data = [
            'title' => 'Refund Requests',
            'pageTitle' => 'Refund Requests',
            'pageSubtitle' => 'Request refunds for your payments',
            'refundRequests' => $refundRequests,
            'refundMethods' => $refundMethods,
            'refundablePayments' => $refundablePayments
        ];
        
        return view('payer/refund-requests', $data);
    }

    /**
     * Submit a refund request
     */
    public function submitRefundRequest()
    {
        // Check if this is an API request
        $isApiEndpoint = strpos($this->request->getUri()->getPath(), '/api/') !== false;
        
        // Set CORS headers if it's an API endpoint
        if ($isApiEndpoint) {
            $this->response->setHeader('Access-Control-Allow-Origin', '*');
            $this->response->setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
            $this->response->setHeader('Access-Control-Allow-Headers', 'Content-Type, Accept, Authorization, X-Requested-With, Origin');
            $this->response->setHeader('Access-Control-Allow-Credentials', 'true');
            $this->response->setHeader('Access-Control-Max-Age', '7200');
        }

        // For web requests, check if it's AJAX
        if (!$isApiEndpoint && !$this->request->isAJAX()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid request method'
            ]);
        }

        // Get payer_id from query parameter for API requests, or from session for web requests
        if ($isApiEndpoint) {
            $payerId = $this->request->getPost('payer_id') ?? $this->request->getGet('payer_id');
            
            // If not in POST/GET, try to get from JSON body
            if (!$payerId) {
                $jsonBody = $this->request->getJSON(true);
                if ($jsonBody && isset($jsonBody['payer_id'])) {
                    $payerId = $jsonBody['payer_id'];
                }
            }
            
            // Validate payer_id for API requests
            if (!$payerId) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Payer ID is required'
                ]);
            }
        } else {
            $payerId = session('payer_id');
            
            if (!$payerId) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Please log in to submit refund requests'
                ]);
            }
        }

        $validation = \Config\Services::validation();
        
        // Get valid refund method codes
        $refundMethodModel = new RefundMethodModel();
        $validRefundMethods = $refundMethodModel->getActiveMethods();
        $validRefundMethodCodes = array_column($validRefundMethods, 'code');
        $validRefundMethodCodes[] = 'original_method'; // Allow original method
        
        $validation->setRules([
            'payment_id' => 'required|integer',
            'refund_amount' => 'required|decimal|greater_than[0]',
            'refund_method' => 'required',
            'refund_reason' => 'permit_empty|string|max_length[500]'
        ]);

        // For API requests with JSON body, validate against JSON data
        if ($isApiEndpoint) {
            $jsonBody = $this->request->getJSON(true);
            if (!$jsonBody) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Invalid JSON data'
                ]);
            }
            if (!$validation->run($jsonBody)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validation->getErrors()
                ]);
            }
        } else {
            // For web requests, validate against request
            if (!$validation->withRequest($this->request)->run()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validation->getErrors()
                ]);
            }
        }

        try {
            // Get data from POST or JSON body (for API requests)
            $jsonBody = $this->request->getJSON(true);
            if ($isApiEndpoint && $jsonBody) {
                $paymentId = $jsonBody['payment_id'] ?? null;
                $refundAmount = isset($jsonBody['refund_amount']) ? (float)$jsonBody['refund_amount'] : null;
                $refundMethod = $jsonBody['refund_method'] ?? null;
                $refundReason = $jsonBody['refund_reason'] ?? '';
            } else {
                $paymentId = $this->request->getPost('payment_id');
                $refundAmount = (float)$this->request->getPost('refund_amount');
                $refundMethod = $this->request->getPost('refund_method');
                $refundReason = $this->request->getPost('refund_reason');
            }
            
            // Validate refund method
            if (!in_array($refundMethod, $validRefundMethodCodes)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Invalid refund method selected'
                ]);
            }

            // Get payment details
            $payment = $this->paymentModel
                ->select('payments.*, contributions.id as contribution_id')
                ->join('contributions', 'contributions.id = payments.contribution_id', 'left')
                ->where('payments.id', $paymentId)
                ->where('payments.payer_id', $payerId)
                ->where('payments.deleted_at', null)
                ->first();

            if (!$payment) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Payment not found'
                ]);
            }

            // Check available refund amount
            $availableAmount = $this->getAvailableRefundAmount($paymentId);
            
            if ($refundAmount > $availableAmount) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => "Refund amount cannot exceed available amount (₱" . number_format($availableAmount, 2) . ")"
                ]);
            }

            // Check for existing pending or processing refunds
            $existingRefund = $this->refundModel
                ->where('payment_id', $paymentId)
                ->whereIn('status', ['pending', 'processing'])
                ->first();

            if ($existingRefund) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'This payment already has a pending or processing refund request'
                ]);
            }

            // Determine final refund method
            $finalRefundMethod = $refundMethod;
            if ($refundMethod === 'original_method') {
                $paymentMethod = $payment['payment_method'] ?? 'cash';
                $methodMapping = [
                    'cash' => 'cash',
                    'online' => 'bank_transfer',
                    'bank' => 'bank_transfer',
                    'check' => 'bank_transfer',
                    'bank_transfer' => 'bank_transfer',
                    'gcash' => 'gcash',
                    'paymaya' => 'paymaya'
                ];
                $finalRefundMethod = $methodMapping[$paymentMethod] ?? 'cash';
            }

            // Create refund request
            $refundData = [
                'payment_id' => $paymentId,
                'payer_id' => $payerId,
                'contribution_id' => $payment['contribution_id'],
                'refund_amount' => round($refundAmount, 2),
                'refund_reason' => $refundReason ?: null,
                'refund_method' => $finalRefundMethod,
                'status' => 'pending',
                'request_type' => 'payer_requested',
                'requested_by_payer' => 1,
                'payer_notes' => $refundReason ?: null
            ];

            $refundId = $this->refundModel->insert($refundData);

            if ($refundId) {
                // Log activity
                $activityLogger = new \App\Services\ActivityLogger();
                $refundData['id'] = $refundId;
                $activityLogger->logRefund('requested', $refundData);

                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Refund request submitted successfully! Reference: ' . ($refundData['refund_reference'] ?? 'REF-' . date('Ymd') . '-' . strtoupper(substr(md5($refundId), 0, 12))),
                    'refund_id' => $refundId
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to submit refund request. Please try again.'
                ]);
            }

        } catch (\Exception $e) {
            log_message('error', 'Refund request error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Return active payment methods for payer (mobile API)
     */
    public function getActivePaymentMethods()
    {
        try {
            $paymentMethodModel = new \App\Models\PaymentMethodModel();
            $methods = $paymentMethodModel->getActiveMethods();
            $data = array_map(function($m) {
                return [
                    'id' => $m['id'],
                    'name' => $m['name'],
                    'icon' => $m['icon'] ?? null,
                    'description' => $m['description'] ?? null,
                ];
            }, $methods);

            return $this->response->setJSON([
                'success' => true,
                'methods' => $data
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error fetching active payment methods: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error fetching payment methods: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Return active refund methods (code + name) for payer modal dropdown
     */
    /**
     * Get refund details for payer (payer-specific endpoint)
     */
    public function getRefundDetails()
    {
        // Check if this is an API request
        $isApiEndpoint = strpos($this->request->getUri()->getPath(), '/api/') !== false;
        
        // Set CORS headers if it's an API endpoint
        if ($isApiEndpoint) {
            $this->response->setHeader('Access-Control-Allow-Origin', '*');
            $this->response->setHeader('Access-Control-Allow-Methods', 'GET, OPTIONS');
            $this->response->setHeader('Access-Control-Allow-Headers', 'Content-Type, Accept, Authorization, X-Requested-With, Origin');
            $this->response->setHeader('Access-Control-Allow-Credentials', 'true');
            $this->response->setHeader('Access-Control-Max-Age', '7200');
        }
        
        // Get payer_id from session (web) or query parameter (API)
        $payerId = $isApiEndpoint 
            ? ($this->request->getGet('payer_id') ?? session('payer_id'))
            : session('payer_id');
        
        if (!$payerId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Please log in to view refund details'
            ])->setStatusCode(401);
        }

        $refundId = $this->request->getGet('refund_id');
        if (!$refundId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Refund ID is required'
            ]);
        }

        // Cast refund_id to integer for proper comparison
        $refundId = (int)$refundId;

        $refundModel = new RefundModel();
        
        // Get refund with details, but only if it belongs to this payer
        $refundDetails = $refundModel->getRefundsWithDetails(null, null, null);
        
        // Find the specific refund that belongs to this payer
        $foundRefund = null;
        foreach ($refundDetails as $r) {
            // Ensure both are compared as integers and check payer_id
            if ((int)$r['id'] === $refundId && (int)$r['payer_id'] === (int)$payerId) {
                $foundRefund = $r;
                break;
            }
        }

        if (!$foundRefund) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Refund not found or you do not have permission to view this refund'
            ]);
        }

        // Normalize profile picture path with fallback
        $foundRefund['profile_picture'] = $this->normalizeProfilePicturePath(
            $foundRefund['profile_picture'] ?? null, 
            $foundRefund['payer_id'] ?? null,
            null,
            'payer'
        );

        return $this->response->setJSON([
            'success' => true,
            'refund' => $foundRefund
        ]);
    }

    public function getActiveRefundMethods()
    {
        // Check if this is an API request
        $isApiEndpoint = strpos($this->request->getUri()->getPath(), '/api/') !== false;
        
        // Set CORS headers if it's an API endpoint
        if ($isApiEndpoint) {
            $this->response->setHeader('Access-Control-Allow-Origin', '*');
            $this->response->setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
            $this->response->setHeader('Access-Control-Allow-Headers', 'Content-Type, Accept, Authorization, X-Requested-With, Origin');
            $this->response->setHeader('Access-Control-Allow-Credentials', 'true');
            $this->response->setHeader('Access-Control-Max-Age', '7200');
        }
        
        // For web requests, check if it's AJAX
        if (!$isApiEndpoint && !$this->request->isAJAX()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid request method'
            ]);
        }

        try {
            $refundMethodModel = new \App\Models\RefundMethodModel();
            $methods = $refundMethodModel->getActiveMethods();
            $data = array_map(function($m) {
                return [
                    'code' => $m['code'],
                    'name' => $m['name']
                ];
            }, $methods);

            // Also include original_method which the backend accepts
            $data[] = ['code' => 'original_method', 'name' => 'Original Payment Method'];

            return $this->response->setJSON([
                'success' => true,
                'methods' => $data
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get available refund amount for a payment
     */
    private function getAvailableRefundAmount($paymentId)
    {
        $payment = $this->paymentModel->find($paymentId);
        if (!$payment) {
            return 0;
        }

        $totalRefunded = $this->refundModel
            ->selectSum('refund_amount')
            ->where('payment_id', $paymentId)
            ->where('status', 'completed')
            ->first();

        $refundedAmount = $totalRefunded['refund_amount'] ?? 0;
        return (float)$payment['amount_paid'] - (float)$refundedAmount;
    }

    /**
     * Mobile API: Get dashboard data
     */
    public function mobileDashboard()
    {
        // Set CORS headers for API requests
        $this->response->setHeader('Access-Control-Allow-Origin', '*');
        $this->response->setHeader('Access-Control-Allow-Methods', 'GET, OPTIONS');
        $this->response->setHeader('Access-Control-Allow-Headers', 'Content-Type, Accept, Authorization, X-Requested-With, Origin');
        $this->response->setHeader('Access-Control-Max-Age', '7200');
        
        // For mobile, get payer_id from query parameter or token
        $payerId = $this->request->getGet('payer_id') ?? session('payer_id');
        
        if (!$payerId) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Not authenticated'
            ]);
        }
        
        // Validate payer exists and get payer data
        $payer = $this->payerModel->find($payerId);
        if (!$payer) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Payer not found'
            ]);
        }
        
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
        
        // Get payment request count
        $pendingRequests = $this->paymentRequestModel->where('payer_id', $payerId)
            ->where('status', 'pending')
            ->countAllResults();
        
        // Normalize profile picture path with fallback
        $payer['profile_picture'] = $this->normalizeProfilePicturePath($payer['profile_picture'] ?? null, $payer['id'] ?? null, null, 'payer');

        return $this->response->setJSON([
            'success' => true,
            'data' => [
                'payer' => $payer,
                'total_paid' => (float)($totalPaid['amount_paid'] ?? 0),
                'recent_payments' => $recentPayments,
                'announcements' => $announcements,
                'pending_requests' => $pendingRequests,
                'total_payments' => count($recentPayments)
            ]
        ]);
    }

    /**
     * Mobile API: Get contributions
     */
    public function mobileContributions()
    {
        $payerId = $this->request->getGet('payer_id') ?? session('payer_id');
        
        if (!$payerId) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Not authenticated'
            ]);
        }
        
        // Get all contributions (both active and inactive)
        $allContributions = $this->contributionModel
            ->orderBy('created_at', 'DESC')
            ->findAll();
        
        // Filter contributions
        $contributions = [];
        foreach ($allContributions as $contribution) {
            if ($contribution['status'] === 'active') {
                $contributions[] = $contribution;
            } else {
                $hasTransactions = $this->paymentModel
                    ->where('payer_id', $payerId)
                    ->where('contribution_id', $contribution['id'])
                    ->where('deleted_at', null)
                    ->countAllResults() > 0;
                
                if ($hasTransactions) {
                    $contributions[] = $contribution;
                }
            }
        }
        
        // Get payment data for each contribution
        foreach ($contributions as &$contribution) {
            $contributionAmount = $contribution['amount'];
            $paymentGroups = $this->paymentModel->select('
                COALESCE(payment_sequence, 1) as payment_sequence,
                SUM(amount_paid) as total_paid,
                COUNT(id) as payment_count,
                MAX(payment_date) as last_payment_date,
                MIN(payment_date) as first_payment_date,
                CASE 
                    WHEN SUM(amount_paid) >= ' . $contributionAmount . ' THEN \'fully paid\'
                    WHEN SUM(amount_paid) > 0 THEN \'partial\'
                    ELSE \'unpaid\'
                END as computed_status,
                ' . $contributionAmount . ' - SUM(amount_paid) as remaining_balance
            ')
            ->where('payer_id', $payerId)
            ->where('contribution_id', $contribution['id'])
            ->where('deleted_at', null)
            ->groupBy('COALESCE(payment_sequence, 1)')
            ->orderBy('payment_sequence', 'ASC')
            ->findAll();
            
            $contribution['payment_groups'] = $paymentGroups;
            $totalPaid = array_sum(array_column($paymentGroups, 'total_paid'));
            $contribution['total_paid'] = $totalPaid;
            $contribution['remaining_balance'] = max(0, $contribution['amount'] - $totalPaid);
        }
        
        return $this->response->setJSON([
            'success' => true,
            'data' => $contributions
        ]);
    }

    /**
     * Mobile API: Get payment history
     */
    public function mobilePaymentHistory()
    {
        $payerId = $this->request->getGet('payer_id') ?? session('payer_id');
        
        if (!$payerId) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Not authenticated'
            ]);
        }
        
        // Get payments grouped by contribution with payer information
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
            payers.payer_name,
            payers.contact_number,
            payers.email_address,
            payers.payer_id as payer_student_id,
            contributions.title as contribution_title,
            contributions.description as contribution_description,
            contributions.amount as contribution_amount
        ')
        ->join('payers', 'payers.id = payments.payer_id', 'left')
        ->join('contributions', 'contributions.id = payments.contribution_id', 'left')
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
        
        // Calculate totals
        foreach ($contributionsWithPayments as &$contribution) {
            $totalPaid = 0;
            foreach ($contribution['payments'] as &$payment) {
                $totalPaid += $payment['amount_paid'];
            }
            $contribution['total_paid'] = $totalPaid;
            $contribution['remaining_amount'] = $contribution['amount'] - $totalPaid;
        }
        
        return $this->response->setJSON([
            'success' => true,
            'data' => array_values($contributionsWithPayments)
        ]);
    }

    /**
     * Mobile API: Get announcements
     */
    public function mobileAnnouncements()
    {
        $announcements = $this->announcementModel->where('status', 'published')
            ->where("(target_audience = 'payers' OR target_audience = 'both' OR target_audience = 'all')")
            ->orderBy('created_at', 'DESC')
            ->findAll();
        
        return $this->response->setJSON([
            'success' => true,
            'data' => $announcements
        ]);
    }

    /**
     * Mobile API: Get payment requests
     */
    public function mobilePaymentRequests()
    {
        $payerId = $this->request->getGet('payer_id') ?? session('payer_id');
        
        if (!$payerId) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Not authenticated'
            ]);
        }
        
        // Get active contributions
        $contributions = $this->contributionModel->where('status', 'active')
            ->orderBy('title', 'ASC')
            ->findAll();
        
        // Get payer's payment requests
        $paymentRequests = $this->paymentRequestModel->getRequestsByPayer($payerId);
        
        // Add base_url to proof of payment paths - use ImageController route
        foreach ($paymentRequests as &$request) {
            if (!empty($request['proof_of_payment_path'])) {
                // Extract filename from path (handles both full paths and just filenames)
                $path = $request['proof_of_payment_path'];
                // Remove any existing uploads/ prefix to avoid duplication
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
        }
        
        return $this->response->setJSON([
            'success' => true,
            'data' => [
                'contributions' => $contributions,
                'payment_requests' => $paymentRequests
            ]
        ]);
    }

    /**
     * Handle CORS preflight OPTIONS request
     */
    public function handleOptions()
    {
        // Set CORS headers for preflight request
        $this->response->setHeader('Access-Control-Allow-Origin', '*');
        $this->response->setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS, PUT, DELETE, PATCH');
        $this->response->setHeader('Access-Control-Allow-Headers', 'Content-Type, Accept, Authorization, X-Requested-With, Origin');
        $this->response->setHeader('Access-Control-Allow-Credentials', 'true');
        $this->response->setHeader('Access-Control-Max-Age', '7200');
        
        // Return empty response for OPTIONS request
        return $this->response->setStatusCode(200)->setBody('');
    }

}
