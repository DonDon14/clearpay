<?php

namespace App\Services;

use Config\Database;

/**
 * Backup Service
 * Handles database backup operations following best practices
 */
class BackupService
{
    protected $db;
    protected $backupPath;
    protected $maxBackups = 10; // Keep last 10 backups

    public function __construct()
    {
        $this->db = Database::connect();
        
        // Set backup directory to user's Documents folder
        // For Windows: C:\Users\User\Documents\clearpaybackups
        $backupDir = 'C:' . DIRECTORY_SEPARATOR . 'Users' . DIRECTORY_SEPARATOR . 'User' . DIRECTORY_SEPARATOR . 'Documents' . DIRECTORY_SEPARATOR . 'clearpaybackups';
        
        // Fallback to writable/backups if the specified directory doesn't exist or can't be created
        if (!is_dir($backupDir)) {
            // Try to create the directory
            if (!@mkdir($backupDir, 0755, true)) {
                // If creation fails, fallback to writable/backups
                $backupDir = WRITEPATH . 'backups' . DIRECTORY_SEPARATOR;
                log_message('warning', 'Could not create backup directory in Documents folder, using writable/backups instead');
            }
        }
        
        $this->backupPath = $backupDir . DIRECTORY_SEPARATOR;
        
        // Ensure backup directory exists
        if (!is_dir($this->backupPath)) {
            mkdir($this->backupPath, 0755, true);
        }
    }

    /**
     * Create a database backup
     * 
     * @return array Result with success status, file path, and filename
     */
    public function createBackup(): array
    {
        try {
            $dbConfig = config('Database');
            $defaultGroup = $dbConfig->defaultGroup;
            $dbSettings = $dbConfig->{$defaultGroup};

            // Generate backup filename with timestamp
            $timestamp = date('Y-m-d_H-i-s');
            $filename = "clearpay_backup_{$timestamp}.sql";
            $filepath = $this->backupPath . $filename;

            // Get database credentials
            $hostname = $dbSettings['hostname'] ?? 'localhost';
            $username = $dbSettings['username'] ?? 'root';
            $password = $dbSettings['password'] ?? '';
            $database = $dbSettings['database'] ?? 'clearpaydb';
            $port = $dbSettings['port'] ?? 3306;

            // Try to use mysqldump (preferred method)
            $mysqldumpPath = $this->findMysqldump();
            
            if ($mysqldumpPath && $this->isWindows()) {
                // Windows: Use mysqldump.exe
                $command = sprintf(
                    '"%s" --host=%s --port=%d --user=%s %s %s > "%s"',
                    $mysqldumpPath,
                    escapeshellarg($hostname),
                    $port,
                    escapeshellarg($username),
                    !empty($password) ? '--password=' . escapeshellarg($password) : '',
                    escapeshellarg($database),
                    $filepath
                );
                
                // Execute command
                exec($command . ' 2>&1', $output, $returnVar);
                
                if ($returnVar !== 0 || !file_exists($filepath) || filesize($filepath) === 0) {
                    throw new \Exception('mysqldump failed: ' . implode("\n", $output));
                }
            } else {
                // Fallback: Use CodeIgniter's database utilities
                $this->createBackupUsingCI($filepath, $database);
            }

            // Compress the backup file
            $compressedFile = $this->compressBackup($filepath);
            
            // Delete uncompressed file
            if (file_exists($filepath)) {
                unlink($filepath);
            }

            // Clean up old backups
            $this->cleanupOldBackups();

            // Log the backup creation
            log_message('info', "Database backup created: {$compressedFile}");

            return [
                'success' => true,
                'filename' => basename($compressedFile),
                'filepath' => $compressedFile,
                'size' => filesize($compressedFile),
                'size_formatted' => $this->formatBytes(filesize($compressedFile)),
                'created_at' => date('Y-m-d H:i:s'),
            ];
        } catch (\Exception $e) {
            log_message('error', 'Backup creation failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Create backup using CodeIgniter's database utilities (fallback method)
     */
    protected function createBackupUsingCI(string $filepath, string $database): void
    {
        $db = Database::connect();
        
        // Get all tables
        $tables = $db->listTables();
        
        if (empty($tables)) {
            throw new \Exception('No tables found in database');
        }

        $output = "-- ClearPay Database Backup\n";
        $output .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
        $output .= "-- Database: {$database}\n\n";
        $output .= "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
        $output .= "SET time_zone = \"+00:00\";\n\n";
        $output .= "/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;\n";
        $output .= "/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;\n";
        $output .= "/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;\n";
        $output .= "/*!40101 SET NAMES utf8mb4 */;\n\n";

        // Backup each table
        foreach ($tables as $table) {
            $output .= "\n-- Table structure for table `{$table}`\n\n";
            $output .= "DROP TABLE IF EXISTS `{$table}`;\n";
            
            // Get table structure
            $createTable = $db->query("SHOW CREATE TABLE `{$table}`")->getRowArray();
            if (isset($createTable['Create Table'])) {
                $output .= $createTable['Create Table'] . ";\n\n";
            }

            // Get table data
            $rows = $db->query("SELECT * FROM `{$table}`")->getResultArray();
            
            if (!empty($rows)) {
                $output .= "-- Dumping data for table `{$table}`\n\n";
                
                foreach ($rows as $row) {
                    $values = [];
                    foreach ($row as $key => $value) {
                        if ($value === null) {
                            $values[] = 'NULL';
                        } else {
                            $values[] = "'" . $db->escapeString($value) . "'";
                        }
                    }
                    $output .= "INSERT INTO `{$table}` VALUES (" . implode(', ', $values) . ");\n";
                }
                $output .= "\n";
            }
        }

        $output .= "\n/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;\n";
        $output .= "/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;\n";
        $output .= "/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;\n";

        // Write to file
        file_put_contents($filepath, $output);
        
        if (!file_exists($filepath) || filesize($filepath) === 0) {
            throw new \Exception('Failed to create backup file');
        }
    }

    /**
     * Compress backup file using gzip
     */
    protected function compressBackup(string $filepath): string
    {
        $compressedFile = $filepath . '.gz';
        
        // Open source file
        $source = fopen($filepath, 'rb');
        if (!$source) {
            throw new \Exception('Cannot open source file for compression');
        }

        // Open destination file
        $dest = gzopen($compressedFile, 'wb9');
        if (!$dest) {
            fclose($source);
            throw new \Exception('Cannot create compressed file');
        }

        // Copy and compress
        while (!feof($source)) {
            gzwrite($dest, fread($source, 8192));
        }

        // Close files
        fclose($source);
        gzclose($dest);

        return $compressedFile;
    }

    /**
     * Clean up old backups, keeping only the most recent ones
     */
    protected function cleanupOldBackups(): void
    {
        $files = glob($this->backupPath . 'clearpay_backup_*.sql.gz');
        
        if (count($files) <= $this->maxBackups) {
            return;
        }

        // Sort by modification time (newest first)
        usort($files, function($a, $b) {
            return filemtime($b) - filemtime($a);
        });

        // Delete old backups
        $filesToDelete = array_slice($files, $this->maxBackups);
        foreach ($filesToDelete as $file) {
            if (file_exists($file)) {
                unlink($file);
                log_message('info', "Old backup deleted: {$file}");
            }
        }
    }

    /**
     * Find mysqldump executable path
     */
    protected function findMysqldump(): ?string
    {
        // Common paths for XAMPP on Windows
        $possiblePaths = [
            'C:\\xampp\\mysql\\bin\\mysqldump.exe',
            'C:\\Program Files\\xampp\\mysql\\bin\\mysqldump.exe',
            'C:\\xampp\\mysql\\bin\\mysqldump',
        ];

        foreach ($possiblePaths as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }

        // Try to find in PATH
        $which = $this->isWindows() ? 'where' : 'which';
        exec("{$which} mysqldump", $output, $returnVar);
        
        if ($returnVar === 0 && !empty($output[0])) {
            return trim($output[0]);
        }

        return null;
    }

    /**
     * Check if running on Windows
     */
    protected function isWindows(): bool
    {
        return strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
    }

    /**
     * Format bytes to human-readable format
     */
    protected function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    /**
     * Get list of available backups
     */
    public function getBackupList(): array
    {
        $files = glob($this->backupPath . 'clearpay_backup_*.sql.gz');
        $backups = [];

        foreach ($files as $file) {
            $backups[] = [
                'filename' => basename($file),
                'filepath' => $file,
                'size' => filesize($file),
                'size_formatted' => $this->formatBytes(filesize($file)),
                'created_at' => date('Y-m-d H:i:s', filemtime($file)),
            ];
        }

        // Sort by creation time (newest first)
        usort($backups, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });

        return $backups;
    }

    /**
     * Get backup file path by filename
     */
    public function getBackupPath(string $filename): ?string
    {
        $filepath = $this->backupPath . $filename;
        
        // Security: Only allow backup files
        if (!file_exists($filepath) || strpos($filename, 'clearpay_backup_') !== 0) {
            return null;
        }

        return $filepath;
    }
}

