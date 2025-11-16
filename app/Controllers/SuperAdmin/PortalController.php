<?php

namespace App\Controllers\SuperAdmin;

use App\Controllers\BaseController;
use App\Models\UserModel;

class PortalController extends BaseController
{
    public function index()
    {
        // Check if user is logged in as super admin
        if (!session()->get('isSuperAdmin')) {
            return redirect()->to('/super-admin/login')->with('error', 'Please login as Super Admin');
        }

        $userModel = new UserModel();
        
        // Get all pending officer signups
        $pendingOfficers = $userModel->where('role', 'officer')
            ->where('status', 'pending')
            ->orderBy('created_at', 'DESC')
            ->findAll();
        
        // Get all officers (approved, rejected, and pending) for full list
        $allOfficers = $userModel->where('role', 'officer')
            ->orderBy('created_at', 'DESC')
            ->findAll();
        
        // Get online officer IDs by checking last_activity
        $onlineOfficerIds = $this->getOnlineOfficerIds();
        
        // Add online status and normalize profile pictures
        foreach ($allOfficers as &$officer) {
            $officer['is_online'] = in_array($officer['id'], $onlineOfficerIds);
            $officer['profile_picture'] = $this->normalizeProfilePicturePath(
                $officer['profile_picture'] ?? null,
                null,
                $officer['id'],
                'user'
            );
        }
        
        // Update last_activity for super admin
        $superAdminId = session()->get('super-admin-id');
        if ($superAdminId) {
            $userModel->update($superAdminId, [
                'last_activity' => date('Y-m-d H:i:s')
            ]);
        }
        
        $data = [
            'title' => 'Super Admin Portal',
            'pageTitle' => 'Super Admin Portal',
            'pageSubtitle' => 'Manage officer approvals and system administration',
            'pendingOfficers' => $pendingOfficers,
            'allOfficers' => $allOfficers,
            'totalPending' => count($pendingOfficers),
            'totalOfficers' => count($allOfficers),
            'onlineOfficers' => count($onlineOfficerIds)
        ];

        return view('super-admin/portal', $data);
    }

    public function approve()
    {
        // Check if user is logged in as super admin
        if (!session()->get('isSuperAdmin')) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Access denied. Only Super Admins can approve officers.'
            ]);
        }

        $userModel = new UserModel();
        $userId = $this->request->getPost('user_id');

        if (!$userId) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'User ID is required.'
            ]);
        }

        $user = $userModel->find($userId);

        if (!$user) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'User not found.'
            ]);
        }

        // Only approve officers
        if ($user['role'] !== 'officer') {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Only officers can be approved through this system.'
            ]);
        }

        // Update user status to approved
        $userModel->update($userId, [
            'status' => 'approved'
        ]);

        // Log activity
        try {
            $activityLogger = new \App\Services\ActivityLogger();
            $activityLogger->logUser('approved', [
                'id' => $userId,
                'name' => $user['name'],
                'username' => $user['username'],
                'role' => $user['role']
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Failed to log officer approval activity: ' . $e->getMessage());
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Officer approved successfully.'
        ]);
    }

    public function reject()
    {
        // Check if user is logged in as super admin
        if (!session()->get('isSuperAdmin')) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Access denied. Only Super Admins can reject officers.'
            ]);
        }

        $userModel = new UserModel();
        $userId = $this->request->getPost('user_id');
        $reason = $this->request->getPost('reason') ?? 'No reason provided';

        if (!$userId) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'User ID is required.'
            ]);
        }

        $user = $userModel->find($userId);

        if (!$user) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'User not found.'
            ]);
        }

        // Only reject officers
        if ($user['role'] !== 'officer') {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Only officers can be rejected through this system.'
            ]);
        }

        // Update user status to rejected
        $userModel->update($userId, [
            'status' => 'rejected'
        ]);

        // Log activity
        try {
            $activityLogger = new \App\Services\ActivityLogger();
            $activityLogger->logUser('rejected', [
                'id' => $userId,
                'name' => $user['name'],
                'username' => $user['username'],
                'role' => $user['role'],
                'reason' => $reason
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Failed to log officer rejection activity: ' . $e->getMessage());
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Officer rejected successfully.'
        ]);
    }

    /**
     * Get list of officer user IDs who are currently online
     * by checking last_activity timestamp (within last 15 minutes)
     */
    private function getOnlineOfficerIds(): array
    {
        $onlineOfficerIds = [];
        
        try {
            $userModel = new UserModel();
            
            // Consider users online if they were active within the last 15 minutes
            $onlineThreshold = date('Y-m-d H:i:s', strtotime('-15 minutes'));
            
            $onlineOfficers = $userModel->where('role', 'officer')
                ->where('last_activity >=', $onlineThreshold)
                ->findAll();
            
            foreach ($onlineOfficers as $officer) {
                $onlineOfficerIds[] = $officer['id'];
            }
            
        } catch (\Exception $e) {
            log_message('error', 'Error checking online officers: ' . $e->getMessage());
        }
        
        return $onlineOfficerIds;
    }
}

