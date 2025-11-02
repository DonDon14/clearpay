<?php

namespace App\Models;

use CodeIgniter\Model;

class ActivityLogModel extends Model
{
    protected $table = 'activity_logs';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'activity_type',
        'entity_type',
        'entity_id',
        'action',
        'title',
        'description',
        'old_values',
        'new_values',
        'user_id',
        'user_type',
        'payer_id',
        'target_audience',
        'is_read',
        'created_at',
        'updated_at'
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'activity_type' => 'required|in_list[announcement,contribution,payment,payer,user,payment_request,refund]',
        'entity_type' => 'required|string',
        'entity_id' => 'required|integer',
        'action' => 'required|in_list[created,updated,deleted,published,unpublished,approved,rejected,processed,completed]',
        'title' => 'required|string|max_length[255]',
        'description' => 'required|string',
        'user_id' => 'required|integer',
        'user_type' => 'required|in_list[admin,payer]',
        'target_audience' => 'required|in_list[admins,payers,both,all]'
    ];

    protected $validationMessages = [];
    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    protected $beforeInsert = [];
    protected $afterInsert = [];
    protected $beforeUpdate = [];
    protected $afterUpdate = [];
    protected $beforeDelete = [];
    protected $afterDelete = [];

    protected $activityReadStatusModel;
    protected $adminReadStatusModel;

    public function __construct()
    {
        parent::__construct();
        $this->activityReadStatusModel = new \App\Models\ActivityReadStatusModel();
        $this->adminReadStatusModel = new \App\Models\AdminReadStatusModel();
    }

    /**
     * Get unread activities for a specific payer
     */
    public function getUnreadForPayers($lastShownId = 0, $payerId = null)
    {
        $query = $this->where('id >', $lastShownId);
        
        if ($payerId) {
            // For specific payer: show general notifications + payer-specific notifications
            // BUT only if target_audience is for payers (not admins)
            $query->groupStart()
                ->groupStart()
                    ->whereIn('target_audience', ['payers', 'both', 'all'])
                    ->where('payer_id IS NULL')
                    ->groupEnd()
                ->orGroupStart()
                    ->where('payer_id', $payerId)
                    ->whereIn('target_audience', ['payers', 'both', 'all'])
                    ->groupEnd()
                ->groupEnd();
        } else {
            // For all payers: show only general notifications
            $query->whereIn('target_audience', ['payers', 'both', 'all'])
                ->where('payer_id IS NULL');
        }
        
        return $query->orderBy('created_at', 'DESC')->first();
    }

    /**
     * Get recent activities for a specific payer with individual read status - OPTIMIZED
     */
    public function getRecentForPayers($limit = 10, $payerId = null)
    {
        // Use raw SQL for better performance
        $sql = "
            SELECT al.*, 
                   CASE WHEN ars.activity_id IS NOT NULL THEN 1 ELSE 0 END as is_read_by_payer
            FROM activity_logs al
            LEFT JOIN activity_read_status ars ON al.id = ars.activity_id AND ars.payer_id = ? AND ars.is_read = 1
            WHERE (
                (al.target_audience IN ('payers', 'both', 'all') AND al.payer_id IS NULL)
                OR (al.payer_id = ? AND al.target_audience IN ('payers', 'both', 'all'))
            )
            ORDER BY al.created_at DESC
            LIMIT ?
        ";
        
        $activities = $this->db->query($sql, [$payerId, $payerId, $limit])->getResultArray();
        
        return $activities;
    }

    /**
     * Mark activity as read for a specific payer
     */
    public function markAsRead($activityId, $payerId = null)
    {
        if ($payerId) {
            // Use individual read status system
            return $this->activityReadStatusModel->markAsRead($activityId, $payerId);
        } else {
            // Fallback to global read status (for backward compatibility)
            return $this->update($activityId, ['is_read' => 1]);
        }
    }

    /**
     * Create activity log entry
     */
    public function logActivity($data)
    {
        // Ensure required fields have defaults
        $data['old_values'] = $data['old_values'] ?? null;
        $data['new_values'] = $data['new_values'] ?? null;
        $data['is_read'] = $data['is_read'] ?? 0;

        return $this->insert($data);
    }

    /**
     * Get unread activities for admin users
     */
    public function getUnreadForAdmins($lastShownId = 0, $userId = null)
    {
        $query = $this->where('id >', $lastShownId);
        
        // Get activities targeted to admins
        $query->groupStart()
            ->where('target_audience', 'admins')
            ->orWhere('target_audience', 'both')
            ->orWhere('target_audience', 'all')
            ->groupEnd();
        
        return $query->orderBy('created_at', 'DESC')->first();
    }

    /**
     * Get recent activities for admin users with individual read status - OPTIMIZED
     */
    public function getRecentForAdmins($limit = 10, $userId = null)
    {
        // Use raw SQL for better performance
        if ($userId) {
            // Exclude activities created by the current admin (they shouldn't see their own actions)
            // Explicitly select all fields including activity_type
            $sql = "
                SELECT al.id,
                       al.activity_type,
                       al.entity_type,
                       al.entity_id,
                       al.action,
                       al.title,
                       al.description,
                       al.old_values,
                       al.new_values,
                       al.user_id,
                       al.user_type,
                       al.payer_id,
                       al.target_audience,
                       al.is_read,
                       al.created_at,
                       al.updated_at,
                       CASE WHEN ars.activity_id IS NOT NULL THEN 1 ELSE 0 END as is_read_by_admin
                FROM activity_logs al
                LEFT JOIN admin_read_status ars ON al.id = ars.activity_id AND ars.user_id = ? AND ars.is_read = 1
                WHERE (
                    al.target_audience IN ('admins', 'both', 'all')
                    AND al.user_id != ?
                )
                ORDER BY al.created_at DESC
                LIMIT ?
            ";
            
            $activities = $this->db->query($sql, [$userId, $userId, $limit])->getResultArray();
        } else {
            // If no userId, just get activities without read status (no exclusion needed)
            // Explicitly select all fields including activity_type
            $sql = "
                SELECT al.id,
                       al.activity_type,
                       al.entity_type,
                       al.entity_id,
                       al.action,
                       al.title,
                       al.description,
                       al.old_values,
                       al.new_values,
                       al.user_id,
                       al.user_type,
                       al.payer_id,
                       al.target_audience,
                       al.is_read,
                       al.created_at,
                       al.updated_at,
                       0 as is_read_by_admin
                FROM activity_logs al
                WHERE (
                    al.target_audience IN ('admins', 'both', 'all')
                )
                ORDER BY al.created_at DESC
                LIMIT ?
            ";
            
            $activities = $this->db->query($sql, [$limit])->getResultArray();
        }
        
        return $activities;
    }

    /**
     * Mark activity as read for a specific admin user
     */
    public function markAsReadByAdmin($activityId, $userId = null)
    {
        if ($userId) {
            // Use individual read status system for admins
            return $this->adminReadStatusModel->markAsRead($activityId, $userId);
        } else {
            // Fallback to global read status (for backward compatibility)
            return $this->update($activityId, ['is_read' => 1]);
        }
    }

    /**
     * Get unread count for a specific admin user
     */
    public function getUnreadCountForAdmin($userId)
    {
        if (!$userId) {
            return 0;
        }
        
        // Use more efficient query
        // Exclude activities created by the current admin (they shouldn't see their own actions)
        $sql = "
            SELECT COUNT(*) as unread_count
            FROM activity_logs al
            LEFT JOIN admin_read_status ars ON al.id = ars.activity_id AND ars.user_id = ? AND ars.is_read = 1
            WHERE (
                al.target_audience IN ('admins', 'both', 'all')
                AND al.user_id != ?
                AND (ars.id IS NULL OR ars.is_read = 0)
            )
        ";
        
        $result = $this->db->query($sql, [$userId, $userId])->getRowArray();
        
        return $result ? (int)$result['unread_count'] : 0;
    }
}
