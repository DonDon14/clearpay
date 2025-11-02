<?php

namespace App\Models;

use CodeIgniter\Model;

class AdminReadStatusModel extends Model
{
    protected $table = 'admin_read_status';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'activity_id',
        'user_id',
        'is_read',
        'read_at',
        'created_at',
        'updated_at'
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'activity_id' => 'required|integer',
        'user_id' => 'required|integer',
        'is_read' => 'required|in_list[0,1]'
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
     * Mark activity as read for a specific admin user
     */
    public function markAsRead($activityId, $userId)
    {
        // Check if record already exists
        $existing = $this->where('activity_id', $activityId)
                         ->where('user_id', $userId)
                         ->first();

        if ($existing) {
            // Update existing record
            return $this->update($existing['id'], [
                'is_read' => 1,
                'read_at' => date('Y-m-d H:i:s')
            ]);
        } else {
            // Create new record
            return $this->insert([
                'activity_id' => $activityId,
                'user_id' => $userId,
                'is_read' => 1,
                'read_at' => date('Y-m-d H:i:s')
            ]);
        }
    }

    /**
     * Mark multiple activities as read for a specific admin user
     */
    public function markMultipleAsRead($activityIds, $userId)
    {
        foreach ($activityIds as $activityId) {
            $this->markAsRead($activityId, $userId);
        }
        return true;
    }

    /**
     * Check if activity is read by a specific admin user
     */
    public function isReadByUser($activityId, $userId)
    {
        $record = $this->where('activity_id', $activityId)
                      ->where('user_id', $userId)
                      ->first();

        return $record ? (bool)$record['is_read'] : false;
    }

    /**
     * Get read status for multiple activities for a specific admin user
     */
    public function getReadStatusForUser($activityIds, $userId)
    {
        $records = $this->whereIn('activity_id', $activityIds)
                       ->where('user_id', $userId)
                       ->where('is_read', 1)
                       ->findAll();

        $readStatus = [];
        foreach ($records as $record) {
            $readStatus[$record['activity_id']] = true;
        }

        return $readStatus;
    }

    /**
     * Get unread activity IDs for a specific admin user
     */
    public function getUnreadActivityIds($userId, $activityIds = null)
    {
        $query = $this->select('activity_id')
                     ->where('user_id', $userId)
                     ->where('is_read', 0);

        if ($activityIds) {
            $query->whereIn('activity_id', $activityIds);
        }

        $records = $query->findAll();
        return array_column($records, 'activity_id');
    }

    /**
     * Get count of unread activities for a specific admin user
     */
    public function getUnreadCount($userId)
    {
        return $this->where('user_id', $userId)
                    ->where('is_read', 0)
                    ->countAllResults();
    }
}

