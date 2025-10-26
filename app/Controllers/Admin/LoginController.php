<?php

namespace App\Controllers\Admin;

use App\Models\UserModel;
use CodeIgniter\Controller;

class LoginController extends Controller
{
    public function index()
    {
        return view('admin/login');
    }

    public function loginPost()
    {
        $session = session();
        $userModel = new UserModel();

        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');

        $user = $userModel->where('username', $username)->first();

        if($user && password_verify($password, $user['password'])) {
            $session->set([
                'user-id'         => $user['id'],
                'username'        => $user['username'],
                'email'           => $user['email'],
                'name'            => $user['name'],
                'role'            => $user['role'],
                'profile_picture' => $user['profile_picture'] ?? null,
                'isLoggedIn'      => true,
            ]);
            return redirect()->to('/dashboard');
        } else {
            return redirect()->back()->with('error', 'Invalid credentials');
        }
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/');
    }
}