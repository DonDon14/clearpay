<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateActivityLogsConstraints extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();
        $isPostgres = strpos(strtolower($db->getPlatform()), 'postgre') !== false;
        
        if ($isPostgres) {
            // Drop existing constraints if they exist
            try {
                $db->query("ALTER TABLE activity_logs DROP CONSTRAINT IF EXISTS activity_logs_activity_type_check");
            } catch (\Exception $e) {
                log_message('error', 'Error dropping activity_type constraint: ' . $e->getMessage());
            }
            
            try {
                $db->query("ALTER TABLE activity_logs DROP CONSTRAINT IF EXISTS activity_logs_action_check");
            } catch (\Exception $e) {
                log_message('error', 'Error dropping action constraint: ' . $e->getMessage());
            }
            
            // Add updated constraints with all required values
            try {
                $db->query("ALTER TABLE activity_logs ADD CONSTRAINT activity_logs_activity_type_check CHECK (activity_type IN ('announcement', 'contribution', 'payment', 'payment_request', 'payer', 'user', 'refund'))");
                log_message('info', 'Successfully updated activity_logs_activity_type_check constraint');
            } catch (\Exception $e) {
                log_message('error', 'Error adding activity_type constraint: ' . $e->getMessage());
            }
            
            try {
                $db->query("ALTER TABLE activity_logs ADD CONSTRAINT activity_logs_action_check CHECK (action IN ('created', 'updated', 'deleted', 'published', 'unpublished', 'approved', 'rejected', 'processed', 'submitted'))");
                log_message('info', 'Successfully updated activity_logs_action_check constraint');
            } catch (\Exception $e) {
                log_message('error', 'Error adding action constraint: ' . $e->getMessage());
            }
        }
    }

    public function down()
    {
        $db = \Config\Database::connect();
        $isPostgres = strpos(strtolower($db->getPlatform()), 'postgre') !== false;
        
        if ($isPostgres) {
            // Revert to original constraints
            try {
                $db->query("ALTER TABLE activity_logs DROP CONSTRAINT IF EXISTS activity_logs_activity_type_check");
                $db->query("ALTER TABLE activity_logs ADD CONSTRAINT activity_logs_activity_type_check CHECK (activity_type IN ('announcement', 'contribution', 'payment', 'payer', 'user'))");
            } catch (\Exception $e) {
                log_message('error', 'Error reverting activity_type constraint: ' . $e->getMessage());
            }
            
            try {
                $db->query("ALTER TABLE activity_logs DROP CONSTRAINT IF EXISTS activity_logs_action_check");
                $db->query("ALTER TABLE activity_logs ADD CONSTRAINT activity_logs_action_check CHECK (action IN ('created', 'updated', 'deleted', 'published', 'unpublished'))");
            } catch (\Exception $e) {
                log_message('error', 'Error reverting action constraint: ' . $e->getMessage());
            }
        }
    }
}

