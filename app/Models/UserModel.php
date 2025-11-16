<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $allowedFields = [
        'name',
        'username',
        'email',
        'phone',
        'password',
        'role',
        'status',
        'is_active',
        'profile_picture',
        'verification_token',
        'email_verified',
        'reset_token',
        'reset_expires',
        'last_activity',
    ];
}