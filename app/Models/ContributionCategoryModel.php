<?php

namespace App\Models;

use CodeIgniter\Model;

class ContributionCategoryModel extends Model
{
    protected $table = 'contribution_categories';
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
        'code' => 'required|max_length[50]|alpha_dash|is_unique[contribution_categories.code,id,{id}]',
        'description' => 'permit_empty',
        'status' => 'required|in_list[active,inactive]',
        'sort_order' => 'permit_empty|integer',
    ];
    protected $validationMessages = [
        'name' => [
            'required' => 'Category name is required.',
            'max_length' => 'Category name cannot exceed 100 characters.',
        ],
        'code' => [
            'required' => 'Category code is required.',
            'max_length' => 'Category code cannot exceed 50 characters.',
            'alpha_dash' => 'Category code can only contain letters, numbers, dashes, and underscores.',
            'is_unique' => 'This category code already exists.',
        ],
        'status' => [
            'required' => 'Status is required.',
            'in_list' => 'Status must be either active or inactive.',
        ],
    ];

    /**
     * Get all active categories
     */
    public function getActiveCategories()
    {
        return $this->where('status', 'active')
            ->orderBy('sort_order', 'ASC')
            ->orderBy('name', 'ASC')
            ->findAll();
    }

    /**
     * Get all categories (including inactive)
     */
    public function getAllCategories()
    {
        return $this->orderBy('sort_order', 'ASC')
            ->orderBy('name', 'ASC')
            ->findAll();
    }

    /**
     * Get category by code
     */
    public function getCategoryByCode($code)
    {
        return $this->where('code', $code)->first();
    }

    /**
     * Get category by ID
     */
    public function getCategoryById($id)
    {
        return $this->find($id);
    }

    /**
     * Toggle category status
     */
    public function toggleStatus($id)
    {
        $category = $this->find($id);
        if (!$category) {
            return false;
        }

        $newStatus = $category['status'] === 'active' ? 'inactive' : 'active';
        return $this->update($id, ['status' => $newStatus]);
    }

    /**
     * Check if category is in use by any contributions
     */
    public function isInUse($id)
    {
        $category = $this->find($id);
        if (!$category) {
            return false;
        }

        $db = \Config\Database::connect();
        $count = $db->table('contributions')
            ->where('category', $category['code'])
            ->countAllResults();

        return $count > 0;
    }
}
