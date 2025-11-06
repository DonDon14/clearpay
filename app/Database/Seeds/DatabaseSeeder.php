<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // IMPORTANT: UserSeeder MUST be called first as other seeders depend on it
        $this->call(UserSeeder::class);
        $this->call(ContributionSeeder::class);
        // PaymentMethodSeeder MUST be called to seed payment methods for validation
        $this->call(PaymentMethodSeeder::class);
        // PaymentSeeder disabled - it needs to be updated for the new table structure
        // $this->call(PaymentSeeder::class);
    }
}