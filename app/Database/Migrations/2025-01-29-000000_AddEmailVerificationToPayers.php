<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddEmailVerificationToPayers extends Migration
{
    public function up()
    {
        // Add email verification fields to payers table
        $fields = [
            'email_verified' => [
                'type' => 'BOOLEAN',
                'default' => false,
                'null' => false,
                'after' => 'email_address'
            ],
            'verification_token' => [
                'type' => 'VARCHAR',
                'constraint' => 10,
                'null' => true,
                'after' => 'email_verified'
            ]
        ];

        $this->forge->addColumn('payers', $fields);
    }

    public function down()
    {
        // Remove email verification fields
        $this->forge->dropColumn('payers', ['email_verified', 'verification_token']);
    }
}

