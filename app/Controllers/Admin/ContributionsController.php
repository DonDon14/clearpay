<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ContributionModel;

class ContributionsController extends BaseController
{
    public function index()
    {
        // For future use if needed
        return redirect()->to('/contributions');
    }

    public function save()
    {
        try {
            // Validation rules
            $rules = [
                'title' => 'required|min_length[3]|max_length[255]',
                'amount' => 'required|decimal',
                'status' => 'required|in_list[active,inactive]'
            ];

            if (!$this->validate($rules)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $this->validator->getErrors()
                ]);
            }

            $model = new ContributionModel();

            // Gather POST data
            $data = [
                'title'       => $this->request->getPost('title'),
                'description' => $this->request->getPost('description'),
                'amount'      => $this->request->getPost('amount'),
                'cost_price'  => $this->request->getPost('cost_price') ?: 0,
                'category'    => $this->request->getPost('category'),
                'status'      => $this->request->getPost('status'),
                'created_by'  => session()->get('user_id') ?: 1, // fallback to user 1
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s')
            ];

            $id = $this->request->getPost('id');

            if ($id) {
                // Update existing contribution
                $result = $model->update($id, $data);
                $message = 'Contribution updated successfully.';
            } else {
                // Insert new contribution
                $result = $model->insert($data);
                $message = 'Contribution added successfully.';
            }

            if ($result) {
                session()->setFlashdata('success', $message);
                return $this->response->setJSON([
                    'success' => true,
                    'message' => $message
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to save contribution'
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
