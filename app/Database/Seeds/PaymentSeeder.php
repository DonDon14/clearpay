<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class PaymentSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'contribution_id' => 1, // Monthly Fee
                'payer_id' => 'STU-2024-001',
                'payer_name' => 'John Doe',
                'contact_number' => '09123456789',
                'email_address' => 'john.doe@email.com',
                'amount_paid' => 1000.00,
                'payment_method' => 'cash',
                'payment_status' => 'fully paid',
                'is_partial_payment' => false,
                'remaining_balance' => 0.00,
                'parent_payment_id' => null,
                'payment_sequence' => 1,
                'reference_number' => 'REF-' . date('Ymd') . '-001',
                'recorded_by' => 1,
                'payment_date' => date('Y-m-d H:i:s', strtotime('-2 hours')),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'contribution_id' => 2, // Special Donation
                'payer_id' => 'STU-2024-002',
                'payer_name' => 'Jane Smith',
                'contact_number' => '09987654321',
                'email_address' => 'jane.smith@email.com',
                'amount_paid' => 300.00,
                'payment_method' => 'online',
                'payment_status' => 'partial',
                'is_partial_payment' => true,
                'remaining_balance' => 200.00,
                'parent_payment_id' => null,
                'payment_sequence' => 1,
                'reference_number' => 'REF-' . date('Ymd') . '-002',
                'recorded_by' => 1,
                'payment_date' => date('Y-m-d H:i:s', strtotime('-4 hours')),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'contribution_id' => 1, // Monthly Fee
                'payer_id' => 'STU-2024-003',
                'payer_name' => 'Mike Johnson',
                'contact_number' => '09555666777',
                'email_address' => 'mike.johnson@email.com',
                'amount_paid' => 1000.00,
                'payment_method' => 'bank',
                'payment_status' => 'fully paid',
                'is_partial_payment' => false,
                'remaining_balance' => 0.00,
                'parent_payment_id' => null,
                'payment_sequence' => 1,
                'reference_number' => 'REF-' . date('Ymd') . '-003',
                'recorded_by' => 1,
                'payment_date' => date('Y-m-d H:i:s', strtotime('-6 hours')),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'contribution_id' => 2, // Special Donation
                'payer_id' => 'STU-2024-004',
                'payer_name' => 'Sarah Wilson',
                'contact_number' => '09111222333',
                'email_address' => 'sarah.wilson@email.com',
                'amount_paid' => 500.00,
                'payment_method' => 'cash',
                'payment_status' => 'fully paid',
                'is_partial_payment' => false,
                'remaining_balance' => 0.00,
                'parent_payment_id' => null,
                'payment_sequence' => 1,
                'reference_number' => 'REF-' . date('Ymd') . '-004',
                'recorded_by' => 1,
                'payment_date' => date('Y-m-d H:i:s', strtotime('-8 hours')),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]
        ];

        // Using Query Builder
        $this->db->table('payers')->insertBatch($data);
    }
}