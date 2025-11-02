<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPasswordToPayers extends Migration
{
    public function up()
    {
        $fields = [
            'password' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'after' => 'payer_id',
                'comment' => 'Hashed password for payer authentication',
            ],
        ];
        $this->forge->addColumn('payers', $fields);
        
        // Set password for existing payers (use payer_id as default password)
        // Only for accounts created by admin (email_verified = 1)
        $db = \Config\Database::connect();
        $payers = $db->table('payers')->where('email_verified', 1)->get()->getResultArray();
        foreach ($payers as $payer) {
            // Set password to hashed payer_id for admin-created accounts
            $hashedPassword = password_hash($payer['payer_id'], PASSWORD_DEFAULT);
            $db->table('payers')->where('id', $payer['id'])->update(['password' => $hashedPassword]);
        }
    }

    public function down()
    {
        $this->forge->dropColumn('payers', 'password');
    }
}