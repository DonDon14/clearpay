<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCustomInstructionsToPaymentMethods extends Migration
{
    public function up()
    {
        // Add custom payment instructions fields to payment_methods table
        $this->forge->addColumn('payment_methods', [
            'account_number' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
                'comment' => 'Account number (e.g., GCash number, bank account)'
            ],
            'account_name' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
                'comment' => 'Account holder name'
            ],
            'qr_code_path' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'comment' => 'Path to uploaded QR code image'
            ],
            'custom_instructions' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Custom payment instructions (HTML allowed)'
            ],
            'reference_prefix' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => true,
                'default' => 'CP',
                'comment' => 'Prefix for payment reference numbers'
            ]
        ]);
    }

    public function down()
    {
        // Remove the custom instructions fields
        $this->forge->dropColumn('payment_methods', [
            'account_number',
            'account_name', 
            'qr_code_path',
            'custom_instructions',
            'reference_prefix'
        ]);
    }
}
