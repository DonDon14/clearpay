<?php

namespace App\Services;

use Exception;

/**
 * Brevo Email Service
 * 
 * Uses Brevo API (HTTPS) instead of SMTP to bypass Render's port blocking
 */
class BrevoEmailService
{
    private $apiKey;
    private $fromEmail;
    private $fromName;
    private $apiUrl = 'https://api.brevo.com/v3/smtp/email';

    public function __construct($apiKey, $fromEmail, $fromName = 'ClearPay')
    {
        $this->apiKey = $apiKey;
        $this->fromEmail = $fromEmail;
        $this->fromName = $fromName;
    }

    /**
     * Send email using Brevo API
     * 
     * @param string $to Recipient email address
     * @param string $subject Email subject
     * @param string $htmlContent HTML email content
     * @param string $textContent Plain text email content (optional)
     * @return array ['success' => bool, 'messageId' => string|null, 'error' => string|null]
     */
    public function send($to, $subject, $htmlContent, $textContent = null)
    {
        try {
            // Prepare email data
            $data = [
                'sender' => [
                    'name' => $this->fromName,
                    'email' => $this->fromEmail
                ],
                'to' => [
                    [
                        'email' => $to
                    ]
                ],
                'subject' => $subject,
                'htmlContent' => $htmlContent
            ];

            // Add text content if provided
            if (!empty($textContent)) {
                $data['textContent'] = $textContent;
            } else {
                // Extract text from HTML as fallback
                $data['textContent'] = strip_tags($htmlContent);
            }

            // Make API request
            $ch = curl_init($this->apiUrl);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode($data),
                CURLOPT_HTTPHEADER => [
                    'accept: application/json',
                    'api-key: ' . $this->apiKey,
                    'content-type: application/json'
                ],
                CURLOPT_TIMEOUT => 30,
                CURLOPT_SSL_VERIFYPEER => true
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            // Check for cURL errors
            if ($curlError) {
                log_message('error', 'Brevo API cURL error: ' . $curlError);
                return [
                    'success' => false,
                    'messageId' => null,
                    'error' => 'Connection error: ' . $curlError
                ];
            }

            // Parse response
            $responseData = json_decode($response, true);

            // Check HTTP status code
            if ($httpCode >= 200 && $httpCode < 300) {
                log_message('info', 'Brevo API email sent successfully. Message ID: ' . ($responseData['messageId'] ?? 'unknown'));
                return [
                    'success' => true,
                    'messageId' => $responseData['messageId'] ?? null,
                    'error' => null
                ];
            } else {
                $errorMsg = $responseData['message'] ?? 'Unknown error';
                if (isset($responseData['code'])) {
                    $errorMsg = '[' . $responseData['code'] . '] ' . $errorMsg;
                }
                log_message('error', 'Brevo API error (HTTP ' . $httpCode . '): ' . $errorMsg);
                log_message('error', 'Brevo API response: ' . $response);
                
                return [
                    'success' => false,
                    'messageId' => null,
                    'error' => $errorMsg
                ];
            }
        } catch (Exception $e) {
            log_message('error', 'Brevo API exception: ' . $e->getMessage());
            return [
                'success' => false,
                'messageId' => null,
                'error' => 'Exception: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Check if Brevo API is available (API key is set)
     */
    public static function isAvailable($apiKey)
    {
        return !empty($apiKey);
    }
}

