<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductModel extends Model
{
    protected $table = 'products';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'title', 'description', 'amount', 'cost_price', 'category', 'status', 'created_by', 'created_at', 'updated_at'
    ];
    protected $useTimestamps = true;
}
