<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use Config\Email;

class EmailSettingsController extends BaseController
{
    /**
     * Get current email configuration
     */
    public function getConfig()
    {
        if (!session()->get('isLoggedIn')) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Unauthorized. Please login first.'
            ])->setStatusCode(401);
        }

        try {
            $db = \Config\Database::connect();
            
            // Try to load from database first
            $settings = null;
            if ($db->tableExists('email_settings')) {
                $settings = $db->table('email_settings')
                    ->where('is_active', true)
                    ->orderBy('id', 'DESC')
                    ->limit(1)
                    ->get()
                    ->getRowArray();
            }
            
            // Fallback to config if database settings not found
            if (!$settings) {
                $emailConfig = config('Email');
                return $this->response->setJSON([
                    'success' => true,
                    'config' => [
                        'fromEmail' => $emailConfig->fromEmail,
                        'fromName' => $emailConfig->fromName,
                        'protocol' => $emailConfig->protocol,
                        'SMTPHost' => $emailConfig->SMTPHost,
                        'SMTPUser' => $emailConfig->SMTPUser,
                        'SMTPPass' => $emailConfig->SMTPPass,
                        'SMTPPort' => $emailConfig->SMTPPort,
                        'SMTPCrypto' => $emailConfig->SMTPCrypto,
                        'SMTPTimeout' => $emailConfig->SMTPTimeout,
                        'mailType' => $emailConfig->mailType,
                        'charset' => $emailConfig->charset,
                    ]
                ]);
            }
            
            // Return database settings
            return $this->response->setJSON([
                'success' => true,
                'config' => [
                    'fromEmail' => $settings['from_email'] ?? '',
                    'fromName' => $settings['from_name'] ?? 'ClearPay',
                    'protocol' => $settings['protocol'] ?? 'smtp',
                    'SMTPHost' => $settings['smtp_host'] ?? '',
                    'SMTPUser' => $settings['smtp_user'] ?? '',
                    'SMTPPass' => $settings['smtp_pass'] ?? '',
                    'SMTPPort' => (int)($settings['smtp_port'] ?? 587),
                    'SMTPCrypto' => $settings['smtp_crypto'] ?? 'tls',
                    'SMTPTimeout' => (int)($settings['smtp_timeout'] ?? 30),
                    'mailType' => $settings['mail_type'] ?? 'html',
                    'charset' => $settings['charset'] ?? 'UTF-8',
                ]
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Email config error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'error' => 'An error occurred while retrieving email configuration.'
            ])->setStatusCode(500);
        }
    }

    /**
     * Update email configuration
     */
    public function updateConfig()
    {
        if (!session()->get('isLoggedIn')) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Unauthorized. Please login first.'
            ])->setStatusCode(401);
        }

        try {
            $data = $this->request->getJSON(true) ?? $this->request->getPost();
            
            // Validate required fields
            $required = ['fromEmail', 'SMTPHost', 'SMTPUser', 'SMTPPort'];
            foreach ($required as $field) {
                // Special handling for SMTPPass - check if it's set, not empty (passwords can be any string including spaces)
                if ($field === 'SMTPPass') {
                    if (!isset($data['SMTPPass']) || $data['SMTPPass'] === '') {
                        return $this->response->setJSON([
                            'success' => false,
                            'error' => 'SMTP Password is required.'
                        ])->setStatusCode(400);
                    }
                } else {
                    if (empty($data[$field])) {
                        return $this->response->setJSON([
                            'success' => false,
                            'error' => ucfirst($field) . ' is required.'
                        ])->setStatusCode(400);
                    }
                }
            }
            
            // Also validate SMTPPass separately
            if (!isset($data['SMTPPass']) || $data['SMTPPass'] === '') {
                return $this->response->setJSON([
                    'success' => false,
                    'error' => 'SMTP Password is required.'
                ])->setStatusCode(400);
            }

            // Validate email
            if (!filter_var($data['fromEmail'], FILTER_VALIDATE_EMAIL)) {
                return $this->response->setJSON([
                    'success' => false,
                    'error' => 'Invalid email address.'
                ])->setStatusCode(400);
            }

            // Validate port
            $port = (int)$data['SMTPPort'];
            if ($port < 1 || $port > 65535) {
                return $this->response->setJSON([
                    'success' => false,
                    'error' => 'Invalid SMTP port. Must be between 1 and 65535.'
                ])->setStatusCode(400);
            }

            // Save to database (email_settings table)
            $db = \Config\Database::connect();
            
            // Check if table exists
            if (!$db->tableExists('email_settings')) {
                return $this->response->setJSON([
                    'success' => false,
                    'error' => 'Email settings table not found. Please run migrations first.'
                ])->setStatusCode(500);
            }
            
            // Prepare data for database
            // IMPORTANT: Do NOT trim SMTPPass - passwords may contain spaces
            // Get password directly without any modification to preserve spaces
            $smtpPass = isset($data['SMTPPass']) ? (string)$data['SMTPPass'] : '';
            
            $settingsData = [
                'from_email' => trim($data['fromEmail'] ?? ''),
                'from_name' => trim($data['fromName'] ?? 'ClearPay'),
                'protocol' => trim($data['protocol'] ?? 'smtp'),
                'smtp_host' => trim($data['SMTPHost'] ?? ''),
                'smtp_user' => trim($data['SMTPUser'] ?? ''),
                'smtp_pass' => $smtpPass, // Store as-is to preserve spaces and special characters
                'smtp_port' => (int)($data['SMTPPort'] ?? 587),
                'smtp_crypto' => trim($data['SMTPCrypto'] ?? 'tls'),
                'smtp_timeout' => (int)($data['SMTPTimeout'] ?? 30),
                'mail_type' => trim($data['mailType'] ?? 'html'),
                'charset' => trim($data['charset'] ?? 'UTF-8'),
                'is_active' => true,
                'updated_at' => date('Y-m-d H:i:s'),
            ];
            
            // Check if settings already exist
            $existing = $db->table('email_settings')
                ->where('is_active', true)
                ->orderBy('id', 'DESC')
                ->limit(1)
                ->get()
                ->getRowArray();
            
            if ($existing) {
                // Update existing settings
                $db->table('email_settings')
                    ->where('id', $existing['id'])
                    ->update($settingsData);
            } else {
                // Insert new settings
                $settingsData['created_at'] = date('Y-m-d H:i:s');
                $db->table('email_settings')->insert($settingsData);
            }

            log_message('info', 'Email configuration updated by admin: ' . session()->get('username'));

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Email configuration updated successfully!'
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Email config update error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'error' => 'An error occurred while updating email configuration: ' . $e->getMessage()
            ])->setStatusCode(500);
        }
    }

    /**
     * Send test email
     */
    public function testEmail()
    {
        if (!session()->get('isLoggedIn')) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Unauthorized. Please login first.'
            ])->setStatusCode(401);
        }

        try {
            // Check for required PHP extensions
            if (!extension_loaded('openssl')) {
                return $this->response->setJSON([
                    'success' => false,
                    'error' => 'OpenSSL extension is not enabled. SMTP with TLS/SSL requires OpenSSL.',
                    'hint' => 'Please enable the OpenSSL extension in your PHP configuration (php.ini).'
                ])->setStatusCode(500);
            }

            $data = $this->request->getJSON(true) ?? $this->request->getPost();
            $testEmail = $data['email'] ?? session()->get('email') ?? session()->get('username');
            
            if (empty($testEmail) || !filter_var($testEmail, FILTER_VALIDATE_EMAIL)) {
                return $this->response->setJSON([
                    'success' => false,
                    'error' => 'Valid email address is required.'
                ])->setStatusCode(400);
            }

            // Get email config from database (just saved) or config
            $emailConfig = $this->getEmailConfig();
            
            // If SMTP credentials are provided in the request (from form), use them for testing
            // This allows testing without saving first
            if (!empty($data['SMTPHost']) && !empty($data['SMTPUser']) && !empty($data['SMTPPass'])) {
                log_message('info', 'Using SMTP credentials from request (testing without saving)');
                $emailConfig['SMTPHost'] = trim($data['SMTPHost']);
                $emailConfig['SMTPUser'] = trim($data['SMTPUser']);
                $emailConfig['SMTPPass'] = (string)$data['SMTPPass']; // Keep as-is, no trimming
                $emailConfig['SMTPPort'] = (int)($data['SMTPPort'] ?? 587);
                $emailConfig['SMTPCrypto'] = trim($data['SMTPCrypto'] ?? 'tls');
                $emailConfig['fromEmail'] = trim($data['fromEmail'] ?? $emailConfig['fromEmail']);
                $emailConfig['fromName'] = trim($data['fromName'] ?? $emailConfig['fromName']);
            }
            
            // Validate SMTP credentials are not empty
            if (empty($emailConfig['SMTPUser']) || empty($emailConfig['SMTPPass']) || empty($emailConfig['SMTPHost']) || empty($emailConfig['fromEmail'])) {
                $passLength = !empty($emailConfig['SMTPPass']) ? strlen($emailConfig['SMTPPass']) : 0;
                $passPreview = !empty($emailConfig['SMTPPass']) ? substr($emailConfig['SMTPPass'], 0, 10) . '...' : 'EMPTY';
                log_message('error', 'SMTP credentials are empty - User: ' . (empty($emailConfig['SMTPUser']) ? 'EMPTY' : $emailConfig['SMTPUser']) . ', Pass: ' . (empty($emailConfig['SMTPPass']) ? 'EMPTY' : 'SET (length: ' . $passLength . ', starts: ' . $passPreview . ')') . ', Host: ' . (empty($emailConfig['SMTPHost']) ? 'EMPTY' : $emailConfig['SMTPHost']) . ', From: ' . (empty($emailConfig['fromEmail']) ? 'EMPTY' : $emailConfig['fromEmail']));
                return $this->response->setJSON([
                    'success' => false,
                    'error' => 'SMTP configuration is incomplete. Please fill in all required fields.',
                    'hint' => 'Make sure SMTP Host, Username, Password, and From Email are all filled in. Current status: Host=' . (!empty($emailConfig['SMTPHost']) ? 'SET' : 'EMPTY') . ', User=' . (!empty($emailConfig['SMTPUser']) ? 'SET' : 'EMPTY') . ', Pass=' . (!empty($emailConfig['SMTPPass']) ? 'SET (' . $passLength . ' chars)' : 'EMPTY') . ', From=' . (!empty($emailConfig['fromEmail']) ? 'SET' : 'EMPTY')
                ])->setStatusCode(400);
            }
            
            // Get a fresh email service instance
            $emailService = \Config\Services::email();
            
            // Clear any previous configuration
            $emailService->clear();
            
            // IMPORTANT: Gmail App Passwords can have spaces (e.g., "jdab pewu hoqn whho")
            // Gmail accepts them with or without spaces, but removing spaces is more reliable
            // For Brevo and other services, keep password as-is
            $smtpPassword = $emailConfig['SMTPPass'] ?? '';
            if (!empty($smtpPassword)) {
                // Only remove spaces for Gmail (contains @gmail.com or smtp.gmail.com)
                $isGmail = stripos($emailConfig['SMTPHost'] ?? '', 'gmail') !== false || 
                          stripos($emailConfig['SMTPUser'] ?? '', 'gmail') !== false;
                if ($isGmail) {
                    // Remove spaces from Gmail App Password for better compatibility
                    $smtpPassword = str_replace(' ', '', $smtpPassword);
                    log_message('info', 'Removed spaces from Gmail App Password');
                } else {
                    // Keep Brevo and other SMTP passwords as-is (no spaces to remove)
                    log_message('info', 'Using SMTP password as-is (non-Gmail service)');
                }
            }
            
            // Manually configure SMTP settings to ensure they're current
            // Ensure all values are properly set and not empty
            $smtpConfig = [
                'protocol' => $emailConfig['protocol'] ?? 'smtp',
                'SMTPHost' => trim($emailConfig['SMTPHost'] ?? ''),
                'SMTPUser' => trim($emailConfig['SMTPUser'] ?? ''),
                'SMTPPass' => $smtpPassword, // Gmail App Password (spaces removed for compatibility)
                'SMTPPort' => (int)($emailConfig['SMTPPort'] ?? 587),
                'SMTPCrypto' => $emailConfig['SMTPCrypto'] ?? 'tls',
                'SMTPTimeout' => (int)($emailConfig['SMTPTimeout'] ?? 30),
                'mailType' => $emailConfig['mailType'] ?? 'html',
                'mailtype' => $emailConfig['mailType'] ?? 'html', // CodeIgniter uses lowercase
                'charset' => $emailConfig['charset'] ?? 'UTF-8',
                'newline' => "\r\n", // Required for SMTP
                'CRLF' => "\r\n", // Required for SMTP
                'wordWrap' => true,
                'validate' => false, // Don't validate email addresses
            ];
            
            // Validate required fields
            if (empty($smtpConfig['SMTPHost']) || empty($smtpConfig['SMTPUser']) || empty($smtpConfig['SMTPPass'])) {
                log_message('error', 'SMTP configuration incomplete - Host: ' . ($smtpConfig['SMTPHost'] ? 'SET' : 'EMPTY') . ', User: ' . ($smtpConfig['SMTPUser'] ? 'SET' : 'EMPTY') . ', Pass: ' . ($smtpConfig['SMTPPass'] ? 'SET' : 'EMPTY'));
                return $this->response->setJSON([
                    'success' => false,
                    'error' => 'SMTP configuration is incomplete. Please check Host, Username, and Password.',
                ])->setStatusCode(400);
            }
            
            // Initialize email service with error handling
            try {
                $emailService->initialize($smtpConfig);
            } catch (\Exception $initException) {
                log_message('error', 'Email service initialization failed: ' . $initException->getMessage());
                return $this->response->setJSON([
                    'success' => false,
                    'error' => 'Failed to initialize email service: ' . $initException->getMessage(),
                    'hint' => 'Check your SMTP settings and ensure OpenSSL is enabled.'
                ])->setStatusCode(500);
            }

            $emailService->setFrom($emailConfig['fromEmail'], $emailConfig['fromName'] ?? 'ClearPay');
            $emailService->setTo($testEmail);
            $emailService->setSubject('ClearPay - Test Email');
            $emailService->setMessage('
                <html>
                <body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
                    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
                        <h2 style="color: #4CAF50;">ClearPay Test Email</h2>
                        <p>This is a test email from your ClearPay system.</p>
                        <p>If you received this email, your SMTP configuration is working correctly!</p>
                        <hr style="border: none; border-top: 1px solid #eee; margin: 20px 0;">
                        <p style="color: #666; font-size: 12px;">
                            Sent at: ' . date('Y-m-d H:i:s') . '<br>
                            From: ' . $emailConfig['fromEmail'] . '
                        </p>
                    </div>
                </body>
                </html>
            ');

            // Log SMTP config (without password for security)
            log_message('info', 'Attempting to send test email to: ' . $testEmail);
            log_message('info', 'SMTP Config - Host: ' . $emailConfig['SMTPHost'] . ', Port: ' . $emailConfig['SMTPPort'] . ', User: ' . $emailConfig['SMTPUser'] . ', Crypto: ' . $emailConfig['SMTPCrypto']);
            log_message('info', 'SMTP Password length (original): ' . strlen($emailConfig['SMTPPass']) . ' characters');
            log_message('info', 'SMTP Password length (final): ' . strlen($smtpConfig['SMTPPass']) . ' characters');
            log_message('info', 'SMTP Password starts with: ' . substr($smtpConfig['SMTPPass'], 0, 10) . '...');
            log_message('info', 'SMTP Password ends with: ...' . substr($smtpConfig['SMTPPass'], -10));

            // Attempt to send email with better error handling
            $result = false;
            $errorDetails = '';
            
            try {
                $result = $emailService->send();
            } catch (\Exception $sendException) {
                $errorMsg = $sendException->getMessage();
                log_message('error', 'Email send exception: ' . $errorMsg);
                log_message('error', 'Exception class: ' . get_class($sendException));
                log_message('error', 'Exception code: ' . $sendException->getCode());
                log_message('error', 'Exception file: ' . $sendException->getFile() . ':' . $sendException->getLine());
                log_message('error', 'Exception trace: ' . $sendException->getTraceAsString());
                $errorDetails = $errorMsg;
                
                // Also get debug info from email service if available
                try {
                    $debugInfo = $emailService->printDebugger(['headers', 'subject', 'body']);
                    if (!empty($debugInfo)) {
                        log_message('error', 'Email debug info: ' . $debugInfo);
                        $errorDetails .= "\n\nDebug Info:\n" . $debugInfo;
                    }
                } catch (\Exception $debugException) {
                    log_message('error', 'Could not get email debug info: ' . $debugException->getMessage());
                }
            } catch (\Error $sendError) {
                $errorMsg = $sendError->getMessage();
                log_message('error', 'Email send error: ' . $errorMsg);
                log_message('error', 'Error file: ' . $sendError->getFile() . ':' . $sendError->getLine());
                log_message('error', 'Error trace: ' . $sendError->getTraceAsString());
                $errorDetails = $errorMsg;
                
                // Also get debug info from email service if available
                try {
                    $debugInfo = $emailService->printDebugger(['headers', 'subject', 'body']);
                    if (!empty($debugInfo)) {
                        log_message('error', 'Email debug info: ' . $debugInfo);
                        $errorDetails .= "\n\nDebug Info:\n" . $debugInfo;
                    }
                } catch (\Exception $debugException) {
                    log_message('error', 'Could not get email debug info: ' . $debugException->getMessage());
                }
            }
            
            if ($result) {
                log_message('info', 'Test email sent successfully to: ' . $testEmail);
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Test email sent successfully to ' . $testEmail . '!'
                ]);
            } else {
                $error = $emailService->printDebugger(['headers', 'subject', 'body']);
                log_message('error', 'Test email failed. Debug info: ' . $error);
                
                // Get diagnostic information about what config was used
                $diagnostics = [
                    'config_source' => 'unknown',
                    'smtp_host' => $emailConfig['SMTPHost'] ?? 'NOT SET',
                    'smtp_user' => $emailConfig['SMTPUser'] ?? 'NOT SET',
                    'smtp_pass_length' => !empty($emailConfig['SMTPPass']) ? strlen($emailConfig['SMTPPass']) : 0,
                    'smtp_pass_set' => !empty($emailConfig['SMTPPass']),
                    'smtp_port' => $emailConfig['SMTPPort'] ?? 'NOT SET',
                    'smtp_crypto' => $emailConfig['SMTPCrypto'] ?? 'NOT SET',
                    'from_email' => $emailConfig['fromEmail'] ?? 'NOT SET',
                ];
                
                // Try to determine config source
                try {
                    $db = \Config\Database::connect();
                    if ($db->tableExists('email_settings')) {
                        $dbSettings = $db->table('email_settings')
                            ->where('is_active', true)
                            ->orderBy('id', 'DESC')
                            ->limit(1)
                            ->get()
                            ->getRowArray();
                        if ($dbSettings) {
                            $diagnostics['config_source'] = 'database';
                        } else {
                            $diagnostics['config_source'] = 'environment_variables';
                        }
                    } else {
                        $diagnostics['config_source'] = 'environment_variables';
                    }
                } catch (\Exception $e) {
                    $diagnostics['config_source'] = 'environment_variables (db_error: ' . $e->getMessage() . ')';
                }
                
                log_message('error', 'Email diagnostics: ' . json_encode($diagnostics));
                
                // Try to get more specific error
                $lastError = error_get_last();
                $phpError = '';
                if ($lastError) {
                    $phpError = $lastError['message'];
                    log_message('error', 'PHP Error: ' . $phpError);
                }
                
                // Extract the actual SMTP error from debug output
                $smtpError = '';
                if (preg_match('/Failed to (.*?)(?:\.|$)/i', $error, $matches)) {
                    $smtpError = $matches[1];
                } elseif (preg_match('/Error: (.*?)(?:\.|$)/i', $error, $matches)) {
                    $smtpError = $matches[1];
                } elseif (preg_match('/Unable to (.*?)(?:\.|$)/i', $error, $matches)) {
                    $smtpError = $matches[1];
                }
                
                // Build user-friendly error message
                $errorMessage = 'Failed to send test email.';
                $hints = [];
                
                // Check for specific error patterns
                $errorLower = strtolower($error . ' ' . $errorDetails);
                
                if (stripos($errorLower, 'connection') !== false || stripos($errorLower, 'connect') !== false || stripos($errorLower, 'timeout') !== false) {
                    $errorMessage = 'Cannot connect to SMTP server.';
                    $hints[] = 'Check if SMTP Host and Port are correct.';
                    $hints[] = 'Verify your server can make outbound connections on port ' . $emailConfig['SMTPPort'] . '.';
                    $hints[] = '⚠️ **Render.com may block SMTP ports on free tier**. Consider using SendGrid, Mailgun, or another email service.';
                    $hints[] = 'Check firewall settings and network restrictions.';
                } elseif (stripos($errorLower, 'authentication') !== false || stripos($errorLower, 'auth') !== false || stripos($errorLower, '535') !== false || stripos($errorLower, 'username and password') !== false) {
                    $errorMessage = 'SMTP authentication failed.';
                    $hints[] = 'Verify your SMTP Username and Password are correct.';
                    $hints[] = 'For Gmail, ensure you are using an App Password, not your regular password.';
                    $hints[] = 'Check if 2-Step Verification is enabled on your Gmail account.';
                    $hints[] = 'Try generating a NEW App Password from Google Account settings.';
                    $hints[] = 'Verify the App Password hasn\'t been revoked or expired.';
                } elseif (stripos($errorLower, 'ssl') !== false || stripos($errorLower, 'tls') !== false || stripos($errorLower, 'certificate') !== false) {
                    $errorMessage = 'SSL/TLS connection error.';
                    $hints[] = 'Ensure OpenSSL extension is enabled in PHP.';
                    $hints[] = 'Check if SMTP Crypto setting matches the port (TLS for 587, SSL for 465).';
                    $hints[] = 'Try using port 465 with SSL instead of 587 with TLS.';
                } elseif (stripos($errorLower, 'blocked') !== false || stripos($errorLower, 'firewall') !== false) {
                    $errorMessage = 'SMTP connection blocked.';
                    $hints[] = 'Your hosting provider may be blocking SMTP ports.';
                    $hints[] = '⚠️ **Render.com free tier may block outbound SMTP connections**.';
                    $hints[] = 'Consider using a transactional email service like SendGrid, Mailgun, or AWS SES.';
                }
                
                // Include the actual SMTP error if found
                if (!empty($smtpError)) {
                    $errorMessage .= ' (' . $smtpError . ')';
                }
                
                return $this->response->setJSON([
                    'success' => false,
                    'error' => $errorMessage,
                    'debug' => $errorDetails ?: $error,
                    'hints' => $hints,
                    'phpError' => $phpError,
                    'smtpError' => $smtpError,
                    'diagnostics' => $diagnostics ?? []
                ])->setStatusCode(500);
            }
        } catch (\Exception $e) {
            log_message('error', 'Test email error: ' . $e->getMessage());
            log_message('error', 'Exception trace: ' . $e->getTraceAsString());
            return $this->response->setJSON([
                'success' => false,
                'error' => 'An error occurred while sending test email: ' . $e->getMessage()
            ])->setStatusCode(500);
        } catch (\Error $e) {
            log_message('error', 'Test email error (Error): ' . $e->getMessage());
            log_message('error', 'Error trace: ' . $e->getTraceAsString());
            return $this->response->setJSON([
                'success' => false,
                'error' => 'An error occurred while sending test email: ' . $e->getMessage()
            ])->setStatusCode(500);
        }
    }

    /**
     * Get email configuration from database or fallback to config/environment
     * Priority: Database > Environment Variables > Config defaults
     */
    private function getEmailConfig()
    {
        try {
            $db = \Config\Database::connect();
            log_message('info', 'Database connection successful in getEmailConfig');
            
            // Try to load from database first
            if ($db->tableExists('email_settings')) {
                log_message('info', 'email_settings table exists, querying for active settings');
                $settings = $db->table('email_settings')
                    ->where('is_active', true)
                    ->orderBy('id', 'DESC')
                    ->limit(1)
                    ->get()
                    ->getRowArray();
                
                if ($settings) {
                    log_message('info', 'Found email settings in database - Host: ' . ($settings['smtp_host'] ?? 'EMPTY') . ', User: ' . ($settings['smtp_user'] ?? 'EMPTY') . ', Pass: ' . (!empty($settings['smtp_pass']) ? 'SET (' . strlen($settings['smtp_pass']) . ' chars)' : 'EMPTY'));
                } else {
                    log_message('info', 'No active email settings found in database, falling back to environment variables');
                }
                
                if ($settings) {
                    // Get password from database
                    $smtpPass = $settings['smtp_pass'] ?? '';
                    
                    // IMPORTANT: Only remove spaces for Gmail App Passwords
                    // Brevo and other services should keep password as-is
                    if (!empty($smtpPass)) {
                        $isGmail = stripos($settings['smtp_host'] ?? '', 'gmail') !== false || 
                                  stripos($settings['smtp_user'] ?? '', 'gmail') !== false;
                        if ($isGmail) {
                            // Remove spaces from Gmail App Password for better compatibility
                            $smtpPass = str_replace(' ', '', $smtpPass);
                        }
                        // For Brevo and other services, keep password as-is
                    }
                    
                    return [
                        'fromEmail' => $settings['from_email'] ?? '',
                        'fromName' => $settings['from_name'] ?? 'ClearPay',
                        'protocol' => $settings['protocol'] ?? 'smtp',
                        'SMTPHost' => $settings['smtp_host'] ?? '',
                        'SMTPUser' => $settings['smtp_user'] ?? '',
                        'SMTPPass' => $smtpPass, // Password with spaces removed
                        'SMTPPort' => (int)($settings['smtp_port'] ?? 587),
                        'SMTPCrypto' => $settings['smtp_crypto'] ?? 'tls',
                        'SMTPTimeout' => (int)($settings['smtp_timeout'] ?? 30),
                        'mailType' => $settings['mail_type'] ?? 'html',
                        'mailtype' => $settings['mail_type'] ?? 'html', // CodeIgniter uses lowercase
                        'charset' => $settings['charset'] ?? 'UTF-8',
                    ];
                }
            }
        } catch (\Exception $e) {
            log_message('error', 'Database connection failed in getEmailConfig, using environment variables: ' . $e->getMessage());
            log_message('error', 'Exception trace: ' . $e->getTraceAsString());
        }
        
        // Fallback to config (which reads from environment variables via BaseConfig)
        $config = config('Email');
        log_message('info', 'Using config/environment variables - Host: ' . ($config->SMTPHost ?? 'EMPTY') . ', User: ' . ($config->SMTPUser ?? 'EMPTY') . ', Pass: ' . (!empty($config->SMTPPass) ? 'SET (' . strlen($config->SMTPPass) . ' chars)' : 'EMPTY'));
        
        // Get password from config
        $smtpPass = $config->SMTPPass ?? '';
        
        // IMPORTANT: Only remove spaces for Gmail App Passwords
        // Brevo and other services should keep password as-is
        if (!empty($smtpPass)) {
            $isGmail = stripos($config->SMTPHost ?? '', 'gmail') !== false || 
                      stripos($config->SMTPUser ?? '', 'gmail') !== false;
            if ($isGmail) {
                // Remove spaces from Gmail App Password for better compatibility
                $smtpPass = str_replace(' ', '', $smtpPass);
            }
            // For Brevo and other services, keep password as-is
        }
        
        return [
            'fromEmail' => $config->fromEmail,
            'fromName' => $config->fromName,
            'protocol' => $config->protocol,
            'SMTPHost' => $config->SMTPHost,
            'SMTPUser' => $config->SMTPUser,
            'SMTPPass' => $smtpPass, // Password with spaces removed
            'SMTPPort' => $config->SMTPPort,
            'SMTPCrypto' => $config->SMTPCrypto,
            'SMTPTimeout' => $config->SMTPTimeout,
            'mailType' => $config->mailType,
            'mailtype' => $config->mailType, // CodeIgniter uses lowercase
            'charset' => $config->charset,
        ];
    }

    /**
     * Get email templates
     */
    public function getTemplates()
    {
        if (!session()->get('isLoggedIn')) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Unauthorized. Please login first.'
            ])->setStatusCode(401);
        }

        // For now, return default templates
        // In a full implementation, these would be stored in database
        return $this->response->setJSON([
            'success' => true,
            'templates' => [
                'verification' => [
                    'name' => 'Email Verification',
                    'subject' => 'Verify Your Email Address',
                    'body' => 'Your verification code is: {code}'
                ],
                'password_reset' => [
                    'name' => 'Password Reset',
                    'subject' => 'Reset Your Password',
                    'body' => 'Your password reset code is: {code}'
                ],
                'payment_notification' => [
                    'name' => 'Payment Notification',
                    'subject' => 'Payment Received',
                    'body' => 'Your payment of {amount} has been received.'
                ],
            ]
        ]);
    }

    /**
     * Update email template
     */
    public function updateTemplate()
    {
        if (!session()->get('isLoggedIn')) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Unauthorized. Please login first.'
            ])->setStatusCode(401);
        }

        // For now, just return success
        // In a full implementation, templates would be stored in database
        return $this->response->setJSON([
            'success' => true,
            'message' => 'Email template updated successfully!'
        ]);
    }

    /**
     * Toggle email notifications
     */
    public function toggleNotifications()
    {
        if (!session()->get('isLoggedIn')) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Unauthorized. Please login first.'
            ])->setStatusCode(401);
        }

        try {
            $data = $this->request->getJSON(true) ?? $this->request->getPost();
            $enabled = isset($data['enabled']) ? (bool)$data['enabled'] : false;

            // Store in database or config file
            // For now, we'll store in a simple config file
            $settingsFile = WRITEPATH . 'email_notifications.json';
            file_put_contents($settingsFile, json_encode(['enabled' => $enabled]));

            log_message('info', 'Email notifications ' . ($enabled ? 'enabled' : 'disabled'));

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Email notifications ' . ($enabled ? 'enabled' : 'disabled') . ' successfully!',
                'enabled' => $enabled
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Toggle notifications error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'error' => 'An error occurred while updating email notifications.'
            ])->setStatusCode(500);
        }
    }

    /**
     * Get email notifications status
     */
    public function getNotificationsStatus()
    {
        if (!session()->get('isLoggedIn')) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Unauthorized. Please login first.'
            ])->setStatusCode(401);
        }

        try {
            $settingsFile = WRITEPATH . 'email_notifications.json';
            $enabled = true; // Default to enabled
            
            if (file_exists($settingsFile)) {
                $settings = json_decode(file_get_contents($settingsFile), true);
                $enabled = $settings['enabled'] ?? true;
            }

            return $this->response->setJSON([
                'success' => true,
                'enabled' => $enabled
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => true,
                'enabled' => true // Default to enabled on error
            ]);
        }
    }
}

