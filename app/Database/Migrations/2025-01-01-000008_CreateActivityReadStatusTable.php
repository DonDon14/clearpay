<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateActivityReadStatusTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'activity_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'comment' => 'Reference to activity_logs.id'
            ],
            'payer_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'comment' => 'Reference to payers.id'
            ],
            'is_read' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'comment' => '0 = unread, 1 = read'
            ],
            'read_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'comment' => 'When the notification was read'
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ]
        ]);
        
        $this->forge->addKey('id', true);
        $this->forge->addKey(['activity_id', 'payer_id'], false, true); // Unique constraint
        $this->forge->addKey('payer_id');
        $this->forge->addKey('is_read');
        $this->forge->addKey('activity_id');
        $this->forge->addKey(['activity_id', 'payer_id', 'is_read']);
        
        $this->forge->addForeignKey('activity_id', 'activity_logs', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('payer_id', 'payers', 'id', 'CASCADE', 'CASCADE');
        
        $this->forge->createTable('activity_read_status');
    }

    public function down()
    {
        $this->forge->dropTable('activity_read_status');
    }
}

