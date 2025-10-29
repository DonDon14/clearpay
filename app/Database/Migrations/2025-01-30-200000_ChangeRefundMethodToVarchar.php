<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ChangeRefundMethodToVarchar extends Migration
{
    public function up()
    {
        // Change refund_method from ENUM to VARCHAR to support dynamic refund methods
        $this->forge->modifyColumn('refunds', [
            'refund_method' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => false,
                'default' => 'original_method',
                'comment' => 'Method used for refund (references refund_methods.code)',
            ],
        ]);
    }

    public function down()
    {
        // Revert back to ENUM if needed
        $this->forge->modifyColumn('refunds', [
            'refund_method' => [
                'type' => 'ENUM',
                'constraint' => ['cash', 'bank_transfer', 'gcash', 'paymaya', 'original_method'],
                'default' => 'original_method',
                'comment' => 'Method used for refund',
            ],
        ]);
    }
}

