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

        // Fetch ALL payments (not just recent 10) like dashboard
        $paymentModel = new PaymentModel();
        $recentPayments = $paymentModel->select('payments.*, payers.payer_id as payer_student_id, payers.payer_name, payers.contact_number, payers.email_address, contributions.title as contribution_title, contributions.id as contrib_id')
            ->join('payers', 'payers.id = payments.payer_id', 'left')
            ->join('contributions', 'contributions.id = payments.contribution_id', 'left')
            ->orderBy('payments.payment_date', 'DESC')
            ->findAll();

        // Add computed status to each payment
        foreach ($recentPayments as &$payment) {
            $payerId = $payment['payer_id'];
            $contributionId = $payment['contrib_id'] ?? $payment['contribution_id'] ?? null;
            $payment['computed_status'] = $paymentModel->getPaymentStatus($payerId, $contributionId);
        }

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
        $allContributions = $contributionModel->findAll();

        // Calculate counts
        $activeCount = 0;
        $inactiveCount = 0;
        $totalCount = count($allContributions);

        foreach ($allContributions as $contrib) {
            if ($contrib['status'] === 'active') {
                $activeCount++;
            } else {
                $inactiveCount++;
            }
        }

        // Sort contributions: active first, then by date
        usort($allContributions, function($a, $b) {
            // First sort by status (active first)
            if ($a['status'] === $b['status']) {
                // If same status, sort by created_at (most recent first)
                return strtotime($b['created_at']) - strtotime($a['created_at']);
            }
            return $a['status'] === 'active' ? -1 : 1;
        });

        // Example: pass session data to the view
        $data = [
            'title' => 'Contributions',
            'pageTitle' => 'Contributions',
            'pageSubtitle' => 'Manage contributions and donations',
            'username' => session()->get('username'),
            'contributions' => $allContributions,
            'activeCount' => $activeCount,
            'inactiveCount' => $inactiveCount,
            'totalCount' => $totalCount,
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

        // Load models
        $payerModel = new \App\Models\PayerModel();
        $paymentModel = new \App\Models\PaymentModel();
        
        // Fetch all payers with payment statistics
        $payers = $payerModel->findAll();
        $payments = $paymentModel->findAll();
        
        // Calculate statistics for each payer
        $payersWithStats = [];
        foreach ($payers as $payer) {
            // Get all payments for this payer
            $payerPayments = $paymentModel->where('payer_id', $payer['id'])->findAll();
            
            $totalPaid = 0;
            $lastPaymentDate = null;
            
            foreach ($payerPayments as $payment) {
                $totalPaid += $payment['amount_paid'];
                if (!$lastPaymentDate || strtotime($payment['payment_date']) > strtotime($lastPaymentDate)) {
                    $lastPaymentDate = $payment['payment_date'];
                }
            }
            
            // Get computed payment status (fully paid, partial, or unpaid)
            $paymentStatus = $paymentModel->getPaymentStatus($payer['id']);
            
            // Determine activity status based on last payment date (within last 30 days = active)
            $activityStatus = 'inactive';
            if ($lastPaymentDate) {
                $daysSinceLastPayment = (time() - strtotime($lastPaymentDate)) / (60 * 60 * 24);
                if ($daysSinceLastPayment <= 30) {
                    $activityStatus = 'active';
                } elseif ($daysSinceLastPayment <= 90) {
                    $activityStatus = 'pending';
                }
            }
            
            $payersWithStats[] = [
                'id' => $payer['id'],
                'payer_id' => $payer['payer_id'],
                'payer_name' => $payer['payer_name'],
                'email_address' => $payer['email_address'],
                'contact_number' => $payer['contact_number'],
                'total_payments' => count($payerPayments),
                'total_paid' => $totalPaid,
                'last_payment' => $lastPaymentDate,
                'payment_status' => $paymentStatus, // Computed: fully paid, partial, unpaid
                'status' => $activityStatus // Activity: active, pending, inactive
            ];
        }
        
        // Calculate overall statistics
        $totalPayers = count($payersWithStats);
        $activePayers = count(array_filter($payersWithStats, fn($p) => $p['status'] === 'active'));
        $totalAmount = array_sum(array_column($payersWithStats, 'total_paid'));
        $avgPaymentPerStudent = $totalPayers > 0 ? $totalAmount / $totalPayers : 0;
        
        $payerStats = [
            'total_payers' => $totalPayers,
            'active_payers' => $activePayers,
            'total_amount' => $totalAmount,
            'avg_payment_per_student' => $avgPaymentPerStudent
        ];

        // Example: pass session data to the view
        $data = [
            'title' => 'Students Management',
            'pageTitle' => 'Payers',
            'pageSubtitle' => 'Manage student records and information',
            'username' => session()->get('username'),
            'payers' => $payersWithStats,
            'payerStats' => $payerStats
        ];

        return view('admin/payers', $data);
    }
    
    public function savePayer()
    {
        // Check if user is logged in
        if (!session()->get('isLoggedIn')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Unauthorized'
            ]);
        }
        
        try {
            $payerModel = new \App\Models\PayerModel();
            
            // Get form data
            $data = [
                'payer_id' => $this->request->getPost('payer_id'),
                'payer_name' => $this->request->getPost('payer_name'),
                'contact_number' => $this->request->getPost('contact_number'),
                'email_address' => $this->request->getPost('email_address')
            ];
            
            // Validate required fields
            if (empty($data['payer_id']) || empty($data['payer_name'])) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Student ID and Name are required'
                ]);
            }
            
            // Check if payer_id already exists
            $existingPayer = $payerModel->where('payer_id', $data['payer_id'])->first();
            if ($existingPayer) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'A payer with this Student ID already exists'
                ]);
            }
            
            // Save to database
            $result = $payerModel->insert($data);
            
            if ($result) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Payer added successfully'
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to add payer'
                ]);
            }
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
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
