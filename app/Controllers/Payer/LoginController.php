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
}
