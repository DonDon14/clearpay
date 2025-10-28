<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateIconFieldForFileUpload extends Migration
{
    public function up()
    {
        // Update the icon column to store file paths instead of FontAwesome classes
        $this->forge->modifyColumn('payment_methods', [
            'icon' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'comment' => 'Path to uploaded icon image file'
            ],
        ]);
    }

    public function down()
    {
        // Revert back to FontAwesome classes
        $this->forge->modifyColumn('payment_methods', [
            'icon' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
                'comment' => 'FontAwesome icon class for the payment method'
            ],
        ]);
    }
}
