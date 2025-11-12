<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateEmailSettingsTable extends Migration
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
            'from_email' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => false,
            ],
            'from_name' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'default' => 'ClearPay',
            ],
            'protocol' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'default' => 'smtp',
            ],
            'smtp_host' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => false,
            ],
            'smtp_user' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => false,
            ],
            'smtp_pass' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'comment' => 'Encrypted SMTP password',
            ],
            'smtp_port' => [
                'type' => 'INT',
                'constraint' => 5,
                'default' => 587,
            ],
            'smtp_crypto' => [
                'type' => 'VARCHAR',
                'constraint' => 10,
                'default' => 'tls',
            ],
            'smtp_timeout' => [
                'type' => 'INT',
                'constraint' => 5,
                'default' => 30,
            ],
            'mail_type' => [
                'type' => 'VARCHAR',
                'constraint' => 10,
                'default' => 'html',
            ],
            'charset' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'default' => 'UTF-8',
            ],
            'is_active' => [
                'type' => 'BOOLEAN',
                'default' => true,
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

        $this->forge->addKey('id', true);
        $this->forge->addKey('is_active');
        $this->forge->createTable('email_settings');

        // Insert default settings (will be overridden by admin)
        $db = \Config\Database::connect();
        $db->table('email_settings')->insert([
            'from_email' => 'project.clearpay@gmail.com',
            'from_name' => 'ClearPay',
            'protocol' => 'smtp',
            'smtp_host' => 'smtp.gmail.com',
            'smtp_user' => 'project.clearpay@gmail.com',
            'smtp_pass' => '', // Will be set by admin
            'smtp_port' => 587,
            'smtp_crypto' => 'tls',
            'smtp_timeout' => 30,
            'mail_type' => 'html',
            'charset' => 'UTF-8',
            'is_active' => true,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function down()
    {
        $this->forge->dropTable('email_settings');
    }
}

