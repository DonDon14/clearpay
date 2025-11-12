<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    public function run()
    {
        // Check if payment methods already exist
        $existingCount = $this->db->table('payment_methods')->countAllResults();
        
        if ($existingCount > 0) {
            echo "Payment methods already exist ({$existingCount} found). Skipping payment method creation.\n";
            return;
        }

        $data = [
            [
                'name' => 'GCash',
                'description' => 'Mobile wallet payment through GCash',
                'account_details' => 'Mobile Number: 0917-123-4567',
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'PayMaya',
                'description' => 'Mobile wallet payment through PayMaya',
                'account_details' => 'Mobile Number: 0918-987-6543',
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Bank Transfer',
                'description' => 'Direct bank transfer payment',
                'account_details' => 'Account Number: 1234-5678-9012',
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Cash',
                'description' => 'Cash payment at office',
                'account_details' => 'Office Location: Main Campus',
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Online Banking',
                'description' => 'Online banking transfer',
                'account_details' => 'Bank: BDO, Account: 1234567890',
                'status' => 'inactive',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        $this->db->table('payment_methods')->insertBatch($data);
        echo "Payment methods seeded successfully.\n";
    }
}
