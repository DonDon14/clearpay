<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddProductCostAndQuantityFields extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();

        if (! $db->fieldExists('cost_price', 'products')) {
            $this->forge->addColumn('products', [
                'cost_price' => [
                    'type' => 'DECIMAL',
                    'constraint' => '10,2',
                    'default' => 0,
                    'null' => false,
                ],
            ]);
        }

        if (! $db->fieldExists('quantity', 'payment_requests')) {
            $this->forge->addColumn('payment_requests', [
                'quantity' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'default' => 1,
                    'null' => false,
                ],
            ]);
        }

        if (! $db->fieldExists('quantity', 'payments')) {
            $this->forge->addColumn('payments', [
                'quantity' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'default' => 1,
                    'null' => false,
                ],
            ]);
        }
    }

    public function down()
    {
        $db = \Config\Database::connect();

        if ($db->fieldExists('cost_price', 'products')) {
            $this->forge->dropColumn('products', 'cost_price');
        }

        if ($db->fieldExists('quantity', 'payment_requests')) {
            $this->forge->dropColumn('payment_requests', 'quantity');
        }

        if ($db->fieldExists('quantity', 'payments')) {
            $this->forge->dropColumn('payments', 'quantity');
        }
    }
}
