<?php

namespace App\Controllers\Admin;

use App\Models\UserModel;
use App\Models\RememberTokenModel;
use CodeIgniter\Controller;
use CodeIgniter\Email\Email;

class LoginController extends Controller
{
    public function index()
    {
        try {
            // If already logged in, redirect to dashboard
            if (session()->get('isLoggedIn')) {
                return redirect()->to('/dashboard');
            }
            
            return view('admin/login');
        } catch (\Exception $e) {
            // If there's any error (database, session, etc.), return a simple error page
            // This prevents 500 errors during health checks or initial setup
            log_message('error', 'LoginController index error: ' . $e->getMessage());
            
            // Return a simple HTML response instead of crashing
            return $this->response->setStatusCode(200)
                ->setBody('<!DOCTYPE html><html><head><title>ClearPay</title></head><body><h1>ClearPay</h1><p>Application is starting up. Please try again in a moment.</p></body></html>');
        }
    }

    public function loginPost()
    {
        $session = session();
        $userModel = new UserModel();

        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');
        $rememberMe = $this->request->getPost('remember');

        // Make username check case-sensitive (SQL might be case-insensitive)
        $user = $userModel->where('username', $username)->first();
        if ($user && $user['username'] === $username && password_verify($password, $user['password'])) {
            // Check if user is approved (officers need approval, admins are auto-approved)
            $status = $user['status'] ?? 'approved'; // Default to approved for existing users without status
            if ($user['role'] === 'officer' && $status !== 'approved') {
                if ($status === 'pending') {
                    return redirect()->back()->with('error', 'Your account is pending approval from a Super Admin. Please wait for approval before logging in.');
                } elseif ($status === 'rejected') {
                    return redirect()->back()->with('error', 'Your account has been rejected. Please contact the system administrator.');
                }
            }
            // Normalize profile picture path
            $normalizedProfilePicture = null;
            if (!empty($user['profile_picture'])) {
                $path = $user['profile_picture'];
                
                // Check if it's a Cloudinary URL first - use as-is
                if (strpos($path, 'res.cloudinary.com') !== false) {
                    // Cloudinary URL - store as-is (full URL)
                    $normalizedProfilePicture = $path;
                } else if (strpos($path, 'http://') === 0 || strpos($path, 'https://') === 0) {
                    // Other full URL - use as-is
                    $normalizedProfilePicture = $path;
                } else {
                    // Local path - normalize it
                    // Remove any base_url or http prefixes
                    $path = preg_replace('#^https?://[^/]+/#', '', $path);
                    $path = preg_replace('#^uploads/profile/#', '', $path);
                    $path = preg_replace('#^profile/#', '', $path);
                    $filename = basename($path);
                    
                    // Verify file exists
                    $filePath = FCPATH . 'uploads/profile/' . $filename;
                    if (file_exists($filePath)) {
                        $normalizedProfilePicture = 'uploads/profile/' . $filename;
                    } else {
                        // Try to find a similar file for this user (fallback)
                        $uploadDir = FCPATH . 'uploads/profile/';
                        $pattern = 'user_' . $user['id'] . '_*';
                        $files = glob($uploadDir . $pattern);
                        if (!empty($files)) {
                            usort($files, function($a, $b) {
                                return filemtime($b) - filemtime($a);
                            });
                            $foundFile = basename($files[0]);
                            $normalizedProfilePicture = 'uploads/profile/' . $foundFile;
                            // Update database with correct path
                            try {
                                $userModel->update($user['id'], ['profile_picture' => $normalizedProfilePicture]);
                            } catch (\Exception $e) {
                                log_message('error', 'Failed to update database with correct profile picture path: ' . $e->getMessage());
                            }
                        }
                    }
                }
            }
            
            // Update last_activity timestamp to track online status
            $userModel->update($user['id'], [
                'last_activity' => date('Y-m-d H:i:s')
            ]);
            
            $session->set([
                'user-id'         => $user['id'],
                'username'        => $user['username'],
                'email'           => $user['email'],
                'name'            => $user['name'],
                'role'            => $user['role'],
                'profile_picture' => $normalizedProfilePicture,
                'isLoggedIn'      => true,
            ]);
            
            // Set session flag to force sidebar expanded on first load after login
            $session->set('forceSidebarExpanded', true);
            
            // Handle Remember Me functionality
            if ($rememberMe) {
                $this->setRememberMeToken($user['id']);
            }
            
            return redirect()->to('/dashboard');
        } else {
            return redirect()->back()->with('error', 'Invalid credentials');
        }
    }

    public function logout()
    {
        $session = session();
        
        // Delete remember me token if exists
        $userId = $session->get('user-id');
        if ($userId) {
            $this->clearRememberMeToken($userId);
        }
        
        // Remove only admin-related keys to avoid logging out payer area
        $session->remove([
            'user-id',
            'username',
            'email',
            'name',
            'role',
            'profile_picture',
            'isLoggedIn',
            'forceSidebarExpanded'
        ]);
        return redirect()->to('/');
    }

    public function register()
    {
        return view('admin/register');
    }

    public function registerPost()
    {
        $session = session();
        $userModel = new UserModel();

        // Validation
        $rules = [
            'name' => 'required|min_length[3]|max_length[100]',
            'username' => 'required|min_length[3]|max_length[50]|is_unique[users.username]',
            'email' => 'required|valid_email|max_length[100]|is_unique[users.email]',
            'phone' => 'permit_empty|max_length[20]',
            'password' => 'required|min_length[6]',
            'confirm_password' => 'required|matches[password]',
            'role' => 'required|in_list[officer]' // Only allow officer role for signups
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'success' => false,
                'errors' => $this->validator->getErrors()
            ]);
        }

        // Get role from form (should only be 'officer')
        $role = $this->request->getPost('role') ?? 'officer';
        
        // Prevent admin role selection during signup
        if ($role === 'admin') {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Admin role cannot be selected during signup. Please contact system administrator.'
            ]);
        }

        // Generate verification code
        $verificationCode = rand(100000, 999999);

        // Get form data
        $data = [
            'name' => $this->request->getPost('name'),
            'username' => $this->request->getPost('username'),
            'email' => $this->request->getPost('email'),
            'phone' => $this->request->getPost('phone'),
            'password' => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
            'role' => $role,
            'status' => 'pending', // Officers require approval
            'email_verified' => false,
            'verification_token' => $verificationCode
        ];

        // Save user
        if ($userModel->insert($data)) {
            $userId = $userModel->insertID();
            
            // Store user ID in session for verification
            $session->set('pending_verification_user_id', $userId);
            $session->set('pending_verification_email', $data['email']);
            
            // Send verification email - wrap in try-catch to prevent registration failure
            $emailSent = false;
            try {
                $emailSent = $this->sendVerificationEmail($data['email'], $data['name'], $verificationCode);
            } catch (\Exception $e) {
                log_message('error', 'Exception while sending verification email (non-fatal): ' . $e->getMessage());
            } catch (\Error $e) {
                log_message('error', 'Error while sending verification email (non-fatal): ' . $e->getMessage());
            }
            
            // Log admin user registration activity for other admins
            try {
                $activityLogger = new \App\Services\ActivityLogger();
                $userData = array_merge($data, ['id' => $userId]);
                $activityLogger->logUser('created', $userData);
            } catch (\Exception $e) {
                log_message('error', 'Failed to log admin user registration activity: ' . $e->getMessage());
            }
            
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Registration successful! Please verify your email. Your account will be reviewed by a Super Admin before you can access the system.',
                'email_sent' => $emailSent,
                'email' => $data['email'],
                'verification_code' => $verificationCode, // For testing purposes
                'requires_approval' => true
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Registration failed. Please try again.'
            ]);
        }
    }

    private function sendVerificationEmail($email, $name, $code)
    {
        try {
            // Get email settings from database or config
            $emailConfig = $this->getEmailConfig();
            
            // Validate SMTP credentials
            if (empty($emailConfig['SMTPUser']) || empty($emailConfig['SMTPPass']) || empty($emailConfig['SMTPHost'])) {
                log_message('error', 'SMTP configuration incomplete for admin verification email');
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
                log_message('error', 'SMTP configuration validation failed for admin verification - Host: ' . ($smtpConfig['SMTPHost'] ? 'SET' : 'EMPTY') . ', User: ' . ($smtpConfig['SMTPUser'] ? 'SET' : 'EMPTY') . ', Pass: ' . ($smtpConfig['SMTPPass'] ? 'SET' : 'EMPTY'));
                return false;
            }
            
            $emailService->initialize($smtpConfig);
            
            $emailService->setFrom($emailConfig['fromEmail'], $emailConfig['fromName']);
            $emailService->setTo($email);
            $emailService->setSubject('Email Verification - ClearPay');
            
            $message = view('emails/verification', [
                'name' => $name,
                'code' => $code
            ]);
            
            $emailService->setMessage($message);
            
            // Log SMTP settings for debugging (without password)
            log_message('info', "Attempting to send verification email to: {$email} using SMTP: {$emailConfig['SMTPHost']}:{$emailConfig['SMTPPort']}");
            
            $result = $emailService->send();
            
            if ($result) {
                log_message('info', "Verification email sent successfully to: {$email}");
                return true;
            } else {
                $error = $emailService->printDebugger(['headers', 'subject']);
                log_message('error', "Failed to send verification email: {$error}");
                return false;
            }
        } catch (\Exception $e) {
            log_message('error', 'Failed to send verification email: ' . $e->getMessage());
            log_message('error', 'Exception trace: ' . $e->getTraceAsString());
            return false;
        } catch (\Error $e) {
            log_message('error', 'Failed to send verification email (Error): ' . $e->getMessage());
            log_message('error', 'Error trace: ' . $e->getTraceAsString());
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

    public function verifyEmail()
    {
        $userModel = new UserModel();
        $session = session();

        $verificationCode = $this->request->getPost('verification_code');
        $userId = $session->get('pending_verification_user_id');

        if (!$userId) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Session expired. Please register again.'
            ]);
        }

        $user = $userModel->find($userId);

        if (!$user) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'User not found.'
            ]);
        }

        if ($user['verification_token'] == $verificationCode) {
            // Update user as verified
            $userModel->update($userId, [
                'email_verified' => true,
                'verification_token' => null
            ]);

            // Clear pending verification session
            $session->remove('pending_verification_user_id');
            $session->remove('pending_verification_email');

            // Auto-login
            $session->set([
                'user-id'         => $user['id'],
                'username'        => $user['username'],
                'email'           => $user['email'],
                'name'            => $user['name'],
                'role'            => $user['role'],
                'profile_picture' => $user['profile_picture'] ?? null,
                'isLoggedIn'      => true,
                'forceSidebarExpanded' => true
            ]);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Email verified successfully!',
                'redirect' => base_url('/dashboard')
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Invalid verification code.'
            ]);
        }
    }

    public function resendVerificationCode()
    {
        $userModel = new UserModel();
        $session = session();

        $userId = $session->get('pending_verification_user_id');
        $email = $session->get('pending_verification_email');

        if (!$userId || !$email) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Session expired. Please register again.'
            ]);
        }

        $user = $userModel->find($userId);

        if (!$user) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'User not found.'
            ]);
        }

        // Generate new verification code
        $verificationCode = rand(100000, 999999);
        
        // Update user with new code
        $userModel->update($userId, [
            'verification_token' => $verificationCode
        ]);

        // Send verification email
        $emailSent = $this->sendVerificationEmail($email, $user['name'], $verificationCode);

        if ($emailSent) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Verification code resent successfully!'
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Failed to send verification email. Please try again.'
            ]);
        }
    }

    public function forgotPassword()
    {
        return view('admin/forgot_password');
    }

    public function forgotPasswordPost()
    {
        $userModel = new UserModel();
        
        $email = $this->request->getPost('email');

        if (!$email) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Email is required.'
            ]);
        }

        $user = $userModel->where('email', $email)->first();

        if (!$user) {
            // Don't reveal that user doesn't exist for security
            return $this->response->setJSON([
                'success' => true,
                'message' => 'If an account with that email exists, you will receive a password reset verification code.'
            ]);
        }

        // Generate reset token
        $resetCode = rand(100000, 999999);
        
        // Set expiration to 15 minutes from now
        $expiresAt = date('Y-m-d H:i:s', strtotime('+15 minutes'));

        // Update user with reset token
        $userModel->update($user['id'], [
            'reset_token' => $resetCode,
            'reset_expires' => $expiresAt
        ]);

        // Send password reset email
        $emailSent = $this->sendPasswordResetEmail($email, $user['name'], $resetCode);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'If an account with that email exists, you will receive a password reset verification code.',
            'email_sent' => $emailSent,
            'reset_code' => $resetCode // For testing purposes - remove in production
        ]);
    }

    private function sendPasswordResetEmail($email, $name, $code)
    {
        try {
            // Get email settings from database or config
            $emailConfig = $this->getEmailConfig();
            
            // Use Brevo API for password reset emails (bypasses Render port blocking)
            $brevoApiKey = $_ENV['BREVO_API_KEY'] ?? getenv('BREVO_API_KEY') ?: null;
            
            if (empty($brevoApiKey)) {
                log_message('error', 'BREVO_API_KEY not found. Password reset emails require Brevo API key. Set BREVO_API_KEY environment variable.');
                log_message('info', 'Get API key from Brevo → Settings → SMTP & API → API Keys');
                return false;
            }
            
            // Get email template
            $htmlMessage = view('emails/password_reset', [
                'name' => $name,
                'code' => $code
            ]);
            
            // Extract text version from HTML
            $textMessage = strip_tags($htmlMessage);
            
            // Use Brevo API only (no SMTP fallback for password reset emails)
            try {
                log_message('info', 'Attempting to send password reset email via Brevo API to admin: ' . $email);
                
                // Check if BrevoEmailService class exists
                if (!class_exists('\App\Services\BrevoEmailService')) {
                    log_message('error', 'BrevoEmailService class not found. Code may not be deployed yet.');
                    return false;
                }
                
                $brevoService = new \App\Services\BrevoEmailService(
                    $brevoApiKey,
                    $emailConfig['fromEmail'],
                    $emailConfig['fromName'] ?? 'ClearPay'
                );
                
                $result = $brevoService->send($email, 'Password Reset Request - ClearPay', $htmlMessage, $textMessage);
                
                if ($result['success']) {
                    log_message('info', 'Password reset email sent successfully via Brevo API to admin: ' . $email);
                    return true;
                } else {
                    log_message('error', 'Brevo API failed to send password reset email: ' . ($result['error'] ?? 'Unknown error'));
                    return false;
                }
            } catch (\Exception $apiException) {
                log_message('error', 'Brevo API exception while sending password reset email: ' . $apiException->getMessage());
                log_message('error', 'Exception trace: ' . $apiException->getTraceAsString());
                return false;
            }
        } catch (\Exception $e) {
            log_message('error', 'Failed to send password reset email to admin: ' . $e->getMessage());
            log_message('error', 'Exception trace: ' . $e->getTraceAsString());
            return false;
        } catch (\Error $e) {
            log_message('error', 'Failed to send password reset email to admin (Error): ' . $e->getMessage());
            log_message('error', 'Error trace: ' . $e->getTraceAsString());
            return false;
        }
    }

    public function verifyResetCode()
    {
        $userModel = new UserModel();
        
        $email = trim($this->request->getPost('email'));
        $resetCode = trim($this->request->getPost('reset_code'));

        if (!$email || !$resetCode) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Email and reset code are required.'
            ]);
        }
        
        // Convert to integer for comparison to avoid type issues
        $resetCode = (int)$resetCode;

        $user = $userModel->where('email', $email)->first();

        if (!$user) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Invalid email address.'
            ]);
        }

        // Debug logging
        log_message('info', "Reset code verification attempt for {$email}");
        log_message('info', "Stored token: " . $user['reset_token']);
        log_message('info', "Submitted code: " . $resetCode);
        log_message('info', "Token type: " . gettype($user['reset_token']));
        log_message('info', "Code type: " . gettype($resetCode));

        // Check if reset token matches and hasn't expired
        if ((int)$user['reset_token'] !== $resetCode) {
            log_message('info', "Code mismatch - stored: " . (int)$user['reset_token'] . " vs submitted: " . $resetCode);
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Invalid reset code.'
            ]);
        }

        if (strtotime($user['reset_expires']) < time()) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Reset code has expired. Please request a new one.'
            ]);
        }

        // Store verification in session
        session()->set('reset_verified_user_id', $user['id']);
        session()->set('reset_verified_email', $email);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Verification successful. You can now reset your password.'
        ]);
    }

    public function resetPassword()
    {
        $userModel = new UserModel();
        $session = session();

        $userId = $session->get('reset_verified_user_id');
        $newPassword = $this->request->getPost('password');
        $confirmPassword = $this->request->getPost('confirm_password');

        if (!$userId) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Session expired. Please start the password reset process again.'
            ]);
        }

        if (!$newPassword || !$confirmPassword) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Password and confirmation are required.'
            ]);
        }

        if ($newPassword !== $confirmPassword) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Passwords do not match.'
            ]);
        }

        if (strlen($newPassword) < 6) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Password must be at least 6 characters long.'
            ]);
        }

        // Update password
        $userModel->update($userId, [
            'password' => password_hash($newPassword, PASSWORD_DEFAULT),
            'reset_token' => null,
            'reset_expires' => null
        ]);

        // Clear reset verification session
        $session->remove('reset_verified_user_id');
        $session->remove('reset_verified_email');

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Password reset successfully! You can now login with your new password.',
            'redirect' => base_url('/')
        ]);
    }

    /**
     * Set Remember Me token when user checks "Remember Me" checkbox
     * Generates a secure token and stores it in both database and cookie
     * 
     * @param int $userId The user ID
     * @return void
     */
    private function setRememberMeToken($userId)
    {
        try {
            $rememberTokenModel = new RememberTokenModel();
            
            // Generate a secure random token (64 bytes = 128 hex characters)
            $rawToken = bin2hex(random_bytes(32));
            
            // Set expiry to 30 days from now
            $expiresAt = date('Y-m-d H:i:s', strtotime('+30 days'));
            
            // Save hashed token to database
            $rememberTokenModel->createToken($userId, $rawToken, $expiresAt);
            
            // Set secure cookie with the raw token
            // Only set Secure flag in production (HTTPS required)
            $isSecure = ENVIRONMENT === 'production';
            $cookie = cookie('remember_token', $rawToken, [
                'expires'  => time() + (30 * 24 * 60 * 60), // 30 days
                'httponly' => true,
                'secure'   => $isSecure, // Only send over HTTPS in production
                'samesite' => 'Lax' // CSRF protection
            ]);
            
            $response = service('response');
            $response->setCookie($cookie);
            
            log_message('info', "Remember Me token set for user ID: {$userId}");
        } catch (\Exception $e) {
            // Log error but don't fail login
            log_message('error', 'Failed to set Remember Me token: ' . $e->getMessage());
        }
    }

    /**
     * Clear Remember Me token on logout
     * Deletes token from database and cookie
     * 
     * @param int $userId The user ID
     * @return void
     */
    private function clearRememberMeToken($userId)
    {
        try {
            $rememberTokenModel = new RememberTokenModel();
            
            // Delete token from database
            $rememberTokenModel->deleteToken($userId);
            
            // Clear cookie by setting it to expire in the past
            $isSecure = ENVIRONMENT === 'production';
            $cookie = cookie('remember_token', '', [
                'expires'  => time() - 3600, // 1 hour ago
                'httponly' => true,
                'secure'   => $isSecure,
                'samesite' => 'Lax'
            ]);
            
            $response = service('response');
            $response->setCookie($cookie);
            
            log_message('info', "Remember Me token cleared for user ID: {$userId}");
        } catch (\Exception $e) {
            log_message('error', 'Failed to clear Remember Me token: ' . $e->getMessage());
        }
    }
}