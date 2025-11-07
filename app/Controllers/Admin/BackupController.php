<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\BackupService;

class BackupController extends BaseController
{
    protected $backupService;

    public function __construct()
    {
        $this->backupService = new BackupService();
    }

    /**
     * Create a new database backup
     */
    public function createBackup()
    {
        // Check if user is logged in
        if (!session()->get('isLoggedIn')) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Unauthorized. Please login first.'
            ])->setStatusCode(401);
        }

        // Check if user is admin (you may want to add role checking here)
        // For now, we'll just check if logged in

        try {
            $result = $this->backupService->createBackup();

            if ($result['success']) {
                // Copy backup to Documents folder for easy access
                $documentsPath = 'C:' . DIRECTORY_SEPARATOR . 'Users' . DIRECTORY_SEPARATOR . 'User' . DIRECTORY_SEPARATOR . 'Documents' . DIRECTORY_SEPARATOR . 'clearpaybackups' . DIRECTORY_SEPARATOR;
                $copiedToDocuments = false;
                
                if (is_dir($documentsPath)) {
                    $destinationPath = $documentsPath . $result['filename'];
                    if (copy($result['filepath'], $destinationPath)) {
                        $copiedToDocuments = true;
                        log_message('info', "Backup file copied to Documents folder: {$destinationPath}");
                    }
                }

                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Database backup created successfully!',
                    'backup' => [
                        'filename' => $result['filename'],
                        'size' => $result['size'],
                        'size_formatted' => $result['size_formatted'],
                        'created_at' => $result['created_at'],
                        'download_url' => base_url('admin/backup/download/' . urlencode($result['filename'])),
                        'stored_at' => $copiedToDocuments ? $documentsPath . $result['filename'] : $result['filepath'],
                        'copied_to_documents' => $copiedToDocuments,
                    ]
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'error' => $result['error'] ?? 'Failed to create backup.'
                ])->setStatusCode(500);
            }
        } catch (\Exception $e) {
            log_message('error', 'Backup creation error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'error' => 'An error occurred while creating the backup: ' . $e->getMessage()
            ])->setStatusCode(500);
        }
    }

    /**
     * Download a backup file
     */
    public function downloadBackup($filename = null)
    {
        // Check if user is logged in
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/admin/login');
        }

        if (!$filename) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Filename is required.'
            ])->setStatusCode(400);
        }

        // Decode filename
        $filename = urldecode($filename);

        // Get backup file path
        $filepath = $this->backupService->getBackupPath($filename);

        if (!$filepath || !file_exists($filepath)) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Backup file not found.'
            ])->setStatusCode(404);
        }

        // Copy file to Documents folder for easy access
        $documentsPath = 'C:' . DIRECTORY_SEPARATOR . 'Users' . DIRECTORY_SEPARATOR . 'User' . DIRECTORY_SEPARATOR . 'Documents' . DIRECTORY_SEPARATOR . 'clearpaybackups' . DIRECTORY_SEPARATOR;
        if (is_dir($documentsPath)) {
            $destinationPath = $documentsPath . $filename;
            if (copy($filepath, $destinationPath)) {
                log_message('info', "Backup file copied to Documents folder: {$destinationPath}");
            }
        }

        // Set headers for file download
        return $this->response->download($filepath, null);
    }

    /**
     * Get list of available backups
     */
    public function listBackups()
    {
        // Check if user is logged in
        if (!session()->get('isLoggedIn')) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Unauthorized. Please login first.'
            ])->setStatusCode(401);
        }

        try {
            $backups = $this->backupService->getBackupList();

            // Add download URLs
            foreach ($backups as &$backup) {
                $backup['download_url'] = base_url('admin/backup/download/' . urlencode($backup['filename']));
            }

            return $this->response->setJSON([
                'success' => true,
                'backups' => $backups,
                'count' => count($backups)
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Backup list error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'error' => 'An error occurred while retrieving backups.'
            ])->setStatusCode(500);
        }
    }
}

