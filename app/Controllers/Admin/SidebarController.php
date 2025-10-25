<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\ContributionModel;
use App\Models\PaymentModel;

class SidebarController extends BaseController
{

    public function payments()
    {
        // Check if user is logged in
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/admin/login');
        }

        // Fetch contributions for the modal dropdown
        $contributionModel = new ContributionModel();
        $contributions = $contributionModel->where('status', 'active')->findAll();

        // Fetch recent payments for display
        $paymentModel = new PaymentModel();
        $recentPayments = $paymentModel->getRecentPayments(10);

        // Example: pass session data to the view
        $data = [
            'title' => 'Admin Payments',
            'pageTitle' => 'Payments',
            'pageSubtitle' => 'Manage payments and transactions',
            'username' => session()->get('username'),
            'contributions' => $contributions,
            'recentPayments' => $recentPayments
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

        // Fetch contributions for the modal dropdown
        $contributionModel = new ContributionModel();
        $contributions = $contributionModel->where('status', 'active')->findAll();

        // Example: pass session data to the view
        $data = [
            'title' => 'Partial Payments',
            'pageTitle' => 'Partial Payments',
            'pageSubtitle' => 'Manage partial payments and transactions',
            'username' => session()->get('username'),
            'contributions' => $contributions,
        ];

        return view('admin/partial_payments', $data);
    }

    public function history()
    {
        // Check if user is logged in
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/admin/login');
        }

        // Fetch contributions for the modal dropdown
        $contributionModel = new ContributionModel();
        $contributions = $contributionModel->where('status', 'active')->findAll();

        // Example: pass session data to the view
        $data = [
            'title' => 'Payment History',
            'pageTitle' => 'Payment History',
            'pageSubtitle' => 'View payment history and details',
            'username' => session()->get('username'),
            'contributions' => $contributions,
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
