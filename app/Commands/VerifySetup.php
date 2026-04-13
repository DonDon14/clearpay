<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class VerifySetup extends BaseCommand
{
    protected $group       = 'Setup';
    protected $name        = 'setup:verify';
    protected $description = 'Verify that ClearPay local setup is ready for end-to-end workflow testing';

    public function run(array $params)
    {
        CLI::write('Verifying ClearPay setup...', 'yellow');
        CLI::newLine();

        $errors = [];
        $warnings = [];
        $db = null;

        CLI::write('1. Database connection', 'cyan');
        try {
            $db = db_connect();
            $db->query('SELECT 1');
            CLI::write('   [OK] Database connection successful', 'green');
        } catch (\Throwable $e) {
            $errors[] = 'Database connection failed: ' . $e->getMessage();
            CLI::write('   [FAIL] Database connection failed', 'red');
        }
        CLI::newLine();

        CLI::write('2. Required database tables', 'cyan');
        if ($db === null) {
            $errors[] = 'Cannot verify required tables because DB connection failed.';
            CLI::write('   [FAIL] Skipped (no DB connection)', 'red');
        } else {
            $tables = $db->listTables();
            $requiredTables = [
                'users',
                'payers',
                'contributions',
                'products',
                'payments',
                'payment_requests',
                'refunds',
                'payment_methods',
            ];

            $missingTables = [];
            foreach ($requiredTables as $table) {
                if (!in_array($table, $tables, true)) {
                    $missingTables[] = $table;
                }
            }

            if (empty($missingTables)) {
                CLI::write('   [OK] All required tables exist', 'green');
            } else {
                $errors[] = 'Missing tables: ' . implode(', ', $missingTables);
                CLI::write('   [FAIL] Missing tables: ' . implode(', ', $missingTables), 'red');
            }
        }
        CLI::newLine();

        CLI::write('3. Payment methods availability', 'cyan');
        if ($db === null) {
            $errors[] = 'Cannot verify payment methods because DB connection failed.';
            CLI::write('   [FAIL] Skipped (no DB connection)', 'red');
        } else {
            $result = $db->table('payment_methods')
                ->selectCount('id', 'count')
                ->where('status', 'active')
                ->get()
                ->getRow();
            $count = (int) ($result->count ?? 0);

            if ($count >= 1) {
                CLI::write("   [OK] Found {$count} active payment method(s)", 'green');
            } else {
                $errors[] = 'No active payment methods found.';
                CLI::write('   [FAIL] No active payment methods found', 'red');
                CLI::write('   Run: php spark db:seed PaymentMethodSeeder', 'yellow');
            }
        }
        CLI::newLine();

        CLI::write('4. Seeded users/payers', 'cyan');
        if ($db === null) {
            $errors[] = 'Cannot verify users/payers because DB connection failed.';
            CLI::write('   [FAIL] Skipped (no DB connection)', 'red');
        } else {
            $userCount = (int) ($db->table('users')->countAllResults() ?? 0);
            $payerCount = (int) ($db->table('payers')->countAllResults() ?? 0);

            if ($userCount > 0) {
                CLI::write("   [OK] Found {$userCount} user(s)", 'green');
            } else {
                $warnings[] = 'No users found. Run: php spark db:seed UserSeeder';
                CLI::write('   [WARN] No users found', 'yellow');
            }

            if ($payerCount > 0) {
                CLI::write("   [OK] Found {$payerCount} payer(s)", 'green');
            } else {
                $warnings[] = 'No payers found. Run your payer seed/setup workflow before E2E tests.';
                CLI::write('   [WARN] No payers found', 'yellow');
            }
        }
        CLI::newLine();

        CLI::write('5. Contributions/products data', 'cyan');
        if ($db === null) {
            $errors[] = 'Cannot verify contributions/products because DB connection failed.';
            CLI::write('   [FAIL] Skipped (no DB connection)', 'red');
        } else {
            $contributionCount = (int) ($db->table('contributions')->countAllResults() ?? 0);
            $productCount = (int) ($db->table('products')->countAllResults() ?? 0);

            if ($contributionCount > 0) {
                CLI::write("   [OK] Found {$contributionCount} contribution item(s)", 'green');
            } else {
                $warnings[] = 'No contributions found.';
                CLI::write('   [WARN] No contributions found', 'yellow');
            }

            if ($productCount > 0) {
                CLI::write("   [OK] Found {$productCount} product item(s)", 'green');
            } else {
                $warnings[] = 'No products found.';
                CLI::write('   [WARN] No products found', 'yellow');
            }
        }
        CLI::newLine();

        CLI::write('6. Upload directories', 'cyan');
        $uploadRoot = FCPATH . 'uploads' . DIRECTORY_SEPARATOR;
        $requiredDirs = ['profile', 'payment_proofs', 'contribution_items', 'product_items'];

        if (!is_dir($uploadRoot)) {
            $errors[] = 'Upload root missing: ' . $uploadRoot;
            CLI::write('   [FAIL] Upload root folder missing', 'red');
        } else {
            CLI::write('   [OK] Upload root exists', 'green');
        }

        foreach ($requiredDirs as $dir) {
            $fullPath = $uploadRoot . $dir;
            if (!is_dir($fullPath)) {
                $warnings[] = 'Missing upload subfolder: ' . $fullPath;
                CLI::write("   [WARN] Missing subfolder: {$dir}", 'yellow');
                continue;
            }

            if (!is_writable($fullPath)) {
                $warnings[] = 'Upload subfolder not writable: ' . $fullPath;
                CLI::write("   [WARN] Not writable: {$dir}", 'yellow');
                continue;
            }

            CLI::write("   [OK] {$dir}", 'green');
        }
        CLI::newLine();

        CLI::write('7. Environment configuration', 'cyan');
        $envPath = ROOTPATH . '.env';
        if (file_exists($envPath)) {
            CLI::write('   [OK] .env file exists', 'green');
        } else {
            $warnings[] = '.env file not found. Create one from .env.example or .env.example.postgresql';
            CLI::write('   [WARN] .env file not found', 'yellow');
        }

        $brevoKey = getenv('BREVO_API_KEY') ?: ($_ENV['BREVO_API_KEY'] ?? '');
        if ($brevoKey !== '') {
            CLI::write('   [OK] BREVO_API_KEY is set', 'green');
        } else {
            $warnings[] = 'BREVO_API_KEY is not set. Verification/reset emails will fail unless SMTP fallback is configured.';
            CLI::write('   [WARN] BREVO_API_KEY is not set', 'yellow');
        }

        $cloudName = getenv('CLOUDINARY_CLOUD_NAME') ?: ($_ENV['CLOUDINARY_CLOUD_NAME'] ?? '');
        $apiKey = getenv('CLOUDINARY_API_KEY') ?: ($_ENV['CLOUDINARY_API_KEY'] ?? '');
        $apiSecret = getenv('CLOUDINARY_API_SECRET') ?: ($_ENV['CLOUDINARY_API_SECRET'] ?? '');
        if ($cloudName !== '' && $apiKey !== '' && $apiSecret !== '') {
            CLI::write('   [OK] Cloudinary credentials are set', 'green');
        } else {
            $warnings[] = 'Cloudinary credentials are incomplete. App will use local uploads instead.';
            CLI::write('   [WARN] Cloudinary credentials are incomplete (local upload fallback expected)', 'yellow');
        }
        CLI::newLine();

        CLI::write('8. Optional email_settings table', 'cyan');
        if ($db === null) {
            $warnings[] = 'Could not verify email_settings table because DB connection failed.';
            CLI::write('   [WARN] Skipped (no DB connection)', 'yellow');
        } else {
            if (!$db->tableExists('email_settings')) {
                $warnings[] = 'email_settings table missing. SMTP UI settings may be unavailable.';
                CLI::write('   [WARN] email_settings table missing', 'yellow');
            } else {
                CLI::write('   [OK] email_settings table exists', 'green');
            }
        }
        CLI::newLine();

        CLI::write('9. Activity logging tables', 'cyan');
        if ($db === null) {
            $warnings[] = 'Could not verify activity logging tables because DB connection failed.';
            CLI::write('   [WARN] Skipped (no DB connection)', 'yellow');
        } else {
            $hasActivityLogs = $db->tableExists('activity_logs');
            $hasUserActivities = $db->tableExists('user_activities');
            if ($hasActivityLogs || $hasUserActivities) {
                CLI::write('   [OK] Activity logging tables detected', 'green');
            } else {
                $warnings[] = 'No activity log table found (expected activity_logs or user_activities).';
                CLI::write('   [WARN] Activity logging tables not detected', 'yellow');
            }
        }
        CLI::newLine();

        CLI::write('========================================', 'yellow');
        CLI::write('Setup Verification Summary', 'yellow');
        CLI::write('========================================', 'yellow');
        CLI::newLine();

        if (empty($errors) && empty($warnings)) {
            CLI::write('[OK] Setup is complete. All checks passed.', 'green');
            CLI::newLine();
            return 0;
        }

        if (!empty($errors)) {
            CLI::write('[FAIL] Errors:', 'red');
            foreach ($errors as $error) {
                CLI::write(' - ' . $error, 'red');
            }
            CLI::newLine();
        }

        if (!empty($warnings)) {
            CLI::write('[WARN] Warnings:', 'yellow');
            foreach ($warnings as $warning) {
                CLI::write(' - ' . $warning, 'yellow');
            }
            CLI::newLine();
        }

        if (!empty($errors)) {
            CLI::write('Fix the errors above before running full workflow tests.', 'red');
            CLI::newLine();
            return 1;
        }

        CLI::write('No blocking errors found. Resolve warnings for production readiness.', 'yellow');
        CLI::newLine();
        return 0;
    }
}
