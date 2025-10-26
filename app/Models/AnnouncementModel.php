<?php

namespace App\Models;

use CodeIgniter\Model;

class AnnouncementModel extends Model
{
    protected $table = 'announcements';
    protected $primaryKey = 'id';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'title',
        'text',
        'type',
        'priority',
        'target_audience',
        'status',
        'created_by',
        'published_at',
        'expires_at'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Validation rules
    protected $validationRules = [
        'title' => 'required|max_length[255]',
        'text' => 'required',
        'type' => 'required|in_list[general,urgent,maintenance,event,deadline]',
        'priority' => 'required|in_list[low,medium,high,critical]',
        'target_audience' => 'required|in_list[admins,payers,both,all,staff,students]',
        'status' => 'required|in_list[draft,published,archived]'
    ];

    protected $validationMessages = [
        'title' => [
            'required' => 'Title is required.',
            'max_length' => 'Title must not exceed 255 characters.'
        ],
        'text' => [
            'required' => 'Content is required.'
        ],
        'type' => [
            'required' => 'Type is required.',
            'in_list' => 'Invalid announcement type.'
        ],
        'priority' => [
            'required' => 'Priority is required.',
            'in_list' => 'Invalid priority level.'
        ],
        'target_audience' => [
            'required' => 'Target audience is required.',
            'in_list' => 'Invalid target audience.'
        ],
        'status' => [
            'required' => 'Status is required.',
            'in_list' => 'Invalid status.'
        ]
    ];

    /**
     * Get all announcements with optional filters
     */
    public function getAllAnnouncements($filters = [])
    {
        $builder = $this->select('announcements.*, users.username as created_by_name')
            ->join('users', 'users.id = announcements.created_by', 'left');

        // Apply filters
        if (!empty($filters['status'])) {
            $builder->where('announcements.status', $filters['status']);
        }

        if (!empty($filters['priority'])) {
            $builder->where('announcements.priority', $filters['priority']);
        }

        if (!empty($filters['target_audience'])) {
            $builder->where('announcements.target_audience', $filters['target_audience']);
        }

        if (!empty($filters['type'])) {
            $builder->where('announcements.type', $filters['type']);
        }

        if (!empty($filters['search'])) {
            $builder->groupStart()
                ->like('announcements.title', $filters['search'])
                ->orLike('announcements.text', $filters['search'])
                ->groupEnd();
        }

        return $builder->orderBy('announcements.created_at', 'DESC')->findAll();
    }

    /**
     * Get published announcements only
     */
    public function getPublishedAnnouncements()
    {
        return $this->where('status', 'published')
            ->where('(expires_at IS NULL OR expires_at > NOW())')
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }

    /**
     * Get announcement statistics
     */
    public function getStats()
    {
        return [
            'total' => $this->countAllResults(false),
            'published' => $this->where('status', 'published')->countAllResults(false),
            'draft' => $this->where('status', 'draft')->countAllResults(false),
            'archived' => $this->where('status', 'archived')->countAllResults(false),
            'expired' => $this->where('expires_at <', date('Y-m-d H:i:s'))->countAllResults(false)
        ];
    }
}
