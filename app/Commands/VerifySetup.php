<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class VerifySetup extends BaseCommand
{
    protected $group       = 'Setup';
    protected $name        = 'setup:verify';
    protected $description = 'Verify that the application is properly set up';

    public function run(array $params)
    {
        CLI::write('Verifying ClearPay Setup...', 'yellow');
        CLI::newLine();

        $errors = [];
        $warnings = [];

        // 1. Check database connection
        CLI::write('1. Checking database connection...', 'cyan');
        try {
            $db = \Config\Database::connect();
            $db->query('SELECT 1');
            CLI::write('   ✓ Database connection successful', 'green');
        } catch (\Exception $e) {
            $errors[] = 'Database connection failed: ' . $e->getMessage();
            CLI::write('   ✗ Database connection failed', 'red');
        }
        CLI::newLine();

        // 2. Check if tables exist
        CLI::write('2. Checking database tables...', 'cyan');
        try {
            $db = \Config\Database::connect();
            $tables = $db->listTables();
            $requiredTables = ['users', 'payers', 'contributions', 'payments', 'payment_methods', 'announcements'];
            $missingTables = [];
            
            foreach ($requiredTables as $table) {
                if (!in_array($table, $tables)) {
                    $missingTables[] = $table;
                }
            }
            
            if (empty($missingTables)) {
                CLI::write('   ✓ All required tables exist', 'green');
            } else {
                $errors[] = 'Missing tables: ' . implode(', ', $missingTables);
                CLI::write('   ✗ Missing tables: ' . implode(', ', $missingTables), 'red');
            }
        } catch (\Exception $e) {
            $errors[] = 'Error checking tables: ' . $e->getMessage();
            CLI::write('   ✗ Error checking tables', 'red');
        }
        CLI::newLine();

        // 3. Check payment methods (CRITICAL)
        CLI::write('3. Checking payment methods (CRITICAL)...', 'cyan');
        try {
            $db = \Config\Database::connect();
            $result = $db->query('SELECT COUNT(*) as count FROM payment_methods WHERE status = "active"')->getRow();
            $count = (int)$result->count;
            
            if ($count >= 4) {
                CLI::write("   ✓ Found {$count} active payment methods", 'green');
            } else {
                $warnings[] = "Only {$count} active payment method(s) found. Expected at least 4.";
                CLI::write("   ⚠ Only {$count} active payment method(s) found", 'yellow');
                CLI::write('   Run: php spark db:seed PaymentMethodSeeder', 'yellow');
            }
        } catch (\Exception $e) {
            $errors[] = 'Error checking payment methods: ' . $e->getMessage();
            CLI::write('   ✗ Error checking payment methods', 'red');
        }
        CLI::newLine();

        // 4. Check users
        CLI::write('4. Checking users...', 'cyan');
        try {
            $db = \Config\Database::connect();
            $result = $db->query('SELECT COUNT(*) as count FROM users')->getRow();
            $count = (int)$result->count;
            
            if ($count > 0) {
                CLI::write("   ✓ Found {$count} user(s)", 'green');
            } else {
                $warnings[] = 'No users found. Run: php spark db:seed UserSeeder';
                CLI::write('   ⚠ No users found', 'yellow');
            }
        } catch (\Exception $e) {
            $errors[] = 'Error checking users: ' . $e->getMessage();
            CLI::write('   ✗ Error checking users', 'red');
        }
        CLI::newLine();

        // 5. Check contributions
        CLI::write('5. Checking contributions...', 'cyan');
        try {
            $db = \Config\Database::connect();
            $result = $db->query('SELECT COUNT(*) as count FROM contributions')->getRow();
            $count = (int)$result->count;
            
            if ($count > 0) {
                CLI::write("   ✓ Found {$count} contribution(s)", 'green');
            } else {
                CLI::write('   ⚠ No contributions found (this is OK if you plan to create them)', 'yellow');
            }
        } catch (\Exception $e) {
            $errors[] = 'Error checking contributions: ' . $e->getMessage();
            CLI::write('   ✗ Error checking contributions', 'red');
        }
        CLI::newLine();

        // 6. Check .env file
        CLI::write('6. Checking environment configuration...', 'cyan');
        $envPath = ROOTPATH . '.env';
        if (file_exists($envPath)) {
            CLI::write('   ✓ .env file exists', 'green');
        } else {
            $warnings[] = '.env file not found. Create one from .env.example';
            CLI::write('   ⚠ .env file not found', 'yellow');
        }
        CLI::newLine();

        // Summary
        CLI::newLine();
        CLI::write('========================================', 'yellow');
        CLI::write('Setup Verification Summary', 'yellow');
        CLI::write('========================================', 'yellow');
        CLI::newLine();

        if (empty($errors) && empty($warnings)) {
            CLI::write('✓ Setup is complete! Everything looks good.', 'green');
            CLI::newLine();
            return 0;
        }

        if (!empty($errors)) {
            CLI::write('✗ ERRORS FOUND:', 'red');
            foreach ($errors as $error) {
                CLI::write('  - ' . $error, 'red');
            }
            CLI::newLine();
        }

        if (!empty($warnings)) {
            CLI::write('⚠ WARNINGS:', 'yellow');
            foreach ($warnings as $warning) {
                CLI::write('  - ' . $warning, 'yellow');
            }
            CLI::newLine();
        }

        if (!empty($errors)) {
            CLI::write('Please fix the errors above before using the application.', 'red');
            CLI::newLine();
            return 1;
        }

        CLI::write('Setup is mostly complete, but please address the warnings above.', 'yellow');
        CLI::newLine();
        return 0;
    }
}

