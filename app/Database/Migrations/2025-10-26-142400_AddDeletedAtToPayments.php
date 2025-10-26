<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDeletedAtToPayments extends Migration
{
    public function up()
    {
        $this->forge->addColumn('payments', [
            'deleted_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
                'after'      => 'updated_at',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('payments', 'deleted_at');
    }
}
