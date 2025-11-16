<?php

namespace App\Controllers\SuperAdmin;

use App\Controllers\BaseController;
use App\Models\UserModel;

class PortalController extends BaseController
{
    public function index()
    {
        // Check if user is logged in as super admin
        if (!session()->get('isSuperAdmin')) {
            return redirect()->to('/super-admin/login')->with('error', 'Please login as Super Admin');
        }

        $userModel = new UserModel();
        
        // Get all pending officer signups
        $pendingOfficers = $userModel->where('role', 'officer')
            ->where('status', 'pending')
            ->orderBy('created_at', 'DESC')
            ->findAll();
        
        // Get all APPROVED officers only (exclude pending and rejected)
        // This is the "All Officers" list - only shows officers who have been approved
        $allOfficers = $userModel->where('role', 'officer')
            ->where('status', 'approved')
            ->orderBy('created_at', 'DESC')
            ->findAll();
        
        // Get all REJECTED officers for the declined requests section
        $rejectedOfficers = $userModel->where('role', 'officer')
            ->where('status', 'rejected')
            ->orderBy('created_at', 'DESC')
            ->findAll();
        
        // Get online officer IDs by checking last_activity
        $onlineOfficerIds = $this->getOnlineOfficerIds();
        
        // Helper function to normalize PostgreSQL boolean values
        $normalizeBoolean = function($value, $default = true) {
            if ($value === null) {
                return $default;
            }
            if (is_string($value)) {
                return in_array(strtolower($value), ['t', 'true', '1', 'yes'], true);
            }
            if (is_numeric($value)) {
                return (bool)(int)$value;
            }
            return (bool)$value;
        };
        
        // Add online status, normalize profile pictures and boolean values
        foreach ($allOfficers as &$officer) {
            $officer['is_online'] = in_array($officer['id'], $onlineOfficerIds);
            $officer['profile_picture'] = $this->normalizeProfilePicturePath(
                $officer['profile_picture'] ?? null,
                null,
                $officer['id'],
                'user'
            );
            // Normalize is_active to proper boolean (PostgreSQL may return 't'/'f' or 1/0)
            $officer['is_active'] = $normalizeBoolean($officer['is_active'] ?? true, true);
        }
        
        // Count active and inactive officers
        $activeOfficers = array_filter($allOfficers, function($officer) {
            return $officer['is_active'] === true;
        });
        $inactiveOfficers = array_filter($allOfficers, function($officer) {
            return $officer['is_active'] === false;
        });
        
        // Update last_activity for super admin
        $superAdminId = session()->get('super-admin-id');
        if ($superAdminId) {
            $userModel->update($superAdminId, [
                'last_activity' => date('Y-m-d H:i:s')
            ]);
        }
        
        $data = [
            'title' => 'Super Admin Portal',
            'pageTitle' => 'Super Admin Portal',
            'pageSubtitle' => 'Manage officer approvals and system administration',
            'pendingOfficers' => $pendingOfficers,
            'allOfficers' => $allOfficers,
            'rejectedOfficers' => $rejectedOfficers,
            'totalPending' => count($pendingOfficers),
            'totalOfficers' => count($allOfficers),
            'totalRejected' => count($rejectedOfficers),
            'activeOfficers' => count($activeOfficers),
            'inactiveOfficers' => count($inactiveOfficers),
            'onlineOfficers' => count($onlineOfficerIds)
        ];

        return view('super-admin/portal', $data);
    }

    public function approve()
    {
        // Check if user is logged in as super admin
        if (!session()->get('isSuperAdmin')) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Access denied. Only Super Admins can approve officers.'
            ]);
        }

        $userModel = new UserModel();
        $userId = $this->request->getPost('user_id');

        if (!$userId) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'User ID is required.'
            ]);
        }

        $user = $userModel->find($userId);

        if (!$user) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'User not found.'
            ]);
        }

        // Only approve officers
        if ($user['role'] !== 'officer') {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Only officers can be approved through this system.'
            ]);
        }

        // Update user status to approved
        $userModel->update($userId, [
            'status' => 'approved'
        ]);

        // Log activity
        try {
            $activityLogger = new \App\Services\ActivityLogger();
            $activityLogger->logUser('approved', [
                'id' => $userId,
                'name' => $user['name'],
                'username' => $user['username'],
                'role' => $user['role']
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Failed to log officer approval activity: ' . $e->getMessage());
        }

        // Send approval email via Brevo
        $this->sendApprovalEmail($user);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Officer approved successfully.'
        ]);
    }

    public function reject()
    {
        // Check if user is logged in as super admin
        if (!session()->get('isSuperAdmin')) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Access denied. Only Super Admins can reject officers.'
            ]);
        }

        $userModel = new UserModel();
        $userId = $this->request->getPost('user_id');
        $reason = $this->request->getPost('reason') ?? 'No reason provided';

        if (!$userId) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'User ID is required.'
            ]);
        }

        $user = $userModel->find($userId);

        if (!$user) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'User not found.'
            ]);
        }

        // Only reject officers
        if ($user['role'] !== 'officer') {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Only officers can be rejected through this system.'
            ]);
        }

        // Update user status to rejected
        $userModel->update($userId, [
            'status' => 'rejected'
        ]);

        // Log activity
        try {
            $activityLogger = new \App\Services\ActivityLogger();
            $activityLogger->logUser('rejected', [
                'id' => $userId,
                'name' => $user['name'],
                'username' => $user['username'],
                'role' => $user['role'],
                'reason' => $reason
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Failed to log officer rejection activity: ' . $e->getMessage());
        }

        // Send rejection email via Brevo
        $this->sendRejectionEmail($user, $reason);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Officer rejected successfully.'
        ]);
    }

    /**
     * Get list of officer user IDs who are currently online
     * by checking last_activity timestamp (within last 15 minutes)
     */
    private function getOnlineOfficerIds(): array
    {
        $onlineOfficerIds = [];
        
        try {
            $userModel = new UserModel();
            
            // Consider users online if they were active within the last 15 minutes
            $onlineThreshold = date('Y-m-d H:i:s', strtotime('-15 minutes'));
            
            $onlineOfficers = $userModel->where('role', 'officer')
                ->where('last_activity >=', $onlineThreshold)
                ->findAll();
            
            foreach ($onlineOfficers as $officer) {
                $onlineOfficerIds[] = $officer['id'];
            }
            
        } catch (\Exception $e) {
            log_message('error', 'Error checking online officers: ' . $e->getMessage());
        }
        
        return $onlineOfficerIds;
    }

    public function deactivate()
    {
        // Check if user is logged in as super admin
        if (!session()->get('isSuperAdmin')) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Access denied. Only Super Admins can deactivate officers.'
            ]);
        }

        $userModel = new UserModel();
        $userId = $this->request->getPost('user_id');
        $reason = $this->request->getPost('reason') ?? 'No reason provided';

        if (!$userId) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'User ID is required.'
            ]);
        }

        $user = $userModel->find($userId);

        if (!$user) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'User not found.'
            ]);
        }

        // Only deactivate officers
        if ($user['role'] !== 'officer') {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Only officers can be deactivated through this system.'
            ]);
        }

        // Update user is_active to false
        $userModel->update($userId, [
            'is_active' => false
        ]);

        // Delete all remember me tokens for this user to force logout
        try {
            $rememberTokenModel = new \App\Models\RememberTokenModel();
            $rememberTokenModel->deleteToken($userId);
        } catch (\Exception $e) {
            log_message('error', 'Failed to delete remember tokens for deactivated user: ' . $e->getMessage());
        }

        // Log activity
        try {
            $activityLogger = new \App\Services\ActivityLogger();
            $activityLogger->logUser('deactivated', [
                'id' => $userId,
                'name' => $user['name'],
                'username' => $user['username'],
                'role' => $user['role'],
                'reason' => $reason
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Failed to log officer deactivation activity: ' . $e->getMessage());
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Officer deactivated successfully.'
        ]);
    }

    public function reactivate()
    {
        // Check if user is logged in as super admin
        if (!session()->get('isSuperAdmin')) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Access denied. Only Super Admins can reactivate officers.'
            ]);
        }

        $userModel = new UserModel();
        $userId = $this->request->getPost('user_id');

        if (!$userId) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'User ID is required.'
            ]);
        }

        $user = $userModel->find($userId);

        if (!$user) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'User not found.'
            ]);
        }

        // Only reactivate officers
        if ($user['role'] !== 'officer') {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Only officers can be reactivated through this system.'
            ]);
        }

        // Update user is_active to true
        $userModel->update($userId, [
            'is_active' => true
        ]);

        // Log activity
        try {
            $activityLogger = new \App\Services\ActivityLogger();
            $activityLogger->logUser('reactivated', [
                'id' => $userId,
                'name' => $user['name'],
                'username' => $user['username'],
                'role' => $user['role']
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Failed to log officer reactivation activity: ' . $e->getMessage());
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Officer reactivated successfully.'
        ]);
    }

    /**
     * Send approval email to officer (Brevo API first, SMTP fallback)
     */
    private function sendApprovalEmail($user)
    {
        try {
            // Check if user has email
            if (empty($user['email'])) {
                log_message('info', 'No email address for user, skipping approval email');
                return false;
            }

            // Get email config
            $emailConfig = $this->getEmailConfig();

            // Get email template
            $htmlMessage = view('emails/officer_approved', [
                'name' => $user['name'] ?? $user['username'],
                'username' => $user['username']
            ]);

            // Extract text version from HTML
            $textMessage = strip_tags($htmlMessage);
            $subject = 'Account Approved - ClearPay Officer Portal';
            
            // Try Brevo API first (works on Render, bypasses port blocking)
            $brevoApiKey = $_ENV['BREVO_API_KEY'] ?? getenv('BREVO_API_KEY') ?: null;
            
            if (!empty($brevoApiKey)) {
                try {
                    log_message('info', 'Attempting to send approval email via Brevo API to officer: ' . $user['email']);
                    
                    if (!class_exists('\App\Services\BrevoEmailService')) {
                        log_message('error', 'BrevoEmailService class not found. Falling back to SMTP.');
                    } else {
                        $brevoService = new \App\Services\BrevoEmailService(
                            $brevoApiKey,
                            $emailConfig['fromEmail'],
                            $emailConfig['fromName'] ?? 'ClearPay'
                        );
                        
                        $result = $brevoService->send($user['email'], $subject, $htmlMessage, $textMessage);
                        
                        if ($result['success']) {
                            log_message('info', 'Approval email sent successfully via Brevo API to officer: ' . $user['email']);
                            return true;
                        } else {
                            log_message('error', 'Brevo API failed to send approval email: ' . ($result['error'] ?? 'Unknown error') . '. Falling back to SMTP.');
                            // Fall through to SMTP attempt
                        }
                    }
                } catch (\Exception $apiException) {
                    log_message('error', 'Brevo API exception while sending approval email: ' . $apiException->getMessage() . '. Falling back to SMTP.');
                    // Fall through to SMTP attempt
                }
            }
            
            // Fallback to SMTP (for localhost or if Brevo API fails/unavailable)
            // Validate SMTP credentials
            if (empty($emailConfig['SMTPUser']) || empty($emailConfig['SMTPPass']) || empty($emailConfig['SMTPHost'])) {
                log_message('error', 'SMTP configuration incomplete for approval email');
                return false;
            }
            
            // Get a fresh email service instance
            $emailService = \Config\Services::email();
            
            // Clear any previous configuration
            $emailService->clear();
            
            // Manually configure SMTP settings
            $smtpConfig = [
                'protocol' => $emailConfig['protocol'] ?? 'smtp',
                'SMTPHost' => trim($emailConfig['SMTPHost'] ?? ''),
                'SMTPUser' => trim($emailConfig['SMTPUser'] ?? ''),
                'SMTPPass' => $emailConfig['SMTPPass'] ?? '',
                'SMTPPort' => (int)($emailConfig['SMTPPort'] ?? 587),
                'SMTPCrypto' => $emailConfig['SMTPCrypto'] ?? 'tls',
                'SMTPTimeout' => (int)($emailConfig['SMTPTimeout'] ?? 30),
                'mailType' => $emailConfig['mailType'] ?? 'html',
                'mailtype' => $emailConfig['mailType'] ?? 'html',
                'charset' => $emailConfig['charset'] ?? 'UTF-8',
                'newline' => "\r\n",
                'CRLF' => "\r\n",
                'wordWrap' => true,
                'validate' => false,
            ];
            
            $emailService->initialize($smtpConfig);
            
            $emailService->setFrom($emailConfig['fromEmail'], $emailConfig['fromName']);
            $emailService->setTo($user['email']);
            $emailService->setSubject($subject);
            $emailService->setMessage($htmlMessage);
            
            log_message('info', "Attempting to send approval email to: {$user['email']} using SMTP: {$emailConfig['SMTPHost']}:{$emailConfig['SMTPPort']}");
            
            // Send email
            $result = $emailService->send();
            
            if ($result) {
                log_message('info', 'Approval email sent successfully via SMTP to officer: ' . $user['email']);
                return true;
            } else {
                log_message('error', 'Failed to send approval email via SMTP: ' . $emailService->printDebugger(['headers', 'subject', 'body']));
                return false;
            }
        } catch (\Exception $e) {
            log_message('error', 'Failed to send approval email to officer: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send rejection email to officer (Brevo API first, SMTP fallback)
     */
    private function sendRejectionEmail($user, $reason = '')
    {
        try {
            // Check if user has email
            if (empty($user['email'])) {
                log_message('info', 'No email address for user, skipping rejection email');
                return false;
            }

            // Get email config
            $emailConfig = $this->getEmailConfig();

            // Get email template
            $htmlMessage = view('emails/officer_rejected', [
                'name' => $user['name'] ?? $user['username'],
                'reason' => $reason
            ]);

            // Extract text version from HTML
            $textMessage = strip_tags($htmlMessage);
            $subject = 'Account Registration Update - ClearPay';
            
            // Try Brevo API first (works on Render, bypasses port blocking)
            $brevoApiKey = $_ENV['BREVO_API_KEY'] ?? getenv('BREVO_API_KEY') ?: null;
            
            if (!empty($brevoApiKey)) {
                try {
                    log_message('info', 'Attempting to send rejection email via Brevo API to officer: ' . $user['email']);
                    
                    if (!class_exists('\App\Services\BrevoEmailService')) {
                        log_message('error', 'BrevoEmailService class not found. Falling back to SMTP.');
                    } else {
                        $brevoService = new \App\Services\BrevoEmailService(
                            $brevoApiKey,
                            $emailConfig['fromEmail'],
                            $emailConfig['fromName'] ?? 'ClearPay'
                        );
                        
                        $result = $brevoService->send($user['email'], $subject, $htmlMessage, $textMessage);
                        
                        if ($result['success']) {
                            log_message('info', 'Rejection email sent successfully via Brevo API to officer: ' . $user['email']);
                            return true;
                        } else {
                            log_message('error', 'Brevo API failed to send rejection email: ' . ($result['error'] ?? 'Unknown error') . '. Falling back to SMTP.');
                            // Fall through to SMTP attempt
                        }
                    }
                } catch (\Exception $apiException) {
                    log_message('error', 'Brevo API exception while sending rejection email: ' . $apiException->getMessage() . '. Falling back to SMTP.');
                    // Fall through to SMTP attempt
                }
            }
            
            // Fallback to SMTP (for localhost or if Brevo API fails/unavailable)
            // Validate SMTP credentials
            if (empty($emailConfig['SMTPUser']) || empty($emailConfig['SMTPPass']) || empty($emailConfig['SMTPHost'])) {
                log_message('error', 'SMTP configuration incomplete for rejection email');
                return false;
            }
            
            // Get a fresh email service instance
            $emailService = \Config\Services::email();
            
            // Clear any previous configuration
            $emailService->clear();
            
            // Manually configure SMTP settings
            $smtpConfig = [
                'protocol' => $emailConfig['protocol'] ?? 'smtp',
                'SMTPHost' => trim($emailConfig['SMTPHost'] ?? ''),
                'SMTPUser' => trim($emailConfig['SMTPUser'] ?? ''),
                'SMTPPass' => $emailConfig['SMTPPass'] ?? '',
                'SMTPPort' => (int)($emailConfig['SMTPPort'] ?? 587),
                'SMTPCrypto' => $emailConfig['SMTPCrypto'] ?? 'tls',
                'SMTPTimeout' => (int)($emailConfig['SMTPTimeout'] ?? 30),
                'mailType' => $emailConfig['mailType'] ?? 'html',
                'mailtype' => $emailConfig['mailType'] ?? 'html',
                'charset' => $emailConfig['charset'] ?? 'UTF-8',
                'newline' => "\r\n",
                'CRLF' => "\r\n",
                'wordWrap' => true,
                'validate' => false,
            ];
            
            $emailService->initialize($smtpConfig);
            
            $emailService->setFrom($emailConfig['fromEmail'], $emailConfig['fromName']);
            $emailService->setTo($user['email']);
            $emailService->setSubject($subject);
            $emailService->setMessage($htmlMessage);
            
            log_message('info', "Attempting to send rejection email to: {$user['email']} using SMTP: {$emailConfig['SMTPHost']}:{$emailConfig['SMTPPort']}");
            
            // Send email
            $result = $emailService->send();
            
            if ($result) {
                log_message('info', 'Rejection email sent successfully via SMTP to officer: ' . $user['email']);
                return true;
            } else {
                log_message('error', 'Failed to send rejection email via SMTP: ' . $emailService->printDebugger(['headers', 'subject', 'body']));
                return false;
            }
        } catch (\Exception $e) {
            log_message('error', 'Failed to send rejection email to officer: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get email configuration from database or environment variables
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
            log_message('error', 'Database connection failed in getEmailConfig, using environment variables: ' . $e->getMessage());
        }
        
        // Fallback to environment variables or config
        $emailConfig = config('Email');
        return [
            'fromEmail' => $_ENV['email.fromEmail'] ?? getenv('email.fromEmail') ?: ($emailConfig->fromEmail ?? ''),
            'fromName' => $_ENV['email.fromName'] ?? getenv('email.fromName') ?: ($emailConfig->fromName ?? 'ClearPay'),
            'protocol' => $_ENV['email.protocol'] ?? getenv('email.protocol') ?: ($emailConfig->protocol ?? 'smtp'),
            'SMTPHost' => $_ENV['email.SMTPHost'] ?? getenv('email.SMTPHost') ?: ($emailConfig->SMTPHost ?? ''),
            'SMTPUser' => $_ENV['email.SMTPUser'] ?? getenv('email.SMTPUser') ?: ($emailConfig->SMTPUser ?? ''),
            'SMTPPass' => $_ENV['email.SMTPPass'] ?? getenv('email.SMTPPass') ?: ($emailConfig->SMTPPass ?? ''),
            'SMTPPort' => (int)($_ENV['email.SMTPPort'] ?? getenv('email.SMTPPort') ?: ($emailConfig->SMTPPort ?? 587)),
            'SMTPCrypto' => $_ENV['email.SMTPCrypto'] ?? getenv('email.SMTPCrypto') ?: ($emailConfig->SMTPCrypto ?? 'tls'),
            'SMTPTimeout' => (int)($_ENV['email.SMTPTimeout'] ?? getenv('email.SMTPTimeout') ?: ($emailConfig->SMTPTimeout ?? 30)),
            'mailType' => $_ENV['email.mailType'] ?? getenv('email.mailType') ?: ($emailConfig->mailType ?? 'html'),
            'charset' => $_ENV['email.charset'] ?? getenv('email.charset') ?: ($emailConfig->charset ?? 'UTF-8'),
        ];
    }
}

