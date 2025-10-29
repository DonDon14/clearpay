<?php

namespace App\Models;

use CodeIgniter\Model;

class PayerModel extends Model
{
    protected $table = 'payers';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'payer_id',
        'payer_name',
        'contact_number',
        'email_address',
        'course_department',
        'profile_picture',
        'created_at',
        'updated_at'
    ];
    protected $useTimestamps = true;
    protected $validationRules = [];
    protected $validationMessages = [];
    protected $skipValidation = true;
} 