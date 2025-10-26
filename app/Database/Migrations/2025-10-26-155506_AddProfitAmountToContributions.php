<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddProfitAmountToContributions extends Migration
{
    public function up()
    {
        $fields = [
            'profit_amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'default'    => 0.00,
                'after'      => 'cost_price',
            ],
        ];
        $this->forge->addColumn('contributions', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('contributions', 'profit_amount');
    }
}
