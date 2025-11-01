<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class PayerAuth implements FilterInterface
{
    // Session timeout in seconds (30 minutes)
    private $sessionTimeout = 1800; // 30 minutes

    public function before(RequestInterface $request, $arguments = null)
    {
        // Check if payer is logged in
        $payerId = session()->get('payer_id');
        $payerLoggedIn = session()->get('payer_logged_in');
        
        if (!$payerId || !$payerLoggedIn) {
            // If it's an AJAX request, return JSON error
            if ($request->isAJAX()) {
                return service('response')->setJSON([
                    'success' => false,
                    'message' => 'Session expired. Please login again.',
                    'session_expired' => true
                ])->setStatusCode(401);
            }
            
            // For regular requests, redirect to login
            session()->setFlashdata('error', 'Please login to access this page.');
            return redirect()->to('payer/login');
        }

        // Check session timeout
        $lastActivity = session()->get('payer_last_activity');
        if ($lastActivity) {
            $timeSinceLastActivity = time() - $lastActivity;
            
            // If session has expired
            if ($timeSinceLastActivity > $this->sessionTimeout) {
                // Clear only payer session keys to avoid terminating admin session
                session()->remove([
                    'payer_id',
                    'payer_student_id',
                    'payer_name',
                    'payer_email',
                    'payer_profile_picture',
                    'payer_logged_in',
                    'payer_last_activity'
                ]);
                
                // If it's an AJAX request, return JSON error
                if ($request->isAJAX()) {
                    return service('response')->setJSON([
                        'success' => false,
                        'message' => 'Your session has expired due to inactivity. Please login again.',
                        'session_expired' => true
                    ])->setStatusCode(401);
                }
                
                // For regular requests, redirect to login
                session()->setFlashdata('error', 'Your session has expired due to inactivity. Please login again.');
                return redirect()->to('payer/login');
            }
        }

        // Update last activity time
        session()->set('payer_last_activity', time());
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do something here if needed
    }
}

