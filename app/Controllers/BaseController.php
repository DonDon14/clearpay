<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Class BaseController
 *
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 * Extend this class in any new controllers:
 *     class Home extends BaseController
 *
 * For security be sure to declare any new methods as protected or private.
 */
abstract class BaseController extends Controller
{
    /**
     * Instance of the main Request object.
     *
     * @var CLIRequest|IncomingRequest
     */
    protected $request;

    /**
     * An array of helpers to be loaded automatically upon
     * class instantiation. These helpers will be available
     * to all other controllers that extend BaseController.
     *
     * @var list<string>
     */
    protected $helpers = [];

    /**
     * Be sure to declare properties for any property fetch you initialized.
     * The creation of dynamic property is deprecated in PHP 8.2.
     */
    // protected $session;

    /**
     * @return void
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Do Not Edit This Line
        parent::initController($request, $response, $logger);

        // Preload any models, libraries, etc, here.

        // E.g.: $this->session = service('session');
    }

    /**
     * Normalize profile picture path with fallback to find similar files
     * This handles cases where database path doesn't match actual file
     * 
     * @param string|null $profilePicturePath The path from database
     * @param int|null $payerId The payer ID for fallback lookup (for payers)
     * @param int|null $userId The user ID for fallback lookup (for admin users)
     * @param string $type Either 'payer' or 'user' to determine lookup pattern
     * @return string|null Normalized relative path or null
     */
    protected function normalizeProfilePicturePath($profilePicturePath, $payerId = null, $userId = null, $type = 'payer')
    {
        if (empty($profilePicturePath)) {
            return null;
        }

        // If it's a Cloudinary URL, return it as-is (full URL)
        if (strpos($profilePicturePath, 'res.cloudinary.com') !== false) {
            return $profilePicturePath;
        }

        // If it's already a full URL (but not Cloudinary), return as-is
        if (strpos($profilePicturePath, 'http://') === 0 || strpos($profilePicturePath, 'https://') === 0) {
            return $profilePicturePath;
        }

        // Extract filename from path, handling various formats
        $path = $profilePicturePath;
        // Remove any base_url or http prefixes
        $path = preg_replace('#^https?://[^/]+/#', '', $path);
        $path = preg_replace('#^uploads/profile/#', '', $path);
        $path = preg_replace('#^profile/#', '', $path);
        $filename = basename($path);
        
        // Verify file exists before setting path
        $filePath = FCPATH . 'uploads/profile/' . $filename;
        if (file_exists($filePath)) {
            // Return relative path (views will apply base_url)
            return 'uploads/profile/' . $filename;
        }
        
        // Log detailed warning about missing file
        $isRender = getenv('RENDER') === 'true' || strpos($_SERVER['SERVER_NAME'] ?? '', 'render.com') !== false;
        if ($isRender) {
            log_message('error', 'Profile picture file missing on Render (ephemeral filesystem): ' . $filePath . ' | Database path: ' . $profilePicturePath . ' | This is expected after deployment - files are lost on redeploy. Consider implementing cloud storage.');
        } else {
            log_message('warning', 'Profile picture not found: ' . $filePath . ' | Database path: ' . $profilePicturePath);
        }
        
        // Try to find a similar file (fallback)
        $entityId = ($type === 'payer' && $payerId) ? $payerId : ($type === 'user' && $userId ? $userId : null);
        
        if ($entityId) {
            $uploadDir = FCPATH . 'uploads/profile/';
            $pattern = ($type === 'payer') ? 'payer_' . $entityId . '_*' : $entityId . '_*';
            $files = glob($uploadDir . $pattern);
            if (!empty($files)) {
                // Use the most recent file
                usort($files, function($a, $b) {
                    return filemtime($b) - filemtime($a);
                });
                $foundFile = basename($files[0]);
                log_message('info', 'Found fallback profile picture: ' . $foundFile . ' for ' . $type . ' ID: ' . $entityId);
                
                // Update database with correct path
                try {
                    if ($type === 'payer') {
                        $payerModel = new \App\Models\PayerModel();
                        $payerModel->update($entityId, ['profile_picture' => 'uploads/profile/' . $foundFile]);
                    } else {
                        $userModel = new \App\Models\UserModel();
                        $userModel->update($entityId, ['profile_picture' => 'uploads/profile/' . $foundFile]);
                    }
                    log_message('info', 'Updated database with correct profile picture path for ' . $type . ' ID: ' . $entityId);
                } catch (\Exception $e) {
                    log_message('error', 'Failed to update database with correct profile picture path: ' . $e->getMessage());
                }
                
                return 'uploads/profile/' . $foundFile;
            }
        }
        
        return null;
    }
}
