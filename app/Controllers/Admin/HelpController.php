<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class HelpController extends BaseController
{
    public function index()
    {
        // Check if user is logged in
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/admin/login');
        }

        $data = [
            'title' => 'Help & Support',
            'pageTitle' => 'Help & Support',
            'pageSubtitle' => 'Get assistance and find answers to common questions',
            'username' => session()->get('username')
        ];

        return view('admin/help', $data);
    }
}

