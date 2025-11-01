<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\PayerModel;

class PayersController extends BaseController
{
    public function create()
    {
        if ($this->request->getMethod() !== 'POST') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid request method'
            ]);
        }

        $validation = \Config\Services::validation();
        
        // Get JSON data from request body
        $jsonData = $this->request->getJSON(true);
        
        if (!$jsonData) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid JSON data'
            ]);
        }
        
        // Set validation rules with custom error messages
        $validation->setRules([
            'payer_name' => 'required|min_length[2]|max_length[100]',
            'new_payer_id' => 'required|min_length[2]|max_length[50]|is_unique[payers.payer_id]',
            'payer_email' => 'required|valid_email|max_length[100]|is_unique[payers.email_address]',
            'payer_phone' => 'required|min_length[7]|max_length[20]'
        ], [
            'payer_name' => [
                'required' => 'Payer name is required',
                'min_length' => 'Payer name must be at least 2 characters',
                'max_length' => 'Payer name cannot exceed 100 characters'
            ],
            'new_payer_id' => [
                'required' => 'Payer ID is required',
                'min_length' => 'Payer ID must be at least 2 characters',
                'max_length' => 'Payer ID cannot exceed 50 characters',
                'is_unique' => 'This Payer ID already exists. Please use a different one.'
            ],
            'payer_email' => [
                'required' => 'Email address is required',
                'valid_email' => 'Please enter a valid email address',
                'max_length' => 'Email address cannot exceed 100 characters',
                'is_unique' => 'This email address is already registered. Please use a different email or select "Existing Payer" instead.'
            ],
            'payer_phone' => [
                'required' => 'Phone number is required',
                'min_length' => 'Phone number must be at least 7 digits',
                'max_length' => 'Phone number cannot exceed 20 characters'
            ]
        ]);

        if (!$validation->run($jsonData)) {
            log_message('error', 'Payer validation failed: ' . json_encode($validation->getErrors()));
            log_message('error', 'Request data: ' . json_encode($jsonData));
            
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validation->getErrors(),
                'debug_data' => $jsonData
            ]);
        }

        try {
            $payerModel = new PayerModel();
            
            // Sanitize and validate phone number using phone_helper
            $contactNumber = !empty($jsonData['payer_phone']) ? sanitize_phone_number($jsonData['payer_phone']) : '';
            
            if (!empty($contactNumber) && !validate_phone_number($contactNumber)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Contact number must be exactly 11 digits (numbers only)'
                ]);
            }
            
            $data = [
                'payer_name' => trim($jsonData['payer_name']),
                'payer_id' => trim($jsonData['new_payer_id']),
                'email_address' => trim($jsonData['payer_email']),
                'contact_number' => $contactNumber,
                'course_department' => !empty($jsonData['course_department']) ? trim($jsonData['course_department']) : null,
                'email_verified' => 1,
                'verification_token' => null,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $payerId = $payerModel->insert($data);
            
            if ($payerId) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Payer created successfully',
                    'payer_id' => $payerId
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to create payer'
                ]);
            }
            
        } catch (\Exception $e) {
            log_message('error', 'Error creating payer: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'An error occurred while creating the payer'
            ]);
        }
    }
}
