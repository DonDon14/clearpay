<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddGrandTotalToContributions extends Migration
{
    public function up()
    {
        $fields = [
            'grand_total' => [
                'type' => 'DECIMAL',
                'constraint' => '12,2',
                'null' => true,
                'default' => null,
                'after' => 'amount',
                'comment' => 'Grand total target to be collected across all payers',
            ],
        ];
        $this->forge->addColumn('contributions', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('contributions', 'grand_total');
    }
}