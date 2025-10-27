<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPaymentSequenceToPaymentRequests extends Migration
{
    public function up()
    {
        $this->forge->addColumn('payment_requests', [
            'payment_sequence' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'comment' => 'Payment group sequence for grouping related payments',
                'after' => 'contribution_id'
            ]
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('payment_requests', 'payment_sequence');
    }
}