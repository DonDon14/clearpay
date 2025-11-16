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
        
        // Get only active admin users
        // For admins (super admins): show if is_active = true
        // For officers: show if is_active = true AND status = 'approved'
        // Use database query to filter efficiently
        $db = \Config\Database::connect();
        $isPostgres = strpos(strtolower($db->getPlatform()), 'postgre') !== false;
        
        // Build query to get active users
        // For admins (super admins): show if is_active = true
        // For officers: show if is_active = true AND status = 'approved'
        if ($isPostgres) {
            // PostgreSQL: use raw SQL for reliable boolean comparison
            $query = $db->table('users')
                ->where('(is_active = true OR is_active = \'t\')', null, false)
                ->groupStart()
                    ->where('role', 'admin')
                    ->orGroupStart()
                        ->where('role', 'officer')
                        ->where('status', 'approved')
                    ->groupEnd()
                ->groupEnd();
        } else {
            // MySQL: simpler boolean check
            $query = $db->table('users')
                ->where('is_active', 1)
                ->groupStart()
                    ->where('role', 'admin')
                    ->orGroupStart()
                        ->where('role', 'officer')
                        ->where('status', 'approved')
                    ->groupEnd()
                ->groupEnd();
        }
        
        $admins = $query->get()->getResultArray();
        
        // Normalize is_active boolean values for consistency
        foreach ($admins as &$admin) {
            $isActiveRaw = $admin['is_active'] ?? true;
            if (is_string($isActiveRaw)) {
                $admin['is_active'] = in_array(strtolower($isActiveRaw), ['t', 'true', '1', 'yes'], true);
            } elseif (is_numeric($isActiveRaw)) {
                $admin['is_active'] = (bool)(int)$isActiveRaw;
            } else {
                $admin['is_active'] = (bool)$isActiveRaw;
            }
        }
        
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
     * Only includes active and approved users
     */
    private function getOnlineAdminIds(): array
    {
        $onlineAdminIds = [];
        
        try {
            $userModel = new UserModel();
            $db = \Config\Database::connect();
            $isPostgres = strpos(strtolower($db->getPlatform()), 'postgre') !== false;
            
            // Consider users online if they were active within the last 15 minutes
            $onlineThreshold = date('Y-m-d H:i:s', strtotime('-15 minutes'));
            
            // Build query to get only active and approved online users
            if ($isPostgres) {
                $query = $db->table('users')
                    ->where('(is_active = true OR is_active = \'t\')', null, false)
                    ->where('last_activity >=', $onlineThreshold)
                    ->groupStart()
                        ->where('role', 'admin')
                        ->orGroupStart()
                            ->where('role', 'officer')
                            ->where('status', 'approved')
                        ->groupEnd()
                    ->groupEnd();
            } else {
                $query = $db->table('users')
                    ->where('is_active', 1)
                    ->where('last_activity >=', $onlineThreshold)
                    ->groupStart()
                        ->where('role', 'admin')
                        ->orGroupStart()
                            ->where('role', 'officer')
                            ->where('status', 'approved')
                        ->groupEnd()
                    ->groupEnd();
            }
            
            $onlineAdmins = $query->get()->getResultArray();
            
            foreach ($onlineAdmins as $admin) {
                $onlineAdminIds[] = $admin['id'];
            }
            
        } catch (\Exception $e) {
            log_message('error', 'Error checking online admins: ' . $e->getMessage());
        }
        
        return $onlineAdminIds;
    }
}

