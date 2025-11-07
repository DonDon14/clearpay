<?php

namespace App\Controllers\Payer;

use App\Controllers\BaseController;
use App\Models\PayerModel;

class LoginController extends BaseController
{
    protected $payerModel;

    public function __construct()
    {
        $this->payerModel = new PayerModel();
    }

    public function index()
    {
        // If already logged in, redirect to dashboard
        if (session('payer_id')) {
            return redirect()->to('payer/dashboard');
        }

        return view('payer/login');
    }

    public function loginPost()
    {
        $payerId = $this->request->getPost('payer_id');
        $password = $this->request->getPost('password');

        $validation = \Config\Services::validation();
        $validation->setRules([
            'payer_id' => 'required',
            'password' => 'required'
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Please enter your Username and Password');
        }

        // Find payer by payer_id (case-sensitive matching)
        $payers = $this->payerModel->findAll();
        $payer = null;
        
        foreach ($payers as $p) {
            // Exact case-sensitive comparison
            if ($p['payer_id'] === $payerId) {
                $payer = $p;
                break;
            }
        }

        if (!$payer) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Invalid Username or Password');
        }
        
        // Check if password exists (required for all payers)
        if (empty($payer['password'])) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Password not set. Please contact administrator.');
        }
        
        // Verify password
        if (!password_verify($password, $payer['password'])) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Invalid Username or Password');
        }

        // Check if email is verified (only if email exists)
        if (!empty($payer['email_address']) && isset($payer['email_verified']) && !$payer['email_verified']) {
            // Store payer info in session for resend verification
            session()->set('pending_verification_payer_id', $payer['id']);
            session()->set('pending_verification_email', $payer['email_address']);
            
            // Resend verification code if not exists
            if (empty($payer['verification_token'])) {
                $verificationCode = rand(100000, 999999);
                $this->payerModel->update($payer['id'], [
                    'verification_token' => (string) $verificationCode
                ]);
                
                // Send verification email
                $signupController = new \App\Controllers\Payer\SignupController();
                $signupController->sendVerificationEmail($payer['email_address'], $payer['payer_name'], $verificationCode);
            }
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Please verify your email address before logging in. Please check your email for the verification code or sign up again to receive a new code.');
        }

        // Set session data
        session()->set([
            // Database primary key (internal)
            'payer_id' => $payer['id'],
            // Public-facing student/payer ID (e.g., 154989)
            'payer_student_id' => $payer['payer_id'],
            'payer_name' => $payer['payer_name'],
            'payer_email' => $payer['email_address'],
            'payer_profile_picture' => $payer['profile_picture'] ?? null,
            'payer_logged_in' => true,
            'payer_last_activity' => time(), // Track last activity for timeout
        ]);

        // Force sidebar expanded on first load
        session()->set('forceSidebarExpanded', true);

        return redirect()->to('payer/dashboard');
    }

    public function logout()
    {
        // Remove only payer-related keys so admin session remains intact
        session()->remove([
            'payer_id',
            'payer_student_id',
            'payer_name',
            'payer_email',
            'payer_profile_picture',
            'payer_logged_in',
            'payer_last_activity'
        ]);
        return redirect()->to('payer/login');
    }

    /**
     * Handle CORS preflight OPTIONS request
     */
    public function handleOptions()
    {
        return $this->response->setStatusCode(200);
    }

    /**
     * Mobile API login endpoint - returns JSON response
     */
    public function mobileLogin()
    {
        // Check if request is JSON
        if (!$this->request->isAJAX() && !$this->request->getHeaderLine('Content-Type')) {
            // Allow mobile requests
        }

        $payerId = $this->request->getPost('payer_id') ?? $this->request->getJSON(true)['payer_id'] ?? null;
        $password = $this->request->getPost('password') ?? $this->request->getJSON(true)['password'] ?? null;

        if (!$payerId || !$password) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Please enter your Username and Password'
            ]);
        }

        // Find payer by payer_id (case-sensitive matching)
        $payers = $this->payerModel->findAll();
        $payer = null;
        
        foreach ($payers as $p) {
            if ($p['payer_id'] === $payerId) {
                $payer = $p;
                break;
            }
        }

        if (!$payer) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Invalid Username or Password'
            ]);
        }
        
        // Check if password exists
        if (empty($payer['password'])) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Password not set. Please contact administrator.'
            ]);
        }
        
        // Verify password
        if (!password_verify($password, $payer['password'])) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Invalid Username or Password'
            ]);
        }

        // Check if email is verified (only if email exists)
        if (!empty($payer['email_address']) && isset($payer['email_verified']) && !$payer['email_verified']) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Please verify your email address before logging in. Please check your email for the verification code or sign up again to receive a new code.',
                'requires_verification' => true
            ]);
        }

        // Return user data for mobile app (don't use session for mobile)
        return $this->response->setJSON([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'id' => (int)$payer['id'], // Ensure ID is an integer
                'payer_id' => $payer['payer_id'],
                'payer_name' => $payer['payer_name'],
                'email' => $payer['email_address'] ?? '',
                'email_address' => $payer['email_address'] ?? '', // For compatibility
                'contact_number' => $payer['contact_number'] ?? '',
                'phone_number' => $payer['contact_number'] ?? '', // For compatibility
                'profile_picture' => $payer['profile_picture'] ?? null,
            ],
            // For mobile, you might want to use JWT tokens instead
            // For now, return a simple token (you should implement proper JWT)
            'token' => base64_encode($payer['id'] . ':' . time())
        ]);
    }

    public function forgotPassword()
    {
        // If already logged in, redirect to dashboard
        if (session('payer_id')) {
            return redirect()->to('payer/dashboard');
        }

        return view('payer/forgot_password');
    }

    public function forgotPasswordPost()
    {
        $email = $this->request->getPost('email');

        if (!$email) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Email is required.'
            ]);
        }

        // Find payer by email_address (case-sensitive matching)
        $payers = $this->payerModel->findAll();
        $payer = null;
        
        foreach ($payers as $p) {
            if (!empty($p['email_address']) && strtolower($p['email_address']) === strtolower($email)) {
                $payer = $p;
                break;
            }
        }

        if (!$payer || empty($payer['email_address'])) {
            // Don't reveal that payer doesn't exist for security
            return $this->response->setJSON([
                'success' => true,
                'message' => 'If an account with that email exists, you will receive a password reset verification code.'
            ]);
        }

        // Generate reset token
        $resetCode = rand(100000, 999999);
        
        // Set expiration to 15 minutes from now
        $expiresAt = date('Y-m-d H:i:s', strtotime('+15 minutes'));

        // Update payer with reset token
        $this->payerModel->update($payer['id'], [
            'reset_token' => (string) $resetCode,
            'reset_expires' => $expiresAt
        ]);

        // Send password reset email
        $emailSent = $this->sendPasswordResetEmail($payer['email_address'], $payer['payer_name'], $resetCode);

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
            $emailService->setSubject('Password Reset Request - ClearPay Payer Portal');
            
            $message = view('emails/password_reset', [
                'name' => $name,
                'code' => $code
            ]);
            
            $emailService->setMessage($message);
            
            log_message('info', "Attempting to send password reset email to payer: {$email}");
            
            $oldErrorReporting = error_reporting(0);
            $result = @$emailService->send();
            error_reporting($oldErrorReporting);
            
            if ($result) {
                log_message('info', "Password reset email sent successfully to payer: {$email}");
                return true;
            } else {
                $error = $emailService->printDebugger(['headers', 'subject']);
                log_message('error', "Failed to send password reset email to payer: {$error}");
                return false;
            }
        } catch (\Exception $e) {
            log_message('error', 'Failed to send password reset email to payer: ' . $e->getMessage());
            return false;
        }
    }

    public function verifyResetCode()
    {
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

        // Find payer by email_address
        $payers = $this->payerModel->findAll();
        $payer = null;
        
        foreach ($payers as $p) {
            if (!empty($p['email_address']) && strtolower($p['email_address']) === strtolower($email)) {
                $payer = $p;
                break;
            }
        }

        if (!$payer || empty($payer['email_address'])) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Invalid email address.'
            ]);
        }

        // Debug logging
        log_message('info', "Reset code verification attempt for payer: {$email}");
        log_message('info', "Stored token: " . $payer['reset_token']);
        log_message('info', "Submitted code: " . $resetCode);

        // Check if reset token matches and hasn't expired
        if (empty($payer['reset_token']) || (int)$payer['reset_token'] !== $resetCode) {
            log_message('info', "Code mismatch - stored: " . (int)$payer['reset_token'] . " vs submitted: " . $resetCode);
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Invalid reset code.'
            ]);
        }

        if (empty($payer['reset_expires']) || strtotime($payer['reset_expires']) < time()) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Reset code has expired. Please request a new one.'
            ]);
        }

        // Store verification in session
        session()->set('reset_verified_payer_id', $payer['id']);
        session()->set('reset_verified_email', $email);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Verification successful. You can now reset your password.'
        ]);
    }

    public function resetPassword()
    {
        $session = session();

        $payerId = $session->get('reset_verified_payer_id');
        $newPassword = $this->request->getPost('password');
        $confirmPassword = $this->request->getPost('confirm_password');

        if (!$payerId) {
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
        $this->payerModel->update($payerId, [
            'password' => password_hash($newPassword, PASSWORD_DEFAULT),
            'reset_token' => null,
            'reset_expires' => null
        ]);

        // Clear reset verification session
        $session->remove('reset_verified_payer_id');
        $session->remove('reset_verified_email');

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Password reset successfully! You can now login with your new password.',
            'redirect' => base_url('payer/login')
        ]);
    }
}
