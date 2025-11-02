<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddContributionCodeToContributions extends Migration
{
    public function up()
    {
        $fields = [
            'contribution_code' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'after'      => 'title',
            ],
        ];
        $this->forge->addColumn('contributions', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('contributions', 'contribution_code');
    }
}
