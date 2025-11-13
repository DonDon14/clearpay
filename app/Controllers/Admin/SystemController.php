<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\BackupService;
use Config\SystemConfig;
use ZipArchive;

class SystemController extends BaseController
{
    protected $systemConfig;
    protected $uptimeFile;

    public function __construct()
    {
        $this->systemConfig = new SystemConfig();
        $this->uptimeFile = WRITEPATH . 'system_uptime.txt';
    }

    /**
     * Initialize uptime tracking
     */
    protected function initializeUptime()
    {
        file_put_contents($this->uptimeFile, date('Y-m-d H:i:s'));
    }

    /**
     * Get system information
     * 
     * @param bool $requireAuth Whether to require authentication
     * @param bool $returnArray If true, returns array instead of response object (for internal calls)
     * @return array|\CodeIgniter\HTTP\ResponseInterface
     */
    public function getSystemInfo($requireAuth = true, $returnArray = false)
    {
        if ($requireAuth && !session()->get('isLoggedIn')) {
            $error = [
                'success' => false,
                'error' => 'Unauthorized. Please login first.'
            ];
            
            if ($returnArray) {
                return $error;
            }
            
            return $this->response->setJSON($error)->setStatusCode(401);
        }

        try {
            // Initialize uptime tracking if file doesn't exist
            if (!file_exists($this->uptimeFile)) {
                $this->initializeUptime();
            }
            // Get system version from config
            $version = $this->systemConfig->version;
            
            // Auto-detect PHP version
            $phpVersion = phpversion();
            
            // Get framework version
            $framework = 'CodeIgniter ' . \CodeIgniter\CodeIgniter::CI_VERSION;
            
            // Get database info
            $db = \Config\Database::connect();
            $dbDriver = $db->getPlatform();
            $database = $dbDriver ?? 'MySQL';
            
            // Get last backup time
            $lastBackup = $this->getLastBackupTime();
            
            // Calculate uptime
            $uptime = $this->calculateUptime();
            
            $result = [
                'success' => true,
                'system_info' => [
                    'version' => $version,
                    'php_version' => $phpVersion,
                    'framework' => $framework,
                    'database' => $database,
                    'last_backup' => $lastBackup,
                    'uptime' => $uptime,
                    'status' => 'online'
                ]
            ];
            
            if ($returnArray) {
                return $result;
            }
            
            return $this->response->setJSON($result);
        } catch (\Exception $e) {
            log_message('error', 'System info error: ' . $e->getMessage());
            $error = [
                'success' => false,
                'error' => 'An error occurred while retrieving system information.'
            ];
            
            if ($returnArray) {
                return $error;
            }
            
            return $this->response->setJSON($error)->setStatusCode(500);
        }
    }

    /**
     * Get last backup time
     */
    protected function getLastBackupTime()
    {
        try {
            $backupService = new BackupService();
            $backups = $backupService->getBackupList();
            
            if (!empty($backups)) {
                // Get the most recent backup (first in list)
                $latestBackup = $backups[0];
                // Extract timestamp from filename or use file modification time
                $backupPath = $backupService->getBackupPath($latestBackup['filename']);
                if (file_exists($backupPath)) {
                    $fileTime = filemtime($backupPath);
                    return date('M j, Y g:i A', $fileTime);
                }
            }
            
            return 'No backups yet';
        } catch (\Exception $e) {
            return 'Unknown';
        }
    }

    /**
     * Calculate system uptime
     * Best practice: Track from system start time stored in file
     */
    protected function calculateUptime()
    {
        try {
            if (!file_exists($this->uptimeFile)) {
                $this->initializeUptime();
            }
            
            $startTime = file_get_contents($this->uptimeFile);
            $startTimestamp = strtotime($startTime);
            $currentTimestamp = time();
            $uptimeSeconds = $currentTimestamp - $startTimestamp;
            
            // Calculate days, hours, minutes
            $days = floor($uptimeSeconds / 86400);
            $hours = floor(($uptimeSeconds % 86400) / 3600);
            $minutes = floor(($uptimeSeconds % 3600) / 60);
            
            $uptimeParts = [];
            if ($days > 0) {
                $uptimeParts[] = $days . ' day' . ($days > 1 ? 's' : '');
            }
            if ($hours > 0) {
                $uptimeParts[] = $hours . ' hour' . ($hours > 1 ? 's' : '');
            }
            if ($minutes > 0 && $days == 0) {
                $uptimeParts[] = $minutes . ' minute' . ($minutes > 1 ? 's' : '');
            }
            
            if (empty($uptimeParts)) {
                return 'Less than a minute';
            }
            
            return implode(', ', $uptimeParts);
        } catch (\Exception $e) {
            log_message('error', 'Uptime calculation error: ' . $e->getMessage());
            return 'Unknown';
        }
    }

    /**
     * Download system logs
     */
    public function downloadLogs()
    {
        if (!session()->get('isLoggedIn')) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Unauthorized. Please login first.'
            ])->setStatusCode(401);
        }

        try {
            // Check if ZipArchive extension is available
            if (!class_exists('ZipArchive')) {
                return $this->response->setJSON([
                    'success' => false,
                    'error' => 'ZipArchive extension is not available. Please contact your server administrator.'
                ])->setStatusCode(500);
            }
            
            $logsPath = WRITEPATH . 'logs' . DIRECTORY_SEPARATOR;
            
            if (!is_dir($logsPath)) {
                return $this->response->setJSON([
                    'success' => false,
                    'error' => 'Logs directory not found.'
                ])->setStatusCode(404);
            }

            // Get all log files
            $logFiles = glob($logsPath . '*.log');
            
            if (empty($logFiles)) {
                return $this->response->setJSON([
                    'success' => false,
                    'error' => 'No log files found.'
                ])->setStatusCode(404);
            }

            // Create a temporary zip file
            $zipPath = WRITEPATH . 'temp' . DIRECTORY_SEPARATOR;
            if (!is_dir($zipPath)) {
                if (!@mkdir($zipPath, 0755, true)) {
                    return $this->response->setJSON([
                        'success' => false,
                        'error' => 'Failed to create temporary directory. Please check file permissions.'
                    ])->setStatusCode(500);
                }
            }
            
            // Ensure directory is writable
            if (!is_writable($zipPath)) {
                @chmod($zipPath, 0755);
                if (!is_writable($zipPath)) {
                    return $this->response->setJSON([
                        'success' => false,
                        'error' => 'Temporary directory is not writable. Please check file permissions.'
                    ])->setStatusCode(500);
                }
            }
            
            $zipFilename = 'clearpay_logs_' . date('Y-m-d_H-i-s') . '.zip';
            $zipFilepath = $zipPath . $zipFilename;

            // Create zip archive
            $zip = new ZipArchive();
            $zipResult = $zip->open($zipFilepath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
            
            if ($zipResult !== true) {
                $errorMessages = [
                    ZipArchive::ER_OK => 'No error',
                    ZipArchive::ER_MULTIDISK => 'Multi-disk zip archives not supported',
                    ZipArchive::ER_RENAME => 'Renaming temporary file failed',
                    ZipArchive::ER_CLOSE => 'Closing zip archive failed',
                    ZipArchive::ER_SEEK => 'Seek error',
                    ZipArchive::ER_READ => 'Read error',
                    ZipArchive::ER_WRITE => 'Write error',
                    ZipArchive::ER_CRC => 'CRC error',
                    ZipArchive::ER_ZIPCLOSED => 'Containing zip archive was closed',
                    ZipArchive::ER_NOENT => 'No such file',
                    ZipArchive::ER_EXISTS => 'File already exists',
                    ZipArchive::ER_OPEN => 'Can\'t open file',
                    ZipArchive::ER_TMPOPEN => 'Failure to create temporary file',
                    ZipArchive::ER_ZLIB => 'Zlib error',
                    ZipArchive::ER_MEMORY => 'Memory allocation failure',
                    ZipArchive::ER_CHANGED => 'Entry has been changed',
                    ZipArchive::ER_COMPNOTSUPP => 'Compression method not supported',
                    ZipArchive::ER_EOF => 'Premature EOF',
                    ZipArchive::ER_INVAL => 'Invalid argument',
                    ZipArchive::ER_NOZIP => 'Not a zip archive',
                    ZipArchive::ER_INTERNAL => 'Internal error',
                    ZipArchive::ER_INCONS => 'Zip archive inconsistent',
                    ZipArchive::ER_REMOVE => 'Can\'t remove file',
                    ZipArchive::ER_DELETED => 'Entry has been deleted',
                ];
                
                $errorMsg = $errorMessages[$zipResult] ?? 'Unknown error (code: ' . $zipResult . ')';
                
                return $this->response->setJSON([
                    'success' => false,
                    'error' => 'Failed to create zip archive: ' . $errorMsg
                ])->setStatusCode(500);
            }

            // Add log files to zip
            foreach ($logFiles as $logFile) {
                if (file_exists($logFile) && is_readable($logFile)) {
                    $zip->addFile($logFile, basename($logFile));
                }
            }

            $zip->close();

            // Verify zip file was created and is readable
            if (!file_exists($zipFilepath) || !is_readable($zipFilepath)) {
                return $this->response->setJSON([
                    'success' => false,
                    'error' => 'Failed to create zip file or file is not readable.'
                ])->setStatusCode(500);
            }
            
            // Check file size
            if (filesize($zipFilepath) === 0) {
                @unlink($zipFilepath);
                return $this->response->setJSON([
                    'success' => false,
                    'error' => 'Created zip file is empty.'
                ])->setStatusCode(500);
            }

            // Download the zip file
            return $this->response->download($zipFilepath, null);
            
        } catch (\Exception $e) {
            log_message('error', 'Logs download error: ' . $e->getMessage());
            log_message('error', 'Logs download trace: ' . $e->getTraceAsString());
            return $this->response->setJSON([
                'success' => false,
                'error' => 'An error occurred while downloading logs: ' . $e->getMessage()
            ])->setStatusCode(500);
        }
    }

    /**
     * Clear system cache
     */
    public function clearCache()
    {
        if (!session()->get('isLoggedIn')) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Unauthorized. Please login first.'
            ])->setStatusCode(401);
        }

        try {
            $cachePath = WRITEPATH . 'cache' . DIRECTORY_SEPARATOR;
            $clearedCount = 0;
            $errors = [];

            if (!is_dir($cachePath)) {
                return $this->response->setJSON([
                    'success' => false,
                    'error' => 'Cache directory not found.'
                ])->setStatusCode(404);
            }

            // Get all cache files (excluding index.html)
            $cacheFiles = glob($cachePath . '*');
            
            foreach ($cacheFiles as $file) {
                if (is_file($file) && basename($file) !== 'index.html') {
                    if (@unlink($file)) {
                        $clearedCount++;
                    } else {
                        $errors[] = basename($file);
                    }
                } elseif (is_dir($file) && basename($file) !== '.') {
                    // Recursively delete directory contents
                    $this->deleteDirectory($file);
                    $clearedCount++;
                }
            }

            // Also clear CodeIgniter's cache using the cache service
            $cache = \Config\Services::cache();
            $cache->clean();

            // Clear debugbar cache if exists
            $debugbarPath = WRITEPATH . 'debugbar' . DIRECTORY_SEPARATOR;
            if (is_dir($debugbarPath)) {
                $debugbarFiles = glob($debugbarPath . '*.json');
                foreach ($debugbarFiles as $file) {
                    if (is_file($file)) {
                        @unlink($file);
                        $clearedCount++;
                    }
                }
            }

            log_message('info', "Cache cleared: {$clearedCount} files/directories removed");

            return $this->response->setJSON([
                'success' => true,
                'message' => 'System cache cleared successfully!',
                'cleared_count' => $clearedCount,
                'errors' => $errors
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Cache clear error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'error' => 'An error occurred while clearing cache: ' . $e->getMessage()
            ])->setStatusCode(500);
        }
    }

    /**
     * Recursively delete directory
     */
    protected function deleteDirectory($dir)
    {
        if (!is_dir($dir)) {
            return false;
        }

        $files = array_diff(scandir($dir), ['.', '..']);
        
        foreach ($files as $file) {
            $filePath = $dir . DIRECTORY_SEPARATOR . $file;
            
            if (is_dir($filePath)) {
                $this->deleteDirectory($filePath);
            } else {
                @unlink($filePath);
            }
        }
        
        return @rmdir($dir);
    }

    /**
     * Update system version
     */
    public function updateVersion()
    {
        if (!session()->get('isLoggedIn')) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Unauthorized. Please login first.'
            ])->setStatusCode(401);
        }

        // Get version from POST or JSON body
        $data = $this->request->getPost();
        $jsonData = $this->request->getJSON(true) ?? [];
        $newVersion = $data['version'] ?? $jsonData['version'] ?? null;
        
        if (!$newVersion) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Version is required.'
            ])->setStatusCode(400);
        }

        // Validate version format (basic validation)
        if (strlen($newVersion) > 50) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Version string is too long (max 50 characters).'
            ])->setStatusCode(400);
        }

        try {
            // Update version in config file
            $configFile = APPPATH . 'Config' . DIRECTORY_SEPARATOR . 'SystemConfig.php';
            
            if (!file_exists($configFile)) {
                return $this->response->setJSON([
                    'success' => false,
                    'error' => 'System configuration file not found.'
                ])->setStatusCode(500);
            }
            
            $configContent = file_get_contents($configFile);
            
            // Replace version line (handle both single and double quotes)
            $configContent = preg_replace(
                '/public string \$version = [\'"]([^\'"]+)[\'"];/',
                "public string \$version = '{$newVersion}';",
                $configContent
            );
            
            // Write back to file
            if (file_put_contents($configFile, $configContent) === false) {
                return $this->response->setJSON([
                    'success' => false,
                    'error' => 'Failed to write to configuration file. Please check file permissions.'
                ])->setStatusCode(500);
            }
            
            // Update the config instance
            $this->systemConfig->version = $newVersion;
            
            log_message('info', "System version updated to: {$newVersion}");
            
            return $this->response->setJSON([
                'success' => true,
                'message' => 'System version updated successfully!',
                'version' => $newVersion
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Version update error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'error' => 'An error occurred while updating version: ' . $e->getMessage()
            ])->setStatusCode(500);
        }
    }
}

