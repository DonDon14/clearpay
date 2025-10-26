<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateAnnouncementsTable extends Migration
{
    public function up()
    {
        // Add 'deadline' to type enum
        $this->db->query("ALTER TABLE announcements MODIFY COLUMN type ENUM('general', 'urgent', 'maintenance', 'event', 'deadline') DEFAULT 'general'");
        
        // Update target_audience to include 'all' and 'staff'
        $this->db->query("ALTER TABLE announcements MODIFY COLUMN target_audience ENUM('admins', 'payers', 'both', 'all', 'staff', 'students') DEFAULT 'both'");
    }

    public function down()
    {
        // Revert to original enum values
        $this->db->query("ALTER TABLE announcements MODIFY COLUMN type ENUM('general', 'urgent', 'maintenance', 'event') DEFAULT 'general'");
        $this->db->query("ALTER TABLE announcements MODIFY COLUMN target_audience ENUM('admins', 'payers', 'both') DEFAULT 'both'");
    }
}
