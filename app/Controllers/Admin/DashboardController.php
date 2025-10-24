<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UserModel;

class DashboardController extends BaseController
{
    public function index()
    {
        // Check if user is logged in
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/admin/login');
        }

        $userModel = new UserModel();
        $user = $userModel->select('username')
                            ->where('id', session()->get('user-id'))
                            ->first();

        // Example: pass session data to the view
        $data = [
            'title' => 'Admin Dashboard',
            'pageTitle' => 'Dashboard',
            'pageSubtitle' => 'Welcome back ' . ucwords($user['username'] ?? 'User') . '!',
            'username' => $user['username'] ?? 'User',
        ];

        return view('admin/dashboard', $data);
    }

    public function payments()
    {
        // Check if user is logged in
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/admin/login');
        }

        // Example: pass session data to the view
        $data = [
            'title' => 'Admin Payments',
            'pageTitle' => 'Payments',
            'pageSubtitle' => 'Manage payments and transactions',
            'username' => session()->get('username'),
        ];

        return view('admin/payments', $data);
    }

}
