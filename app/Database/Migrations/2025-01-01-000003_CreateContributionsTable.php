<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateContributionsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'title'             => ['type' => 'VARCHAR', 'constraint' => 100],
            'contribution_code' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'description'       => ['type' => 'TEXT', 'null' => true],
            'amount'            => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0.00],
            'grand_total'       => ['type' => 'DECIMAL', 'constraint' => '12,2', 'null' => true, 'default' => null, 'comment' => 'Grand total target to be collected across all payers'],
            'category'          => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'status'            => ['type' => 'ENUM', 'constraint' => ['active', 'inactive'], 'default' => 'active'],
            'created_by'        => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'cost_price'        => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0.00],
            'profit_amount'     => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0.00],
            'created_at'        => ['type' => 'DATETIME', 'null' => true, 'default' => null],
            'updated_at'        => ['type' => 'DATETIME', 'null' => true, 'default' => null],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('created_by', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('contributions', true);
    }

    public function down()
    {
        $this->forge->dropTable('contributions', true);
    }
}

