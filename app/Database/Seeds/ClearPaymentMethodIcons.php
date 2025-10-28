<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ClearPaymentMethodIcons extends Seeder
{
    public function run()
    {
        // Clear all icon paths to show placeholders
        $this->db->table('payment_methods')->update(['icon' => null]);
    }
}
