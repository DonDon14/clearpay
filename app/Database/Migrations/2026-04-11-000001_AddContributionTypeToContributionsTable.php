<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddContributionTypeToContributionsTable extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();
        $isPostgres = strpos(strtolower($db->getPlatform()), 'postgre') !== false;

        $field = $isPostgres
            ? ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'contribution']
            : ['type' => 'ENUM', 'constraint' => ['contribution', 'product'], 'default' => 'contribution'];

        $this->forge->addColumn('contributions', [
            'contribution_type' => $field + ['after' => 'title'],
        ]);

        $db->table('contributions')
            ->where('contribution_type', null)
            ->update(['contribution_type' => 'contribution']);

        if ($isPostgres) {
            $db->query("ALTER TABLE contributions ADD CONSTRAINT contributions_contribution_type_check CHECK (contribution_type IN ('contribution', 'product'))");
        }
    }

    public function down()
    {
        $db = \Config\Database::connect();
        $isPostgres = strpos(strtolower($db->getPlatform()), 'postgre') !== false;

        if ($isPostgres) {
            $db->query('ALTER TABLE contributions DROP CONSTRAINT IF EXISTS contributions_contribution_type_check');
        }

        $this->forge->dropColumn('contributions', 'contribution_type');
    }
}
