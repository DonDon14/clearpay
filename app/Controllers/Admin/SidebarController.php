<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class SidebarController extends BaseController
{
    private function checkAuth()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }
        return null;
    }

    public function payments()
    {
        $authCheck = $this->checkAuth();
        if ($authCheck) return $authCheck;

        $data = [
            'title' => 'Payments Management',
            'pageTitle' => 'Payments',
            'pageSubtitle' => 'Manage student payments and transactions',
            'username' => session()->get('username'),
        ];

        return view('admin/payments', $data);
    }

    public function contributions()
    {
        $authCheck = $this->checkAuth();
        if ($authCheck) return $authCheck;

        $data = [
            'title' => 'Contributions Management',
            'pageTitle' => 'Contributions',
            'pageSubtitle' => 'Manage fee types and contributions',
            'username' => session()->get('username'),
        ];

        return view('admin/contributions', $data);
    }

    public function students()
    {
        $authCheck = $this->checkAuth();
        if ($authCheck) return $authCheck;

        $data = [
            'title' => 'Students Management',
            'pageTitle' => 'Students',
            'pageSubtitle' => 'Manage student records and information',
            'username' => session()->get('username'),
        ];

        return view('admin/students', $data);
    }

    public function announcements()
    {
        $authCheck = $this->checkAuth();
        if ($authCheck) return $authCheck;

        $data = [
            'title' => 'Announcements',
            'pageTitle' => 'Announcements',
            'pageSubtitle' => 'Manage system announcements',
            'username' => session()->get('username'),
        ];

        return view('admin/announcements', $data);
    }
}
