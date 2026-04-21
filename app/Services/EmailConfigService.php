<?php

namespace App\Services;

class EmailConfigService
{
    /**
     * Get email configuration from database or fallback to config/environment.
     *
     * @return array<string, mixed>
     */
    public function getConfig(): array
    {
        try {
            $db = \Config\Database::connect();

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
                        'SMTPPort' => (int) ($settings['smtp_port'] ?? 587),
                        'SMTPCrypto' => $settings['smtp_crypto'] ?? 'tls',
                        'SMTPTimeout' => (int) ($settings['smtp_timeout'] ?? 30),
                        'mailType' => $settings['mail_type'] ?? 'html',
                        'charset' => $settings['charset'] ?? 'UTF-8',
                    ];
                }
            }
        } catch (\Exception $e) {
            log_message('debug', 'Email settings table not found, using config: ' . $e->getMessage());
        }

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
}
