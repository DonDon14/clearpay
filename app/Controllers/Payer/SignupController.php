<?php

namespace App\Controllers\Payer;

use App\Controllers\BaseController;
use App\Models\PayerModel;

class SignupController extends BaseController
{
    protected $payerModel;

    public function __construct()
    {
        $this->payerModel = new PayerModel();
        helper(['phone_helper']);
    }

    public function index()
    {
        // If already logged in, redirect to dashboard
        if (session('payer_id')) {
            return redirect()->to('payer/dashboard');
        }

        return view('payer/signup');
    }

    public function signupPost()
    {
        // Get form data
        $data = [
            'payer_id' => trim($this->request->getPost('payer_id')),
            'payer_name' => trim($this->request->getPost('payer_name')),
            'contact_number' => trim($this->request->getPost('contact_number')),
            'email_address' => trim($this->request->getPost('email_address')),
            'course_department' => trim($this->request->getPost('course_department'))
        ];
        
        $password = $this->request->getPost('password');
        $confirmPassword = $this->request->getPost('confirm_password');

        // Validate required fields
        $validation = \Config\Services::validation();
        $validation->setRules([
            'payer_id' => 'required|min_length[3]|max_length[50]',
            'password' => 'required|min_length[6]|max_length[255]',
            'confirm_password' => 'required|matches[password]',
            'payer_name' => 'required|min_length[3]|max_length[255]',
            'email_address' => 'permit_empty|valid_email|max_length[100]',
            'contact_number' => 'permit_empty',
            'course_department' => 'permit_empty|max_length[100]'
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            $errors = $validation->getErrors();
            return redirect()->back()
                ->withInput()
                ->with('error', implode(', ', $errors));
        }

        try {
            // Check if payer_id already exists (case-sensitive)
            // Get all payers and check for exact case-sensitive match
            $allPayers = $this->payerModel->findAll();
            foreach ($allPayers as $p) {
                if ($p['payer_id'] === $data['payer_id']) {
                    return redirect()->back()
                        ->withInput()
                        ->with('error', 'A payer with this Student ID already exists');
                }
                // Only check email if provided
                if (!empty($data['email_address']) && $p['email_address'] === $data['email_address']) {
                    return redirect()->back()
                        ->withInput()
                        ->with('error', 'A payer with this email address already exists');
                }
            }
            
            // Hash password
            $data['password'] = password_hash($password, PASSWORD_DEFAULT);

            // Validate and sanitize phone number if provided
            if (!empty($data['contact_number'])) {
                // Sanitize phone number (remove non-numeric characters)
                $data['contact_number'] = sanitize_phone_number($data['contact_number']);
                
                // Validate phone number format (must be exactly 11 digits)
                if (!validate_phone_number($data['contact_number'])) {
                    return redirect()->back()
                        ->withInput()
                        ->with('error', 'Contact number must be exactly 11 digits (numbers only)');
                }
            } else {
                $data['contact_number'] = null;
            }

            // Validate email format if provided
            if (!empty($data['email_address']) && !filter_var($data['email_address'], FILTER_VALIDATE_EMAIL)) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Invalid email address format');
            }
            
            // Set email to null if empty
            if (empty($data['email_address'])) {
                $data['email_address'] = null;
            }

            // Handle profile picture upload if provided
            $profilePicturePath = null;
            $file = $this->request->getFile('profile_picture');
            
            if ($file && $file->isValid() && !$file->hasMoved()) {
                // Validate file type
                $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                if (!in_array($file->getMimeType(), $allowedTypes)) {
                    return redirect()->back()
                        ->withInput()
                        ->with('error', 'Invalid image file type. Only JPG, PNG, and GIF are allowed');
                }

                // Validate file size (2MB max)
                if ($file->getSize() > 2 * 1024 * 1024) {
                    return redirect()->back()
                        ->withInput()
                        ->with('error', 'Image size must be less than 2MB');
                }

                // Generate unique filename
                $newName = 'payer_' . $data['payer_id'] . '_' . time() . '.' . $file->getExtension();
                
                // Ensure upload directory exists
                $uploadPath = FCPATH . 'uploads/profile/';
                if (!is_dir($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
                }

                // Move file to uploads directory
                if ($file->move($uploadPath, $newName)) {
                    $profilePicturePath = 'uploads/profile/' . $newName;
                } else {
                    return redirect()->back()
                        ->withInput()
                        ->with('error', 'Failed to upload profile picture. Please try again.');
                }
            }

            // Add profile picture path if uploaded
            if ($profilePicturePath) {
                $data['profile_picture'] = $profilePicturePath;
            }

            // Handle email verification only if email is provided
            $verificationCode = null;
            if (!empty($data['email_address'])) {
                // Generate verification code
                $verificationCode = rand(100000, 999999);
                
                // Add email verification fields
                $data['email_verified'] = false;
                $data['verification_token'] = (string) $verificationCode;
            } else {
                // No email provided - auto-verify (no email verification needed)
                $data['email_verified'] = true;
                $data['verification_token'] = null;
            }

            // Save to database
            $result = $this->payerModel->insert($data);

            if ($result) {
                $payerId = $this->payerModel->getInsertID();
                
                $emailSent = false;
                
                // Only send verification email if email is provided
                if (!empty($data['email_address']) && $verificationCode) {
                    // Store payer ID in session for verification
                    session()->set('pending_verification_payer_id', $payerId);
                    session()->set('pending_verification_email', $data['email_address']);
                    
                    // Send verification email - wrap in try-catch to prevent registration failure
                    try {
                        $emailSent = $this->sendVerificationEmail($data['email_address'], $data['payer_name'], $verificationCode);
                    } catch (\Exception $e) {
                        log_message('error', 'Exception while sending verification email (non-fatal): ' . $e->getMessage());
                    } catch (\Error $e) {
                        log_message('error', 'Error while sending verification email (non-fatal): ' . $e->getMessage());
                    }
                }
                
                // Log payer signup activity for admin notification
                try {
                    $activityLogger = new \App\Services\ActivityLogger();
                    $payerData = array_merge($data, ['id' => $payerId]);
                    // Remove password from activity log
                    unset($payerData['password']);
                    $activityLogger->logPayer('created', $payerData);
                } catch (\Exception $e) {
                    log_message('error', 'Failed to log payer signup activity: ' . $e->getMessage());
                }
                
                // Log success
                log_message('info', 'New payer signed up: ' . $data['payer_name'] . ' (ID: ' . $data['payer_id'] . ')');
                
                // Return JSON response
                if (!empty($data['email_address']) && $verificationCode) {
                    // Email provided - show verification modal
                    return $this->response->setJSON([
                        'success' => true,
                        'message' => 'Account created successfully! Please verify your email.',
                        'email_sent' => $emailSent,
                        'email' => $data['email_address'],
                        'verification_code' => $verificationCode, // For testing purposes
                        'requires_verification' => true
                    ]);
                } else {
                    // No email - account created successfully
                    return $this->response->setJSON([
                        'success' => true,
                        'message' => 'Account created successfully! You can now login.',
                        'requires_verification' => false,
                        'redirect' => base_url('payer/login')
                    ]);
                }
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'error' => 'Failed to create account. Please try again.'
                ]);
            }
        } catch (\Exception $e) {
            log_message('error', 'Payer signup error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'error' => 'An error occurred: ' . $e->getMessage()
            ]);
        }
    }

    public function sendVerificationEmail($email, $name, $code)
    {
        try {
            // Get email settings from database or config
            $emailConfig = $this->getEmailConfig();
            
            // Initialize email service with fresh config
            $emailService = \Config\Services::email();
            
            // Manually configure SMTP settings to ensure they're current
            $emailService->initialize([
                'protocol' => $emailConfig['protocol'],
                'SMTPHost' => $emailConfig['SMTPHost'],
                'SMTPUser' => $emailConfig['SMTPUser'],
                'SMTPPass' => $emailConfig['SMTPPass'],
                'SMTPPort' => $emailConfig['SMTPPort'],
                'SMTPCrypto' => $emailConfig['SMTPCrypto'],
                'SMTPTimeout' => $emailConfig['SMTPTimeout'] ?? 30,
                'mailType' => $emailConfig['mailType'],
                'charset' => $emailConfig['charset'] ?? 'UTF-8',
            ]);
            
            $emailService->setFrom($emailConfig['fromEmail'], $emailConfig['fromName']);
            $emailService->setTo($email);
            $emailService->setSubject('Email Verification - ClearPay Payer Portal');
            
            $message = view('emails/verification', [
                'name' => $name,
                'code' => $code
            ]);
            
            $emailService->setMessage($message);
            
            // Log SMTP settings for debugging (without password)
            log_message('info', "Attempting to send verification email to payer: {$email} using SMTP: {$emailConfig['SMTPHost']}:{$emailConfig['SMTPPort']}");
            
            $result = $emailService->send();
            
            if ($result) {
                log_message('info', "Verification email sent successfully to payer: {$email}");
                return true;
            } else {
                $error = $emailService->printDebugger(['headers', 'subject']);
                log_message('error', "Failed to send verification email to payer: {$error}");
                return false;
            }
        } catch (\Exception $e) {
            log_message('error', 'Failed to send verification email to payer: ' . $e->getMessage());
            log_message('error', 'Exception trace: ' . $e->getTraceAsString());
            return false;
        } catch (\Error $e) {
            log_message('error', 'Failed to send verification email to payer (Error): ' . $e->getMessage());
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
        $session = session();
        $verificationCode = $this->request->getPost('verification_code');
        $payerId = $session->get('pending_verification_payer_id');

        if (!$payerId) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Session expired. Please sign up again.'
            ]);
        }

        $payer = $this->payerModel->find($payerId);

        if (!$payer) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Payer not found.'
            ]);
        }

        // Compare verification codes (both as strings)
        if ((string) $payer['verification_token'] === (string) $verificationCode) {
            // Update payer as verified
            $this->payerModel->update($payerId, [
                'email_verified' => true,
                'verification_token' => null
            ]);

            // Clear pending verification session
            $session->remove('pending_verification_payer_id');
            $session->remove('pending_verification_email');

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Email verified successfully! You can now login.',
                'redirect' => base_url('payer/login')
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
        $session = session();
        $payerId = $session->get('pending_verification_payer_id');
        $email = $session->get('pending_verification_email');

        if (!$payerId || !$email) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Session expired. Please sign up again.'
            ]);
        }

        $payer = $this->payerModel->find($payerId);

        if (!$payer) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Payer not found.'
            ]);
        }

        // Generate new verification code
        $verificationCode = rand(100000, 999999);
        
        // Update payer with new code
        $this->payerModel->update($payerId, [
            'verification_token' => (string) $verificationCode
        ]);

        // Send verification email
        $emailSent = $this->sendVerificationEmail($email, $payer['payer_name'], $verificationCode);

        if ($emailSent) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Verification code resent successfully!'
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Failed to send verification email. Please try again.',
                'verification_code' => $verificationCode // For testing purposes
            ]);
        }
    }

    /**
     * Handle CORS preflight OPTIONS request
     */
    public function handleOptions()
    {
        // Set CORS headers
        $this->response->setHeader('Access-Control-Allow-Origin', '*');
        $this->response->setHeader('Access-Control-Allow-Methods', 'POST, OPTIONS');
        $this->response->setHeader('Access-Control-Allow-Headers', 'Content-Type, Accept, Authorization, X-Requested-With, Origin');
        $this->response->setHeader('Access-Control-Max-Age', '7200');
        return $this->response->setStatusCode(200);
    }

    /**
     * Mobile API signup endpoint - returns JSON response
     */
    public function mobileSignup()
    {
        // Set CORS headers
        $this->response->setHeader('Access-Control-Allow-Origin', '*');
        $this->response->setHeader('Access-Control-Allow-Methods', 'POST, OPTIONS');
        $this->response->setHeader('Access-Control-Allow-Headers', 'Content-Type, Accept, Authorization, X-Requested-With, Origin');
        $this->response->setHeader('Access-Control-Max-Age', '7200');

        // Get form data from POST or JSON
        $postData = $this->request->getPost();
        $jsonData = $this->request->getJSON(true) ?? [];
        $data = array_merge($postData, $jsonData);

        $formData = [
            'payer_id' => trim($data['payer_id'] ?? ''),
            'payer_name' => trim($data['payer_name'] ?? ''),
            'contact_number' => trim($data['contact_number'] ?? ''),
            'email_address' => trim($data['email_address'] ?? ''),
            'course_department' => trim($data['course_department'] ?? '')
        ];
        
        $password = $data['password'] ?? '';
        $confirmPassword = $data['confirm_password'] ?? '';

        // Validate required fields
        $validation = \Config\Services::validation();
        $validation->setRules([
            'payer_id' => 'required|min_length[3]|max_length[50]',
            'password' => 'required|min_length[6]|max_length[255]',
            'confirm_password' => 'required|matches[password]',
            'payer_name' => 'required|min_length[3]|max_length[255]',
            'email_address' => 'permit_empty|valid_email|max_length[100]',
            'contact_number' => 'permit_empty',
            'course_department' => 'permit_empty|max_length[100]'
        ]);

        if (!$validation->run($data)) {
            $errors = $validation->getErrors();
            return $this->response->setJSON([
                'success' => false,
                'error' => implode(', ', $errors)
            ]);
        }

        try {
            // Check if payer_id already exists (case-sensitive)
            $allPayers = $this->payerModel->findAll();
            foreach ($allPayers as $p) {
                if ($p['payer_id'] === $formData['payer_id']) {
                    return $this->response->setJSON([
                        'success' => false,
                        'error' => 'A payer with this Student ID already exists'
                    ]);
                }
                // Only check email if provided
                if (!empty($formData['email_address']) && $p['email_address'] === $formData['email_address']) {
                    return $this->response->setJSON([
                        'success' => false,
                        'error' => 'A payer with this email address already exists'
                    ]);
                }
            }
            
            // Hash password
            $formData['password'] = password_hash($password, PASSWORD_DEFAULT);

            // Validate and sanitize phone number if provided
            if (!empty($formData['contact_number'])) {
                $formData['contact_number'] = sanitize_phone_number($formData['contact_number']);
                
                if (!validate_phone_number($formData['contact_number'])) {
                    return $this->response->setJSON([
                        'success' => false,
                        'error' => 'Contact number must be exactly 11 digits (numbers only)'
                    ]);
                }
            } else {
                $formData['contact_number'] = null;
            }

            // Validate email format if provided
            if (!empty($formData['email_address']) && !filter_var($formData['email_address'], FILTER_VALIDATE_EMAIL)) {
                return $this->response->setJSON([
                    'success' => false,
                    'error' => 'Invalid email address format'
                ]);
            }

            // Handle email verification
            $verificationCode = null;
            if (!empty($formData['email_address'])) {
                $verificationCode = rand(100000, 999999);
                $formData['email_verified'] = false;
                $formData['verification_token'] = (string) $verificationCode;
            } else {
                $formData['email_verified'] = true;
                $formData['verification_token'] = null;
            }

            // Save to database
            $result = $this->payerModel->insert($formData);

            if ($result) {
                $payerId = $this->payerModel->getInsertID();
                
                $emailSent = false;
                
                // Only send verification email if email is provided
                if (!empty($formData['email_address']) && $verificationCode) {
                    // Store payer ID in session for verification
                    session()->set('pending_verification_payer_id', $payerId);
                    session()->set('pending_verification_email', $formData['email_address']);
                    
                    // Send verification email
                    try {
                        $emailSent = $this->sendVerificationEmail($formData['email_address'], $formData['payer_name'], $verificationCode);
                    } catch (\Exception $e) {
                        log_message('error', 'Exception while sending verification email (non-fatal): ' . $e->getMessage());
                    }
                }
                
                // Log payer signup activity
                try {
                    $activityLogger = new \App\Services\ActivityLogger();
                    $payerData = array_merge($formData, ['id' => $payerId]);
                    unset($payerData['password']);
                    $activityLogger->logPayer('created', $payerData);
                } catch (\Exception $e) {
                    log_message('error', 'Failed to log payer signup activity: ' . $e->getMessage());
                }
                
                // Return JSON response
                if (!empty($formData['email_address']) && $verificationCode) {
                    return $this->response->setJSON([
                        'success' => true,
                        'message' => 'Account created successfully! Please verify your email.',
                        'email_sent' => $emailSent,
                        'email' => $formData['email_address'],
                        'verification_code' => $verificationCode, // For testing purposes
                        'requires_verification' => true
                    ]);
                } else {
                    return $this->response->setJSON([
                        'success' => true,
                        'message' => 'Account created successfully! You can now login.',
                        'requires_verification' => false
                    ]);
                }
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'error' => 'Failed to create account. Please try again.'
                ]);
            }
        } catch (\Exception $e) {
            log_message('error', 'Payer signup error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'error' => 'An error occurred: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Mobile API verify email endpoint
     * Now works without session - uses email + verification code
     */
    public function mobileVerifyEmail()
    {
        // Set CORS headers
        $this->response->setHeader('Access-Control-Allow-Origin', '*');
        $this->response->setHeader('Access-Control-Allow-Methods', 'POST, OPTIONS');
        $this->response->setHeader('Access-Control-Allow-Headers', 'Content-Type, Accept, Authorization, X-Requested-With, Origin');
        $this->response->setHeader('Access-Control-Max-Age', '7200');

        $data = $this->request->getPost();
        $jsonData = $this->request->getJSON(true) ?? [];
        $verificationCode = $data['verification_code'] ?? $jsonData['verification_code'] ?? null;
        $email = $data['email'] ?? $jsonData['email'] ?? null;

        if (!$verificationCode) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Verification code is required.'
            ]);
        }

        // Try to find payer by email + verification code (session-independent)
        $payer = null;
        
        if ($email) {
            // Find by email and verification code
            $payers = $this->payerModel->where('email_address', $email)
                ->where('verification_token', (string) $verificationCode)
                ->where('email_verified', false)
                ->findAll();
            
            if (!empty($payers)) {
                $payer = $payers[0];
            }
        }
        
        // Fallback: Try session if available (for backward compatibility)
        if (!$payer) {
            $session = session();
            $payerId = $session->get('pending_verification_payer_id');
            
            if ($payerId) {
                $payer = $this->payerModel->find($payerId);
                
                if ($payer && (string) $payer['verification_token'] === (string) $verificationCode) {
                    // Payer found via session
                } else {
                    $payer = null;
                }
            }
        }

        if (!$payer) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Invalid verification code or email. Please check your email and try again.'
            ]);
        }

        // Verify the code matches
        if ((string) $payer['verification_token'] !== (string) $verificationCode) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Invalid verification code.'
            ]);
        }

        // Update payer as verified
        $this->payerModel->update($payer['id'], [
            'email_verified' => true,
            'verification_token' => null
        ]);

        // Clear pending verification session if exists
        $session = session();
        $session->remove('pending_verification_payer_id');
        $session->remove('pending_verification_email');

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Email verified successfully! You can now login.'
        ]);
    }

    /**
     * Mobile API resend verification code endpoint
     */
    public function mobileResendVerificationCode()
    {
        // Set CORS headers
        $this->response->setHeader('Access-Control-Allow-Origin', '*');
        $this->response->setHeader('Access-Control-Allow-Methods', 'POST, OPTIONS');
        $this->response->setHeader('Access-Control-Allow-Headers', 'Content-Type, Accept, Authorization, X-Requested-With, Origin');
        $this->response->setHeader('Access-Control-Max-Age', '7200');

        $session = session();
        $payerId = $session->get('pending_verification_payer_id');
        $email = $session->get('pending_verification_email');

        if (!$payerId || !$email) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Session expired. Please sign up again.'
            ]);
        }

        $payer = $this->payerModel->find($payerId);

        if (!$payer) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Payer not found.'
            ]);
        }

        // Generate new verification code
        $verificationCode = rand(100000, 999999);
        
        // Update payer with new code
        $this->payerModel->update($payerId, [
            'verification_token' => (string) $verificationCode
        ]);

        // Send verification email
        $emailSent = $this->sendVerificationEmail($email, $payer['payer_name'], $verificationCode);

        if ($emailSent) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Verification code resent successfully!',
                'verification_code' => $verificationCode // For testing purposes
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Failed to send verification email. Please try again.',
                'verification_code' => $verificationCode // For testing purposes
            ]);
        }
    }
}

