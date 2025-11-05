<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUsersTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                 => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'name'               => ['type' => 'VARCHAR', 'constraint' => 100],
            'username'           => ['type' => 'VARCHAR', 'constraint' => 50, 'unique' => true],
            'email'              => ['type' => 'VARCHAR', 'constraint' => 100, 'unique' => true],
            'phone'              => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'profile_picture'    => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'email_verified'     => ['type' => 'BOOLEAN', 'default' => false],
            'verification_token' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'reset_token'        => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'reset_expires'      => ['type' => 'DATETIME', 'null' => true],
            'password'           => ['type' => 'VARCHAR', 'constraint' => 255],
            'role'               => ['type' => 'ENUM', 'constraint' => ['admin', 'officer'], 'default' => 'officer'],
            'permissions'        => ['type' => 'JSON', 'null' => true],
            'created_at'         => ['type' => 'DATETIME', 'null' => true, 'default' => null],
            'updated_at'         => ['type' => 'DATETIME', 'null' => true, 'default' => null],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('users', true);
    }

    public function down()
    {
        $this->forge->dropTable('users', true);
    }
}

