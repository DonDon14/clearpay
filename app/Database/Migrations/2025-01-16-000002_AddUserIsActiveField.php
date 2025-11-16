<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddUserIsActiveField extends Migration
{
    public function up()
    {
        $this->forge->addColumn('users', [
            'is_active' => [
                'type' => 'BOOLEAN',
                'default' => true,
                'null' => false,
                'after' => 'status'
            ]
        ]);
        
        // Set all existing users to active
        $this->db->table('users')->update(['is_active' => true]);
    }

    public function down()
    {
        $this->forge->dropColumn('users', ['is_active']);
    }
}


