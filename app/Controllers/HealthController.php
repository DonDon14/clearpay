<?php

namespace App\Controllers;

use CodeIgniter\Controller;

/**
 * Health check controller for Render.com health checks
 * Returns 200 OK to indicate the service is running
 * 
 * Note: This is a fallback. The primary health check is at /health.php
 * which bypasses CodeIgniter entirely for maximum reliability.
 */
class HealthController extends Controller
{
    /**
     * Health check endpoint
     * Returns simple 200 OK response for Render health checks
     */
    public function index()
    {
        try {
            // Try to return JSON response
            return $this->response
                ->setStatusCode(200)
                ->setContentType('application/json')
                ->setBody(json_encode([
                    'status' => 'ok',
                    'service' => 'clearpay',
                    'timestamp' => date('Y-m-d H:i:s')
                ]));
        } catch (\Exception $e) {
            // If anything fails, return simple 200 OK
            header('Content-Type: application/json');
            http_response_code(200);
            echo json_encode([
                'status' => 'ok',
                'service' => 'clearpay',
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            exit(0);
        }
    }
}

