<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdatePaymentStatusColumn extends Migration
{
    public function up()
    {
        $this->forge->modifyColumn('payers', [
            'payment_status' => [
                'name' => 'payment_status',
                'type' => 'VARCHAR',
                'constraint' => 20,
                'default' => 'Partial',
                'null' => false,
            ],
        ]);
    }

    public function down()
    {
        
    }
}
