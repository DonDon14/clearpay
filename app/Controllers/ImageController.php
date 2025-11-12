<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class ImageController extends Controller
{
    /**
     * Serve images from uploads directory
     * This ensures images are accessible even when static file serving fails
     */
    public function serve($type, $filename)
    {
        // Validate type to prevent directory traversal
        $allowedTypes = ['profile', 'payment_proofs'];
        if (!in_array($type, $allowedTypes)) {
            return $this->response->setStatusCode(404)->setJSON([
                'error' => 'Invalid image type'
            ]);
        }
        
        // Sanitize filename to prevent directory traversal
        $filename = basename($filename);
        
        // Construct file path
        $filePath = FCPATH . 'uploads/' . $type . '/' . $filename;
        
        // Check if file exists
        if (!file_exists($filePath) || !is_file($filePath)) {
            // Return 404 with proper headers
            $this->response->setStatusCode(404);
            $this->response->setContentType('image/png');
            
            // Return a 1x1 transparent PNG as placeholder
            $transparent = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');
            return $this->response->setBody($transparent);
        }
        
        // Get file extension to set proper content type
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $contentTypes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp'
        ];
        
        $contentType = $contentTypes[$extension] ?? 'application/octet-stream';
        
        // Set headers
        $this->response->setContentType($contentType);
        $this->response->setHeader('Cache-Control', 'public, max-age=31536000');
        $this->response->setHeader('Expires', gmdate('D, d M Y H:i:s', time() + 31536000) . ' GMT');
        
        // Read and output file
        $fileContent = file_get_contents($filePath);
        return $this->response->setBody($fileContent);
    }
}

