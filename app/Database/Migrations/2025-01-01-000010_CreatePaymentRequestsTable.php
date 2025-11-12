<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePaymentRequestsTable extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();
        $isPostgres = strpos(strtolower($db->getPlatform()), 'postgre') !== false;
        
        $statusField = $isPostgres 
            ? ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'pending']
            : ['type' => 'ENUM', 'constraint' => ['pending', 'approved', 'rejected', 'processed'], 'default' => 'pending'];
        
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'payer_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => false,
            ],
            'contribution_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => false,
            ],
            'payment_sequence' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'comment' => 'Payment group sequence for grouping related payments',
            ],
            'requested_amount' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'null' => false,
            ],
            'payment_method' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => false,
                'default' => 'GCash',
            ],
            'reference_number' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'proof_of_payment_path' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'status' => $statusField,
            'notes' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'requested_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'processed_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'processed_by' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'admin_notes' => [
                'type' => 'TEXT',
                'null' => true,
            ],
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
        $this->forge->addKey('payer_id');
        $this->forge->addKey('contribution_id');
        $this->forge->addKey('status');
        $this->forge->addKey('requested_at');
        
        // Add foreign key constraints
        $this->forge->addForeignKey('payer_id', 'payers', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('contribution_id', 'contributions', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('processed_by', 'users', 'id', 'SET NULL', 'CASCADE');

        $this->forge->createTable('payment_requests');
        
        // Add CHECK constraint for PostgreSQL
        if ($isPostgres) {
            $db->query("ALTER TABLE payment_requests ADD CONSTRAINT payment_requests_status_check CHECK (status IN ('pending', 'approved', 'rejected', 'processed'))");
        }
    }

    public function down()
    {
        $this->forge->dropTable('payment_requests');
    }
}

