<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ContributionModel;
use App\Models\PaymentModel;

class PaymentsController extends BaseController
{
    public function index()
    {
        // Check if user is logged in
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $contributionModel = new ContributionModel();
        $contributions = $contributionModel->findAll();

        $data = [
            'title' => 'Payments Management',
            'pageTitle' => 'Payments',
            'pageSubtitle' => 'Manage student payments and transactions',
            'username' => session()->get('username'),
            'contributions' => $contributions,
        ];

        return view('admin/payments', $data);
    }

    public function history()
    {
        // Check if user is logged in
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $data = [
            'title' => 'Payment History',
            'pageTitle' => 'Payment History',
            'pageSubtitle' => 'View all payment transactions and records',
            'username' => session()->get('username'),
        ];

        return view('admin/payment-history', $data);
    }

    public function analytics()
    {
        // Check if user is logged in
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $data = [
            'title' => 'Payment Analytics',
            'pageTitle' => 'Analytics & Reports',
            'pageSubtitle' => 'View payment statistics and generate reports',
            'username' => session()->get('username'),
        ];

        return view('admin/analytics', $data);
    }

    public function save()
    {
        try {
            // Validation rules
            $rules = [
                'payer_name' => 'required|min_length[3]|max_length[255]',
                'payer_id' => 'required|min_length[3]|max_length[50]',
                'contribution_id' => 'required|integer',
                'amount_paid' => 'required|decimal',
                'payment_method' => 'required|in_list[cash,online,check,bank]',
                'is_partial_payment' => 'required|in_list[0,1]',
                'payment_date' => 'required'
            ];

            if (!$this->validate($rules)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $this->validator->getErrors()
                ]);
            }

            $paymentModel = new PaymentModel();

            // Determine payment status
            $isPartial = $this->request->getPost('is_partial_payment') == '1';
            $remainingBalance = (float) $this->request->getPost('remaining_balance');
            $paymentStatus = ($isPartial && $remainingBalance > 0) ? 'partial' : 'fully paid';

            // Generate reference number
            $referenceNumber = 'REF-' . date('Ymd') . '-' . strtoupper(uniqid());

            // Gather POST data
            $data = [
                'contribution_id' => $this->request->getPost('contribution_id'),
                'payer_id' => $this->request->getPost('payer_id'),
                'payer_name' => $this->request->getPost('payer_name'),
                'contact_number' => $this->request->getPost('contact_number'),
                'email_address' => $this->request->getPost('email_address'),
                'amount_paid' => $this->request->getPost('amount_paid'),
                'payment_method' => $this->request->getPost('payment_method'),
                'payment_status' => $paymentStatus,
                'is_partial_payment' => $isPartial ? 1 : 0,
                'remaining_balance' => $remainingBalance,
                'parent_payment_id' => $this->request->getPost('parent_payment_id') ?: null,
                'payment_sequence' => 1, // Default to 1, can be enhanced later
                'reference_number' => $referenceNumber,
                'recorded_by' => session()->get('user_id') ?: 1,
                'payment_date' => $this->request->getPost('payment_date'),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $id = $this->request->getPost('id');

            if ($id) {
                // Update existing payment
                $result = $paymentModel->update($id, $data);
                $message = 'Payment updated successfully.';
            } else {
                // Insert new payment
                $result = $paymentModel->insert($data);
                $message = 'Payment recorded successfully.';
            }

            if ($result) {
                session()->setFlashdata('success', $message);
                return $this->response->setJSON([
                    'success' => true,
                    'message' => $message,
                    'reference_number' => $referenceNumber
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to save payment'
                ]);
            }

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ]);
        }
    }
}