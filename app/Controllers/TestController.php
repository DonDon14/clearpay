<?php

namespace App\Controllers;

use CodeIgniter\Controller;

/**
 * Test Controller
 * Temporary controller for debugging Cloudinary configuration
 */
class TestController extends Controller
{
    /**
     * Test Cloudinary configuration
     * Returns JSON with Cloudinary status and environment variables
     */
    public function cloudinaryStatus()
    {
        // Get environment variables
        $cloudName = $_ENV['CLOUDINARY_CLOUD_NAME'] ?? getenv('CLOUDINARY_CLOUD_NAME') ?: null;
        $apiKey = $_ENV['CLOUDINARY_API_KEY'] ?? getenv('CLOUDINARY_API_KEY') ?: null;
        $apiSecret = $_ENV['CLOUDINARY_API_SECRET'] ?? getenv('CLOUDINARY_API_SECRET') ?: null;
        
        // Try to initialize Cloudinary service
        $cloudinaryStatus = 'not_configured';
        $cloudinaryError = null;
        $cloudinaryUrl = null;
        
        try {
            $cloudinaryService = new \App\Services\CloudinaryService();
            if ($cloudinaryService->isConfigured()) {
                $cloudinaryStatus = 'configured';
            } else {
                $cloudinaryStatus = 'not_configured';
            }
        } catch (\Exception $e) {
            $cloudinaryStatus = 'error';
            $cloudinaryError = $e->getMessage();
        }
        
        // Return status
        return $this->response->setJSON([
            'cloudinary_status' => $cloudinaryStatus,
            'cloudinary_error' => $cloudinaryError,
            'env_vars' => [
                'CLOUDINARY_CLOUD_NAME' => $cloudName ? 'SET (' . strlen($cloudName) . ' chars)' : 'NOT SET',
                'CLOUDINARY_API_KEY' => $apiKey ? 'SET (' . strlen($apiKey) . ' chars)' : 'NOT SET',
                'CLOUDINARY_API_SECRET' => $apiSecret ? 'SET (' . strlen($apiSecret) . ' chars)' : 'NOT SET',
            ],
            'raw_values' => [
                'CLOUDINARY_CLOUD_NAME' => $cloudName,
                'CLOUDINARY_API_KEY' => $apiKey ? substr($apiKey, 0, 5) . '...' : null,
                'CLOUDINARY_API_SECRET' => $apiSecret ? substr($apiSecret, 0, 5) . '...' : null,
            ],
            'server_info' => [
                'SERVER_NAME' => $_SERVER['SERVER_NAME'] ?? 'NOT SET',
                'RENDER_ENV' => getenv('RENDER') ?: 'NOT SET',
            ]
        ]);
    }
}

