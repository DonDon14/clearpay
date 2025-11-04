<?php

namespace App\Models;

use CodeIgniter\Model;

class PayerModel extends Model
{
    protected $table = 'payers';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'payer_id',
        'password',
        'payer_name',
        'contact_number',
        'email_address',
        'course_department',
        'profile_picture',
        'email_verified',
        'verification_token',
        'reset_token',
        'reset_expires',
        'created_at',
        'updated_at'
    ];
    protected $useTimestamps = true;
    protected $validationRules = [];
    protected $validationMessages = [];
    protected $skipValidation = true;
} 