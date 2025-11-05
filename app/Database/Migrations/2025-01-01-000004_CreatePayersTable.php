<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePayersTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'payer_id'          => ['type' => 'VARCHAR', 'constraint' => 50],
            'password'          => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true, 'comment' => 'Hashed password for payer authentication'],
            'payer_name'        => ['type' => 'VARCHAR', 'constraint' => 100],
            'contact_number'    => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'email_address'     => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'course_department' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'email_verified'    => ['type' => 'BOOLEAN', 'default' => false, 'null' => false],
            'verification_token' => ['type' => 'VARCHAR', 'constraint' => 10, 'null' => true],
            'reset_token'       => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true, 'comment' => 'Password reset token for forgot password functionality'],
            'reset_expires'     => ['type' => 'DATETIME', 'null' => true, 'comment' => 'Password reset token expiration time'],
            'profile_picture'   => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true, 'comment' => 'Path to profile picture file'],
            'created_at'        => ['type' => 'DATETIME', 'null' => true, 'default' => null],
            'updated_at'        => ['type' => 'DATETIME', 'null' => true, 'default' => null],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('payer_id', false, true); // Unique key on payer_id
        $this->forge->createTable('payers', true);
    }

    public function down()
    {
        $this->forge->dropTable('payers', true);
    }
}

