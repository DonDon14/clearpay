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

        // Find payer by payer_id and email
        $payer = $this->payerModel->where('payer_id', $payerId)
            ->where('email_address', $email)
            ->first();

        if (!$payer) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Invalid Payer ID or Email Address');
        }

        // Set session data
        session()->set([
            'payer_id' => $payer['id'],
            'payer_name' => $payer['payer_name'],
            'payer_email' => $payer['email_address'],
            'payer_logged_in' => true,
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
