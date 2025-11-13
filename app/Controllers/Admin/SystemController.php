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

            // Create a temporary directory
            $tempPath = WRITEPATH . 'temp' . DIRECTORY_SEPARATOR;
            if (!is_dir($tempPath)) {
                if (!@mkdir($tempPath, 0755, true)) {
                    return $this->response->setJSON([
                        'success' => false,
                        'error' => 'Failed to create temporary directory. Please check file permissions.'
                    ])->setStatusCode(500);
                }
            }
            
            // Ensure directory is writable
            if (!is_writable($tempPath)) {
                @chmod($tempPath, 0755);
                if (!is_writable($tempPath)) {
                    return $this->response->setJSON([
                        'success' => false,
                        'error' => 'Temporary directory is not writable. Please check file permissions.'
                    ])->setStatusCode(500);
                }
            }
            
            $timestamp = date('Y-m-d_H-i-s');
            $outputFilename = 'clearpay_logs_' . $timestamp;
            $outputFilepath = null;
            
            // Try ZipArchive first (preferred method)
            if (class_exists('ZipArchive')) {
                $zipFilepath = $tempPath . $outputFilename . '.zip';
                $zip = new ZipArchive();
                $zipResult = $zip->open($zipFilepath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
                
                if ($zipResult === true) {
                    // Add log files to zip
                    foreach ($logFiles as $logFile) {
                        if (file_exists($logFile) && is_readable($logFile)) {
                            $zip->addFile($logFile, basename($logFile));
                        }
                    }
                    
                    $zip->close();
                    
                    // Verify zip file was created
                    if (file_exists($zipFilepath) && is_readable($zipFilepath) && filesize($zipFilepath) > 0) {
                        $outputFilepath = $zipFilepath;
                    }
                }
            }
            
            // Fallback 1: Try shell zip command (Linux/Unix)
            if (!$outputFilepath && function_exists('exec')) {
                $zipFilepath = $tempPath . $outputFilename . '.zip';
                $zipCommand = 'cd ' . escapeshellarg($logsPath) . ' && zip -q ' . escapeshellarg($zipFilepath) . ' *.log 2>&1';
                exec($zipCommand, $output, $returnVar);
                
                if ($returnVar === 0 && file_exists($zipFilepath) && filesize($zipFilepath) > 0) {
                    $outputFilepath = $zipFilepath;
                }
            }
            
            // Fallback 2: Try tar.gz (common on Linux)
            if (!$outputFilepath && function_exists('exec')) {
                $tarFilepath = $tempPath . $outputFilename . '.tar.gz';
                $tarCommand = 'cd ' . escapeshellarg($logsPath) . ' && tar -czf ' . escapeshellarg($tarFilepath) . ' *.log 2>&1';
                exec($tarCommand, $output, $returnVar);
                
                if ($returnVar === 0 && file_exists($tarFilepath) && filesize($tarFilepath) > 0) {
                    $outputFilepath = $tarFilepath;
                }
            }
            
            // Fallback 3: Create a simple concatenated text file
            if (!$outputFilepath) {
                $txtFilepath = $tempPath . $outputFilename . '.txt';
                $content = "-- ClearPay System Logs\n";
                $content .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
                $content .= "-- Total Files: " . count($logFiles) . "\n\n";
                $content .= str_repeat('=', 80) . "\n\n";
                
                foreach ($logFiles as $logFile) {
                    if (file_exists($logFile) && is_readable($logFile)) {
                        $content .= "\n" . str_repeat('=', 80) . "\n";
                        $content .= "FILE: " . basename($logFile) . "\n";
                        $content .= str_repeat('=', 80) . "\n\n";
                        $content .= file_get_contents($logFile);
                        $content .= "\n\n";
                    }
                }
                
                if (file_put_contents($txtFilepath, $content) !== false && filesize($txtFilepath) > 0) {
                    $outputFilepath = $txtFilepath;
                }
            }
            
            // If all methods failed
            if (!$outputFilepath) {
                return $this->response->setJSON([
                    'success' => false,
                    'error' => 'Failed to create archive. ZipArchive extension is not available and shell commands are not accessible.'
                ])->setStatusCode(500);
            }

            // Download the file
            return $this->response->download($outputFilepath, null);
            
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

