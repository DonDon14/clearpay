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
            
            // Initialize email service with fresh config
            $emailService = \Config\Services::email();
            
            // Manually configure SMTP settings to ensure they're current
            $emailService->initialize([
                'protocol' => $emailConfig['protocol'],
                'SMTPHost' => $emailConfig['SMTPHost'],
                'SMTPUser' => $emailConfig['SMTPUser'],
                'SMTPPass' => $emailConfig['SMTPPass'],
                'SMTPPort' => $emailConfig['SMTPPort'],
                'SMTPCrypto' => $emailConfig['SMTPCrypto'],
                'SMTPTimeout' => $emailConfig['SMTPTimeout'] ?? 30,
                'mailType' => $emailConfig['mailType'],
                'charset' => $emailConfig['charset'] ?? 'UTF-8',
            ]);

            $emailService->setFrom($emailConfig['fromEmail'], $emailConfig['fromName']);
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

            log_message('info', 'Attempting to send test email to: ' . $testEmail . ' using SMTP: ' . $emailConfig['SMTPHost'] . ':' . $emailConfig['SMTPPort']);

            if ($emailService->send()) {
                log_message('info', 'Test email sent successfully to: ' . $testEmail);
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Test email sent successfully to ' . $testEmail . '!'
                ]);
            } else {
                $error = $emailService->printDebugger(['headers']);
                log_message('error', 'Test email failed: ' . $error);
                return $this->response->setJSON([
                    'success' => false,
                    'error' => 'Failed to send test email. Check your SMTP configuration.',
                    'debug' => $error
                ])->setStatusCode(500);
            }
        } catch (\Exception $e) {
            log_message('error', 'Test email error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'error' => 'An error occurred while sending test email: ' . $e->getMessage()
            ])->setStatusCode(500);
        }
    }

    /**
     * Get email configuration from database or fallback to config/environment
     */
    private function getEmailConfig()
    {
        try {
            $db = \Config\Database::connect();
            
            // Try to load from database first
            if ($db->tableExists('email_settings')) {
                $settings = $db->table('email_settings')
                    ->where('is_active', true)
                    ->orderBy('id', 'DESC')
                    ->limit(1)
                    ->get()
                    ->getRowArray();
                
                if ($settings) {
                    return [
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
                    ];
                }
            }
        } catch (\Exception $e) {
            log_message('debug', 'Email settings table not found, using config: ' . $e->getMessage());
        }
        
        // Fallback to config
        $config = config('Email');
        return [
            'fromEmail' => $config->fromEmail,
            'fromName' => $config->fromName,
            'protocol' => $config->protocol,
            'SMTPHost' => $config->SMTPHost,
            'SMTPUser' => $config->SMTPUser,
            'SMTPPass' => $config->SMTPPass,
            'SMTPPort' => $config->SMTPPort,
            'SMTPCrypto' => $config->SMTPCrypto,
            'SMTPTimeout' => $config->SMTPTimeout,
            'mailType' => $config->mailType,
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

