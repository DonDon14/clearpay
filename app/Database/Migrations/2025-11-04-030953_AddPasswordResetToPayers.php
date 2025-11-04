<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPasswordResetToPayers extends Migration
{
    public function up()
    {
        // Add password reset fields to payers table
        $fields = [
            'reset_token' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'after' => 'verification_token',
                'comment' => 'Password reset token for forgot password functionality',
            ],
            'reset_expires' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'reset_token',
                'comment' => 'Password reset token expiration time',
            ],
        ];
        $this->forge->addColumn('payers', $fields);
    }

    public function down()
    {
        // Remove password reset fields
        $this->forge->dropColumn('payers', ['reset_token', 'reset_expires']);
    }
}
