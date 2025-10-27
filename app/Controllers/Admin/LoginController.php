<?php

namespace App\Controllers\Admin;

use App\Models\UserModel;
use CodeIgniter\Controller;
use CodeIgniter\Email\Email;

class LoginController extends Controller
{
    public function index()
    {
        return view('admin/login');
    }

    public function loginPost()
    {
        $session = session();
        $userModel = new UserModel();

        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');

        $user = $userModel->where('username', $username)->first();

        if($user && password_verify($password, $user['password'])) {
            $session->set([
                'user-id'         => $user['id'],
                'username'        => $user['username'],
                'email'           => $user['email'],
                'name'            => $user['name'],
                'role'            => $user['role'],
                'profile_picture' => $user['profile_picture'] ?? null,
                'isLoggedIn'      => true,
            ]);
            
            // Set session flag to force sidebar expanded on first load after login
            $session->set('forceSidebarExpanded', true);
            
            return redirect()->to('/dashboard');
        } else {
            return redirect()->back()->with('error', 'Invalid credentials');
        }
    }

    public function logout()
    {
        session()->destroy();
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
            'role' => 'permit_empty|in_list[admin,officer]'
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'success' => false,
                'errors' => $this->validator->getErrors()
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
            'role' => $this->request->getPost('role') ?? 'officer',
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
            
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Registration successful! Please verify your email.',
                'email_sent' => $emailSent,
                'email' => $data['email'],
                'verification_code' => $verificationCode // For testing purposes
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
            // Initialize email service - it will load the Email config automatically
            $emailService = \Config\Services::email();
            
            // Use configured email settings
            $config = config('Email');
            $fromEmail = $config->fromEmail;
            $fromName = $config->fromName;
            
            $emailService->setFrom($fromEmail, $fromName);
            $emailService->setTo($email);
            $emailService->setSubject('Email Verification - ClearPay');
            
            $message = view('emails/verification', [
                'name' => $name,
                'code' => $code
            ]);
            
            $emailService->setMessage($message);
            
            // Log SMTP settings for debugging
            log_message('info', "Attempting to send email to: {$email}");
            log_message('info', "Using SMTP: {$config->SMTPHost}:{$config->SMTPPort} with user: {$config->SMTPUser}");
            
            // Suppress errors during email sending and log instead
            $oldErrorReporting = error_reporting(0);
            $result = @$emailService->send();
            error_reporting($oldErrorReporting);
            
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
            log_message('error', 'Exception details: ' . $e->getTraceAsString());
            return false;
        } catch (\Error $e) {
            log_message('error', 'Failed to send verification email (Error): ' . $e->getMessage());
            log_message('error', 'Exception details: ' . $e->getTraceAsString());
            return false;
        }
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
            $emailService = \Config\Services::email();
            
            $config = config('Email');
            $fromEmail = $config->fromEmail;
            $fromName = $config->fromName;
            
            $emailService->setFrom($fromEmail, $fromName);
            $emailService->setTo($email);
            $emailService->setSubject('Password Reset Request - ClearPay');
            
            $message = view('emails/password_reset', [
                'name' => $name,
                'code' => $code
            ]);
            
            $emailService->setMessage($message);
            
            log_message('info', "Attempting to send password reset email to: {$email}");
            
            $oldErrorReporting = error_reporting(0);
            $result = @$emailService->send();
            error_reporting($oldErrorReporting);
            
            if ($result) {
                log_message('info', "Password reset email sent successfully to: {$email}");
                return true;
            } else {
                $error = $emailService->printDebugger(['headers', 'subject']);
                log_message('error', "Failed to send password reset email: {$error}");
                return false;
            }
        } catch (\Exception $e) {
            log_message('error', 'Failed to send password reset email: ' . $e->getMessage());
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
}