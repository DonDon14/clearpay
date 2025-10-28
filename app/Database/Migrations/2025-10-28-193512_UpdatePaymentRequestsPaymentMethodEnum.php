<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdatePaymentRequestsPaymentMethodEnum extends Migration
{
    public function up()
    {
        // Update the payment_method ENUM to match the actual payment method names from payment_methods table
        $this->forge->modifyColumn('payment_requests', [
            'payment_method' => [
                'type' => 'ENUM',
                'constraint' => ['Cash', 'GCash', 'PayMaya', 'Bank Transfer', 'Online Banking'],
                'default' => 'GCash',
                'null' => false,
            ],
        ]);
    }

    public function down()
    {
        // Revert back to the original ENUM values
        $this->forge->modifyColumn('payment_requests', [
            'payment_method' => [
                'type' => 'ENUM',
                'constraint' => ['cash', 'online', 'bank_transfer', 'gcash', 'paymaya'],
                'default' => 'online',
                'null' => false,
            ],
        ]);
    }
}
