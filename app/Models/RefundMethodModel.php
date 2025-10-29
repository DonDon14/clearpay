<?php

namespace App\Models;

use CodeIgniter\Model;

class RefundMethodModel extends Model
{
    protected $table = 'refund_methods';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'name',
        'code',
        'description',
        'status',
        'sort_order',
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $validationRules = [
        'name' => 'required|max_length[100]',
        'code' => 'required|max_length[50]|alpha_dash|is_unique[refund_methods.code,id,{id}]',
        'description' => 'permit_empty',
        'status' => 'required|in_list[active,inactive]',
        'sort_order' => 'permit_empty|integer',
    ];
    protected $validationMessages = [
        'name' => [
            'required' => 'Refund method name is required.',
            'max_length' => 'Refund method name cannot exceed 100 characters.',
        ],
        'code' => [
            'required' => 'Refund method code is required.',
            'max_length' => 'Refund method code cannot exceed 50 characters.',
            'alpha_dash' => 'Refund method code can only contain letters, numbers, dashes, and underscores.',
            'is_unique' => 'A refund method with this code already exists.',
        ],
        'status' => [
            'required' => 'Status is required.',
            'in_list' => 'Status must be either active or inactive.',
        ],
    ];

    /**
     * Get all active refund methods
     */
    public function getActiveMethods()
    {
        return $this->where('status', 'active')
            ->orderBy('sort_order', 'ASC')
            ->orderBy('name', 'ASC')
            ->findAll();
    }

    /**
     * Get all refund methods (active and inactive)
     */
    public function getAllMethods()
    {
        return $this->orderBy('sort_order', 'ASC')
            ->orderBy('name', 'ASC')
            ->findAll();
    }

    /**
     * Get refund method by ID
     */
    public function getMethodById($id)
    {
        return $this->find($id);
    }

    /**
     * Get refund method by code
     */
    public function getMethodByCode($code)
    {
        return $this->where('code', $code)->first();
    }

    /**
     * Toggle refund method status
     */
    public function toggleStatus($id)
    {
        $method = $this->find($id);
        if (!$method) {
            return false;
        }

        $newStatus = $method['status'] === 'active' ? 'inactive' : 'active';
        return $this->update($id, ['status' => $newStatus]);
    }

    /**
     * Get all refund method codes as array (for validation)
     */
    public function getAllCodes()
    {
        $methods = $this->findAll();
        return array_column($methods, 'code');
    }

    /**
     * Get all active refund method codes as array (for validation)
     */
    public function getActiveCodes()
    {
        $methods = $this->getActiveMethods();
        return array_column($methods, 'code');
    }
}

