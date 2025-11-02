<?php

namespace App\Controllers\Admin\Settings;

use App\Controllers\BaseController;
use App\Models\ContributionCategoryModel;

class ContributionCategoryController extends BaseController
{
    protected $categoryModel;

    public function __construct()
    {
        $this->categoryModel = new ContributionCategoryModel();
    }

    /**
     * Get categories data for AJAX requests
     */
    public function getData()
    {
        // Check if user is logged in
        if (!session()->get('isLoggedIn')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Unauthorized'
            ]);
        }

        $categories = $this->categoryModel->getAllCategories();

        return $this->response->setJSON([
            'success' => true,
            'data' => $categories
        ]);
    }

    /**
     * Store a newly created category
     */
    public function store()
    {
        // Check if user is logged in
        if (!session()->get('isLoggedIn')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Unauthorized'
            ]);
        }

        $rules = [
            'name' => 'required|max_length[100]',
            'code' => 'required|max_length[50]|alpha_dash|is_unique[contribution_categories.code]',
            'description' => 'permit_empty',
            'status' => 'required|in_list[active,inactive]',
            'sort_order' => 'permit_empty|integer',
        ];

        $messages = [
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

        if (!$this->validate($rules, $messages)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $this->validator->getErrors()
            ]);
        }

        // Auto-generate code from name if not provided
        $code = $this->request->getPost('code');
        if (empty($code)) {
            $name = $this->request->getPost('name');
            $code = strtolower(str_replace(' ', '_', preg_replace('/[^a-zA-Z0-9\s]/', '', $name)));
        }

        $data = [
            'name' => $this->request->getPost('name'),
            'code' => $code,
            'description' => $this->request->getPost('description'),
            'status' => $this->request->getPost('status'),
            'sort_order' => (int)($this->request->getPost('sort_order') ?? 0),
        ];

        $result = $this->categoryModel->insert($data);

        if ($result) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Category created successfully',
                'data' => $this->categoryModel->find($result)
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to create category'
            ]);
        }
    }

    /**
     * Update the specified category
     */
    public function update($id)
    {
        // Check if user is logged in
        if (!session()->get('isLoggedIn')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Unauthorized'
            ]);
        }

        $category = $this->categoryModel->find($id);

        if (!$category) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Category not found'
            ]);
        }

        $rules = [
            'name' => 'required|max_length[100]',
            'code' => "required|max_length[50]|alpha_dash|is_unique[contribution_categories.code,id,{$id}]",
            'description' => 'permit_empty',
            'status' => 'required|in_list[active,inactive]',
            'sort_order' => 'permit_empty|integer',
        ];

        $messages = [
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

        if (!$this->validate($rules, $messages)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $this->validator->getErrors()
            ]);
        }

        $data = [
            'name' => $this->request->getPost('name'),
            'code' => $this->request->getPost('code'),
            'description' => $this->request->getPost('description'),
            'status' => $this->request->getPost('status'),
            'sort_order' => (int)($this->request->getPost('sort_order') ?? $category['sort_order'] ?? 0),
        ];

        $result = $this->categoryModel->update($id, $data);

        if ($result) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Category updated successfully',
                'data' => $this->categoryModel->find($id)
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to update category'
            ]);
        }
    }

    /**
     * Remove the specified category
     */
    public function delete($id)
    {
        // Check if user is logged in
        if (!session()->get('isLoggedIn')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Unauthorized'
            ]);
        }

        $category = $this->categoryModel->find($id);

        if (!$category) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Category not found'
            ]);
        }

        // Check if category is in use
        if ($this->categoryModel->isInUse($id)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Cannot delete category. It is currently being used by one or more contributions.'
            ]);
        }

        $result = $this->categoryModel->delete($id);

        if ($result) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Category deleted successfully'
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to delete category'
            ]);
        }
    }

    /**
     * Toggle category status
     */
    public function toggleStatus($id)
    {
        // Check if user is logged in
        if (!session()->get('isLoggedIn')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Unauthorized'
            ]);
        }

        $result = $this->categoryModel->toggleStatus($id);

        if ($result) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Category status updated successfully',
                'data' => $this->categoryModel->find($id)
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to update category status'
            ]);
        }
    }
}
