<?php

namespace App\Controllers\Admin\Settings;

use App\Controllers\BaseController;
use App\Models\RefundMethodModel;

class RefundMethodController extends BaseController
{
    protected $refundMethodModel;

    public function __construct()
    {
        $this->refundMethodModel = new RefundMethodModel();
    }

    /**
     * Get refund methods data for AJAX requests
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

        $refundMethods = $this->refundMethodModel->getAllMethods();

        return $this->response->setJSON([
            'success' => true,
            'data' => $refundMethods
        ]);
    }

    /**
     * Store a newly created refund method
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
            'code' => 'required|max_length[50]|alpha_dash|is_unique[refund_methods.code]',
            'description' => 'permit_empty',
            'status' => 'required|in_list[active,inactive]',
            'sort_order' => 'permit_empty|integer',
        ];

        $messages = [
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

        if (!$this->validate($rules, $messages)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $this->validator->getErrors()
            ]);
        }

        $data = [
            'name' => $this->request->getPost('name'),
            'code' => strtolower($this->request->getPost('code')),
            'description' => $this->request->getPost('description'),
            'status' => $this->request->getPost('status'),
            'sort_order' => (int)($this->request->getPost('sort_order') ?? 0),
        ];

        $result = $this->refundMethodModel->insert($data);

        if ($result) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Refund method created successfully',
                'data' => $this->refundMethodModel->find($result)
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to create refund method',
                'errors' => $this->refundMethodModel->errors()
            ]);
        }
    }

    /**
     * Update the specified refund method
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

        $refundMethod = $this->refundMethodModel->find($id);

        if (!$refundMethod) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Refund method not found'
            ]);
        }

        // Get the new code value
        $newCode = strtolower($this->request->getPost('code'));
        
        // Check if code is being changed and if new code already exists (excluding current record)
        $existingMethod = $this->refundMethodModel->getMethodByCode($newCode);
        if ($existingMethod && $existingMethod['id'] != $id) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => [
                    'code' => 'A refund method with this code already exists.'
                ]
            ]);
        }

        $rules = [
            'name' => 'required|max_length[100]',
            'code' => 'required|max_length[50]|alpha_dash',
            'description' => 'permit_empty',
            'status' => 'required|in_list[active,inactive]',
            'sort_order' => 'permit_empty|integer',
        ];

        $messages = [
            'name' => [
                'required' => 'Refund method name is required.',
                'max_length' => 'Refund method name cannot exceed 100 characters.',
            ],
            'code' => [
                'required' => 'Refund method code is required.',
                'max_length' => 'Refund method code cannot exceed 50 characters.',
                'alpha_dash' => 'Refund method code can only contain letters, numbers, dashes, and underscores.',
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
            'code' => strtolower($this->request->getPost('code')),
            'description' => $this->request->getPost('description'),
            'status' => $this->request->getPost('status'),
            'sort_order' => (int)($this->request->getPost('sort_order') ?? 0),
        ];

        // Skip model validation since we've already validated in the controller
        $result = $this->refundMethodModel->skipValidation(true)->update($id, $data);

        if ($result) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Refund method updated successfully',
                'data' => $this->refundMethodModel->find($id)
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to update refund method',
                'errors' => $this->refundMethodModel->errors()
            ]);
        }
    }

    /**
     * Remove the specified refund method
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

        $refundMethod = $this->refundMethodModel->find($id);

        if (!$refundMethod) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Refund method not found'
            ]);
        }

        $result = $this->refundMethodModel->delete($id);

        if ($result) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Refund method deleted successfully'
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to delete refund method'
            ]);
        }
    }

    /**
     * Toggle refund method status
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

        $result = $this->refundMethodModel->toggleStatus($id);

        if ($result) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Refund method status updated successfully'
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to update refund method status'
            ]);
        }
    }
}

