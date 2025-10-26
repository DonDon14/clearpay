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

        // Fetch contributions from database
        $contributionModel = new ContributionModel();
        $contributions = $contributionModel->findAll();

        // Example: pass session data to the view
        $data = [
            'title' => 'Contributions',
            'pageTitle' => 'Contributions',
            'pageSubtitle' => 'Manage contributions and donations',
            'username' => session()->get('username'),
            'contributions' => $contributions,
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

        // Fetch user data
        $userModel = new UserModel();
        $user = $userModel->where('id', session()->get('user-id'))->first();

        // Example: pass session data to the view
        $data = [
            'title' => 'Profile',
            'pageTitle' => 'Profile',
            'pageSubtitle' => 'View and edit your profile',
            'username' => session()->get('username'),
            'user' => $user
        ];

        return view('admin/profile', $data);
    }

    public function update()
    {
        // Check if user is logged in
        if (!session()->get('isLoggedIn')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Unauthorized'
            ]);
        }

        $userModel = new UserModel();
        $userId = session()->get('user-id');
        $user = $userModel->find($userId);

        if (!$user) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'User not found'
            ]);
        }

        // Prepare update data
        $updateData = [];

        // Update name if provided
        if ($this->request->getPost('name')) {
            $updateData['name'] = $this->request->getPost('name');
        }

        // Update email if provided
        if ($this->request->getPost('email')) {
            $updateData['email'] = $this->request->getPost('email');
        }

        // Update phone if provided
        if ($this->request->getPost('phone')) {
            $updateData['phone'] = $this->request->getPost('phone');
        }

        // Handle password change
        if ($this->request->getPost('change_password') == '1') {
            $currentPassword = $this->request->getPost('current_password');
            $newPassword = $this->request->getPost('new_password');

            // Verify current password
            if (!password_verify($currentPassword, $user['password'])) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Current password is incorrect'
                ]);
            }

            $updateData['password'] = password_hash($newPassword, PASSWORD_DEFAULT);
        }

        // Handle profile picture upload
        $profilePicFile = $this->request->getFile('profile_picture');
        if ($profilePicFile && $profilePicFile->isValid() && !$profilePicFile->hasMoved()) {
            $newName = $profilePicFile->getRandomName();
            
            // Store in both writable and public directories
            $writablePath = WRITEPATH . 'uploads/profile/';
            $publicPath = FCPATH . 'uploads/profile/';
            
            // Create directories if they don't exist
            if (!is_dir($writablePath)) {
                mkdir($writablePath, 0777, true);
            }
            if (!is_dir($publicPath)) {
                mkdir($publicPath, 0777, true);
            }

            // Move to writable first
            if ($profilePicFile->move($writablePath, $newName)) {
                // Copy to public directory for web access
                copy($writablePath . $newName, $publicPath . $newName);
                $updateData['profile_picture'] = 'uploads/profile/' . $newName;
            }
        }

        // Update user data
        if (!empty($updateData)) {
            $userModel->update($userId, $updateData);
        }

        // Update session data
        if (isset($updateData['name'])) {
            session()->set('name', $updateData['name']);
        }
        if (isset($updateData['email'])) {
            session()->set('email', $updateData['email']);
        }
        if (isset($updateData['profile_picture'])) {
            session()->set('profile_picture', $updateData['profile_picture']);
        }

        // Get updated user data
        $updatedUser = $userModel->find($userId);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Profile updated successfully',
            'user' => $updatedUser,
            'profile_picture_url' => $updatedUser['profile_picture'] ? base_url($updatedUser['profile_picture']) : null
        ]);
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
