<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdatePaymentMethodColumnToVarchar extends Migration
{
    public function up()
    {
        // Change payment_method from ENUM to VARCHAR to support dynamic payment methods
        $this->forge->modifyColumn('payments', [
            'payment_method' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
                'default' => null,
            ],
        ]);
    }

    public function down()
    {
        // Revert back to ENUM (if needed)
        $this->forge->modifyColumn('payments', [
            'payment_method' => [
                'type' => 'ENUM',
                'constraint' => ['cash', 'online', 'check', 'bank'],
                'default' => 'cash',
            ],
        ]);
    }
}