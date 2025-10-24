<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class SidebarController extends BaseController
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
    public function contributions()
    {
        // Check if user is logged in
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/admin/login');
        }

        // Example: pass session data to the view
        $data = [
            'title' => 'Contributions',
            'pageTitle' => 'Contributions',
            'pageSubtitle' => 'Manage contributions and donations',
            'username' => session()->get('username'),
        ];

        return view('admin/contributions', $data);
    }
    
    public function partialPayments()
    {
        // Check if user is logged in
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/admin/login');
        }

        // Example: pass session data to the view
        $data = [
            'title' => 'Partial Payments',
            'pageTitle' => 'Partial Payments',
            'pageSubtitle' => 'Manage partial payments and transactions',
            'username' => session()->get('username'),
        ];

        return view('admin/partial_payments', $data);
    }

    public function history()
    {
        // Check if user is logged in
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/admin/login');
        }

        // Example: pass session data to the view
        $data = [
            'title' => 'Payment History',
            'pageTitle' => 'Payment History',
            'pageSubtitle' => 'View payment history and details',
            'username' => session()->get('username'),
        ];

        return view('admin/history', $data);
    }

    public function analytics()
    {
        // Check if user is logged in
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/admin/login');
        }

        // Example: pass session data to the view
        $data = [
            'title' => 'Analytics',
            'pageTitle' => 'Analytics',
            'pageSubtitle' => 'View analytics and reports',
            'username' => session()->get('username'),
        ];

        return view('admin/analytics', $data);
    }

    public function payers()
    {
        // Check if user is logged in
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/admin/login');
        }

        // Example: pass session data to the view
        $data = [
            'title' => 'Students Management',
            'pageTitle' => 'Payers',
            'pageSubtitle' => 'Manage student records and information',
            'username' => session()->get('username'),
        ];

        return view('admin/payers', $data);
    }
    public function announcements()
    {
        // Check if user is logged in
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/admin/login');
        }

        // Example: pass session data to the view
        $data = [
            'title' => 'Announcements',
            'pageTitle' => 'Announcements',
            'pageSubtitle' => 'Manage announcements and notifications',
            'username' => session()->get('username'),
        ];

        return view('admin/announcements', $data);
    }

    public function profile()
    {
        // Check if user is logged in
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/admin/login');
        }

        // Example: pass session data to the view
        $data = [
            'title' => 'Profile',
            'pageTitle' => 'Profile',
            'pageSubtitle' => 'View and edit your profile',
            'username' => session()->get('username'),
        ];

        return view('admin/profile', $data);
    }

    public function settings()
    {
        // Check if user is logged in
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/admin/login');
        }

        // Example: pass session data to the view
        $data = [
            'title' => 'Settings',
            'pageTitle' => 'Settings',
            'pageSubtitle' => 'Manage your account settings',
            'username' => session()->get('username'),
        ];

        return view('admin/settings', $data);
    }
}
