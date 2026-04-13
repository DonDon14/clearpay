<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddItemImagesAndProductRefunds extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();
        $platform = strtolower($db->getPlatform());
        $isPostgres = strpos($platform, 'postgre') !== false;

        if (!$db->fieldExists('image_path', 'contributions')) {
            $this->forge->addColumn('contributions', [
                'image_path' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'null' => true,
                    'after' => 'description',
                ],
            ]);
        }

        if (!$db->fieldExists('image_path', 'products')) {
            $this->forge->addColumn('products', [
                'image_path' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'null' => true,
                    'after' => 'description',
                ],
            ]);
        }

        if (!$db->fieldExists('product_id', 'refunds')) {
            $this->forge->addColumn('refunds', [
                'product_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'null' => true,
                    'after' => 'contribution_id',
                ],
            ]);
        }

        if ($db->fieldExists('contribution_id', 'refunds')) {
            if ($isPostgres) {
                $db->query('ALTER TABLE refunds ALTER COLUMN contribution_id DROP NOT NULL');
            } else {
                $this->forge->modifyColumn('refunds', [
                    'contribution_id' => [
                        'type' => 'INT',
                        'constraint' => 11,
                        'unsigned' => true,
                        'null' => true,
                    ],
                ]);
            }
        }

        $foreignKeys = $db->getForeignKeyData('refunds');
        $hasProductFk = false;
        foreach ($foreignKeys as $key) {
            if (($key->column_name ?? null) === 'product_id') {
                $hasProductFk = true;
                break;
            }
        }

        if (!$hasProductFk) {
            $this->forge->addKey('product_id');
            $this->forge->addForeignKey('product_id', 'products', 'id', 'SET NULL', 'CASCADE');
            $this->forge->processIndexes('refunds');
        }

        if ($db->fieldExists('product_id', 'refunds')) {
            if ($isPostgres) {
                $db->query('
                    UPDATE refunds
                    SET product_id = payments.product_id
                    FROM payments
                    WHERE refunds.payment_id = payments.id
                      AND payments.product_id IS NOT NULL
                      AND refunds.product_id IS NULL
                ');
            } else {
                $db->query('
                    UPDATE refunds
                    JOIN payments ON refunds.payment_id = payments.id
                    SET refunds.product_id = payments.product_id
                    WHERE payments.product_id IS NOT NULL
                      AND refunds.product_id IS NULL
                ');
            }
        }
    }

    public function down()
    {
        $db = \Config\Database::connect();
        $platform = strtolower($db->getPlatform());
        $isPostgres = strpos($platform, 'postgre') !== false;

        if ($db->fieldExists('product_id', 'refunds')) {
            $foreignKeys = $db->getForeignKeyData('refunds');
            foreach ($foreignKeys as $key) {
                if (($key->column_name ?? null) === 'product_id' && !empty($key->constraint_name)) {
                    $this->forge->dropForeignKey('refunds', $key->constraint_name);
                }
            }

            $this->forge->dropColumn('refunds', 'product_id');
        }

        if ($db->fieldExists('contribution_id', 'refunds')) {
            if ($isPostgres) {
                $db->query('ALTER TABLE refunds ALTER COLUMN contribution_id SET NOT NULL');
            } else {
                $this->forge->modifyColumn('refunds', [
                    'contribution_id' => [
                        'type' => 'INT',
                        'constraint' => 11,
                        'unsigned' => true,
                        'null' => false,
                    ],
                ]);
            }
        }

        if ($db->fieldExists('image_path', 'products')) {
            $this->forge->dropColumn('products', 'image_path');
        }

        if ($db->fieldExists('image_path', 'contributions')) {
            $this->forge->dropColumn('contributions', 'image_path');
        }
    }
}
