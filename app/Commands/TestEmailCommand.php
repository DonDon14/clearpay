<?php

namespace App\Commands;

use App\Services\EmailConfigService;
use App\Services\EmailDeliveryService;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class TestEmailCommand extends BaseCommand
{
    protected $group = 'Setup';
    protected $name = 'email:test';
    protected $description = 'Send a ClearPay test email using the configured email delivery method.';
    protected $usage = 'email:test [recipient]';

    public function run(array $params)
    {
        $emailConfig = (new EmailConfigService())->getConfig();
        $recipient = trim((string) ($params[0] ?? $emailConfig['fromEmail'] ?? ''));

        if ($recipient === '') {
            CLI::error('No recipient provided and no fromEmail configured.');
            return EXIT_ERROR;
        }

        $htmlMessage = view('emails/verification', [
            'name' => 'ClearPay Tester',
            'code' => '123456',
        ]);

        $sent = (new EmailDeliveryService())->send(
            $emailConfig,
            $recipient,
            'ClearPay Email Test',
            $htmlMessage,
            'ClearPay email delivery test. Verification code: 123456'
        );

        if (!$sent) {
            CLI::error('Test email failed. Check writable/logs for SMTP or Brevo details.');
            return EXIT_ERROR;
        }

        CLI::write('Test email sent to ' . $recipient, 'green');
        return EXIT_SUCCESS;
    }
}
