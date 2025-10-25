<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ContributionSeeder extends Seeder
{
    public function run()
    {
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
    }
}
