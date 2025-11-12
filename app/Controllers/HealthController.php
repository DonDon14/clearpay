<?php

namespace App\Controllers;

use CodeIgniter\Controller;

/**
 * Health check controller for Render.com health checks
 * Returns 200 OK to indicate the service is running
 */
class HealthController extends Controller
{
    /**
     * Health check endpoint
     * Returns simple 200 OK response for Render health checks
     */
    public function index()
    {
        return $this->response->setStatusCode(200)->setJSON([
            'status' => 'ok',
            'service' => 'clearpay',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }
}

