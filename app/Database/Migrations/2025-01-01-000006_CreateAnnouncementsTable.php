<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAnnouncementsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'              => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'title'           => ['type' => 'VARCHAR', 'constraint' => 100],
            'text'            => ['type' => 'TEXT'],
            'type'            => ['type' => 'ENUM', 'constraint' => ['general', 'urgent', 'maintenance', 'event', 'deadline'], 'default' => 'general'],
            'priority'        => ['type' => 'ENUM', 'constraint' => ['low', 'medium', 'high', 'critical'], 'default' => 'low'],
            'target_audience' => ['type' => 'ENUM', 'constraint' => ['admins', 'payers', 'both', 'all', 'staff', 'students'], 'default' => 'both'],
            'status'          => ['type' => 'ENUM', 'constraint' => ['draft', 'published', 'archived'], 'default' => 'draft'],
            'created_by'      => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'published_at'    => ['type' => 'DATETIME', 'null' => true],
            'expires_at'      => ['type' => 'DATETIME', 'null' => true],
            'created_at'      => ['type' => 'DATETIME', 'null' => true, 'default' => null],
            'updated_at'      => ['type' => 'DATETIME', 'null' => true, 'default' => null],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('created_by', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('announcements', true);
    }

    public function down()
    {
        $this->forge->dropTable('announcements', true);
    }
}

