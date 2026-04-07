<?php

namespace Tests\Support\Database\Seeds;

use CodeIgniter\Database\Seeder;

class WorkflowSmokeSeeder extends Seeder
{
    public function run(): void
    {
        $now = date('Y-m-d H:i:s');

        $this->db->table('users')->insertBatch([
            [
                'id' => 1,
                'name' => 'Admin Smoke',
                'username' => 'adminsmoke',
                'email' => 'adminsmoke@example.com',
                'password' => password_hash('Secret123!', PASSWORD_DEFAULT),
                'role' => 'admin',
                'status' => 'approved',
                'is_active' => 1,
                'email_verified' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 2,
                'name' => 'Officer Pending',
                'username' => 'officerpending',
                'email' => 'officerpending@example.com',
                'password' => password_hash('Secret123!', PASSWORD_DEFAULT),
                'role' => 'officer',
                'status' => 'pending',
                'is_active' => 1,
                'email_verified' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        $this->db->table('payers')->insertBatch([
            [
                'id' => 1,
                'payer_id' => '2024-0001',
                'password' => password_hash('payer-one', PASSWORD_DEFAULT),
                'payer_name' => 'Lianne Santos',
                'contact_number' => '09170000001',
                'email_address' => 'lianne@example.com',
                'course_department' => 'BSIT',
                'email_verified' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 2,
                'payer_id' => '2024-0002',
                'password' => password_hash('payer-two', PASSWORD_DEFAULT),
                'payer_name' => 'Marco Reyes',
                'contact_number' => '09170000002',
                'email_address' => 'marco@example.com',
                'course_department' => 'BSCS',
                'email_verified' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        $this->db->table('contributions')->insertBatch([
            [
                'id' => 1,
                'title' => 'Partial Contribution',
                'description' => 'Used for partial payment warning smoke test',
                'amount' => 100.00,
                'category' => 'General',
                'status' => 'active',
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 2,
                'title' => 'Full Contribution',
                'description' => 'Used for fully paid warning smoke test',
                'amount' => 100.00,
                'category' => 'General',
                'status' => 'active',
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 3,
                'title' => 'Open Contribution',
                'description' => 'Used for save payment and payer request smoke tests',
                'amount' => 120.00,
                'category' => 'General',
                'status' => 'active',
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        $this->db->table('payment_methods')->insertBatch([
            [
                'id' => 1,
                'name' => 'Cash',
                'description' => 'Cash payments',
                'status' => 'active',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 2,
                'name' => 'GCash',
                'description' => 'Digital wallet payments',
                'status' => 'active',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        $this->db->table('refund_methods')->insertBatch([
            [
                'id' => 1,
                'name' => 'Cash',
                'code' => 'cash',
                'description' => 'Cash refund',
                'status' => 'active',
                'sort_order' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 2,
                'name' => 'GCash',
                'code' => 'gcash',
                'description' => 'GCash refund',
                'status' => 'active',
                'sort_order' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 3,
                'name' => 'Bank Transfer',
                'code' => 'bank_transfer',
                'description' => 'Bank transfer refund',
                'status' => 'active',
                'sort_order' => 3,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        $this->db->table('payments')->insertBatch([
            [
                'id' => 1,
                'payer_id' => 1,
                'contribution_id' => 1,
                'amount_paid' => 40.00,
                'payment_method' => 'Cash',
                'payment_status' => 'partial',
                'is_partial_payment' => 1,
                'remaining_balance' => 60.00,
                'payment_sequence' => 1,
                'reference_number' => 'REF-SMOKE-1',
                'receipt_number' => 'RCPT-SMOKE-1',
                'recorded_by' => 1,
                'payment_date' => '2026-04-01 08:00:00',
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null,
            ],
            [
                'id' => 2,
                'payer_id' => 1,
                'contribution_id' => 2,
                'amount_paid' => 100.00,
                'payment_method' => 'Cash',
                'payment_status' => 'fully paid',
                'is_partial_payment' => 0,
                'remaining_balance' => 0.00,
                'payment_sequence' => 1,
                'reference_number' => 'REF-SMOKE-2',
                'receipt_number' => 'RCPT-SMOKE-2',
                'recorded_by' => 1,
                'payment_date' => '2026-04-02 08:00:00',
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null,
            ],
        ]);
    }
}
