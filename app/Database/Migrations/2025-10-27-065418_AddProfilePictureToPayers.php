<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddProfilePictureToPayers extends Migration
{
    public function up()
    {
        $this->forge->addColumn('payers', [
            'profile_picture' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'comment' => 'Path to profile picture file'
            ]
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('payers', 'profile_picture');
    }
}