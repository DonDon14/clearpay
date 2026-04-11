<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddProductIdToPaymentsAndRequests extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();
        $platform = strtolower($db->DBDriver);

        if (!$db->fieldExists('product_id', 'payments')) {
            $this->forge->addColumn('payments', [
                'product_id' => [
                    'type' => 'INT',
                    'unsigned' => true,
                    'null' => true,
                    'after' => 'contribution_id',
                ],
            ]);
        }

        if (!$db->fieldExists('product_id', 'payment_requests')) {
            $this->forge->addColumn('payment_requests', [
                'product_id' => [
                    'type' => 'INT',
                    'unsigned' => true,
                    'null' => true,
                    'after' => 'contribution_id',
                ],
            ]);
        }

        if ($platform === 'postgre') {
            $db->query('ALTER TABLE payments ALTER COLUMN contribution_id DROP NOT NULL');
            $db->query('ALTER TABLE payment_requests ALTER COLUMN contribution_id DROP NOT NULL');
        } else {
            $this->forge->modifyColumn('payments', [
                'contribution_id' => [
                    'name' => 'contribution_id',
                    'type' => 'INT',
                    'unsigned' => true,
                    'null' => true,
                ],
            ]);

            $this->forge->modifyColumn('payment_requests', [
                'contribution_id' => [
                    'name' => 'contribution_id',
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'null' => true,
                ],
            ]);
        }
    }

    public function down()
    {
        $this->forge->dropColumn('payments', 'product_id');
        $this->forge->dropColumn('payment_requests', 'product_id');
    }
}
