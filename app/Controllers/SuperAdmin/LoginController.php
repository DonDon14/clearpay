<?php

namespace App\Controllers\SuperAdmin;

use App\Models\UserModel;
use CodeIgniter\Controller;

class LoginController extends Controller
{
    public function index()
    {
        // If already logged in as super admin, redirect to portal
        if (session()->get('isSuperAdmin')) {
            return redirect()->to('/super-admin/portal');
        }

        return view('super-admin/login');
    }

    public function loginPost()
    {
        $session = session();
        $userModel = new UserModel();

        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');

        // Make username check case-sensitive
        $user = $userModel->where('username', $username)->first();
        
        if ($user && $user['username'] === $username && password_verify($password, $user['password'])) {
            // Check if user is a super admin (role = 'admin')
            if ($user['role'] !== 'admin') {
                return redirect()->back()->with('error', 'Access denied. This portal is only for Super Admins.');
            }

            // Update last_activity timestamp
            $userModel->update($user['id'], [
                'last_activity' => date('Y-m-d H:i:s')
            ]);

            // Set super admin session
            $session->set([
                'super-admin-id' => $user['id'],
                'super-admin-username' => $user['username'],
                'super-admin-email' => $user['email'],
                'super-admin-name' => $user['name'],
                'isSuperAdmin' => true,
            ]);

            return redirect()->to('/super-admin/portal');
        } else {
            return redirect()->back()->with('error', 'Invalid credentials');
        }
    }

    public function logout()
    {
        $session = session();
        
        // Remove only super admin session keys
        $session->remove([
            'super-admin-id',
            'super-admin-username',
            'super-admin-email',
            'super-admin-name',
            'isSuperAdmin',
        ]);
        
        return redirect()->to('/super-admin/login');
    }
}

