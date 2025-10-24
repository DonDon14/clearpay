<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTables extends Migration
{
    public function up()
    {
        /**
         * USERS TABLE
         */
        $this->forge->addField([
            'id'                 => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'name'               => ['type' => 'VARCHAR', 'constraint' => 100],
            'username'           => ['type' => 'VARCHAR', 'constraint' => 50, 'unique' => true],
            'email'              => ['type' => 'VARCHAR', 'constraint' => 100, 'unique' => true],
            'phone'              => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'profile_picture'    => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'email_verified'     => ['type' => 'BOOLEAN', 'default' => false],
            'verification_token' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'reset_token'        => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'reset_expires'      => ['type' => 'DATETIME', 'null' => true],
            'password'           => ['type' => 'VARCHAR', 'constraint' => 255],
            'role'               => ['type' => 'ENUM', 'constraint' => ['admin', 'officer'], 'default' => 'officer'],
            'permissions'        => ['type' => 'JSON', 'null' => true],
            'created_at'         => ['type' => 'DATETIME', 'null' => true, 'default' => null],
            'updated_at'         => ['type' => 'DATETIME', 'null' => true, 'default' => null],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('users', true);

        /**
         * USER ACTIVITIES TABLE
         */
        $this->forge->addField([
            'id'            => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'user_id'       => ['type' => 'INT', 'unsigned' => true],
            'activity_type' => ['type' => 'ENUM', 'constraint' => ['create', 'update', 'delete', 'login', 'logout']],
            'entity_type'   => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'entity_id'     => ['type' => 'INT', 'null' => true],
            'description'   => ['type' => 'TEXT', 'null' => true],
            'metadata'      => ['type' => 'JSON', 'null' => true],
            'ip_address'    => ['type' => 'VARCHAR', 'constraint' => 45, 'null' => true],
            'user_agent'    => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'created_at'    => ['type' => 'DATETIME', 'null' => true, 'default' => null],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('user_activities', true);

        /**
         * CONTRIBUTIONS TABLE
         */
        $this->forge->addField([
            'id'            => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'title'         => ['type' => 'VARCHAR', 'constraint' => 100],
            'description'   => ['type' => 'TEXT', 'null' => true],
            'amount'        => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0.00],
            'category'      => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'status'        => ['type' => 'ENUM', 'constraint' => ['active', 'inactive'], 'default' => 'active'],
            'created_by'    => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'cost_price'    => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0.00],
            'created_at'    => ['type' => 'DATETIME', 'null' => true, 'default' => null],
            'updated_at'    => ['type' => 'DATETIME', 'null' => true, 'default' => null],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('created_by', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('contributions', true);

        /**
         * PAYERS TABLE
         */
        $this->forge->addField([
            'id'                => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'contribution_id'   => ['type' => 'INT', 'unsigned' => true],
            'payer_id'          => ['type' => 'VARCHAR', 'constraint' => 50],
            'payer_name'      => ['type' => 'VARCHAR', 'constraint' => 100],
            'contact_number'    => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'email_address'     => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'amount_paid'       => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0.00],
            'payment_method'    => ['type' => 'ENUM', 'constraint' => ['cash', 'online', 'check', 'bank'], 'default' => 'cash'],
            'payment_status'    => ['type' => 'ENUM', 'constraint' => ['paid', 'pending', 'failed'], 'default' => 'pending'],
            'is_partial_payment'=> ['type' => 'BOOLEAN', 'default' => false],
            'remaining_balance' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0.00],
            'parent_payment_id' => ['type' => 'INT', 'null' => true],
            'payment_sequence'  => ['type' => 'INT', 'null' => true],
            'reference_number'  => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'receipt_number'    => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'qr_receipt_path'   => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'recorded_by'       => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'payment_date'      => ['type' => 'DATETIME'],
            'created_at'        => ['type' => 'DATETIME', 'null' => true, 'default' => null],
            'updated_at'        => ['type' => 'DATETIME', 'null' => true, 'default' => null],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('contribution_id', 'contributions', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('recorded_by', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('payers', true);

        /**
         * ANNOUNCEMENTS TABLE
         */
        $this->forge->addField([
            'id'              => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'title'           => ['type' => 'VARCHAR', 'constraint' => 100],
            'text'            => ['type' => 'TEXT'],
            'type'            => ['type' => 'ENUM', 'constraint' => ['general', 'urgent', 'maintenance', 'event'], 'default' => 'general'],
            'priority'        => ['type' => 'ENUM', 'constraint' => ['low', 'medium', 'high', 'critical'], 'default' => 'low'],
            'target_audience' => ['type' => 'ENUM', 'constraint' => ['admins', 'payers', 'both'], 'default' => 'both'],
            'status'          => ['type' => 'ENUM', 'constraint' => ['draft', 'published', 'archived'], 'default' => 'draft'],
            'created_by'      => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'published_at'    => ['type' => 'DATETIME', 'null' => true],
            'expires_at'      => ['type' => 'DATETIME', 'null' => true],
            'created_at'      => ['type' => 'DATETIME', 'null' => true, 'default' => null],
            'updated_at'      => ['type' => 'DATETIME', 'null' => true, 'default' => null],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('created_by', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('announcements', true);
    }

    public function down()
    {
        $this->forge->dropTable('announcements', true);
        $this->forge->dropTable('payers', true);
        $this->forge->dropTable('contributions', true);
        $this->forge->dropTable('user_activities', true);
        $this->forge->dropTable('users', true);
    }
}
