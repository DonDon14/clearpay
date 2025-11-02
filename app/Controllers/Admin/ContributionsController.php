<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ContributionModel;
use App\Services\ActivityLogger;

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
                'amount' => 'required|numeric',
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

            // Get user ID from session with proper fallback - use user-id (hyphen) as per LoginController
            $userId = session()->get('user-id');
            
            // If no user ID in session, we need to handle this gracefully
            // Since the FK constraint allows NULL, we can set it to NULL
            // But first, let's ensure we're actually logged in
            if (!$userId && !session()->get('isLoggedIn')) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'You must be logged in to create a contribution'
                ]);
            }
            
            $createdBy = $userId; // This can be NULL

            // Gather POST data
            $data = [
                'title'             => $this->request->getPost('title'),
                'contribution_code' => $this->request->getPost('contribution_code') ?: null,
                'description'       => $this->request->getPost('description'),
                'amount'            => $this->request->getPost('amount'),
                'cost_price'        => $this->request->getPost('cost_price') ?: 0,
                'category'          => $this->request->getPost('category'),
                'status'            => $this->request->getPost('status'),
                'created_by'        => $createdBy ?: null, // Ensure it's NULL not empty string
                'created_at'        => date('Y-m-d H:i:s'),
                'updated_at'        => date('Y-m-d H:i:s')
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
                // Log activity using new ActivityLogger
                $activityLogger = new ActivityLogger();
                
                if ($id) {
                    // Update existing contribution - get old data first and include ID
                    $oldData = $model->find($id);
                    $data['id'] = $id;
                    $activityLogger->logContribution('updated', $data, $oldData);
                } else {
                    // Create new contribution - use the insert result as ID
                    $data['id'] = $result;
                    $activityLogger->logContribution('created', $data);
                }
                
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
            // Log the full error for debugging
            log_message('error', 'Contribution save error: ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());
            
            return $this->response->setJSON([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ]);
        }
    }

    public function get($id)
    {
        try {
            $model = new ContributionModel();
            $contribution = $model->find($id);

            if ($contribution) {
                return $this->response->setJSON([
                    'success' => true,
                    'contribution' => $contribution
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Contribution not found'
                ]);
            }
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ]);
        }
    }

    public function update($id)
    {
        try {
            // Validation rules
            $rules = [
                'title' => 'required|min_length[3]|max_length[255]',
                'amount' => 'required|numeric',
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

            // Check if contribution exists
            $existing = $model->find($id);
            if (!$existing) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Contribution not found'
                ]);
            }

            // Gather POST data
            $data = [
                'title'             => $this->request->getPost('title'),
                'contribution_code' => $this->request->getPost('contribution_code') ?: null,
                'description'       => $this->request->getPost('description'),
                'amount'            => $this->request->getPost('amount'),
                'cost_price'        => $this->request->getPost('cost_price') ?: 0,
                'category'          => $this->request->getPost('category'),
                'status'            => $this->request->getPost('status'),
                'updated_at'        => date('Y-m-d H:i:s')
            ];

            $result = $model->update($id, $data);

            if ($result) {
                // Log user activity
                $this->logUserActivity('update', 'contribution', $id, 'Updated contribution: ' . $data['title']);
                
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Contribution updated successfully.'
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to update contribution'
                ]);
            }

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ]);
        }
    }

    public function delete($id)
    {
        try {
            $model = new ContributionModel();

            // Check if contribution exists
            $existing = $model->find($id);
            if (!$existing) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Contribution not found'
                ]);
            }

            $result = $model->delete($id);

            if ($result) {
                // Log user activity
                $this->logUserActivity('delete', 'contribution', $id, 'Deleted contribution: ' . $existing['title']);
                
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Contribution deleted successfully.'
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to delete contribution'
                ]);
            }

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ]);
        }
    }

    public function toggleStatus($id)
    {
        try {
            $model = new ContributionModel();

            // Check if contribution exists
            $existing = $model->find($id);
            if (!$existing) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Contribution not found'
                ]);
            }

            // Toggle status
            $newStatus = $existing['status'] === 'active' ? 'inactive' : 'active';
            
            $result = $model->update($id, [
                'status' => $newStatus,
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            if ($result) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Contribution status updated successfully.',
                    'newStatus' => $newStatus
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to update contribution status'
                ]);
            }

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Log user activity
     */
    private function logUserActivity($activityType, $entityType, $entityId, $description)
    {
        try {
            $db = \Config\Database::connect();
            
            $data = [
                'user_id' => session()->get('user-id'),
                'activity_type' => $activityType,
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'description' => $description,
                'ip_address' => $this->request->getIPAddress(),
                'user_agent' => $this->request->getUserAgent(),
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $db->table('user_activities')->insert($data);
        } catch (\Exception $e) {
            // Log error but don't fail the main operation
            log_message('error', 'Failed to log user activity: ' . $e->getMessage());
        }
    }
}
