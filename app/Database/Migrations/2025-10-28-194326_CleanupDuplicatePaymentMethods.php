<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CleanupDuplicatePaymentMethods extends Migration
{
    public function up()
    {
        // Clean up duplicate payment methods
        // First, let's see what we have
        $query = $this->db->query("SELECT id, name, status FROM payment_methods ORDER BY name");
        $results = $query->getResultArray();
        
        // Group by name to find duplicates
        $duplicates = [];
        foreach ($results as $row) {
            $name = $row['name'];
            if (!isset($duplicates[$name])) {
                $duplicates[$name] = [];
            }
            $duplicates[$name][] = $row['id'];
        }
        
        // Remove duplicates, keeping the first one
        foreach ($duplicates as $name => $ids) {
            if (count($ids) > 1) {
                // Keep the first ID, delete the rest
                $keepId = array_shift($ids);
                foreach ($ids as $deleteId) {
                    $this->db->query("DELETE FROM payment_methods WHERE id = ?", [$deleteId]);
                }
            }
        }
        
        // Also ensure we have the correct active payment methods
        $this->db->query("DELETE FROM payment_methods"); // Clear all
        
        // Insert the correct payment methods
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
        ];
        
        $this->db->table('payment_methods')->insertBatch($data);
    }

    public function down()
    {
        // This migration is not easily reversible
        // The down method is intentionally left empty
    }
}
