<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Check if admin user already exists
        $existingUser = $this->db->table('users')
            ->where('username', 'admin')
            ->orWhere('email', 'admin@example.com')
            ->get()
            ->getRow();

        if ($existingUser) {
            echo "Admin user already exists. Skipping user creation.\n";
            return;
        }

        $data = [
            'name'     => 'Admin User',
            'username' => 'admin',
            'email'    => 'admin@example.com',
            'password' => password_hash('admin123', PASSWORD_DEFAULT),
            'role'     => 'admin',
        ];

        $this->db->table('users')->insert($data);

        echo "User seeding executed.\n";
    }
}