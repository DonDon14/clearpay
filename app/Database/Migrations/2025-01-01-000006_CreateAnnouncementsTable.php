<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAnnouncementsTable extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();
        $isPostgres = strpos(strtolower($db->getPlatform()), 'postgre') !== false;
        
        $typeField = $isPostgres 
            ? ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'general']
            : ['type' => 'ENUM', 'constraint' => ['general', 'urgent', 'maintenance', 'event', 'deadline'], 'default' => 'general'];
        $priorityField = $isPostgres 
            ? ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'low']
            : ['type' => 'ENUM', 'constraint' => ['low', 'medium', 'high', 'critical'], 'default' => 'low'];
        $targetAudienceField = $isPostgres 
            ? ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'both']
            : ['type' => 'ENUM', 'constraint' => ['admins', 'payers', 'both', 'all', 'staff', 'students'], 'default' => 'both'];
        $statusField = $isPostgres 
            ? ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'draft']
            : ['type' => 'ENUM', 'constraint' => ['draft', 'published', 'archived'], 'default' => 'draft'];
        
        $this->forge->addField([
            'id'              => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'title'           => ['type' => 'VARCHAR', 'constraint' => 100],
            'text'            => ['type' => 'TEXT'],
            'type'            => $typeField,
            'priority'        => $priorityField,
            'target_audience' => $targetAudienceField,
            'status'          => $statusField,
            'created_by'      => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'published_at'    => ['type' => 'DATETIME', 'null' => true],
            'expires_at'      => ['type' => 'DATETIME', 'null' => true],
            'created_at'      => ['type' => 'DATETIME', 'null' => true, 'default' => null],
            'updated_at'      => ['type' => 'DATETIME', 'null' => true, 'default' => null],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('created_by', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('announcements', true);
        
        // Add CHECK constraints for PostgreSQL
        if ($isPostgres) {
            $db->query("ALTER TABLE announcements ADD CONSTRAINT announcements_type_check CHECK (type IN ('general', 'urgent', 'maintenance', 'event', 'deadline'))");
            $db->query("ALTER TABLE announcements ADD CONSTRAINT announcements_priority_check CHECK (priority IN ('low', 'medium', 'high', 'critical'))");
            $db->query("ALTER TABLE announcements ADD CONSTRAINT announcements_target_audience_check CHECK (target_audience IN ('admins', 'payers', 'both', 'all', 'staff', 'students'))");
            $db->query("ALTER TABLE announcements ADD CONSTRAINT announcements_status_check CHECK (status IN ('draft', 'published', 'archived'))");
        }
    }

    public function down()
    {
        $this->forge->dropTable('announcements', true);
    }
}

