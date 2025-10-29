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
        $email = $this->request->getPost('email_address');

        $validation = \Config\Services::validation();
        $validation->setRules([
            'payer_id' => 'required',
            'email_address' => 'required|valid_email'
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Please enter valid Payer ID and Email');
        }

        // Find payer by payer_id and email (case-sensitive matching)
        // Get all payers and filter by exact case-sensitive match
        $payers = $this->payerModel->findAll();
        $payer = null;
        
        foreach ($payers as $p) {
            // Exact case-sensitive comparison
            if ($p['payer_id'] === $payerId && $p['email_address'] === $email) {
                $payer = $p;
                break;
            }
        }

        if (!$payer) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Invalid Payer ID or Email Address');
        }

        // Check if email is verified
        if (isset($payer['email_verified']) && !$payer['email_verified']) {
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
            'payer_id' => $payer['id'],
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
        session()->destroy();
        return redirect()->to('payer/login');
    }
}
