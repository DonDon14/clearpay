<?php

namespace App\Controllers;

use CodeIgniter\Controller;

/**
 * Image Controller
 * Serves images with proper CORS headers for Flutter Web compatibility
 */
class ImageController extends Controller
{
    /**
     * Serve image files with CORS headers
     * This allows Flutter Web apps to load images from different origins
     * 
     * Handles routes like:
     * - /uploads/profile/filename.png -> $subfolder='profile', $filename='filename.png'
     * - /uploads/logo.png -> $subfolder='logo.png', $filename=''
     * 
     * @param string $subfolder The subfolder (profile, payment_proofs, etc.) or full path like 'logo.png'
     * @param string $filename The filename (empty for logo.png)
     * @return \CodeIgniter\HTTP\Response
     */
    public function serve($subfolder = '', $filename = '')
    {
        // Log request for debugging (use error level so it shows in Render logs)
        log_message('error', 'ImageController::serve called - Subfolder: [' . $subfolder . '], Filename: [' . $filename . ']');
        log_message('error', 'ImageController::serve - Request URI: ' . $this->request->getUri()->getPath());
        log_message('error', 'ImageController::serve - Request Method: ' . $this->request->getMethod());
        log_message('error', 'ImageController::serve - Full URI: ' . (string)$this->request->getUri());
        
        // Set CORS headers for image requests (CRITICAL for Flutter Web)
        $this->response->setHeader('Access-Control-Allow-Origin', '*');
        $this->response->setHeader('Access-Control-Allow-Methods', 'GET, OPTIONS, HEAD');
        $this->response->setHeader('Access-Control-Allow-Headers', 'Content-Type, Accept, Authorization, X-Requested-With, Origin');
        $this->response->setHeader('Access-Control-Max-Age', '86400');
        
        // Handle OPTIONS preflight request
        if ($this->request->getMethod() === 'OPTIONS') {
            log_message('info', 'ImageController::serve - Handling OPTIONS preflight');
            return $this->response->setStatusCode(200)->setBody('');
        }
        
        $filePath = null;
        
        // Handle logo requests: /uploads/logo.png -> $subfolder='logo.png', $filename=''
        if (preg_match('/^logo\.(png|jpg|jpeg|svg|ico)$/i', $subfolder)) {
            $extension = pathinfo($subfolder, PATHINFO_EXTENSION);
            $filePath = FCPATH . 'uploads' . DIRECTORY_SEPARATOR . 'logo.' . $extension;
        } elseif ($filename) {
            // Handle two-segment routes: /uploads/profile/filename.png
            // Route: /uploads/profile/filename.png -> $subfolder='profile', $filename='filename.png'
            $allowedSubfolders = ['profile', 'payment_proofs', 'payment_methods', 'qr_receipts'];
            if (!in_array($subfolder, $allowedSubfolders)) {
                return $this->response->setStatusCode(403)->setBody('Invalid subfolder: ' . $subfolder);
            }
            
            // Sanitize filename to prevent directory traversal
            $filename = basename($filename);
            $filename = str_replace(['../', '..\\', '/..', '\\..'], '', $filename);
            
            // Construct full file path
            $filePath = FCPATH . 'uploads' . DIRECTORY_SEPARATOR . $subfolder . DIRECTORY_SEPARATOR . $filename;
        } else {
            // Single segment - invalid format, need either logo.png or subfolder/filename
            return $this->response->setStatusCode(400)->setBody('Invalid image path format. Expected: /uploads/subfolder/filename or /uploads/logo.png');
        }
        
        // Security: Ensure file is within uploads directory
        $realUploadsPath = realpath(FCPATH . 'uploads');
        $realFilePath = realpath($filePath);
        
        if (!$realFilePath || strpos($realFilePath, $realUploadsPath) !== 0) {
            return $this->response->setStatusCode(404)->setBody('Image not found');
        }
        
        // Check if file exists
        if (!file_exists($filePath) || !is_file($filePath)) {
            return $this->response->setStatusCode(404)->setBody('Image not found');
        }
        
        // Get file mime type
        $mimeType = mime_content_type($filePath);
        if (!$mimeType) {
            // Fallback based on extension
            $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            $mimeTypes = [
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'png' => 'image/png',
                'gif' => 'image/gif',
                'webp' => 'image/webp',
                'svg' => 'image/svg+xml',
                'ico' => 'image/x-icon',
            ];
            $mimeType = $mimeTypes[$extension] ?? 'application/octet-stream';
        }
        
        // Set content type
        $this->response->setContentType($mimeType);
        
        // Set cache headers
        $this->response->setHeader('Cache-Control', 'public, max-age=31536000');
        $this->response->setHeader('Expires', gmdate('D, d M Y H:i:s', time() + 31536000) . ' GMT');
        
        // Read and output file
        $fileContent = file_get_contents($filePath);
        
        return $this->response->setBody($fileContent);
    }
}
