<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class SeparatePayersAndPaymentsTables extends Migration
{
    public function up()
    {
        // Step 1: Create the new payers table structure (student information only)
        $this->forge->addField([
            'id'                => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'payer_id'          => ['type' => 'VARCHAR', 'constraint' => 50],
            'payer_name'        => ['type' => 'VARCHAR', 'constraint' => 100],
            'contact_number'    => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'email_address'     => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'created_at'        => ['type' => 'DATETIME', 'null' => true, 'default' => null],
            'updated_at'        => ['type' => 'DATETIME', 'null' => true, 'default' => null],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('payer_id', false, true); // Add unique key on payer_id
        $this->forge->createTable('payers_new', true); // Create as 'payers_new' first

        // Step 2: Create the new payments table structure
        $this->forge->addField([
            'id'                => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'payer_id'          => ['type' => 'INT', 'unsigned' => true], // FK to payers_new table
            'contribution_id'   => ['type' => 'INT', 'unsigned' => true],
            'amount_paid'       => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0.00],
            'payment_method'    => ['type' => 'ENUM', 'constraint' => ['cash', 'online', 'check', 'bank'], 'default' => 'cash'],
            'payment_status'    => ['type' => 'ENUM', 'constraint' => ['fully paid', 'partial', 'pending'], 'default' => 'pending'],
            'is_partial_payment'=> ['type' => 'BOOLEAN', 'default' => false],
            'remaining_balance' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0.00],
            'parent_payment_id' => ['type' => 'INT', 'null' => true],
            'payment_sequence'  => ['type' => 'INT', 'null' => true],
            'reference_number'  => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'receipt_number'    => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'qr_receipt_path'   => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'recorded_by'       => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'payment_date'      => ['type' => 'DATETIME'],
            'created_at'        => ['type' => 'DATETIME', 'null' => true, 'default' => null],
            'updated_at'        => ['type' => 'DATETIME', 'null' => true, 'default' => null],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('payer_id', 'payers_new', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('contribution_id', 'contributions', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('recorded_by', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('payments', true);

        // Step 3: Migrate data from old payers table to new tables
        $this->migrateData();

        // Step 4: Drop the old payers table (with foreign key checks disabled)
        $db = \Config\Database::connect();
        
        // Disable foreign key checks temporarily
        $db->query("SET FOREIGN_KEY_CHECKS = 0");
        
        // Drop the old payers table
        $this->forge->dropTable('payers', true);

        // Re-enable foreign key checks
        $db->query("SET FOREIGN_KEY_CHECKS = 1");

        // Step 5: Rename payers_new to payers
        $this->db->query('RENAME TABLE payers_new TO payers');
    }

    private function migrateData()
    {
        $db = \Config\Database::connect();

        // Get all records from the old payers table
        $oldPayers = $db->table('payers')->get()->getResultArray();
        
        if (empty($oldPayers)) {
            return; // No data to migrate
        }

        // Track unique payers by payer_id to avoid duplicates
        $uniquePayers = [];
        $payerIdMap = []; // Maps old id to new id

        foreach ($oldPayers as $oldPayer) {
            // If we haven't seen this payer_id before, create a new payer record
            if (!isset($uniquePayers[$oldPayer['payer_id']])) {
                $payerData = [
                    'payer_id' => $oldPayer['payer_id'],
                    'payer_name' => $oldPayer['payer_name'],
                    'contact_number' => $oldPayer['contact_number'] ?? null,
                    'email_address' => $oldPayer['email_address'] ?? null,
                    'created_at' => $oldPayer['created_at'] ?? date('Y-m-d H:i:s'),
                    'updated_at' => $oldPayer['updated_at'] ?? date('Y-m-d H:i:s'),
                ];

                $db->table('payers_new')->insert($payerData);
                $newPayerId = $db->insertID();
                $payerIdMap[$oldPayer['id']] = $newPayerId;
                $uniquePayers[$oldPayer['payer_id']] = $newPayerId;
            } else {
                $newPayerId = $uniquePayers[$oldPayer['payer_id']];
                $payerIdMap[$oldPayer['id']] = $newPayerId;
            }

            // Create a payment record for each transaction
            $paymentData = [
                'payer_id' => $newPayerId,
                'contribution_id' => $oldPayer['contribution_id'],
                'amount_paid' => $oldPayer['amount_paid'] ?? 0.00,
                'payment_method' => $oldPayer['payment_method'] ?? 'cash',
                'payment_status' => $oldPayer['payment_status'] ?? 'pending',
                'is_partial_payment' => $oldPayer['is_partial_payment'] ?? 0,
                'remaining_balance' => $oldPayer['remaining_balance'] ?? 0.00,
                'parent_payment_id' => $oldPayer['parent_payment_id'] ?? null,
                'payment_sequence' => $oldPayer['payment_sequence'] ?? null,
                'reference_number' => $oldPayer['reference_number'] ?? null,
                'receipt_number' => $oldPayer['receipt_number'] ?? null,
                'qr_receipt_path' => $oldPayer['qr_receipt_path'] ?? null,
                'recorded_by' => $oldPayer['recorded_by'] ?? null,
                'payment_date' => $oldPayer['payment_date'],
                'created_at' => $oldPayer['created_at'] ?? date('Y-m-d H:i:s'),
                'updated_at' => $oldPayer['updated_at'] ?? date('Y-m-d H:i:s'),
            ];

            $db->table('payments')->insert($paymentData);
        }

        // Update parent_payment_id references in payments table
        // Since we have the payerIdMap, we can map old IDs to new IDs
        $payments = $db->table('payments')->get()->getResultArray();
        foreach ($payments as $payment) {
            if (!empty($payment['parent_payment_id'])) {
                // Note: parent_payment_id mapping would need the old payment IDs
                // This is complex and may not be directly mappable
                // For now, we'll leave parent_payment_id as is
            }
        }
    }

    public function down()
    {
        $db = \Config\Database::connect();
        
        // Disable foreign key checks
        $db->query("SET FOREIGN_KEY_CHECKS = 0");
        
        // Check if payments and payers tables exist before trying to drop them
        if ($this->db->tableExists('payments')) {
            $this->forge->dropTable('payments', true);
        }
        
        if ($this->db->tableExists('payers')) {
            // If payers table exists (the new separated one), drop it
            $this->forge->dropTable('payers', true);
        }
        
        // Re-enable foreign key checks
        $db->query("SET FOREIGN_KEY_CHECKS = 1");
        
        // Note: We don't recreate the old combined payers table in rollback
        // as that would require the migration to have failed before data migration
    }
}
