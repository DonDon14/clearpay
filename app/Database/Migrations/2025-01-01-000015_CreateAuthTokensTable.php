<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAuthTokensTable extends Migration
{
    public function up()
    {
        /**
         * AUTH TOKENS TABLE
         * Stores remember me tokens for secure persistent login
         */
        $this->forge->addField([
            'id'            => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'user_id'       => ['type' => 'INT', 'unsigned' => true],
            'token'         => ['type' => 'VARCHAR', 'constraint' => 255],
            'expires_at'    => ['type' => 'DATETIME'],
            'created_at'    => ['type' => 'DATETIME', 'null' => true, 'default' => null]
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('user_id', false);
        $this->forge->addKey('token', false);
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('auth_tokens', true);
    }

    public function down()
    {
        $this->forge->dropTable('auth_tokens', true);
    }
}

