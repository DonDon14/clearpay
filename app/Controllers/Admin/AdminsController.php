<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UserModel;

class AdminsController extends BaseController
{
    public function index()
    {
        // Check if user is logged in
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/admin/login');
        }

        $userModel = new UserModel();
        
        // Get all admin users
        $admins = $userModel->findAll();
        
        // Get online admin IDs by checking active sessions
        $onlineAdminIds = $this->getOnlineAdminIds();
        
        // Add online status to each admin
        foreach ($admins as &$admin) {
            $admin['is_online'] = in_array($admin['id'], $onlineAdminIds);
            $admin['profile_picture'] = $this->normalizeProfilePicturePath(
                $admin['profile_picture'] ?? null,
                null,
                $admin['id'],
                'user'
            );
        }
        
        // Sort admins: online first, then by name
        usort($admins, function($a, $b) {
            if ($a['is_online'] !== $b['is_online']) {
                return $b['is_online'] ? 1 : -1;
            }
            return strcmp($a['name'] ?? $a['username'], $b['name'] ?? $b['username']);
        });
        
        $data = [
            'title' => 'Admins Management',
            'pageTitle' => 'Admins',
            'pageSubtitle' => 'View all administrators and their online status',
            'admins' => $admins,
            'totalAdmins' => count($admins),
            'onlineAdmins' => count($onlineAdminIds)
        ];

        return view('admin/admins', $data);
    }
    
    /**
     * Get list of admin user IDs who are currently online
     * by checking last_activity timestamp (within last 15 minutes)
     */
    private function getOnlineAdminIds(): array
    {
        $onlineAdminIds = [];
        
        try {
            $userModel = new UserModel();
            
            // Consider users online if they were active within the last 15 minutes
            $onlineThreshold = date('Y-m-d H:i:s', strtotime('-15 minutes'));
            
            $onlineAdmins = $userModel->where('last_activity >=', $onlineThreshold)
                ->findAll();
            
            foreach ($onlineAdmins as $admin) {
                $onlineAdminIds[] = $admin['id'];
            }
            
        } catch (\Exception $e) {
            log_message('error', 'Error checking online admins: ' . $e->getMessage());
        }
        
        return $onlineAdminIds;
    }
}

