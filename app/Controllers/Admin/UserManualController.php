<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class UserManualController extends BaseController
{
    public function index()
    {
        // Check if user is logged in
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/admin/login');
        }

        $data = [
            'title' => 'User Manual',
            'pageTitle' => 'User Manual',
            'pageSubtitle' => 'Complete guide to using ClearPay',
            'username' => session()->get('username')
        ];

        return view('admin/user-manual', $data);
    }
}

