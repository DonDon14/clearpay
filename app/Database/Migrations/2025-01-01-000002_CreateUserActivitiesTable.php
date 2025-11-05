<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUserActivitiesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'            => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'user_id'       => ['type' => 'INT', 'unsigned' => true],
            'activity_type' => ['type' => 'ENUM', 'constraint' => ['create', 'update', 'delete', 'login', 'logout']],
            'entity_type'   => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'entity_id'     => ['type' => 'INT', 'null' => true],
            'description'   => ['type' => 'TEXT', 'null' => true],
            'metadata'      => ['type' => 'JSON', 'null' => true],
            'ip_address'    => ['type' => 'VARCHAR', 'constraint' => 45, 'null' => true],
            'user_agent'    => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'created_at'    => ['type' => 'DATETIME', 'null' => true, 'default' => null],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('user_activities', true);
    }

    public function down()
    {
        $this->forge->dropTable('user_activities', true);
    }
}

