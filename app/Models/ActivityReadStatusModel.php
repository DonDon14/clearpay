<?php

namespace App\Models;

use CodeIgniter\Model;

class ActivityReadStatusModel extends Model
{
    protected $table = 'activity_read_status';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'activity_id',
        'payer_id',
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
        'payer_id' => 'required|integer',
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
     * Mark activity as read for a specific payer
     */
    public function markAsRead($activityId, $payerId)
    {
        // Check if record already exists
        $existing = $this->where('activity_id', $activityId)
                         ->where('payer_id', $payerId)
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
                'payer_id' => $payerId,
                'is_read' => 1,
                'read_at' => date('Y-m-d H:i:s')
            ]);
        }
    }

    /**
     * Check if activity is read by a specific payer
     */
    public function isReadByPayer($activityId, $payerId)
    {
        $record = $this->where('activity_id', $activityId)
                      ->where('payer_id', $payerId)
                      ->first();

        return $record ? (bool)$record['is_read'] : false;
    }

    /**
     * Get read status for multiple activities for a specific payer
     */
    public function getReadStatusForPayer($activityIds, $payerId)
    {
        $records = $this->whereIn('activity_id', $activityIds)
                       ->where('payer_id', $payerId)
                       ->where('is_read', 1)
                       ->findAll();

        $readStatus = [];
        foreach ($records as $record) {
            $readStatus[$record['activity_id']] = true;
        }

        return $readStatus;
    }

    /**
     * Get unread activity IDs for a specific payer
     */
    public function getUnreadActivityIds($payerId, $activityIds = null)
    {
        $query = $this->where('payer_id', $payerId)
                     ->where('is_read', 0);

        if ($activityIds) {
            $query->whereIn('activity_id', $activityIds);
        }

        $records = $query->findAll();
        return array_column($records, 'activity_id');
    }
}
