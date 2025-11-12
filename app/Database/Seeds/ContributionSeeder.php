<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ContributionSeeder extends Seeder
{
    public function run()
    {
        // Check if contributions already exist
        $existingCount = $this->db->table('contributions')->countAllResults();
        
        if ($existingCount > 0) {
            echo "Contributions already exist ({$existingCount} found). Skipping contribution creation.\n";
            return;
        }

        // Ensure admin user exists (created_by = 1)
        $adminUser = $this->db->table('users')->where('id', 1)->get()->getRow();
        if (!$adminUser) {
            echo "Warning: Admin user (ID: 1) does not exist. Skipping contribution seeding.\n";
            return;
        }

        $data = [
            [
                'title'       => 'Monthly Fee',
                'description' => 'Monthly contribution for October',
                'amount'      => 1000.00,
                'category'    => 'fee',
                'status'      => 'active',
                'created_by'  => 1,
                'cost_price'  => 0.00,
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ],
            [
                'title'       => 'Special Donation',
                'description' => 'One-time donation',
                'amount'      => 500.00,
                'category'    => 'donation',
                'status'      => 'active',
                'created_by'  => 1,
                'cost_price'  => 0.00,
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ],
        ];

        $this->db->table('contributions')->insertBatch($data);
        echo "Contributions seeded successfully.\n";
    }
}
