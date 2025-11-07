<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class ApiDocumentationController extends BaseController
{
    public function index()
    {
        // Check if user is logged in
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/admin/login');
        }

        $data = [
            'title' => 'API Documentation',
            'pageTitle' => 'API Documentation',
            'pageSubtitle' => 'Complete API reference for ClearPay',
            'username' => session()->get('username')
        ];

        return view('admin/api-documentation', $data);
    }
}

