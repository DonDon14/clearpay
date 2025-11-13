<?php

namespace App\Services;

use Cloudinary\Cloudinary;
use Cloudinary\Configuration\Configuration;
use Cloudinary\Api\Upload\UploadApi;

/**
 * Cloudinary Service
 * Handles file uploads to Cloudinary cloud storage
 * This solves the ephemeral filesystem issue on Render
 */
class CloudinaryService
{
    protected $cloudinary;
    protected $uploadApi;
    protected $isConfigured = false;
    
    public function __construct()
    {
        // Try multiple methods to read environment variables (same pattern as Email.php)
        $cloudName = $_ENV['CLOUDINARY_CLOUD_NAME'] ?? getenv('CLOUDINARY_CLOUD_NAME') ?: null;
        $apiKey = $_ENV['CLOUDINARY_API_KEY'] ?? getenv('CLOUDINARY_API_KEY') ?: null;
        $apiSecret = $_ENV['CLOUDINARY_API_SECRET'] ?? getenv('CLOUDINARY_API_SECRET') ?: null;
        
        // Trim whitespace
        $cloudName = $cloudName ? trim($cloudName) : null;
        $apiKey = $apiKey ? trim($apiKey) : null;
        $apiSecret = $apiSecret ? trim($apiSecret) : null;
        
        // Debug logging to see what we're getting (use error level so it shows in Render logs)
        log_message('error', 'Cloudinary init - CloudName: ' . ($cloudName ? 'SET (' . strlen($cloudName) . ' chars): [' . $cloudName . ']' : 'NOT SET'));
        log_message('error', 'Cloudinary init - APIKey: ' . ($apiKey ? 'SET (' . strlen($apiKey) . ' chars): [' . substr($apiKey, 0, 5) . '...]' : 'NOT SET'));
        log_message('error', 'Cloudinary init - APISecret: ' . ($apiSecret ? 'SET (' . strlen($apiSecret) . ' chars): [' . substr($apiSecret, 0, 5) . '...]' : 'NOT SET'));
        
        // Check if values are empty (after trimming)
        if (empty($cloudName) || empty($apiKey) || empty($apiSecret)) {
            log_message('error', 'Cloudinary credentials not configured. Missing: ' . 
                (empty($cloudName) ? 'CLOUDINARY_CLOUD_NAME ' : '') .
                (empty($apiKey) ? 'CLOUDINARY_API_KEY ' : '') .
                (empty($apiSecret) ? 'CLOUDINARY_API_SECRET' : '') .
                '. Falling back to local storage.');
            $this->isConfigured = false;
            return;
        }
        
        try {
            // Use the simplest initialization method - pass config directly to constructor
            $this->cloudinary = new Cloudinary([
                'cloud' => [
                    'cloud_name' => $cloudName,
                    'api_key' => $apiKey,
                    'api_secret' => $apiSecret
                ],
                'url' => [
                    'secure' => true
                ]
            ]);
            
            $this->uploadApi = $this->cloudinary->uploadApi();
            $this->isConfigured = true;
            
            log_message('info', 'Cloudinary service initialized successfully');
        } catch (\Exception $e) {
            log_message('error', 'Failed to initialize Cloudinary: ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());
            $this->isConfigured = false;
        } catch (\Error $e) {
            log_message('error', 'Failed to initialize Cloudinary (Error): ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());
            $this->isConfigured = false;
        }
    }
    
    /**
     * Check if Cloudinary is configured and available
     * 
     * @return bool
     */
    public function isConfigured()
    {
        return $this->isConfigured;
    }
    
    /**
     * Upload a file to Cloudinary
     * 
     * @param string $filePath Local file path or file content
     * @param string $folder Cloudinary folder (e.g., 'profile', 'payment_proofs')
     * @param string|null $publicId Optional public ID (if null, Cloudinary generates one)
     * @param array $options Additional upload options
     * @return array|false Returns array with 'url' and 'public_id' on success, false on failure
     */
    public function upload($filePath, $folder = 'profile', $publicId = null, $options = [])
    {
        if (!$this->isConfigured) {
            log_message('error', 'Cloudinary upload attempted but service is not configured');
            return false;
        }
        
        try {
            $uploadOptions = array_merge([
                'folder' => $folder,
                'resource_type' => 'image',
                'transformation' => [
                    ['width' => 400, 'height' => 400, 'crop' => 'fill', 'gravity' => 'face', 'quality' => 'auto']
                ],
                'overwrite' => true,
            ], $options);
            
            // If public_id is provided, add it to options
            if ($publicId !== null) {
                $uploadOptions['public_id'] = $publicId;
            }
            
            // Upload the file
            $result = $this->uploadApi->upload($filePath, $uploadOptions);
            
            log_message('info', 'File uploaded to Cloudinary successfully: ' . $result['secure_url']);
            
            return [
                'url' => $result['secure_url'],
                'public_id' => $result['public_id'],
                'format' => $result['format'] ?? null,
                'width' => $result['width'] ?? null,
                'height' => $result['height'] ?? null,
                'bytes' => $result['bytes'] ?? null,
            ];
        } catch (\Exception $e) {
            log_message('error', 'Cloudinary upload failed: ' . $e->getMessage());
            log_message('error', 'Cloudinary upload error trace: ' . $e->getTraceAsString());
            return false;
        }
    }
    
    /**
     * Upload file from CodeIgniter UploadedFile object
     * 
     * @param \CodeIgniter\HTTP\Files\UploadedFile $file
     * @param string $folder Cloudinary folder
     * @param string|null $publicId Optional public ID
     * @return array|false
     */
    public function uploadFile($file, $folder = 'profile', $publicId = null)
    {
        if (!$this->isConfigured) {
            log_message('error', 'Cloudinary uploadFile called but service is not configured');
            return false;
        }
        
        // Get the temporary file path
        $tempPath = $file->getTempName();
        
        log_message('error', 'Cloudinary uploadFile - Temp path: ' . $tempPath);
        log_message('error', 'Cloudinary uploadFile - File exists: ' . (file_exists($tempPath) ? 'YES' : 'NO'));
        log_message('error', 'Cloudinary uploadFile - File size: ' . (file_exists($tempPath) ? filesize($tempPath) : 'N/A'));
        
        if (!file_exists($tempPath)) {
            log_message('error', 'Cloudinary upload: Temporary file not found: ' . $tempPath);
            return false;
        }
        
        $uploadResult = $this->upload($tempPath, $folder, $publicId);
        log_message('error', 'Cloudinary uploadFile - upload() returned: ' . gettype($uploadResult));
        return $uploadResult;
    }
    
    /**
     * Delete a file from Cloudinary
     * 
     * @param string $publicId The public ID of the file to delete
     * @return bool
     */
    public function delete($publicId)
    {
        if (!$this->isConfigured) {
            return false;
        }
        
        try {
            $result = $this->uploadApi->destroy($publicId);
            
            if (isset($result['result']) && $result['result'] === 'ok') {
                log_message('info', 'File deleted from Cloudinary: ' . $publicId);
                return true;
            } else {
                log_message('warning', 'Cloudinary delete returned unexpected result: ' . json_encode($result));
                return false;
            }
        } catch (\Exception $e) {
            log_message('error', 'Cloudinary delete failed: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Extract public_id from Cloudinary URL
     * 
     * @param string $url Cloudinary URL
     * @return string|null Public ID or null if not a Cloudinary URL
     */
    public function extractPublicId($url)
    {
        // Cloudinary URLs format: https://res.cloudinary.com/{cloud_name}/image/upload/{folder}/{public_id}.{ext}
        if (preg_match('/res\.cloudinary\.com\/[^\/]+\/image\/upload\/(?:v\d+\/)?(?:.+\/)?(.+?)(?:\.[^.]+)?$/', $url, $matches)) {
            return $matches[1];
        }
        
        return null;
    }
    
    /**
     * Check if a URL is a Cloudinary URL
     * 
     * @param string $url
     * @return bool
     */
    public function isCloudinaryUrl($url)
    {
        return strpos($url, 'res.cloudinary.com') !== false;
    }
}

