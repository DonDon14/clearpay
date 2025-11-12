<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateActivityLogsTable extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();
        $isPostgres = strpos(strtolower($db->getPlatform()), 'postgre') !== false;
        
        $activityTypeField = $isPostgres 
            ? ['type' => 'VARCHAR', 'constraint' => 20, 'null' => false]
            : ['type' => 'ENUM', 'constraint' => ['announcement', 'contribution', 'payment', 'payer', 'user'], 'null' => false];
        $actionField = $isPostgres 
            ? ['type' => 'VARCHAR', 'constraint' => 20, 'null' => false]
            : ['type' => 'ENUM', 'constraint' => ['created', 'updated', 'deleted', 'published', 'unpublished'], 'null' => false];
        $userTypeField = $isPostgres 
            ? ['type' => 'VARCHAR', 'constraint' => 20, 'null' => false]
            : ['type' => 'ENUM', 'constraint' => ['admin', 'payer'], 'null' => false];
        $targetAudienceField = $isPostgres 
            ? ['type' => 'VARCHAR', 'constraint' => 20, 'null' => false]
            : ['type' => 'ENUM', 'constraint' => ['admins', 'payers', 'both', 'all'], 'null' => false];
        
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'activity_type' => $activityTypeField,
            'entity_type' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => false,
            ],
            'entity_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => false,
            ],
            'action' => $actionField,
            'title' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => false,
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => false,
            ],
            'old_values' => [
                'type' => 'JSON',
                'null' => true,
            ],
            'new_values' => [
                'type' => 'JSON',
                'null' => true,
            ],
            'user_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => false,
            ],
            'user_type' => $userTypeField,
            'payer_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
                'comment' => 'Specific payer ID for payer-specific notifications'
            ],
            'target_audience' => $targetAudienceField,
            'is_read' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addKey(['target_audience', 'created_at']);
        $this->forge->addKey(['entity_type', 'entity_id']);
        $this->forge->addKey(['user_id', 'user_type']);
        $this->forge->addKey('is_read');
        $this->forge->addKey('target_audience');
        $this->forge->addKey('payer_id');
        $this->forge->addKey('created_at');
        $this->forge->addKey(['target_audience', 'payer_id', 'created_at']);

        $this->forge->createTable('activity_logs');
        
        // Add CHECK constraints for PostgreSQL
        if ($isPostgres) {
            $db->query("ALTER TABLE activity_logs ADD CONSTRAINT activity_logs_activity_type_check CHECK (activity_type IN ('announcement', 'contribution', 'payment', 'payer', 'user'))");
            $db->query("ALTER TABLE activity_logs ADD CONSTRAINT activity_logs_action_check CHECK (action IN ('created', 'updated', 'deleted', 'published', 'unpublished'))");
            $db->query("ALTER TABLE activity_logs ADD CONSTRAINT activity_logs_user_type_check CHECK (user_type IN ('admin', 'payer'))");
            $db->query("ALTER TABLE activity_logs ADD CONSTRAINT activity_logs_target_audience_check CHECK (target_audience IN ('admins', 'payers', 'both', 'all'))");
        }
    }

    public function down()
    {
        $this->forge->dropTable('activity_logs');
    }
}

