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
        'activity_type' => 'required|in_list[announcement,contribution,payment,payer,user]',
        'entity_type' => 'required|string',
        'entity_id' => 'required|integer',
        'action' => 'required|in_list[created,updated,deleted,published,unpublished]',
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

    public function __construct()
    {
        parent::__construct();
        $this->activityReadStatusModel = new \App\Models\ActivityReadStatusModel();
    }

    /**
     * Get unread activities for a specific payer
     */
    public function getUnreadForPayers($lastShownId = 0, $payerId = null)
    {
        $query = $this->where('id >', $lastShownId);
        
        if ($payerId) {
            // For specific payer: show general notifications + payer-specific notifications
            $query->groupStart()
                ->groupStart()
                    ->where('target_audience', 'payers')
                    ->orWhere('target_audience', 'both')
                    ->orWhere('target_audience', 'all')
                    ->groupEnd()
                    ->where('payer_id IS NULL')
                ->orWhere('payer_id', $payerId)
                ->groupEnd();
        } else {
            // For all payers: show only general notifications
            $query->groupStart()
                ->where('target_audience', 'payers')
                ->orWhere('target_audience', 'both')
                ->orWhere('target_audience', 'all')
                ->groupEnd()
                ->where('payer_id IS NULL');
        }
        
        return $query->orderBy('created_at', 'DESC')->first();
    }

    /**
     * Get recent activities for a specific payer with individual read status
     */
    public function getRecentForPayers($limit = 10, $payerId = null)
    {
        $query = $this;
        
        if ($payerId) {
            // For specific payer: show general notifications + payer-specific notifications
            $query->groupStart()
                ->groupStart()
                    ->where('target_audience', 'payers')
                    ->orWhere('target_audience', 'both')
                    ->orWhere('target_audience', 'all')
                    ->groupEnd()
                    ->where('payer_id IS NULL')
                ->orWhere('payer_id', $payerId)
                ->groupEnd();
        } else {
            // For all payers: show only general notifications
            $query->groupStart()
                ->where('target_audience', 'payers')
                ->orWhere('target_audience', 'both')
                ->orWhere('target_audience', 'all')
                ->groupEnd()
                ->where('payer_id IS NULL');
        }
        
        $activities = $query->orderBy('created_at', 'DESC')->limit($limit)->findAll();
        
        // Add individual read status for each activity
        if ($payerId && !empty($activities)) {
            $activityIds = array_column($activities, 'id');
            $readStatus = $this->activityReadStatusModel->getReadStatusForPayer($activityIds, $payerId);
            
            foreach ($activities as &$activity) {
                $activity['is_read_by_payer'] = isset($readStatus[$activity['id']]);
            }
        }
        
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
}
