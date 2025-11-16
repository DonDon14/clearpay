<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddUserStatusAndActivityFields extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();
        $isPostgres = strpos(strtolower($db->getPlatform()), 'postgre') !== false;
        
        // Add status field for officer approval
        $statusField = $isPostgres 
            ? ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'approved', 'null' => false]
            : ['type' => 'ENUM', 'constraint' => ['pending', 'approved', 'rejected'], 'default' => 'approved', 'null' => false];
        
        $this->forge->addColumn('users', [
            'status' => $statusField,
            'last_activity' => ['type' => 'DATETIME', 'null' => true, 'default' => null]
        ]);
        
        // Add CHECK constraint for PostgreSQL
        if ($isPostgres) {
            $db->query("ALTER TABLE users ADD CONSTRAINT users_status_check CHECK (status IN ('pending', 'approved', 'rejected'))");
        }
        
        // Set existing users to approved status
        $this->db->table('users')->update(['status' => 'approved']);
        
        // Set existing admin users (super admins) to approved
        $this->db->table('users')
            ->where('role', 'admin')
            ->update(['status' => 'approved']);
    }

    public function down()
    {
        $this->forge->dropColumn('users', ['status', 'last_activity']);
        
        // Remove CHECK constraint for PostgreSQL
        $db = \Config\Database::connect();
        $isPostgres = strpos(strtolower($db->getPlatform()), 'postgre') !== false;
        if ($isPostgres) {
            try {
                $db->query("ALTER TABLE users DROP CONSTRAINT IF EXISTS users_status_check");
            } catch (\Exception $e) {
                // Constraint might not exist, ignore
            }
        }
    }
}

