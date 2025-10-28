<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddIconToPaymentMethods extends Migration
{
    public function up()
    {
        $this->forge->addColumn('payment_methods', [
            'icon' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
                'after' => 'name',
                'comment' => 'FontAwesome icon class for the payment method'
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('payment_methods', 'icon');
    }
}
