<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class PayerSeeder extends Seeder
{
    public function run()
    {
        // Sample data for the payers table
        $data = [
            [
                'contribution_id'   => 1,
                'payer_id'          => 'PAYER001',
                'payer_name'        => 'John Doe',
                'contact_number'    => '09171234567',
                'email_address'     => 'johndoe@example.com',
                'amount_paid'       => 1000.00,
                'payment_method'    => 'cash',
                'payment_status'    => 'paid',
                'is_partial_payment'=> false,
                'remaining_balance' => 0.00,
                'parent_payment_id' => null,
                'payment_sequence'  => 1,
                'reference_number'  => 'REF12345',
                'receipt_number'    => 'RCPT12345',
                'qr_receipt_path'   => null,
                'recorded_by'       => 1, // assuming user_id 1 exists
                'payment_date'      => date('Y-m-d H:i:s'),
                'created_at'        => date('Y-m-d H:i:s'),
                'updated_at'        => date('Y-m-d H:i:s')
            ],
            [
                'contribution_id'   => 2,
                'payer_id'          => 'PAYER002',
                'payer_name'        => 'Jane Smith',
                'contact_number'    => '09179876543',
                'email_address'     => 'janesmith@example.com',
                'amount_paid'       => 500.00,
                'payment_method'    => 'online',
                'payment_status'    => 'pending',
                'is_partial_payment'=> true,
                'remaining_balance' => 200.00,
                'parent_payment_id' => null,
                'payment_sequence'  => 1,
                'reference_number'  => 'REF67890',
                'receipt_number'    => 'RCPT67890',
                'qr_receipt_path'   => null,
                'recorded_by'       => 1,
                'payment_date'      => date('Y-m-d H:i:s'),
                'created_at'        => date('Y-m-d H:i:s'),
                'updated_at'        => date('Y-m-d H:i:s')
            ]
        ];

        // Insert data
        $this->db->table('payers')->insertBatch($data);
    }
}
