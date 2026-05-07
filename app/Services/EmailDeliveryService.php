<?php

namespace App\Services;

class EmailDeliveryService
{
    /**
     * Send an HTML email using Brevo API when configured, otherwise SMTP.
     *
     * @param array<string, mixed> $emailConfig
     */
    public function send(array $emailConfig, string $to, string $subject, string $htmlMessage, ?string $textMessage = null): bool
    {
        if (trim($to) === '') {
            log_message('info', 'Email send skipped: recipient is empty');
            return false;
        }

        $textMessage = $textMessage ?: trim(strip_tags($htmlMessage));

        $brevoApiKey = $_ENV['BREVO_API_KEY'] ?? getenv('BREVO_API_KEY') ?: null;
        if (!empty($brevoApiKey)) {
            try {
                $brevoService = new BrevoEmailService(
                    $brevoApiKey,
                    (string) ($emailConfig['fromEmail'] ?? ''),
                    (string) ($emailConfig['fromName'] ?? 'ClearPay')
                );

                $result = $brevoService->send($to, $subject, $htmlMessage, $textMessage);
                if (!empty($result['success'])) {
                    log_message('info', 'Email sent via Brevo API to: ' . $to);
                    return true;
                }

                log_message('error', 'Brevo API email failed: ' . ($result['error'] ?? 'Unknown error') . '. Falling back to SMTP.');
            } catch (\Throwable $e) {
                log_message('error', 'Brevo API email exception: ' . $e->getMessage() . '. Falling back to SMTP.');
            }
        }

        if (
            empty($emailConfig['fromEmail'])
            || empty($emailConfig['SMTPHost'])
            || empty($emailConfig['SMTPUser'])
            || empty($emailConfig['SMTPPass'])
        ) {
            log_message('error', 'SMTP configuration incomplete. Email not sent to: ' . $to);
            return false;
        }

        $smtpConfig = [
            'protocol' => $emailConfig['protocol'] ?? 'smtp',
            'SMTPHost' => trim((string) ($emailConfig['SMTPHost'] ?? '')),
            'SMTPUser' => trim((string) ($emailConfig['SMTPUser'] ?? '')),
            'SMTPPass' => (string) ($emailConfig['SMTPPass'] ?? ''),
            'SMTPPort' => (int) ($emailConfig['SMTPPort'] ?? 587),
            'SMTPCrypto' => $emailConfig['SMTPCrypto'] ?? 'tls',
            'SMTPTimeout' => max(10, (int) ($emailConfig['SMTPTimeout'] ?? 30)),
            'mailType' => $emailConfig['mailType'] ?? 'html',
            'mailtype' => $emailConfig['mailType'] ?? 'html',
            'charset' => $emailConfig['charset'] ?? 'UTF-8',
            'newline' => "\r\n",
            'CRLF' => "\r\n",
            'wordWrap' => true,
            'validate' => false,
        ];

        try {
            $emailService = \Config\Services::email();
            $emailService->clear();
            $emailService->initialize($smtpConfig);
            $emailService->setFrom((string) $emailConfig['fromEmail'], (string) ($emailConfig['fromName'] ?? 'ClearPay'));
            $emailService->setTo($to);
            $emailService->setSubject($subject);
            $emailService->setMessage($htmlMessage);

            if ($emailService->send()) {
                log_message('info', 'Email sent via SMTP to: ' . $to);
                return true;
            }

            log_message('error', 'SMTP email failed: ' . $emailService->printDebugger(['headers', 'subject']));
            return false;
        } catch (\Throwable $e) {
            log_message('error', 'SMTP email exception: ' . $e->getMessage());
            return false;
        }
    }
}
