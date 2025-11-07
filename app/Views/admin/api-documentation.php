<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<link rel="stylesheet" href="<?= base_url('css/dashboard.css') ?>" />

<div class="container-fluid mb-4">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-1 fw-semibold"><?= $pageTitle ?? 'API Documentation' ?></h1>
                    <p class="text-muted mb-0"><?= $pageSubtitle ?? 'Complete API reference for ClearPay' ?></p>
                </div>
                <div>
                    <a href="<?= base_url('help') ?>" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>Back to Help
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- API Overview -->
    <div class="row mb-4">
        <div class="col-12">
            <?= view('partials/container-card', [
                'title' => 'API Overview',
                'subtitle' => 'Introduction to ClearPay API',
                'bodyClass' => '',
                'content' => '
                    <div class="content-section">
                        <h4 class="mb-3">Base URL</h4>
                        <p class="mb-3">All API endpoints are relative to:</p>
                        <div class="bg-light p-3 rounded mb-4">
                            <code>' . base_url() . 'api/payer/</code>
                        </div>
                        
                        <h4 class="mb-3 mt-4">Authentication</h4>
                        <p class="mb-3">Most API endpoints require authentication. Include the authentication token in the request headers:</p>
                        <div class="bg-light p-3 rounded mb-4">
                            <code>Authorization: Bearer {token}</code>
                        </div>
                        <p class="mb-3">The token is obtained from the login endpoint and should be stored securely.</p>
                        
                        <h4 class="mb-3 mt-4">Request Format</h4>
                        <p class="mb-3">All requests should include:</p>
                        <ul>
                            <li><strong>Content-Type:</strong> application/json</li>
                            <li><strong>Accept:</strong> application/json</li>
                            <li><strong>Authorization:</strong> Bearer {token} (for protected endpoints)</li>
                        </ul>
                        
                        <h4 class="mb-3 mt-4">Response Format</h4>
                        <p class="mb-3">All responses are in JSON format with the following structure:</p>
                        <div class="bg-light p-3 rounded mb-4">
                            <pre class="mb-0">{
  "success": true|false,
  "message": "Response message",
  "data": { ... },
  "error": "Error message (if any)"
}</pre>
                        </div>
                        
                        <h4 class="mb-3 mt-4">CORS</h4>
                        <p class="mb-3">All API endpoints support CORS (Cross-Origin Resource Sharing) for mobile app integration. Preflight OPTIONS requests are automatically handled.</p>
                    </div>
                '
            ]) ?>
        </div>
    </div>

    <!-- Authentication Endpoints -->
    <div class="row mb-4">
        <div class="col-12">
            <?= view('partials/container-card', [
                'title' => 'Authentication Endpoints',
                'subtitle' => 'Login, signup, and password management',
                'bodyClass' => '',
                'content' => '
                    <div class="content-section">
                        
                        <div class="api-endpoint mb-4">
                            <h5 class="text-primary">POST /api/payer/login</h5>
                            <p class="text-muted">Authenticate a payer and receive an access token</p>
                            
                            <h6 class="mt-3">Request Body:</h6>
                            <div class="bg-light p-3 rounded mb-3">
                                <pre class="mb-0">{
  "payer_id": "string (required)",
  "password": "string (required)"
}</pre>
                            </div>
                            
                            <h6>Response (Success):</h6>
                            <div class="bg-light p-3 rounded mb-3">
                                <pre class="mb-0">{
  "success": true,
  "message": "Login successful",
  "token": "authentication_token",
  "data": {
    "id": 1,
    "payer_id": "STUDENT001",
    "payer_name": "John Doe",
    "email_address": "john@example.com"
  }
}</pre>
                            </div>
                            
                            <h6>Response (Error):</h6>
                            <div class="bg-light p-3 rounded mb-3">
                                <pre class="mb-0">{
  "success": false,
  "message": "Invalid credentials"
}</pre>
                            </div>
                        </div>
                        
                        <div class="api-endpoint mb-4">
                            <h5 class="text-primary">POST /api/payer/signup</h5>
                            <p class="text-muted">Register a new payer account</p>
                            
                            <h6 class="mt-3">Request Body:</h6>
                            <div class="bg-light p-3 rounded mb-3">
                                <pre class="mb-0">{
  "payer_id": "string (required)",
  "password": "string (required)",
  "full_name": "string (required)",
  "email": "string (required)",
  "contact_number": "string (optional)",
  "course": "string (optional)"
}</pre>
                            </div>
                            
                            <h6>Response (Success):</h6>
                            <div class="bg-light p-3 rounded mb-3">
                                <pre class="mb-0">{
  "success": true,
  "message": "Registration successful. Please verify your email.",
  "verification_code": "123456"
}</pre>
                            </div>
                        </div>
                        
                        <div class="api-endpoint mb-4">
                            <h5 class="text-primary">POST /api/payer/verify-email</h5>
                            <p class="text-muted">Verify email address with verification code</p>
                            
                            <h6 class="mt-3">Request Body:</h6>
                            <div class="bg-light p-3 rounded mb-3">
                                <pre class="mb-0">{
  "email": "string (required)",
  "verification_code": "string (required)"
}</pre>
                            </div>
                        </div>
                        
                        <div class="api-endpoint mb-4">
                            <h5 class="text-primary">POST /api/payer/forgot-password</h5>
                            <p class="text-muted">Request password reset code</p>
                            
                            <h6 class="mt-3">Request Body:</h6>
                            <div class="bg-light p-3 rounded mb-3">
                                <pre class="mb-0">{
  "email": "string (required)"
}</pre>
                            </div>
                        </div>
                        
                        <div class="api-endpoint mb-4">
                            <h5 class="text-primary">POST /api/payer/reset-password</h5>
                            <p class="text-muted">Reset password using reset code</p>
                            
                            <h6 class="mt-3">Request Body:</h6>
                            <div class="bg-light p-3 rounded mb-3">
                                <pre class="mb-0">{
  "email": "string (required)",
  "reset_code": "string (required)",
  "new_password": "string (required)"
}</pre>
                            </div>
                        </div>
                    </div>
                '
            ]) ?>
        </div>
    </div>

    <!-- Dashboard Endpoints -->
    <div class="row mb-4">
        <div class="col-12">
            <?= view('partials/container-card', [
                'title' => 'Dashboard Endpoints',
                'subtitle' => 'Get dashboard data and statistics',
                'bodyClass' => '',
                'content' => '
                    <div class="content-section">
                        
                        <div class="api-endpoint mb-4">
                            <h5 class="text-primary">GET /api/payer/dashboard</h5>
                            <p class="text-muted">Get payer dashboard data</p>
                            
                            <h6 class="mt-3">Query Parameters:</h6>
                            <ul>
                                <li><code>payer_id</code> (required) - Payer ID</li>
                            </ul>
                            
                            <h6>Response:</h6>
                            <div class="bg-light p-3 rounded mb-3">
                                <pre class="mb-0">{
  "success": true,
  "data": {
    "total_contributions": 5,
    "unpaid_contributions": 2,
    "partially_paid": 1,
    "fully_paid": 2,
    "total_paid": 5000.00,
    "total_due": 3000.00
  }
}</pre>
                            </div>
                        </div>
                        
                        <div class="api-endpoint mb-4">
                            <h5 class="text-primary">GET /api/payer/contributions</h5>
                            <p class="text-muted">Get list of contributions for the payer</p>
                            
                            <h6 class="mt-3">Query Parameters:</h6>
                            <ul>
                                <li><code>payer_id</code> (required) - Payer ID</li>
                            </ul>
                        </div>
                        
                        <div class="api-endpoint mb-4">
                            <h5 class="text-primary">GET /api/payer/payment-history</h5>
                            <p class="text-muted">Get payment history for the payer</p>
                            
                            <h6 class="mt-3">Query Parameters:</h6>
                            <ul>
                                <li><code>payer_id</code> (required) - Payer ID</li>
                            </ul>
                        </div>
                        
                        <div class="api-endpoint mb-4">
                            <h5 class="text-primary">GET /api/payer/announcements</h5>
                            <p class="text-muted">Get announcements for the payer</p>
                            
                            <h6 class="mt-3">Query Parameters:</h6>
                            <ul>
                                <li><code>payer_id</code> (required) - Payer ID</li>
                            </ul>
                        </div>
                    </div>
                '
            ]) ?>
        </div>
    </div>

    <!-- Payment Request Endpoints -->
    <div class="row mb-4">
        <div class="col-12">
            <?= view('partials/container-card', [
                'title' => 'Payment Request Endpoints',
                'subtitle' => 'Submit and manage payment requests',
                'bodyClass' => '',
                'content' => '
                    <div class="content-section">
                        
                        <div class="api-endpoint mb-4">
                            <h5 class="text-primary">GET /api/payer/payment-requests</h5>
                            <p class="text-muted">Get payment requests for the payer</p>
                            
                            <h6 class="mt-3">Query Parameters:</h6>
                            <ul>
                                <li><code>payer_id</code> (required) - Payer ID</li>
                            </ul>
                        </div>
                        
                        <div class="api-endpoint mb-4">
                            <h5 class="text-primary">POST /api/payer/submit-payment-request</h5>
                            <p class="text-muted">Submit a new payment request</p>
                            
                            <h6 class="mt-3">Request Body:</h6>
                            <div class="bg-light p-3 rounded mb-3">
                                <pre class="mb-0">{
  "payer_id": "integer (required)",
  "contribution_id": "integer (required)",
  "requested_amount": "decimal (required)",
  "payment_method": "string (required)",
  "reference_number": "string (optional)",
  "proof_of_payment": "file (optional)",
  "notes": "string (optional)"
}</pre>
                            </div>
                            
                            <h6>Response (Success):</h6>
                            <div class="bg-light p-3 rounded mb-3">
                                <pre class="mb-0">{
  "success": true,
  "message": "Payment request submitted successfully",
  "data": {
    "request_id": 1,
    "status": "pending"
  }
}</pre>
                            </div>
                        </div>
                        
                        <div class="api-endpoint mb-4">
                            <h5 class="text-primary">GET /api/payer/payment-methods</h5>
                            <p class="text-muted">Get available payment methods</p>
                            
                            <h6>Response:</h6>
                            <div class="bg-light p-3 rounded mb-3">
                                <pre class="mb-0">{
  "success": true,
  "data": [
    {
      "id": 1,
      "code": "gcash",
      "name": "GCash",
      "instructions": "Send payment to..."
    }
  ]
}</pre>
                            </div>
                        </div>
                    </div>
                '
            ]) ?>
        </div>
    </div>

    <!-- Refund Request Endpoints -->
    <div class="row mb-4">
        <div class="col-12">
            <?= view('partials/container-card', [
                'title' => 'Refund Request Endpoints',
                'subtitle' => 'Submit and manage refund requests',
                'bodyClass' => '',
                'content' => '
                    <div class="content-section">
                        
                        <div class="api-endpoint mb-4">
                            <h5 class="text-primary">GET /api/payer/refund-requests</h5>
                            <p class="text-muted">Get refund requests for the payer</p>
                            
                            <h6 class="mt-3">Query Parameters:</h6>
                            <ul>
                                <li><code>payer_id</code> (required) - Payer ID</li>
                            </ul>
                        </div>
                        
                        <div class="api-endpoint mb-4">
                            <h5 class="text-primary">POST /api/payer/submit-refund-request</h5>
                            <p class="text-muted">Submit a new refund request</p>
                            
                            <h6 class="mt-3">Request Body:</h6>
                            <div class="bg-light p-3 rounded mb-3">
                                <pre class="mb-0">{
  "payer_id": "integer (required)",
  "payment_id": "integer (required)",
  "refund_amount": "decimal (required)",
  "refund_method": "string (required)",
  "refund_reason": "string (required)",
  "payer_notes": "string (optional)"
}</pre>
                            </div>
                        </div>
                        
                        <div class="api-endpoint mb-4">
                            <h5 class="text-primary">GET /api/payer/refund-methods</h5>
                            <p class="text-muted">Get available refund methods</p>
                        </div>
                    </div>
                '
            ]) ?>
        </div>
    </div>

    <!-- Activity/Notification Endpoints -->
    <div class="row mb-4">
        <div class="col-12">
            <?= view('partials/container-card', [
                'title' => 'Activity & Notification Endpoints',
                'subtitle' => 'Get activities and notifications',
                'bodyClass' => '',
                'content' => '
                    <div class="content-section">
                        
                        <div class="api-endpoint mb-4">
                            <h5 class="text-primary">GET /api/payer/check-new-activities</h5>
                            <p class="text-muted">Check for new activities/notifications</p>
                            
                            <h6 class="mt-3">Query Parameters:</h6>
                            <ul>
                                <li><code>payer_id</code> (required) - Payer ID</li>
                            </ul>
                        </div>
                        
                        <div class="api-endpoint mb-4">
                            <h5 class="text-primary">GET /api/payer/get-all-activities</h5>
                            <p class="text-muted">Get all activities for the payer</p>
                            
                            <h6 class="mt-3">Query Parameters:</h6>
                            <ul>
                                <li><code>payer_id</code> (required) - Payer ID</li>
                            </ul>
                            
                            <h6>Response:</h6>
                            <div class="bg-light p-3 rounded mb-3">
                                <pre class="mb-0">{
  "success": true,
  "data": [
    {
      "id": 1,
      "activity_type": "payment_approved",
      "title": "Payment Approved",
      "message": "Your payment request has been approved",
      "is_read_by_payer": 0,
      "created_at": "2024-01-01 12:00:00"
    }
  ]
}</pre>
                            </div>
                        </div>
                        
                        <div class="api-endpoint mb-4">
                            <h5 class="text-primary">POST /api/payer/mark-activity-read/{id}</h5>
                            <p class="text-muted">Mark an activity as read</p>
                            
                            <h6 class="mt-3">URL Parameters:</h6>
                            <ul>
                                <li><code>id</code> (required) - Activity ID</li>
                            </ul>
                            
                            <h6 class="mt-3">Query Parameters:</h6>
                            <ul>
                                <li><code>payer_id</code> (required) - Payer ID</li>
                            </ul>
                        </div>
                    </div>
                '
            ]) ?>
        </div>
    </div>

    <!-- Profile Endpoints -->
    <div class="row mb-4">
        <div class="col-12">
            <?= view('partials/container-card', [
                'title' => 'Profile Endpoints',
                'subtitle' => 'Manage payer profile',
                'bodyClass' => '',
                'content' => '
                    <div class="content-section">
                        
                        <div class="api-endpoint mb-4">
                            <h5 class="text-primary">POST /api/payer/upload-profile-picture</h5>
                            <p class="text-muted">Upload profile picture</p>
                            
                            <h6 class="mt-3">Request Format:</h6>
                            <p>Multipart/form-data</p>
                            <ul>
                                <li><code>payer_id</code> (required) - Payer ID</li>
                                <li><code>profile_picture</code> (required) - Image file</li>
                            </ul>
                        </div>
                        
                        <div class="api-endpoint mb-4">
                            <h5 class="text-primary">POST /payer/update-profile</h5>
                            <p class="text-muted">Update payer profile information</p>
                            
                            <h6 class="mt-3">Request Body:</h6>
                            <div class="bg-light p-3 rounded mb-3">
                                <pre class="mb-0">{
  "payer_id": "integer (required)",
  "full_name": "string (optional)",
  "email": "string (optional)",
  "contact_number": "string (optional)",
  "course": "string (optional)"
}</pre>
                            </div>
                        </div>
                    </div>
                '
            ]) ?>
        </div>
    </div>

    <!-- Error Codes -->
    <div class="row mb-4">
        <div class="col-12">
            <?= view('partials/container-card', [
                'title' => 'Error Codes',
                'subtitle' => 'Common HTTP status codes and errors',
                'bodyClass' => '',
                'content' => '
                    <div class="content-section">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Status Code</th>
                                    <th>Description</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><code>200</code></td>
                                    <td>Success - Request completed successfully</td>
                                </tr>
                                <tr>
                                    <td><code>201</code></td>
                                    <td>Created - Resource created successfully</td>
                                </tr>
                                <tr>
                                    <td><code>400</code></td>
                                    <td>Bad Request - Invalid request parameters</td>
                                </tr>
                                <tr>
                                    <td><code>401</code></td>
                                    <td>Unauthorized - Authentication required or invalid token</td>
                                </tr>
                                <tr>
                                    <td><code>403</code></td>
                                    <td>Forbidden - Access denied</td>
                                </tr>
                                <tr>
                                    <td><code>404</code></td>
                                    <td>Not Found - Resource not found</td>
                                </tr>
                                <tr>
                                    <td><code>422</code></td>
                                    <td>Unprocessable Entity - Validation error</td>
                                </tr>
                                <tr>
                                    <td><code>500</code></td>
                                    <td>Internal Server Error - Server error</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                '
            ]) ?>
        </div>
    </div>

    <!-- Code Examples -->
    <div class="row mb-4">
        <div class="col-12">
            <?= view('partials/container-card', [
                'title' => 'Code Examples',
                'subtitle' => 'Example implementations',
                'bodyClass' => '',
                'content' => '
                    <div class="content-section">
                        <h4 class="mb-3">JavaScript/Fetch Example</h4>
                        <div class="bg-light p-3 rounded mb-4">
                            <pre class="mb-0">// Login Example
fetch(\'' . base_url() . 'api/payer/login\', {
    method: \'POST\',
    headers: {
        \'Content-Type\': \'application/json\',
        \'Accept\': \'application/json\'
    },
    body: JSON.stringify({
        payer_id: \'STUDENT001\',
        password: \'password123\'
    })
})
.then(response => response.json())
.then(data => {
    if (data.success) {
        // Store token
        localStorage.setItem(\'token\', data.token);
    }
});

// Authenticated Request Example
const token = localStorage.getItem(\'token\');
fetch(\'' . base_url() . 'api/payer/dashboard?payer_id=1\', {
    headers: {
        \'Authorization\': `Bearer ${token}`,
        \'Content-Type\': \'application/json\',
        \'Accept\': \'application/json\'
    }
})
.then(response => response.json())
.then(data => console.log(data));</pre>
                        </div>
                        
                        <h4 class="mb-3 mt-4">cURL Example</h4>
                        <div class="bg-light p-3 rounded mb-4">
                            <pre class="mb-0"># Login
curl -X POST \'' . base_url() . 'api/payer/login\' \\
  -H "Content-Type: application/json" \\
  -H "Accept: application/json" \\
  -d \'{
    "payer_id": "STUDENT001",
    "password": "password123"
  }\'

# Get Dashboard (with token)
curl -X GET \'' . base_url() . 'api/payer/dashboard?payer_id=1\' \\
  -H "Authorization: Bearer YOUR_TOKEN" \\
  -H "Accept: application/json"</pre>
                        </div>
                    </div>
                '
            ]) ?>
        </div>
    </div>
</div>

<style>
.content-section {
    line-height: 1.8;
}

.api-endpoint {
    border-left: 4px solid #007bff;
    padding-left: 1rem;
    margin-bottom: 2rem;
}

.api-endpoint h5 {
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.api-endpoint h6 {
    font-weight: 600;
    font-size: 0.9rem;
    margin-top: 1rem;
    color: #495057;
}

.content-section pre {
    font-size: 0.85rem;
    background-color: #f8f9fa;
    padding: 1rem;
    border-radius: 0.25rem;
    overflow-x: auto;
}

.content-section code {
    background-color: #f8f9fa;
    padding: 2px 6px;
    border-radius: 3px;
    font-size: 0.9em;
    color: #e83e8c;
}

.content-section table {
    font-size: 0.9rem;
}

.content-section table code {
    background-color: transparent;
    padding: 0;
    color: #007bff;
}
</style>

<?= $this->endSection() ?>

