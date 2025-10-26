<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\AnnouncementModel;

class AnnouncementsController extends BaseController
{
    public function index()
    {
        // Check if user is logged in
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/admin/login');
        }

        $announcementModel = new AnnouncementModel();
        
        // Get all announcements
        $announcements = $announcementModel->getAllAnnouncements();
        
        // Get statistics
        $stats = $announcementModel->getStats();

        $data = [
            'title' => 'Announcements Management',
            'pageTitle' => 'Announcements',
            'pageSubtitle' => 'Create and manage system announcements for students and staff',
            'username' => session()->get('username'),
            'announcements' => $announcements,
            'stats' => $stats
        ];

        return view('admin/announcements', $data);
    }

    public function save()
    {
        try {
            // Check if user is logged in
            if (!session()->get('isLoggedIn')) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Unauthorized'
                ]);
            }

            $model = new AnnouncementModel();

            // Get user ID from session
            $userId = session()->get('user-id');

            // Gather POST data
            $data = [
                'title' => $this->request->getPost('title'),
                'text' => $this->request->getPost('content'), // Note: form uses 'content' but DB uses 'text'
                'type' => $this->request->getPost('type'),
                'priority' => $this->request->getPost('priority'),
                'target_audience' => $this->request->getPost('target_audience'),
                'status' => $this->request->getPost('status'),
                'created_by' => $userId,
                'expires_at' => $this->request->getPost('expires_at') ?: null
            ];

            // Set published_at if status is published
            if ($data['status'] === 'published') {
                $data['published_at'] = date('Y-m-d H:i:s');
            } else {
                $data['published_at'] = null;
            }

            $id = $this->request->getPost('announcement_id');

            if ($id) {
                // Get existing announcement data for activity logging
                $existing = $model->find($id);
                
                // Update existing announcement
                // Don't update published_at if already published
                if ($data['status'] !== 'published') {
                    unset($data['published_at']);
                }
                
                $result = $model->update($id, $data);
                $message = 'Announcement updated successfully.';
            } else {
                // Insert new announcement
                $result = $model->insert($data);
                $message = 'Announcement created successfully.';
            }

            if ($result) {
                // Log user activity
                if ($id && isset($existing)) {
                    // Build activity description with changes for updates
                    $changes = [];
                    if ($data['title'] !== $existing['title']) {
                        $changes[] = "Title: {$existing['title']} → {$data['title']}";
                    }
                    if ($data['type'] !== $existing['type']) {
                        $changes[] = "Type: {$existing['type']} → {$data['type']}";
                    }
                    if ($data['status'] !== $existing['status']) {
                        $changes[] = "Status: {$existing['status']} → {$data['status']}";
                    }
                    
                    $activityDescription = !empty($changes) 
                        ? 'Updated announcement (' . implode(', ', $changes) . ')' 
                        : 'Updated announcement: ' . $data['title'];
                    $this->logUserActivity('update', 'announcement', $id, $activityDescription);
                } else {
                    // For new announcements, just show the title
                    $this->logUserActivity('create', 'announcement', $result, 'Added new announcement: ' . $data['title']);
                }
                
                return $this->response->setJSON([
                    'success' => true,
                    'message' => $message
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to save announcement',
                    'errors' => $model->errors()
                ]);
            }

        } catch (\Exception $e) {
            log_message('error', 'Announcement save error: ' . $e->getMessage());
            
            return $this->response->setJSON([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ]);
        }
    }

    public function get($id)
    {
        try {
            // Check if user is logged in
            if (!session()->get('isLoggedIn')) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Unauthorized'
                ]);
            }

            $model = new AnnouncementModel();
            $announcement = $model->find($id);

            if (!$announcement) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Announcement not found'
                ]);
            }

            return $this->response->setJSON([
                'success' => true,
                'announcement' => $announcement
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    public function delete($id)
    {
        try {
            // Check if user is logged in
            if (!session()->get('isLoggedIn')) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Unauthorized'
                ]);
            }

            $model = new AnnouncementModel();
            
            // Check if announcement exists
            $announcement = $model->find($id);
            if (!$announcement) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Announcement not found'
                ]);
            }

            // Delete the announcement
            $deleted = $model->delete($id);

            if ($deleted) {
                // Log user activity
                $this->logUserActivity('delete', 'announcement', $id, 'Deleted announcement: ' . $announcement['title']);
                
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Announcement deleted successfully'
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to delete announcement'
                ]);
            }

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    public function updateStatus($id)
    {
        try {
            // Check if user is logged in
            if (!session()->get('isLoggedIn')) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Unauthorized'
                ]);
            }

            $model = new AnnouncementModel();
            
            // Check if announcement exists
            $announcement = $model->find($id);
            if (!$announcement) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Announcement not found'
                ]);
            }

            $newStatus = $this->request->getPost('status');
            
            if (!$newStatus) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Status is required'
                ]);
            }

            $data = ['status' => $newStatus];
            
            // Set published_at if status is being changed to published
            if ($newStatus === 'published' && $announcement['status'] !== 'published') {
                $data['published_at'] = date('Y-m-d H:i:s');
            }

            // Update status
            $updated = $model->update($id, $data);

            if ($updated) {
                // Log user activity
                $this->logUserActivity('update', 'announcement', $id, 'Updated announcement status to: ' . $newStatus . ' - ' . $announcement['title']);
                
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Announcement status updated successfully'
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to update announcement status'
                ]);
            }

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

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
