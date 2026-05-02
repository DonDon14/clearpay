<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddUniqueEmailToPayers extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();
        $driver = strtolower($db->DBDriver ?? '');

        if ($driver === 'postgres' || $driver === 'postgre') {
            // Unique only for non-null emails, case-insensitive.
            $db->query('CREATE UNIQUE INDEX IF NOT EXISTS uq_payers_email_lower ON payers (LOWER(email_address)) WHERE email_address IS NOT NULL');
            return;
        }

        // MySQL: standard unique index allows multiple NULL values.
        // Use explicit index name and guard for repeated migration runs.
        $existing = $db->query("SHOW INDEX FROM payers WHERE Key_name = 'uq_payers_email_address'")->getResultArray();
        if (empty($existing)) {
            $this->forge->addUniqueKey('email_address', 'uq_payers_email_address');
            $this->forge->processIndexes('payers');
        }
    }

    public function down()
    {
        $db = \Config\Database::connect();
        $driver = strtolower($db->DBDriver ?? '');

        if ($driver === 'postgres' || $driver === 'postgre') {
            $db->query('DROP INDEX IF EXISTS uq_payers_email_lower');
            return;
        }

        $existing = $db->query("SHOW INDEX FROM payers WHERE Key_name = 'uq_payers_email_address'")->getResultArray();
        if (!empty($existing)) {
            $this->forge->dropKey('payers', 'uq_payers_email_address', true);
        }
    }
}

