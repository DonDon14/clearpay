<?php

namespace App\Models;

use CodeIgniter\Model;

class ContributionModel extends Model
{
    protected $table = 'contributions';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'title', 'description', 'amount', 'category', 'status', 'created_by', 'cost_price'
    ];
    protected $useTimestamps = true; // automatically fill created_at, updated_at
}
