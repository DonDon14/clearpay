<?php

namespace App\Services;

class EmailDeliveryService
{
    private EmailConfigService $emailConfigService;

    public function __construct(?EmailConfigService $emailConfigService = null)
    {
        $this->emailConfigService = $emailConfigService ?? new EmailConfigService();
    }

    public function sendTemplateEmail(
        string $toEmail,
        string $subject,
        string $htmlMessage,
        ?string $textMessage = null,
        string $context = 'transactional email'
    ): bool {
        if (empty($toEmail)) {
            log_message('info', "No recipient email for {$context}, skipping send");
            return false;
        }

        $textMessage = $textMessage ?? strip_tags($htmlMessage);

        try {
            $emailConfig = $this->emailConfigService->getConfig();

            if ($this->sendViaBrevo($emailConfig, $toEmail, $subject, $htmlMessage, $textMessage, $context)) {
                return true;
            }

            return $this->sendViaSmtp($emailConfig, $toEmail, $subject, $htmlMessage, $context);
        } catch (\Exception $e) {
            log_message('error', "Failed to send {$context}: " . $e->getMessage());
            log_message('error', 'Exception details: ' . $e->getTraceAsString());
            return false;
        } catch (\Error $e) {
            log_message('error', "Failed to send {$context} (Error): " . $e->getMessage());
            log_message('error', 'Exception details: ' . $e->getTraceAsString());
            return false;
        }
    }

    private function sendViaBrevo(
        array $emailConfig,
        string $toEmail,
        string $subject,
        string $htmlMessage,
        string $textMessage,
        string $context
    ): bool {
        $brevoApiKey = $_ENV['BREVO_API_KEY'] ?? getenv('BREVO_API_KEY') ?: null;
        if (empty($brevoApiKey)) {
            return false;
        }

        try {
            log_message('info', "Attempting to send {$context} via Brevo API to: {$toEmail}");

            if (!class_exists('\App\Services\BrevoEmailService')) {
                log_message('error', 'BrevoEmailService class not found. Falling back to SMTP.');
                return false;
            }

            $brevoService = new \App\Services\BrevoEmailService(
                $brevoApiKey,
                $emailConfig['fromEmail'] ?? '',
                $emailConfig['fromName'] ?? 'ClearPay'
            );

            $result = $brevoService->send($toEmail, $subject, $htmlMessage, $textMessage);
            if (!empty($result['success'])) {
                log_message('info', "{$context} sent successfully via Brevo API to: {$toEmail}");
                return true;
            }

            log_message(
                'error',
                "Brevo API failed to send {$context}: " . ($result['error'] ?? 'Unknown error') . '. Falling back to SMTP.'
            );
            return false;
        } catch (\Throwable $e) {
            log_message('error', 'Brevo API exception while sending ' . $context . ': ' . $e->getMessage() . '. Falling back to SMTP.');
            return false;
        }
    }

    private function sendViaSmtp(
        array $emailConfig,
        string $toEmail,
        string $subject,
        string $htmlMessage,
        string $context
    ): bool {
        if (empty($emailConfig['SMTPUser']) || empty($emailConfig['SMTPPass']) || empty($emailConfig['SMTPHost'])) {
            log_message('error', "SMTP configuration incomplete for {$context}");
            return false;
        }

        $emailService = \Config\Services::email();
        $emailService->clear();

        $smtpConfig = [
            'protocol' => $emailConfig['protocol'] ?? 'smtp',
            'SMTPHost' => trim($emailConfig['SMTPHost'] ?? ''),
            'SMTPUser' => trim($emailConfig['SMTPUser'] ?? ''),
            'SMTPPass' => $emailConfig['SMTPPass'] ?? '',
            'SMTPPort' => (int) ($emailConfig['SMTPPort'] ?? 587),
            'SMTPCrypto' => $emailConfig['SMTPCrypto'] ?? 'tls',
            'SMTPTimeout' => (int) ($emailConfig['SMTPTimeout'] ?? 30),
            'mailType' => $emailConfig['mailType'] ?? 'html',
            'mailtype' => $emailConfig['mailType'] ?? 'html',
            'charset' => $emailConfig['charset'] ?? 'UTF-8',
            'newline' => "\r\n",
            'CRLF' => "\r\n",
            'wordWrap' => true,
            'validate' => false,
        ];

        if (empty($smtpConfig['SMTPHost']) || empty($smtpConfig['SMTPUser']) || empty($smtpConfig['SMTPPass'])) {
            log_message(
                'error',
                "SMTP configuration validation failed for {$context} - Host: " .
                ($smtpConfig['SMTPHost'] ? 'SET' : 'EMPTY') .
                ', User: ' . ($smtpConfig['SMTPUser'] ? 'SET' : 'EMPTY') .
                ', Pass: ' . ($smtpConfig['SMTPPass'] ? 'SET' : 'EMPTY')
            );
            return false;
        }

        $emailService->initialize($smtpConfig);
        $emailService->setFrom($emailConfig['fromEmail'] ?? '', $emailConfig['fromName'] ?? 'ClearPay');
        $emailService->setTo($toEmail);
        $emailService->setSubject($subject);
        $emailService->setMessage($htmlMessage);

        log_message(
            'info',
            "Attempting to send {$context} to: {$toEmail} using SMTP: {$smtpConfig['SMTPHost']}:{$smtpConfig['SMTPPort']}"
        );

        $result = $emailService->send();
        if ($result) {
            log_message('info', "{$context} sent successfully to: {$toEmail}");
            return true;
        }

        $error = $emailService->printDebugger(['headers', 'subject']);
        log_message('error', "Failed to send {$context}: {$error}");
        return false;
    }
}
