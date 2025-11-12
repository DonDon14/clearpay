<?php
/**
 * Simple health check endpoint for Render.com
 * This bypasses CodeIgniter to ensure it always works
 */

header('Content-Type: application/json');
http_response_code(200);

echo json_encode([
    'status' => 'ok',
    'service' => 'clearpay',
    'timestamp' => date('Y-m-d H:i:s')
]);

exit(0);

