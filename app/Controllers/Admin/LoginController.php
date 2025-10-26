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

    public function register()
    {
        return view('admin/register');
    }

    public function registerPost()
    {
        $session = session();
        $userModel = new UserModel();

        // Validation
        $rules = [
            'name' => 'required|min_length[3]|max_length[100]',
            'username' => 'required|min_length[3]|max_length[50]|is_unique[users.username]',
            'email' => 'required|valid_email|max_length[100]|is_unique[users.email]',
            'phone' => 'permit_empty|max_length[20]',
            'password' => 'required|min_length[6]',
            'confirm_password' => 'required|matches[password]',
            'role' => 'permit_empty|in_list[admin,officer]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        // Get form data
        $data = [
            'name' => $this->request->getPost('name'),
            'username' => $this->request->getPost('username'),
            'email' => $this->request->getPost('email'),
            'phone' => $this->request->getPost('phone'),
            'password' => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
            'role' => $this->request->getPost('role') ?? 'officer',
            'email_verified' => false,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        // Save user
        if ($userModel->insert($data)) {
            $userId = $userModel->insertID();
            
            // Auto-login after registration
            $user = $userModel->find($userId);
            $session->set([
                'user-id'         => $user['id'],
                'username'        => $user['username'],
                'email'           => $user['email'],
                'name'            => $user['name'],
                'role'            => $user['role'],
                'profile_picture' => $user['profile_picture'] ?? null,
                'isLoggedIn'      => true,
            ]);
            
            return redirect()->to('/dashboard')->with('success', 'Registration successful!');
        } else {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Registration failed. Please try again.');
        }
    }
}