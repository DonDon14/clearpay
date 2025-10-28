<?php

namespace App\Controllers\Admin\Settings;

use App\Controllers\BaseController;
use App\Models\PaymentMethodModel;

class PaymentMethodController extends BaseController
{
    protected $paymentMethodModel;

    public function __construct()
    {
        $this->paymentMethodModel = new PaymentMethodModel();
    }

    /**
     * Display a listing of payment methods
     */
    public function index()
    {
        // Check if user is logged in
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/admin/login');
        }

        $data = [
            'title' => 'Payment Methods',
            'pageTitle' => 'Manage Payment Methods',
            'pageSubtitle' => 'Configure available payment methods for the system',
            'paymentMethods' => $this->paymentMethodModel->orderBy('name', 'ASC')->findAll(),
        ];

        return view('admin/settings/payment_methods/index', $data);
    }

    /**
     * Show the form for creating a new payment method
     */
    public function create()
    {
        // Check if user is logged in
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/admin/login');
        }

        $data = [
            'title' => 'Add Payment Method',
            'pageTitle' => 'Add New Payment Method',
            'pageSubtitle' => 'Create a new payment method',
        ];

        return view('admin/settings/payment_methods/create', $data);
    }

    /**
     * Store a newly created payment method
     */
    public function store()
    {
        // Check if user is logged in
        if (!session()->get('isLoggedIn')) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Unauthorized'
                ]);
            }
            return redirect()->to('/admin/login');
        }

        $rules = [
            'name' => 'required|max_length[100]|is_unique[payment_methods.name]',
            'icon' => 'permit_empty|max_length[255]',
            'description' => 'permit_empty|max_length[1000]',
            'account_details' => 'permit_empty|max_length[255]',
            'status' => 'required|in_list[active,inactive]',
        ];

        $messages = [
            'name' => [
                'required' => 'Payment method name is required.',
                'max_length' => 'Payment method name cannot exceed 100 characters.',
                'is_unique' => 'A payment method with this name already exists.',
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

        if (!$this->validate($rules, $messages)) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $this->validator->getErrors()
                ]);
            }
            return redirect()->back()
                ->withInput()
                ->with('validation_errors', $this->validator->getErrors());
        }

        $data = [
            'name' => $this->request->getPost('name'),
            'description' => $this->request->getPost('description'),
            'account_details' => $this->request->getPost('account_details'),
            'status' => $this->request->getPost('status'),
        ];

        // Handle icon file upload
        $iconFile = $this->request->getFile('icon');
        if ($iconFile && $iconFile->isValid() && !$iconFile->hasMoved()) {
            $iconPath = $this->uploadIcon($iconFile);
            if ($iconPath) {
                $data['icon'] = $iconPath;
            }
        }

        if ($this->paymentMethodModel->insert($data)) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Payment method created successfully.'
                ]);
            }
            return redirect()->to('/admin/settings/payment-methods')
                ->with('success', 'Payment method created successfully.');
        } else {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to create payment method. Please try again.'
                ]);
            }
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create payment method. Please try again.');
        }
    }

    /**
     * Show the form for editing the specified payment method
     */
    public function edit($id)
    {
        // Check if user is logged in
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/admin/login');
        }

        $paymentMethod = $this->paymentMethodModel->find($id);
        
        if (!$paymentMethod) {
            return redirect()->to('/admin/settings/payment-methods')
                ->with('error', 'Payment method not found.');
        }

        $data = [
            'title' => 'Edit Payment Method',
            'pageTitle' => 'Edit Payment Method',
            'pageSubtitle' => 'Update payment method details',
            'paymentMethod' => $paymentMethod,
        ];

        return view('admin/settings/payment_methods/edit', $data);
    }

    /**
     * Update the specified payment method
     */
    public function update($id)
    {
        // Check if user is logged in
        if (!session()->get('isLoggedIn')) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Unauthorized'
                ]);
            }
            return redirect()->to('/admin/login');
        }

        $paymentMethod = $this->paymentMethodModel->find($id);
        
        if (!$paymentMethod) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Payment method not found.'
                ]);
            }
            return redirect()->to('/admin/settings/payment-methods')
                ->with('error', 'Payment method not found.');
        }

        $rules = [
            'name' => "required|max_length[100]|is_unique[payment_methods.name,id,{$id}]",
            'icon' => 'permit_empty|max_length[255]',
            'description' => 'permit_empty|max_length[1000]',
            'account_details' => 'permit_empty|max_length[255]',
            'status' => 'required|in_list[active,inactive]',
        ];

        $messages = [
            'name' => [
                'required' => 'Payment method name is required.',
                'max_length' => 'Payment method name cannot exceed 100 characters.',
                'is_unique' => 'A payment method with this name already exists.',
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

        if (!$this->validate($rules, $messages)) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $this->validator->getErrors()
                ]);
            }
            return redirect()->back()
                ->withInput()
                ->with('validation_errors', $this->validator->getErrors());
        }

        $data = [
            'name' => $this->request->getPost('name'),
            'description' => $this->request->getPost('description'),
            'account_details' => $this->request->getPost('account_details'),
            'status' => $this->request->getPost('status'),
        ];

        // Handle icon file upload
        $iconFile = $this->request->getFile('icon');
        if ($iconFile && $iconFile->isValid() && !$iconFile->hasMoved()) {
            $iconPath = $this->uploadIcon($iconFile);
            if ($iconPath) {
                $data['icon'] = $iconPath;
            }
        }

        if ($this->paymentMethodModel->update($id, $data)) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Payment method updated successfully.'
                ]);
            }
            return redirect()->to('/admin/settings/payment-methods')
                ->with('success', 'Payment method updated successfully.');
        } else {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to update payment method. Please try again.'
                ]);
            }
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update payment method. Please try again.');
        }
    }

    /**
     * Delete the specified payment method
     */
    public function delete($id)
    {
        // Check if user is logged in
        if (!session()->get('isLoggedIn')) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Unauthorized'
                ]);
            }
            return redirect()->to('/admin/login');
        }

        $paymentMethod = $this->paymentMethodModel->find($id);
        
        if (!$paymentMethod) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Payment method not found.'
                ]);
            }
            return redirect()->to('/admin/settings/payment-methods')
                ->with('error', 'Payment method not found.');
        }

        if ($this->paymentMethodModel->delete($id)) {
            // Delete the icon file if it exists
            if (!empty($paymentMethod['icon'])) {
                $this->deleteIconFile($paymentMethod['icon']);
            }
            
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Payment method deleted successfully.'
                ]);
            }
            return redirect()->to('/admin/settings/payment-methods')
                ->with('success', 'Payment method deleted successfully.');
        } else {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to delete payment method. Please try again.'
                ]);
            }
            return redirect()->to('/admin/settings/payment-methods')
                ->with('error', 'Failed to delete payment method. Please try again.');
        }
    }

    /**
     * Toggle payment method status
     */
    public function toggleStatus($id)
    {
        // Check if user is logged in
        if (!session()->get('isLoggedIn')) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Unauthorized'
                ]);
            }
            return redirect()->to('/admin/login');
        }

        $paymentMethod = $this->paymentMethodModel->find($id);
        
        if (!$paymentMethod) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Payment method not found.'
                ]);
            }
            return redirect()->to('/admin/settings/payment-methods')
                ->with('error', 'Payment method not found.');
        }

        if ($this->paymentMethodModel->toggleStatus($id)) {
            $newStatus = $paymentMethod['status'] === 'active' ? 'inactive' : 'active';
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => "Payment method status changed to {$newStatus}."
                ]);
            }
            return redirect()->to('/admin/settings/payment-methods')
                ->with('success', "Payment method status changed to {$newStatus}.");
        } else {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to update payment method status. Please try again.'
                ]);
            }
            return redirect()->to('/admin/settings/payment-methods')
                ->with('error', 'Failed to update payment method status. Please try again.');
        }
    }

    /**
     * Upload icon file and return the path
     */
    private function uploadIcon($iconFile)
    {
        // Validate file type
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($iconFile->getMimeType(), $allowedTypes)) {
            return false;
        }

        // Validate file size (max 2MB)
        if ($iconFile->getSize() > 2 * 1024 * 1024) {
            return false;
        }

        // Create directories if they don't exist
        $uploadPath = FCPATH . 'uploads/payment_methods/';
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        // Generate unique filename
        $newName = $iconFile->getRandomName();
        
        // Move file to upload directory
        if ($iconFile->move($uploadPath, $newName)) {
            return 'uploads/payment_methods/' . $newName;
        }

        return false;
    }

    /**
     * Delete icon file
     */
    private function deleteIconFile($iconPath)
    {
        if ($iconPath && file_exists(FCPATH . $iconPath)) {
            unlink(FCPATH . $iconPath);
        }
    }

    /**
     * Get payment methods data as JSON for AJAX requests
     */
    public function getData()
    {
        if (!session()->get('isLoggedIn')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Unauthorized'
            ]);
        }

        $paymentMethods = $this->paymentMethodModel->orderBy('name', 'ASC')->findAll();
        
        return $this->response->setJSON([
            'success' => true,
            'data' => $paymentMethods
        ]);
    }

    /**
     * Test payment methods
     */
    public function test()
    {
        return $this->response->setJSON([
            'success' => true,
            'message' => 'Payment methods tested successfully'
        ]);
    }
}
