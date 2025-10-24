<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class PaymentsController extends BaseController
{
    public function index()
    {
        // Check if user is logged in
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $data = [
            'title' => 'Payments Management',
            'pageTitle' => 'Payments',
            'pageSubtitle' => 'Manage student payments and transactions',
            'username' => session()->get('username'),
        ];

        return view('admin/payments', $data);
    }

    public function history()
    {
        // Check if user is logged in
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $data = [
            'title' => 'Payment History',
            'pageTitle' => 'Payment History',
            'pageSubtitle' => 'View all payment transactions and records',
            'username' => session()->get('username'),
        ];

        return view('admin/payment-history', $data);
    }

    public function analytics()
    {
        // Check if user is logged in
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $data = [
            'title' => 'Payment Analytics',
            'pageTitle' => 'Analytics & Reports',
            'pageSubtitle' => 'View payment statistics and generate reports',
            'username' => session()->get('username'),
        ];

        return view('admin/analytics', $data);
    }
}