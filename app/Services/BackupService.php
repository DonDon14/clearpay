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
        
        // Determine backup directory based on environment
        // On Render/cloud: use writable/backups
        // On Windows localhost: try Documents folder first, then fallback to writable/backups
        $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
        $backupDir = null;
        
        if ($isWindows) {
            // Windows: Try user's Documents folder first
            $backupDir = 'C:' . DIRECTORY_SEPARATOR . 'Users' . DIRECTORY_SEPARATOR . 'User' . DIRECTORY_SEPARATOR . 'Documents' . DIRECTORY_SEPARATOR . 'clearpaybackups';
            
            // Check if directory exists or can be created
            if (!is_dir($backupDir)) {
                if (!@mkdir($backupDir, 0755, true)) {
                    $backupDir = null; // Will fallback to writable/backups
                }
            }
        }
        
        // Fallback to writable/backups (works on all platforms including Render)
        if (!$backupDir || !is_dir($backupDir) || !is_writable($backupDir)) {
            $backupDir = WRITEPATH . 'backups';
            if ($isWindows && $backupDir !== WRITEPATH . 'backups') {
                log_message('warning', 'Could not create backup directory in Documents folder, using writable/backups instead');
            }
        }
        
        $this->backupPath = rtrim($backupDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        
        // Ensure backup directory exists and is writable
        if (!is_dir($this->backupPath)) {
            if (!@mkdir($this->backupPath, 0755, true)) {
                throw new \Exception('Failed to create backup directory: ' . $this->backupPath);
            }
        }
        
        // Ensure directory is writable
        if (!is_writable($this->backupPath)) {
            @chmod($this->backupPath, 0755);
            if (!is_writable($this->backupPath)) {
                throw new \Exception('Backup directory is not writable: ' . $this->backupPath);
            }
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
            
            // Detect database driver to set correct default port and backup method
            $dbDriver = $dbSettings['DBDriver'] ?? 'MySQLi';
            $isPostgres = (strtolower($dbDriver) === 'postgre');
            $port = $dbSettings['port'] ?? ($isPostgres ? 5432 : 3306);
            
            // For PostgreSQL, always use CI method (no pg_dump on server)
            // For MySQL, try mysqldump first, then fallback to CI method
            $useMysqldump = !$isPostgres && $this->isWindows();
            $mysqldumpPath = $useMysqldump ? $this->findMysqldump() : null;
            
            if ($mysqldumpPath && file_exists($mysqldumpPath)) {
                // Windows: Try mysqldump.exe first
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
                
                // If mysqldump fails, fallback to CI method
                if ($returnVar !== 0 || !file_exists($filepath) || filesize($filepath) === 0) {
                    log_message('info', 'mysqldump failed, falling back to CI method: ' . implode("\n", $output));
                    // Fallback: Use CodeIgniter's database utilities
                    $this->createBackupUsingCI($filepath, $database);
                }
            } else {
                // Fallback: Use CodeIgniter's database utilities (works for both MySQL and PostgreSQL)
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
        
        // Detect database driver
        $dbConfig = config('Database');
        $defaultGroup = $dbConfig->defaultGroup;
        $dbSettings = $dbConfig->{$defaultGroup};
        $dbDriver = $dbSettings['DBDriver'] ?? 'MySQLi';
        $isPostgres = (strtolower($dbDriver) === 'postgre');
        
        // Get all tables
        $tables = $db->listTables();
        
        if (empty($tables)) {
            throw new \Exception('No tables found in database');
        }

        $output = "-- ClearPay Database Backup\n";
        $output .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
        $output .= "-- Database: {$database}\n";
        $output .= "-- Database Driver: {$dbDriver}\n\n";
        
        if (!$isPostgres) {
            // MySQL-specific headers
            $output .= "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
            $output .= "SET time_zone = \"+00:00\";\n\n";
            $output .= "/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;\n";
            $output .= "/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;\n";
            $output .= "/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;\n";
            $output .= "/*!40101 SET NAMES utf8mb4 */;\n\n";
        } else {
            // PostgreSQL-specific headers
            $output .= "-- PostgreSQL Backup\n";
            $output .= "BEGIN;\n\n";
        }

        // Backup each table
        foreach ($tables as $table) {
            $tableName = $isPostgres ? $table : "`{$table}`";
            $output .= "\n-- Table structure for table {$tableName}\n\n";
            $output .= "DROP TABLE IF EXISTS {$tableName};\n";
            
            // Get table structure - different syntax for MySQL vs PostgreSQL
            if ($isPostgres) {
                // PostgreSQL: Get table structure using information_schema
                try {
                    $columns = $db->query("
                        SELECT 
                            column_name, 
                            data_type, 
                            character_maximum_length,
                            numeric_precision,
                            numeric_scale,
                            is_nullable,
                            column_default
                        FROM information_schema.columns 
                        WHERE table_schema = 'public' AND table_name = '{$table}' 
                        ORDER BY ordinal_position
                    ")->getResultArray();
                    
                    if (!empty($columns)) {
                        $output .= "CREATE TABLE {$tableName} (\n";
                        $columnDefs = [];
                        foreach ($columns as $col) {
                            $def = "  " . $col['column_name'] . " " . strtoupper($col['data_type']);
                            
                            // Handle data type with length/precision
                            if ($col['character_maximum_length']) {
                                $def .= "(" . $col['character_maximum_length'] . ")";
                            } elseif ($col['numeric_precision'] && $col['numeric_scale']) {
                                $def .= "(" . $col['numeric_precision'] . "," . $col['numeric_scale'] . ")";
                            } elseif ($col['numeric_precision']) {
                                $def .= "(" . $col['numeric_precision'] . ")";
                            }
                            
                            if ($col['is_nullable'] === 'NO') {
                                $def .= " NOT NULL";
                            }
                            
                            if ($col['column_default']) {
                                $def .= " DEFAULT " . $col['column_default'];
                            }
                            
                            $columnDefs[] = $def;
                        }
                        $output .= implode(",\n", $columnDefs) . "\n);\n\n";
                    } else {
                        throw new \Exception("No columns found for table {$table}");
                    }
                } catch (\Exception $e) {
                    log_message('error', 'Failed to get PostgreSQL table structure: ' . $e->getMessage());
                    throw new \Exception('Failed to get table structure for ' . $table . ': ' . $e->getMessage());
                }
            } else {
                // MySQL: Use SHOW CREATE TABLE
                $createTable = $db->query("SHOW CREATE TABLE `{$table}`")->getRowArray();
                if (isset($createTable['Create Table'])) {
                    $output .= $createTable['Create Table'] . ";\n\n";
                }
            }

            // Get table data
            $tableNameQuery = $isPostgres ? $table : "`{$table}`";
            $rows = $db->query("SELECT * FROM {$tableNameQuery}")->getResultArray();
            
            if (!empty($rows)) {
                $output .= "-- Dumping data for table {$tableName}\n\n";
                
                foreach ($rows as $row) {
                    $values = [];
                    foreach ($row as $key => $value) {
                        if ($value === null) {
                            $values[] = 'NULL';
                        } else {
                            $escaped = $db->escapeString($value);
                            // PostgreSQL uses single quotes, MySQL also uses single quotes
                            $values[] = "'" . $escaped . "'";
                        }
                    }
                    $output .= "INSERT INTO {$tableName} VALUES (" . implode(', ', $values) . ");\n";
                }
                $output .= "\n";
            }
        }

        if (!$isPostgres) {
            $output .= "\n/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;\n";
            $output .= "/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;\n";
            $output .= "/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;\n";
        } else {
            $output .= "\nCOMMIT;\n";
        }

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

