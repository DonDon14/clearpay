<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UpdatePaymentMethodsWithIcons extends Seeder
{
    public function run()
    {
        $data = [
            [
                'name' => 'GCash',
                'icon' => 'fas fa-mobile-alt',
                'description' => 'Mobile wallet payment through GCash',
                'account_details' => 'Mobile Number: 0917-123-4567',
                'status' => 'active',
            ],
            [
                'name' => 'PayMaya',
                'icon' => 'fas fa-mobile-alt',
                'description' => 'Mobile wallet payment through PayMaya',
                'account_details' => 'Mobile Number: 0918-987-6543',
                'status' => 'active',
            ],
            [
                'name' => 'Bank Transfer',
                'icon' => 'fas fa-university',
                'description' => 'Direct bank transfer payment',
                'account_details' => 'Account Number: 1234-5678-9012',
                'status' => 'active',
            ],
            [
                'name' => 'Cash',
                'icon' => 'fas fa-money-bill-wave',
                'description' => 'Cash payment at office',
                'account_details' => 'Office Location: Main Campus',
                'status' => 'active',
            ],
            [
                'name' => 'Online Banking',
                'icon' => 'fas fa-university',
                'description' => 'Online banking transfer',
                'account_details' => 'Bank: BDO, Account: 1234567890',
                'status' => 'inactive',
            ],
        ];

        foreach ($data as $method) {
            $this->db->table('payment_methods')
                ->where('name', $method['name'])
                ->update(['icon' => $method['icon']]);
        }
    }
}
