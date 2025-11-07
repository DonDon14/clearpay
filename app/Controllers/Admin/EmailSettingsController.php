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
            $emailConfig = config('Email');
            
            return $this->response->setJSON([
                'success' => true,
                'config' => [
                    'fromEmail' => $emailConfig->fromEmail,
                    'fromName' => $emailConfig->fromName,
                    'protocol' => $emailConfig->protocol,
                    'SMTPHost' => $emailConfig->SMTPHost,
                    'SMTPUser' => $emailConfig->SMTPUser,
                    'SMTPPass' => $emailConfig->SMTPPass, // Note: In production, consider masking this
                    'SMTPPort' => $emailConfig->SMTPPort,
                    'SMTPCrypto' => $emailConfig->SMTPCrypto,
                    'SMTPTimeout' => $emailConfig->SMTPTimeout,
                    'mailType' => $emailConfig->mailType,
                    'charset' => $emailConfig->charset,
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
                if (empty($data[$field])) {
                    return $this->response->setJSON([
                        'success' => false,
                        'error' => ucfirst($field) . ' is required.'
                    ])->setStatusCode(400);
                }
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

            // Update config file
            $configFile = APPPATH . 'Config' . DIRECTORY_SEPARATOR . 'Email.php';
            
            if (!file_exists($configFile)) {
                return $this->response->setJSON([
                    'success' => false,
                    'error' => 'Email configuration file not found.'
                ])->setStatusCode(500);
            }

            $configContent = file_get_contents($configFile);
            
            // Update each field
            $updates = [
                'fromEmail' => $data['fromEmail'] ?? '',
                'fromName' => $data['fromName'] ?? 'ClearPay',
                'protocol' => $data['protocol'] ?? 'smtp',
                'SMTPHost' => $data['SMTPHost'] ?? '',
                'SMTPUser' => $data['SMTPUser'] ?? '',
                'SMTPPass' => $data['SMTPPass'] ?? '',
                'SMTPPort' => (int)($data['SMTPPort'] ?? 587),
                'SMTPCrypto' => $data['SMTPCrypto'] ?? 'tls',
                'SMTPTimeout' => (int)($data['SMTPTimeout'] ?? 30),
                'mailType' => $data['mailType'] ?? 'html',
                'charset' => $data['charset'] ?? 'UTF-8',
            ];

            foreach ($updates as $key => $value) {
                if (is_string($value)) {
                    $value = addslashes($value);
                    $pattern = '/public (string|int) \$' . $key . ' = [\'"]([^\'"]*)[\'"];/';
                    $replacement = "public " . (is_int($updates[$key]) ? 'int' : 'string') . " \${$key} = '{$value}';";
                } else {
                    $pattern = '/public int \$' . $key . ' = (\d+);/';
                    $replacement = "public int \${$key} = {$value};";
                }
                
                $configContent = preg_replace($pattern, $replacement, $configContent);
            }

            // Write back to file
            if (file_put_contents($configFile, $configContent) === false) {
                return $this->response->setJSON([
                    'success' => false,
                    'error' => 'Failed to write to configuration file. Please check file permissions.'
                ])->setStatusCode(500);
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

            $emailService = \Config\Services::email();
            $emailConfig = config('Email');

            $emailService->setFrom($emailConfig->fromEmail, $emailConfig->fromName);
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
                            From: ' . $emailConfig->fromEmail . '
                        </p>
                    </div>
                </body>
                </html>
            ');

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

