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
            'pageTitle' => 'Payment Methods',
            'pageSubtitle' => 'Manage payment methods and their instructions',
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
            'title' => 'Create Payment Method',
            'pageTitle' => 'Create Payment Method',
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
            'account_number' => 'permit_empty|max_length[100]',
            'account_name' => 'permit_empty|max_length[100]',
            'qr_code' => 'permit_empty|uploaded[qr_code]|max_size[qr_code,2048]|ext_in[qr_code,png,jpg,jpeg]',
            'custom_instructions' => 'permit_empty',
            'reference_prefix' => 'permit_empty|max_length[20]',
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
            'account_number' => [
                'max_length' => 'Account number cannot exceed 100 characters.',
            ],
            'account_name' => [
                'max_length' => 'Account name cannot exceed 100 characters.',
            ],
            'qr_code' => [
                'uploaded' => 'Please upload a valid QR code image.',
                'max_size' => 'QR code image size cannot exceed 2MB.',
                'ext_in' => 'QR code must be a PNG, JPG, or JPEG file.',
            ],
            'reference_prefix' => [
                'max_length' => 'Reference prefix cannot exceed 20 characters.',
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

        // Handle QR code upload
        $qrCodePath = null;
        $qrCodeFile = $this->request->getFile('qr_code');
        
        if ($qrCodeFile && $qrCodeFile->isValid() && !$qrCodeFile->hasMoved()) {
            $uploadPath = FCPATH . 'uploads/payment_methods/qr_codes/';
            
            // Create directory if it doesn't exist
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }
            
            $newName = 'qr_' . time() . '_' . $qrCodeFile->getRandomName();
            if ($qrCodeFile->move($uploadPath, $newName)) {
                $qrCodePath = 'uploads/payment_methods/qr_codes/' . $newName;
            }
        }

        $data = [
            'name' => $this->request->getPost('name'),
            'description' => $this->request->getPost('description'),
            'account_details' => $this->request->getPost('account_details'),
            'account_number' => $this->request->getPost('account_number'),
            'account_name' => $this->request->getPost('account_name'),
            'qr_code_path' => $qrCodePath,
            'custom_instructions' => $this->request->getPost('custom_instructions'),
            'reference_prefix' => $this->request->getPost('reference_prefix') ?: 'CP',
            'status' => $this->request->getPost('status'),
        ];

        $result = $this->paymentMethodModel->insert($data);

        if ($result) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Payment method created successfully'
                ]);
            }
            return redirect()->to('/admin/settings/payment-methods')
                ->with('success', 'Payment method created successfully');
        } else {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to create payment method'
                ]);
            }
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create payment method');
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
                ->with('error', 'Payment method not found');
        }

        $data = [
            'title' => 'Edit Payment Method',
            'pageTitle' => 'Edit Payment Method',
            'pageSubtitle' => 'Edit payment method details',
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
                    'message' => 'Payment method not found'
                ]);
            }
            return redirect()->to('/admin/settings/payment-methods')
                ->with('error', 'Payment method not found');
        }

        $rules = [
            'name' => "required|max_length[100]|is_unique[payment_methods.name,id,{$id}]",
            'icon' => 'permit_empty|max_length[255]',
            'description' => 'permit_empty|max_length[1000]',
            'account_details' => 'permit_empty|max_length[255]',
            'account_number' => 'permit_empty|max_length[100]',
            'account_name' => 'permit_empty|max_length[100]',
            'qr_code' => 'permit_empty|uploaded[qr_code]|max_size[qr_code,2048]|ext_in[qr_code,png,jpg,jpeg]',
            'custom_instructions' => 'permit_empty',
            'reference_prefix' => 'permit_empty|max_length[20]',
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
            'account_number' => [
                'max_length' => 'Account number cannot exceed 100 characters.',
            ],
            'account_name' => [
                'max_length' => 'Account name cannot exceed 100 characters.',
            ],
            'qr_code' => [
                'uploaded' => 'Please upload a valid QR code image.',
                'max_size' => 'QR code image size cannot exceed 2MB.',
                'ext_in' => 'QR code must be a PNG, JPG, or JPEG file.',
            ],
            'reference_prefix' => [
                'max_length' => 'Reference prefix cannot exceed 20 characters.',
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

        // Handle QR code upload
        $qrCodePath = $paymentMethod['qr_code_path']; // Keep existing if no new upload
        $qrCodeFile = $this->request->getFile('qr_code');
        
        if ($qrCodeFile && $qrCodeFile->isValid() && !$qrCodeFile->hasMoved()) {
            $uploadPath = FCPATH . 'uploads/payment_methods/qr_codes/';
            
            // Create directory if it doesn't exist
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }
            
            // Delete old QR code if it exists
            if ($qrCodePath && file_exists(FCPATH . $qrCodePath)) {
                unlink(FCPATH . $qrCodePath);
            }
            
            $newName = 'qr_' . time() . '_' . $qrCodeFile->getRandomName();
            if ($qrCodeFile->move($uploadPath, $newName)) {
                $qrCodePath = 'uploads/payment_methods/qr_codes/' . $newName;
            }
        }

        $data = [
            'name' => $this->request->getPost('name'),
            'description' => $this->request->getPost('description'),
            'account_details' => $this->request->getPost('account_details'),
            'account_number' => $this->request->getPost('account_number'),
            'account_name' => $this->request->getPost('account_name'),
            'qr_code_path' => $qrCodePath,
            'custom_instructions' => $this->request->getPost('custom_instructions'),
            'reference_prefix' => $this->request->getPost('reference_prefix') ?: 'CP',
            'status' => $this->request->getPost('status'),
        ];

        $result = $this->paymentMethodModel->update($id, $data);

        if ($result) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Payment method updated successfully'
                ]);
            }
            return redirect()->to('/admin/settings/payment-methods')
                ->with('success', 'Payment method updated successfully');
        } else {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to update payment method'
                ]);
            }
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update payment method');
        }
    }

    /**
     * Remove the specified payment method
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

        $paymentMethod = $this->paymentMethodModel->find($id);

        if (!$paymentMethod) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Payment method not found'
            ]);
        }

        // Delete QR code file if it exists
        if ($paymentMethod['qr_code_path'] && file_exists(FCPATH . $paymentMethod['qr_code_path'])) {
            unlink(FCPATH . $paymentMethod['qr_code_path']);
        }

        $result = $this->paymentMethodModel->delete($id);

        if ($result) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Payment method deleted successfully'
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to delete payment method'
            ]);
        }
    }

    /**
     * Toggle payment method status
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

        $result = $this->paymentMethodModel->toggleStatus($id);

        if ($result) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Payment method status updated successfully'
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to update payment method status'
            ]);
        }
    }

    /**
     * Get payment methods data for AJAX requests
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

        $paymentMethods = $this->paymentMethodModel->orderBy('name', 'ASC')->findAll();

        return $this->response->setJSON([
            'success' => true,
            'data' => $paymentMethods
        ]);
    }

    /**
     * Get payment method with custom instructions
     */
    public function getInstructions($name)
    {
        $method = $this->paymentMethodModel->getMethodWithInstructions($name);
        
        if (!$method) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Payment method not found'
            ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'method' => $method
        ]);
    }
}