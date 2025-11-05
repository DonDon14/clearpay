<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateRefundsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'payment_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => false,
                'comment' => 'Reference to the payment being refunded',
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
            'refund_amount' => [
                'type' => 'DECIMAL',
                'constraint' => '12,2',
                'null' => false,
            ],
            'refund_reason' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'refund_method' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => false,
                'default' => 'original_method',
                'comment' => 'Method used for refund (references refund_methods.code)',
            ],
            'refund_reference' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
                'comment' => 'Reference number for refund transaction',
            ],
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['pending', 'processing', 'completed', 'rejected', 'cancelled'],
                'default' => 'pending',
            ],
            'request_type' => [
                'type' => 'ENUM',
                'constraint' => ['admin_initiated', 'payer_requested'],
                'default' => 'admin_initiated',
                'comment' => 'Who initiated the refund - admin or payer request',
            ],
            'requested_by_payer' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'comment' => '1 if requested by payer, 0 if by admin',
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
                'comment' => 'User ID who processed the refund',
            ],
            'admin_notes' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'payer_notes' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Notes from payer when requesting refund',
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
        $this->forge->addKey('payment_id');
        $this->forge->addKey('payer_id');
        $this->forge->addKey('contribution_id');
        $this->forge->addKey('status');
        $this->forge->addKey('requested_at');
        
        // Add foreign key constraints
        $this->forge->addForeignKey('payment_id', 'payments', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('payer_id', 'payers', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('contribution_id', 'contributions', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('processed_by', 'users', 'id', 'SET NULL', 'CASCADE');

        $this->forge->createTable('refunds');
    }

    public function down()
    {
        $this->forge->dropTable('refunds');
    }
}

