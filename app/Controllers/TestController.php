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
        $debugInfo = [];
        
        try {
            // Check if Cloudinary classes exist
            $debugInfo['cloudinary_class_exists'] = class_exists('\Cloudinary\Cloudinary');
            $debugInfo['configuration_class_exists'] = class_exists('\Cloudinary\Configuration\Configuration');
            $debugInfo['upload_api_class_exists'] = class_exists('\Cloudinary\Api\Upload\UploadApi');
            
            $cloudinaryService = new \App\Services\CloudinaryService();
            $debugInfo['service_created'] = true;
            $debugInfo['is_configured'] = $cloudinaryService->isConfigured();
            
            if ($cloudinaryService->isConfigured()) {
                $cloudinaryStatus = 'configured';
            } else {
                $cloudinaryStatus = 'not_configured';
                // Try to manually check what went wrong
                if (empty($cloudName) || empty($apiKey) || empty($apiSecret)) {
                    $cloudinaryError = 'Environment variables are empty';
                } else {
                    $cloudinaryError = 'Service initialization failed - check logs for details';
                }
            }
        } catch (\Exception $e) {
            $cloudinaryStatus = 'error';
            $cloudinaryError = $e->getMessage();
            $debugInfo['exception'] = [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ];
        } catch (\Error $e) {
            $cloudinaryStatus = 'error';
            $cloudinaryError = $e->getMessage();
            $debugInfo['error'] = [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ];
        }
        
        // Return status
        return $this->response->setJSON([
            'cloudinary_status' => $cloudinaryStatus,
            'cloudinary_error' => $cloudinaryError,
            'debug_info' => $debugInfo,
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
    
    /**
     * Test Cloudinary upload with a sample file
     * This helps diagnose upload issues
     */
    public function testCloudinaryUpload()
    {
        // Check if Cloudinary is configured
        $cloudinaryService = new \App\Services\CloudinaryService();
        
        if (!$cloudinaryService->isConfigured()) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Cloudinary is not configured',
                'details' => 'Check /test/cloudinary-status for configuration details'
            ]);
        }
        
        // Try to create a test image and upload it
        try {
            // Create a simple test image (1x1 pixel PNG)
            $testImagePath = sys_get_temp_dir() . '/cloudinary_test_' . time() . '.png';
            $image = imagecreatetruecolor(1, 1);
            imagepng($image, $testImagePath);
            imagedestroy($image);
            
            // Upload test image
            $publicId = 'test_' . time();
            $result = $cloudinaryService->upload($testImagePath, 'profile', $publicId);
            
            // Clean up test file
            @unlink($testImagePath);
            
            if ($result && isset($result['url'])) {
                // Delete the test image from Cloudinary
                $cloudinaryService->delete('profile/' . $publicId);
                
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Cloudinary upload test successful',
                    'uploaded_url' => $result['url'],
                    'public_id' => $result['public_id'] ?? null
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'error' => 'Upload returned invalid result',
                    'result' => $result
                ]);
            }
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Exception during upload test: ' . $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}

