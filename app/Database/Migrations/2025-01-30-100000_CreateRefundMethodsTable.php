<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateRefundMethodsTable extends Migration
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
            'name' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => false,
                'comment' => 'Display name of the refund method (e.g., Cash, Bank Transfer)',
            ],
            'code' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => false,
                'comment' => 'System code for the refund method (e.g., cash, bank_transfer, gcash, paymaya)',
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Description of the refund method',
            ],
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['active', 'inactive'],
                'default' => 'active',
                'comment' => 'Status of the refund method',
            ],
            'sort_order' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
                'comment' => 'Order for displaying refund methods',
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
        $this->forge->addUniqueKey('code');
        $this->forge->addKey('status');
        $this->forge->createTable('refund_methods', true);

        // Insert default refund methods
        $defaultMethods = [
            [
                'name' => 'Cash',
                'code' => 'cash',
                'description' => 'Refund via cash payment',
                'status' => 'active',
                'sort_order' => 1,
            ],
            [
                'name' => 'Bank Transfer',
                'code' => 'bank_transfer',
                'description' => 'Refund via bank transfer',
                'status' => 'active',
                'sort_order' => 2,
            ],
            [
                'name' => 'GCash',
                'code' => 'gcash',
                'description' => 'Refund via GCash',
                'status' => 'active',
                'sort_order' => 3,
            ],
            [
                'name' => 'PayMaya',
                'code' => 'paymaya',
                'description' => 'Refund via PayMaya',
                'status' => 'active',
                'sort_order' => 4,
            ],
            [
                'name' => 'Original Payment Method',
                'code' => 'original_method',
                'description' => 'Refund using the same method as the original payment',
                'status' => 'active',
                'sort_order' => 5,
            ],
        ];

        $this->db->table('refund_methods')->insertBatch($defaultMethods);
    }

    public function down()
    {
        $this->forge->dropTable('refund_methods', true);
    }
}

