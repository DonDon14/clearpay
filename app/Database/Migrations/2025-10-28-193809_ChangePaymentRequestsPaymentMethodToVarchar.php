<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ChangePaymentRequestsPaymentMethodToVarchar extends Migration
{
    public function up()
    {
        // Change payment_method from ENUM to VARCHAR to make it dynamic
        $this->forge->modifyColumn('payment_requests', [
            'payment_method' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => false,
                'default' => 'GCash', // Default to GCash since it's commonly used
            ],
        ]);
    }

    public function down()
    {
        // Revert back to ENUM (if needed for rollback)
        $this->forge->modifyColumn('payment_requests', [
            'payment_method' => [
                'type' => 'ENUM',
                'constraint' => ['Cash', 'GCash', 'PayMaya', 'Bank Transfer', 'Online Banking'],
                'default' => 'GCash',
                'null' => false,
            ],
        ]);
    }
}
