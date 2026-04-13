<?php

namespace Tests\Support\Database\Migrations;

use CodeIgniter\Database\Migration;

class WorkflowSmokeMigration extends Migration
{
    protected $DBGroup = 'tests';

    public function up(): void
    {
        $this->forge->addField([
            'id' => ['type' => 'INTEGER', 'auto_increment' => true],
            'name' => ['type' => 'VARCHAR', 'constraint' => 100],
            'username' => ['type' => 'VARCHAR', 'constraint' => 50],
            'email' => ['type' => 'VARCHAR', 'constraint' => 100],
            'phone' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'password' => ['type' => 'TEXT'],
            'role' => ['type' => 'VARCHAR', 'constraint' => 20],
            'status' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'approved'],
            'is_active' => ['type' => 'INTEGER', 'default' => 1],
            'profile_picture' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'verification_token' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'email_verified' => ['type' => 'INTEGER', 'default' => 1],
            'reset_token' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'reset_expires' => ['type' => 'DATETIME', 'null' => true],
            'last_activity' => ['type' => 'DATETIME', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('users', true);

        $this->forge->addField([
            'id' => ['type' => 'INTEGER', 'auto_increment' => true],
            'payer_id' => ['type' => 'VARCHAR', 'constraint' => 50],
            'password' => ['type' => 'TEXT'],
            'payer_name' => ['type' => 'VARCHAR', 'constraint' => 255],
            'contact_number' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'email_address' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'course_department' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'profile_picture' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'email_verified' => ['type' => 'INTEGER', 'default' => 1],
            'verification_token' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'reset_token' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'reset_expires' => ['type' => 'DATETIME', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('payers', true);

        $this->forge->addField([
            'id' => ['type' => 'INTEGER', 'auto_increment' => true],
            'title' => ['type' => 'VARCHAR', 'constraint' => 255],
            'description' => ['type' => 'TEXT', 'null' => true],
            'amount' => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => '0.00'],
            'grand_total' => ['type' => 'DECIMAL', 'constraint' => '10,2', 'null' => true],
            'category' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'status' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'active'],
            'created_by' => ['type' => 'INTEGER', 'null' => true],
            'cost_price' => ['type' => 'DECIMAL', 'constraint' => '10,2', 'null' => true],
            'profit_amount' => ['type' => 'DECIMAL', 'constraint' => '10,2', 'null' => true],
            'contribution_code' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('contributions', true);

        $this->forge->addField([
            'id' => ['type' => 'INTEGER', 'auto_increment' => true],
            'title' => ['type' => 'VARCHAR', 'constraint' => 255],
            'description' => ['type' => 'TEXT', 'null' => true],
            'amount' => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => '0.00'],
            'cost_price' => ['type' => 'DECIMAL', 'constraint' => '10,2', 'null' => true],
            'profit_amount' => ['type' => 'DECIMAL', 'constraint' => '10,2', 'null' => true],
            'status' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'active'],
            'created_by' => ['type' => 'INTEGER', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('products', true);

        $this->forge->addField([
            'id' => ['type' => 'INTEGER', 'auto_increment' => true],
            'name' => ['type' => 'VARCHAR', 'constraint' => 100],
            'icon' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'description' => ['type' => 'TEXT', 'null' => true],
            'account_details' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'account_number' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'account_name' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'qr_code_path' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'custom_instructions' => ['type' => 'TEXT', 'null' => true],
            'reference_prefix' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'status' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'active'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('payment_methods', true);

        $this->forge->addField([
            'id' => ['type' => 'INTEGER', 'auto_increment' => true],
            'name' => ['type' => 'VARCHAR', 'constraint' => 100],
            'code' => ['type' => 'VARCHAR', 'constraint' => 50],
            'description' => ['type' => 'TEXT', 'null' => true],
            'status' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'active'],
            'sort_order' => ['type' => 'INTEGER', 'default' => 0],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('refund_methods', true);

        $this->forge->addField([
            'id' => ['type' => 'INTEGER', 'auto_increment' => true],
            'payer_id' => ['type' => 'INTEGER'],
            'contribution_id' => ['type' => 'INTEGER', 'null' => true],
            'product_id' => ['type' => 'INTEGER', 'null' => true],
            'quantity' => ['type' => 'INTEGER', 'default' => 1],
            'amount_paid' => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => '0.00'],
            'payment_method' => ['type' => 'VARCHAR', 'constraint' => 100],
            'payment_status' => ['type' => 'VARCHAR', 'constraint' => 20],
            'is_partial_payment' => ['type' => 'INTEGER', 'default' => 0],
            'remaining_balance' => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => '0.00'],
            'parent_payment_id' => ['type' => 'INTEGER', 'null' => true],
            'payment_sequence' => ['type' => 'INTEGER', 'default' => 1],
            'reference_number' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'receipt_number' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'qr_receipt_path' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'recorded_by' => ['type' => 'INTEGER', 'null' => true],
            'payment_date' => ['type' => 'DATETIME'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('payments', true);

        $this->forge->addField([
            'id' => ['type' => 'INTEGER', 'auto_increment' => true],
            'payer_id' => ['type' => 'INTEGER'],
            'contribution_id' => ['type' => 'INTEGER', 'null' => true],
            'product_id' => ['type' => 'INTEGER', 'null' => true],
            'quantity' => ['type' => 'INTEGER', 'default' => 1],
            'payment_sequence' => ['type' => 'INTEGER', 'null' => true],
            'requested_amount' => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => '0.00'],
            'payment_method' => ['type' => 'VARCHAR', 'constraint' => 100],
            'reference_number' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'proof_of_payment_path' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'status' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'pending'],
            'notes' => ['type' => 'TEXT', 'null' => true],
            'requested_at' => ['type' => 'DATETIME', 'null' => true],
            'processed_at' => ['type' => 'DATETIME', 'null' => true],
            'processed_by' => ['type' => 'INTEGER', 'null' => true],
            'admin_notes' => ['type' => 'TEXT', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('payment_requests', true);

        $this->forge->addField([
            'id' => ['type' => 'INTEGER', 'auto_increment' => true],
            'payment_id' => ['type' => 'INTEGER'],
            'payer_id' => ['type' => 'INTEGER'],
            'contribution_id' => ['type' => 'INTEGER', 'null' => true],
            'product_id' => ['type' => 'INTEGER', 'null' => true],
            'refund_amount' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => '0.00'],
            'refund_reason' => ['type' => 'TEXT', 'null' => true],
            'refund_method' => ['type' => 'VARCHAR', 'constraint' => 50],
            'refund_reference' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'status' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'pending'],
            'request_type' => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'admin_initiated'],
            'requested_by_payer' => ['type' => 'INTEGER', 'default' => 0],
            'requested_at' => ['type' => 'DATETIME', 'null' => true],
            'processed_at' => ['type' => 'DATETIME', 'null' => true],
            'processed_by' => ['type' => 'INTEGER', 'null' => true],
            'admin_notes' => ['type' => 'TEXT', 'null' => true],
            'payer_notes' => ['type' => 'TEXT', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('refunds', true);

        $this->forge->addField([
            'id' => ['type' => 'INTEGER', 'auto_increment' => true],
            'activity_type' => ['type' => 'VARCHAR', 'constraint' => 50],
            'entity_type' => ['type' => 'VARCHAR', 'constraint' => 50],
            'entity_id' => ['type' => 'INTEGER'],
            'action' => ['type' => 'VARCHAR', 'constraint' => 50],
            'title' => ['type' => 'VARCHAR', 'constraint' => 255],
            'description' => ['type' => 'TEXT'],
            'old_values' => ['type' => 'TEXT', 'null' => true],
            'new_values' => ['type' => 'TEXT', 'null' => true],
            'user_id' => ['type' => 'INTEGER'],
            'user_type' => ['type' => 'VARCHAR', 'constraint' => 20],
            'payer_id' => ['type' => 'INTEGER', 'null' => true],
            'target_audience' => ['type' => 'VARCHAR', 'constraint' => 20],
            'is_read' => ['type' => 'INTEGER', 'default' => 0],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('activity_logs', true);

        $this->forge->addField([
            'id' => ['type' => 'INTEGER', 'auto_increment' => true],
            'user_id' => ['type' => 'INTEGER'],
            'activity_type' => ['type' => 'VARCHAR', 'constraint' => 100],
            'entity_type' => ['type' => 'VARCHAR', 'constraint' => 100],
            'entity_id' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'description' => ['type' => 'TEXT'],
            'metadata' => ['type' => 'TEXT', 'null' => true],
            'ip_address' => ['type' => 'VARCHAR', 'constraint' => 45, 'null' => true],
            'user_agent' => ['type' => 'TEXT', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('user_activities', true);

        $this->forge->addField([
            'id' => ['type' => 'INTEGER', 'auto_increment' => true],
            'from_email' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'from_name' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'protocol' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'smtp_host' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'smtp_user' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'smtp_pass' => ['type' => 'TEXT', 'null' => true],
            'smtp_port' => ['type' => 'INTEGER', 'null' => true],
            'smtp_crypto' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'mail_type' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'is_active' => ['type' => 'INTEGER', 'default' => 1],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('email_settings', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('email_settings', true);
        $this->forge->dropTable('user_activities', true);
        $this->forge->dropTable('activity_logs', true);
        $this->forge->dropTable('refunds', true);
        $this->forge->dropTable('payment_requests', true);
        $this->forge->dropTable('payments', true);
        $this->forge->dropTable('refund_methods', true);
        $this->forge->dropTable('payment_methods', true);
        $this->forge->dropTable('products', true);
        $this->forge->dropTable('contributions', true);
        $this->forge->dropTable('payers', true);
        $this->forge->dropTable('users', true);
    }
}
