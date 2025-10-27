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

    /**
     * Get unread activities for payers
     */
    public function getUnreadForPayers($lastShownId = 0)
    {
        return $this->groupStart()
            ->where('target_audience', 'payers')
            ->orWhere('target_audience', 'both')
            ->orWhere('target_audience', 'all')
            ->groupEnd()
            ->where('id >', $lastShownId)
            ->orderBy('created_at', 'DESC')
            ->first();
    }

    /**
     * Get recent activities for payers
     */
    public function getRecentForPayers($limit = 10)
    {
        return $this->groupStart()
            ->where('target_audience', 'payers')
            ->orWhere('target_audience', 'both')
            ->orWhere('target_audience', 'all')
            ->groupEnd()
            ->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    /**
     * Mark activity as read
     */
    public function markAsRead($id)
    {
        return $this->update($id, ['is_read' => 1]);
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
