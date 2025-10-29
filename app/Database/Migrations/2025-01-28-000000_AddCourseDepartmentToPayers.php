<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCourseDepartmentToPayers extends Migration
{
    public function up()
    {
        // Add course_department column to payers table
        $fields = [
            'course_department' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
                'after' => 'email_address'
            ]
        ];
        
        $this->forge->addColumn('payers', $fields);
    }

    public function down()
    {
        // Remove course_department column
        $this->forge->dropColumn('payers', 'course_department');
    }
}

