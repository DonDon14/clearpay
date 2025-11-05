<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePaymentsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'payer_id'          => ['type' => 'INT', 'unsigned' => true],
            'contribution_id'   => ['type' => 'INT', 'unsigned' => true],
            'amount_paid'       => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0.00],
            'payment_method'    => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true, 'default' => null],
            'payment_status'    => ['type' => 'ENUM', 'constraint' => ['fully paid', 'partial', 'pending'], 'default' => 'pending'],
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
            'deleted_at'        => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('payer_id', 'payers', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('contribution_id', 'contributions', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('recorded_by', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('payments', true);
    }

    public function down()
    {
        $this->forge->dropTable('payments', true);
    }
}

