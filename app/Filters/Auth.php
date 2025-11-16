<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\UserModel;

class Auth implements FilterInterface
{
   public function before(RequestInterface $request, $arguments = null)
    {
        // Check if user is logged in
        if (!session()->get('isLoggedIn')) {
            // If not logged in, redirect to login page
            return redirect()->to('/');
        }
        
        // Check if the logged-in user is still active (not deactivated)
        $userId = session()->get('user-id');
        if ($userId) {
            $userModel = new UserModel();
            $user = $userModel->find($userId);
            
            if ($user) {
                // Normalize is_active boolean (PostgreSQL may return 't'/'f' or 1/0)
                $isActiveRaw = $user['is_active'] ?? true;
                $isActive = true; // Default to active
                
                if ($isActiveRaw !== null) {
                    if (is_string($isActiveRaw)) {
                        $isActive = in_array(strtolower($isActiveRaw), ['t', 'true', '1', 'yes'], true);
                    } elseif (is_numeric($isActiveRaw)) {
                        $isActive = (bool)(int)$isActiveRaw;
                    } else {
                        $isActive = (bool)$isActiveRaw;
                    }
                }
                
                // Check if user is deactivated
                if (!$isActive) {
                    // Destroy session
                    session()->destroy();
                    
                    // If it's an AJAX request, return JSON error
                    if ($request->isAJAX()) {
                        return service('response')->setJSON([
                            'success' => false,
                            'message' => 'Your account has been deactivated. Please contact the system administrator.',
                            'account_deactivated' => true
                        ])->setStatusCode(403);
                    }
                    
                    // For regular requests, redirect to login with error message
                    return redirect()->to('/')->with('error', 'Your account has been deactivated. Please contact the system administrator for assistance.');
                }
                
                // Also check if officer status is still approved
                if ($user['role'] === 'officer') {
                    $status = $user['status'] ?? 'approved';
                    if ($status !== 'approved') {
                        // Destroy session
                        session()->destroy();
                        
                        // If it's an AJAX request, return JSON error
                        if ($request->isAJAX()) {
                            return service('response')->setJSON([
                                'success' => false,
                                'message' => 'Your account status has changed. Please contact the system administrator.',
                                'account_status_changed' => true
                            ])->setStatusCode(403);
                        }
                        
                        // For regular requests, redirect to login with error message
                        $errorMsg = $status === 'pending' 
                            ? 'Your account is pending approval. Please wait for Super Admin approval.'
                            : 'Your account has been rejected. Please contact the system administrator for assistance.';
                        
                        return redirect()->to('/')->with('error', $errorMsg);
                    }
                }
            }
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do something here
    }
}

