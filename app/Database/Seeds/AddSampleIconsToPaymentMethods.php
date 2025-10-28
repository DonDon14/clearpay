<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class AddSampleIconsToPaymentMethods extends Seeder
{
    public function run()
    {
        // Update the database with sample icon paths for demonstration
        $sampleIcons = [
            'GCash' => 'uploads/payment_methods/gcash_sample.png',
            'PayMaya' => 'uploads/payment_methods/paymaya_sample.png', 
            'Bank Transfer' => 'uploads/payment_methods/bank_sample.png',
            'Cash' => 'uploads/payment_methods/cash_sample.png',
            'Online Banking' => 'uploads/payment_methods/online_banking_sample.png'
        ];

        foreach ($sampleIcons as $methodName => $iconPath) {
            $this->db->table('payment_methods')
                ->where('name', $methodName)
                ->update(['icon' => $iconPath]);
        }
    }
}
