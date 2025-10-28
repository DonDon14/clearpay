<?php

namespace App\Models;

use CodeIgniter\Model;

class PaymentMethodModel extends Model
{
    protected $table = 'payment_methods';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'name',
        'icon',
        'description',
        'account_details',
        'status',
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $validationRules = [
        'name' => 'required|max_length[100]',
        'icon' => 'permit_empty|max_length[255]',
        'description' => 'permit_empty|max_length[1000]',
        'account_details' => 'permit_empty|max_length[255]',
        'status' => 'required|in_list[active,inactive]',
    ];
    protected $validationMessages = [
        'name' => [
            'required' => 'Payment method name is required.',
            'max_length' => 'Payment method name cannot exceed 100 characters.',
        ],
        'icon' => [
            'max_length' => 'Icon file path cannot exceed 255 characters.',
        ],
        'description' => [
            'max_length' => 'Description cannot exceed 1000 characters.',
        ],
        'account_details' => [
            'max_length' => 'Account details cannot exceed 255 characters.',
        ],
        'status' => [
            'required' => 'Status is required.',
            'in_list' => 'Status must be either active or inactive.',
        ],
    ];

    /**
     * Get all active payment methods
     */
    public function getActiveMethods()
    {
        return $this->where('status', 'active')->orderBy('name', 'ASC')->findAll();
    }

    /**
     * Get payment method by ID
     */
    public function getMethodById($id)
    {
        return $this->find($id);
    }

    /**
     * Toggle payment method status
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
}
