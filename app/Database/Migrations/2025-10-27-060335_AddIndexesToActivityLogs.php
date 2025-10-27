<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddIndexesToActivityLogs extends Migration
{
    public function up()
    {
        // Add indexes for better query performance
        $this->db->query('CREATE INDEX idx_activity_logs_target_audience ON activity_logs(target_audience)');
        $this->db->query('CREATE INDEX idx_activity_logs_payer_id ON activity_logs(payer_id)');
        $this->db->query('CREATE INDEX idx_activity_logs_created_at ON activity_logs(created_at)');
        $this->db->query('CREATE INDEX idx_activity_logs_composite ON activity_logs(target_audience, payer_id, created_at)');
        
        // Add indexes for activity_read_status table
        $this->db->query('CREATE INDEX idx_activity_read_status_activity_id ON activity_read_status(activity_id)');
        $this->db->query('CREATE INDEX idx_activity_read_status_payer_id ON activity_read_status(payer_id)');
        $this->db->query('CREATE INDEX idx_activity_read_status_composite ON activity_read_status(activity_id, payer_id, is_read)');
    }

    public function down()
    {
        // Drop indexes
        $this->db->query('DROP INDEX idx_activity_logs_target_audience ON activity_logs');
        $this->db->query('DROP INDEX idx_activity_logs_payer_id ON activity_logs');
        $this->db->query('DROP INDEX idx_activity_logs_created_at ON activity_logs');
        $this->db->query('DROP INDEX idx_activity_logs_composite ON activity_logs');
        
        $this->db->query('DROP INDEX idx_activity_read_status_activity_id ON activity_read_status');
        $this->db->query('DROP INDEX idx_activity_read_status_payer_id ON activity_read_status');
        $this->db->query('DROP INDEX idx_activity_read_status_composite ON activity_read_status');
    }
}