<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPayerIdToActivityLogs extends Migration
{
    public function up()
    {
        $this->forge->addColumn('activity_logs', [
            'payer_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
                'comment' => 'Specific payer ID for payer-specific notifications'
            ]
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('activity_logs', 'payer_id');
    }
}
