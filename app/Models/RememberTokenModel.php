<?php

namespace App\Models;

use CodeIgniter\Model;

class RememberTokenModel extends Model
{
    protected $table = 'auth_tokens';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'user_id',
        'token',
        'expires_at',
        'created_at'
    ];
    protected $useTimestamps = false;
    protected $dateFormat = 'datetime';

    /**
     * Create a new remember token for a user
     * 
     * @param int $userId The user ID
     * @param string $rawToken The raw token (before hashing)
     * @param string $expiryDate Expiry date in datetime format
     * @return bool|int Insert ID on success, false on failure
     */
    public function createToken($userId, $rawToken, $expiryDate)
    {
        // Delete any existing tokens for this user first
        $this->deleteToken($userId);
        
        // Hash the token before storing
        $hashedToken = password_hash($rawToken, PASSWORD_DEFAULT);
        
        $data = [
            'user_id'    => $userId,
            'token'      => $hashedToken,
            'expires_at' => $expiryDate,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        return $this->insert($data);
    }

    /**
     * Find user by remember token
     * 
     * @param string $rawToken The raw token from cookie
     * @return array|null User data if token is valid, null otherwise
     */
    public function findUserByToken($rawToken)
    {
        // Get all tokens (we need to verify against hashed tokens)
        $tokens = $this->where('expires_at >', date('Y-m-d H:i:s'))->findAll();
        
        if (empty($tokens)) {
            return null;
        }
        
        // Check each token to find a match
        foreach ($tokens as $token) {
            if (password_verify($rawToken, $token['token'])) {
                // Token is valid, get user data
                $userModel = new UserModel();
                $user = $userModel->find($token['user_id']);
                
                if ($user) {
                    return $user;
                }
            }
        }
        
        return null;
    }

    /**
     * Delete all tokens for a specific user
     * 
     * @param int $userId The user ID
     * @return bool True on success, false on failure
     */
    public function deleteToken($userId)
    {
        return $this->where('user_id', $userId)->delete();
    }

    /**
     * Delete expired tokens
     * Cleanup method to run periodically
     * 
     * @return int Number of deleted tokens
     */
    public function deleteExpiredTokens()
    {
        return $this->where('expires_at <', date('Y-m-d H:i:s'))->delete();
    }

    /**
     * Get token by raw token value
     * Helper method for internal use
     * 
     * @param string $rawToken The raw token
     * @return array|null Token data if found, null otherwise
     */
    public function getTokenByRaw($rawToken)
    {
        $tokens = $this->findAll();
        
        foreach ($tokens as $token) {
            if (password_verify($rawToken, $token['token'])) {
                return $token;
            }
        }
        
        return null;
    }
}

