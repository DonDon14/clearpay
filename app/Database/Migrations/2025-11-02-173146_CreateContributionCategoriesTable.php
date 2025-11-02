<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateContributionCategoriesTable extends Migration
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
            'name' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => false,
                'comment' => 'Display name of the category (e.g., Tuition Fee, Library Fee)',
            ],
            'code' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => false,
                'comment' => 'System code for the category (e.g., tuition, library, laboratory)',
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Description of the category',
            ],
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['active', 'inactive'],
                'default' => 'active',
                'comment' => 'Status of the category',
            ],
            'sort_order' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
                'comment' => 'Order for displaying categories',
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
        $this->forge->addUniqueKey('code');
        $this->forge->addKey('status');
        $this->forge->createTable('contribution_categories', true);

        // Insert default categories
        $defaultCategories = [
            [
                'name' => 'Tuition Fee',
                'code' => 'tuition',
                'description' => 'Tuition fee category',
                'status' => 'active',
                'sort_order' => 1,
            ],
            [
                'name' => 'Library Fee',
                'code' => 'library',
                'description' => 'Library fee category',
                'status' => 'active',
                'sort_order' => 2,
            ],
            [
                'name' => 'Laboratory Fee',
                'code' => 'laboratory',
                'description' => 'Laboratory fee category',
                'status' => 'active',
                'sort_order' => 3,
            ],
            [
                'name' => 'Registration Fee',
                'code' => 'registration',
                'description' => 'Registration fee category',
                'status' => 'active',
                'sort_order' => 4,
            ],
            [
                'name' => 'Development Fee',
                'code' => 'development',
                'description' => 'Development fee category',
                'status' => 'active',
                'sort_order' => 5,
            ],
            [
                'name' => 'Medical Fee',
                'code' => 'medical',
                'description' => 'Medical fee category',
                'status' => 'active',
                'sort_order' => 6,
            ],
            [
                'name' => 'Guidance Fee',
                'code' => 'guidance',
                'description' => 'Guidance fee category',
                'status' => 'active',
                'sort_order' => 7,
            ],
            [
                'name' => 'Athletic Fee',
                'code' => 'athletic',
                'description' => 'Athletic fee category',
                'status' => 'active',
                'sort_order' => 8,
            ],
            [
                'name' => 'Computer Fee',
                'code' => 'computer',
                'description' => 'Computer fee category',
                'status' => 'active',
                'sort_order' => 9,
            ],
            [
                'name' => 'Damage Fee',
                'code' => 'damage',
                'description' => 'Damage fee category',
                'status' => 'active',
                'sort_order' => 10,
            ],
            [
                'name' => 'Other',
                'code' => 'other',
                'description' => 'Other category',
                'status' => 'active',
                'sort_order' => 11,
            ],
        ];

        $this->db->table('contribution_categories')->insertBatch($defaultCategories);
    }

    public function down()
    {
        $this->forge->dropTable('contribution_categories', true);
    }
}