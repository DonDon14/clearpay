<?php

namespace App\Models;

use CodeIgniter\Model;

class AnalyticsFindingReviewModel extends Model
{
    protected $table = 'analytics_finding_reviews';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'admin_user_id',
        'finding_type',
        'payment_id',
    ];
    protected $useTimestamps = true;
}

