<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateProductsTable extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();
        $isPostgres = strpos(strtolower($db->getPlatform()), 'postgre') !== false;

        $statusField = $isPostgres
            ? ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'active']
            : ['type' => 'ENUM', 'constraint' => ['active', 'inactive'], 'default' => 'active'];

        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'title' => ['type' => 'VARCHAR', 'constraint' => 255],
            'description' => ['type' => 'TEXT', 'null' => true],
            'amount' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0.00],
            'category' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'status' => $statusField,
            'created_by' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('status');
        $this->forge->addForeignKey('created_by', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('products', true);

        if ($isPostgres) {
            $db->query("ALTER TABLE products ADD CONSTRAINT products_status_check CHECK (status IN ('active', 'inactive'))");
        }
    }

    public function down()
    {
        $db = \Config\Database::connect();
        $isPostgres = strpos(strtolower($db->getPlatform()), 'postgre') !== false;
        if ($isPostgres) {
            $db->query('ALTER TABLE products DROP CONSTRAINT IF EXISTS products_status_check');
        }
        $this->forge->dropTable('products', true);
    }
}
