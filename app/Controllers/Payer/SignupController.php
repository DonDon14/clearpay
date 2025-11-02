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
            // Initialize email service
            $emailService = \Config\Services::email();
            
            // Use configured email settings
            $config = config('Email');
            $fromEmail = $config->fromEmail;
            $fromName = $config->fromName;
            
            $emailService->setFrom($fromEmail, $fromName);
            $emailService->setTo($email);
            $emailService->setSubject('Email Verification - ClearPay Payer Portal');
            
            $message = view('emails/verification', [
                'name' => $name,
                'code' => $code
            ]);
            
            $emailService->setMessage($message);
            
            // Log SMTP settings for debugging
            log_message('info', "Attempting to send verification email to payer: {$email}");
            
            // Suppress errors during email sending and log instead
            $oldErrorReporting = error_reporting(0);
            $result = @$emailService->send();
            error_reporting($oldErrorReporting);
            
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
            return false;
        } catch (\Error $e) {
            log_message('error', 'Failed to send verification email to payer (Error): ' . $e->getMessage());
            return false;
        }
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
}

