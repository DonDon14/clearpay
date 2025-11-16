<?php

namespace App\Controllers\SuperAdmin;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\ActivityLogModel;
use App\Database\RawSql;

class UserActivityHistoryController extends BaseController
{
    public function index()
    {
        // Check if user is logged in as super admin
        if (!session()->get('isSuperAdmin')) {
            return redirect()->to('/super-admin/login')->with('error', 'Please login as Super Admin');
        }

        $userModel = new UserModel();
        $activityLogModel = new ActivityLogModel();
        
        // Get all users (admins and officers)
        $allUsers = $userModel->orderBy('name', 'ASC')->findAll();
        
        // Get selected user ID from query parameter
        $selectedUserId = $this->request->getGet('user_id');
        
        // Get activities for selected user or all users
        $activities = [];
        $selectedUser = null;
        
        if ($selectedUserId) {
            $selectedUser = $userModel->find($selectedUserId);
            
            if ($selectedUser) {
                // Get activities from activity_logs where this user is the actor (user_id)
                $activities = $activityLogModel
                    ->where('user_id', $selectedUserId)
                    ->orderBy('created_at', 'DESC')
                    ->findAll(100); // Limit to 100 most recent
                
                // Also get activities from user_activities table if it exists
                try {
                    $db = \Config\Database::connect();
                    if ($db->tableExists('user_activities')) {
                        $userActivities = $db->table('user_activities')
                            ->where('user_id', $selectedUserId)
                            ->orderBy('created_at', 'DESC')
                            ->get(50)
                            ->getResultArray();
                        
                        // Merge and format user_activities
                        foreach ($userActivities as $activity) {
                            $activities[] = [
                                'id' => 'ua_' . $activity['id'],
                                'activity_type' => 'user_activity',
                                'entity_type' => $activity['entity_type'] ?? 'general',
                                'entity_id' => $activity['entity_id'] ?? null,
                                'action' => $activity['activity_type'] ?? 'unknown',
                                'title' => $this->formatUserActivityTitle($activity),
                                'description' => $activity['description'] ?? '',
                                'user_id' => $activity['user_id'],
                                'user_type' => 'admin',
                                'created_at' => $activity['created_at'] ?? date('Y-m-d H:i:s')
                            ];
                        }
                    }
                } catch (\Exception $e) {
                    log_message('error', 'Error fetching user_activities: ' . $e->getMessage());
                }
            }
        } else {
            // Get all activities from activity_logs (user-related)
            $activities = $activityLogModel
                ->where('activity_type', 'user')
                ->orWhere('entity_type', 'user')
                ->orderBy('created_at', 'DESC')
                ->findAll(200); // Limit to 200 most recent
        }
        
        // Sort all activities by created_at descending
        usort($activities, function($a, $b) {
            $timeA = strtotime($a['created_at'] ?? '1970-01-01');
            $timeB = strtotime($b['created_at'] ?? '1970-01-01');
            return $timeB - $timeA;
        });
        
        // Limit to 100 most recent
        $activities = array_slice($activities, 0, 100);
        
        // Get user names for activities
        foreach ($activities as &$activity) {
            $actorUserId = $activity['user_id'] ?? null;
            if ($actorUserId) {
                $actor = $userModel->find($actorUserId);
                $activity['actor_name'] = $actor ? ($actor['name'] ?? $actor['username']) : 'Unknown';
                $activity['actor_role'] = $actor ? ($actor['role'] ?? 'unknown') : 'unknown';
            } else {
                $activity['actor_name'] = 'System';
                $activity['actor_role'] = 'system';
            }
        }
        
        // Update last_activity for super admin
        $superAdminId = session()->get('super-admin-id');
        if ($superAdminId) {
            $userModel->update($superAdminId, [
                'last_activity' => date('Y-m-d H:i:s')
            ]);
        }
        
        $data = [
            'title' => 'User Activity History',
            'pageTitle' => 'User Activity History',
            'pageSubtitle' => 'View all activities performed by users and officers',
            'allUsers' => $allUsers,
            'selectedUser' => $selectedUser,
            'selectedUserId' => $selectedUserId,
            'activities' => $activities,
            'totalActivities' => count($activities)
        ];

        return view('super-admin/user-activity-history', $data);
    }
    
    /**
     * Format user activity title from user_activities table
     */
    private function formatUserActivityTitle($activity)
    {
        $action = $activity['activity_type'] ?? 'unknown';
        $entityType = $activity['entity_type'] ?? 'item';
        
        $actionMap = [
            'create' => 'Created',
            'update' => 'Updated',
            'delete' => 'Deleted',
            'login' => 'Logged In',
            'logout' => 'Logged Out'
        ];
        
        $actionText = $actionMap[$action] ?? ucfirst($action);
        
        return "{$actionText} {$entityType}";
    }
}


