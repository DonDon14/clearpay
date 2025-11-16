<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\RememberTokenModel;
use App\Models\UserModel;

class RememberMe implements FilterInterface
{
    /**
     * Check for Remember Me cookie and auto-login user if valid
     * 
     * This filter runs before routes are processed. If no session exists
     * but a valid remember_token cookie is found, it automatically logs
     * the user in by creating a session.
     * 
     * @param RequestInterface $request
     * @param array|null $arguments
     * @return void|ResponseInterface
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        // Only check if not already logged in
        if (!session()->get('isLoggedIn')) {
            // Check if remember_token cookie exists
            $rememberToken = $request->getCookie('remember_token');
            
            if ($rememberToken) {
                try {
                    $rememberTokenModel = new RememberTokenModel();
                    
                    // Find user by token
                    $user = $rememberTokenModel->findUserByToken($rememberToken);
                    
                    if ($user) {
                        // Check if user is active (deactivated users cannot auto-login)
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
                        
                        // Also check if officer status is approved
                        $canLogin = true;
                        if ($user['role'] === 'officer') {
                            $status = $user['status'] ?? 'approved';
                            $canLogin = ($status === 'approved');
                        }
                        
                        if ($isActive && $canLogin) {
                            // Token is valid and user is active, auto-login the user
                            session()->set([
                                'user-id'         => $user['id'],
                                'username'        => $user['username'],
                                'email'           => $user['email'],
                                'name'            => $user['name'],
                                'role'            => $user['role'],
                                'profile_picture' => $user['profile_picture'] ?? null,
                                'isLoggedIn'      => true,
                            ]);
                            
                            // Set session flag to force sidebar expanded on first load
                            session()->set('forceSidebarExpanded', true);
                            
                            log_message('info', "Auto-login successful via Remember Me for user: {$user['username']}");
                        } else {
                            // User is deactivated or not approved, clear token and cookie
                            $rememberTokenModel->deleteToken($user['id']);
                            $this->clearRememberMeCookie();
                            log_message('info', "Auto-login blocked - user deactivated or not approved: {$user['username']}");
                        }
                    } else {
                        // Invalid token, clear the cookie
                        $this->clearRememberMeCookie();
                        log_message('info', 'Invalid Remember Me token, cookie cleared');
                    }
                } catch (\Exception $e) {
                    log_message('error', 'Remember Me auto-login error: ' . $e->getMessage());
                    // Clear cookie on error
                    $this->clearRememberMeCookie();
                }
            }
        }
    }

    /**
     * Clear the remember_token cookie
     * Called when token is invalid or expired
     * 
     * @return void
     */
    private function clearRememberMeCookie()
    {
        try {
            $isSecure = ENVIRONMENT === 'production';
            $cookie = cookie('remember_token', '', [
                'expires'  => time() - 3600,
                'httponly' => true,
                'secure'   => $isSecure,
                'samesite' => 'Lax'
            ]);
            
            $response = service('response');
            $response->setCookie($cookie);
        } catch (\Exception $e) {
            log_message('error', 'Failed to clear Remember Me cookie: ' . $e->getMessage());
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do something here if needed
    }
}

