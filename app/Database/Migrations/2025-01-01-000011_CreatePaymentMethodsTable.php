<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePaymentMethodsTable extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();
        $isPostgres = strpos(strtolower($db->getPlatform()), 'postgre') !== false;
        
        $statusField = $isPostgres 
            ? ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'active']
            : ['type' => 'ENUM', 'constraint' => ['active', 'inactive'], 'default' => 'active'];
        
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'name' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => false,
            ],
            'icon' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'comment' => 'Path to uploaded icon image file'
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'account_details' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'account_number' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
                'comment' => 'Account number (e.g., GCash number, bank account)'
            ],
            'account_name' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
                'comment' => 'Account holder name'
            ],
            'qr_code_path' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'comment' => 'Path to uploaded QR code image'
            ],
            'custom_instructions' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Custom payment instructions (HTML allowed)'
            ],
            'reference_prefix' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => true,
                'default' => 'CP',
                'comment' => 'Prefix for payment reference numbers'
            ],
            'status' => $statusField,
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('status');
        
        $this->forge->createTable('payment_methods');
        
        // Add CHECK constraint for PostgreSQL
        if ($isPostgres) {
            $db->query("ALTER TABLE payment_methods ADD CONSTRAINT payment_methods_status_check CHECK (status IN ('active', 'inactive'))");
        }
    }

    public function down()
    {
        $this->forge->dropTable('payment_methods');
    }
}

