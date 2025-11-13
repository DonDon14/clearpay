<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Email extends BaseConfig
{
    // Use environment variables with fallback to defaults
    public string $fromEmail  = '';
    public string $fromName   = 'ClearPay';
    public string $recipients = '';

    /**
     * The "user agent"
     */
    public string $userAgent = 'CodeIgniter';

    /**
     * The mail sending protocol: mail, sendmail, smtp
     */
    public string $protocol = 'smtp';

    /**
     * The server path to Sendmail.
     */
    public string $mailPath = '/usr/sbin/sendmail';

    /**
     * SMTP Server Hostname
     */
    public string $SMTPHost = 'smtp.gmail.com';

    /**
     * SMTP Username
     */
    public string $SMTPUser = '';

    /**
     * SMTP Password
     * For Gmail: Use an "App Password" from your Google Account settings
     * Steps: Google Account > Security > 2-Step Verification > App Passwords
     * NOTE: This is a Gmail App Password, NOT your regular Gmail password
     */
    public string $SMTPPass = '';

    /**
     * SMTP Port
     */
    public int $SMTPPort = 587;

    /**
     * SMTP Timeout (in seconds)
     */
    public int $SMTPTimeout = 30;

    /**
     * Enable persistent SMTP connections
     */
    public bool $SMTPKeepAlive = false;

    /**
     * SMTP Encryption.
     *
     * @var string '', 'tls' or 'ssl'. 'tls' will issue a STARTTLS command
     *             to the server. 'ssl' means implicit SSL. Connection on port
     *             465 should set this to ''.
     */
    public string $SMTPCrypto = 'tls';

    /**
     * Enable word-wrap
     */
    public bool $wordWrap = true;

    /**
     * Character count to wrap at
     */
    public int $wrapChars = 76;

    /**
     * Type of mail, either 'text' or 'html'
     */
    public string $mailType = 'html';

    /**
     * Character set (utf-8, iso-8859-1, etc.)
     */
    public string $charset = 'UTF-8';

    /**
     * Whether to validate the email address
     */
    public bool $validate = false;

    /**
     * Email Priority. 1 = highest. 5 = lowest. 3 = normal
     */
    public int $priority = 3;

    /**
     * Newline character. (Use “\r\n” to comply with RFC 822)
     */
    public string $CRLF = "\r\n";

    /**
     * Newline character. (Use “\r\n” to comply with RFC 822)
     */
    public string $newline = "\r\n";

    /**
     * Enable BCC Batch Mode.
     */
    public bool $BCCBatchMode = false;

    /**
     * Number of emails in each BCC batch
     */
    public int $BCCBatchSize = 200;

    /**
     * Enable notify message from server
     */
    public bool $DSN = false;

    /**
     * Constructor - Load from database first, then environment variables, then defaults
     */
    public function __construct()
    {
        parent::__construct();
        
        // Try to load from database first
        try {
            $db = \Config\Database::connect();
            $settings = $db->table('email_settings')
                ->where('is_active', true)
                ->orderBy('id', 'DESC')
                ->limit(1)
                ->get()
                ->getRowArray();
            
            if ($settings) {
                $this->fromEmail = $settings['from_email'] ?? '';
                $this->fromName = $settings['from_name'] ?? 'ClearPay';
                $this->protocol = $settings['protocol'] ?? 'smtp';
                $this->SMTPHost = $settings['smtp_host'] ?? '';
                $this->SMTPUser = $settings['smtp_user'] ?? '';
                $this->SMTPPass = $settings['smtp_pass'] ?? '';
                $this->SMTPPort = (int)($settings['smtp_port'] ?? 587);
                $this->SMTPCrypto = $settings['smtp_crypto'] ?? 'tls';
                $this->mailType = $settings['mail_type'] ?? 'html';
                return; // Exit early if database settings found
            }
        } catch (\Exception $e) {
            // If database table doesn't exist yet, fall through to environment variables
            log_message('debug', 'Email settings table not found, using environment variables: ' . $e->getMessage());
        }
        
        // Fallback to environment variables
        // Try both dot notation and underscore notation (some systems don't support dots in env var names)
        $this->fromEmail = $_ENV['email.fromEmail'] ?? $_ENV['email_fromEmail'] ?? getenv('email.fromEmail') ?: getenv('email_fromEmail') ?: 'project.clearpay@gmail.com';
        $this->fromName = $_ENV['email.fromName'] ?? $_ENV['email_fromName'] ?? getenv('email.fromName') ?: getenv('email_fromName') ?: 'ClearPay';
        $this->protocol = $_ENV['email.protocol'] ?? $_ENV['email_protocol'] ?? getenv('email.protocol') ?: getenv('email_protocol') ?: 'smtp';
        $this->SMTPHost = $_ENV['email.SMTPHost'] ?? $_ENV['email_SMTPHost'] ?? getenv('email.SMTPHost') ?: getenv('email_SMTPHost') ?: 'smtp.gmail.com';
        $this->SMTPUser = $_ENV['email.SMTPUser'] ?? $_ENV['email_SMTPUser'] ?? getenv('email.SMTPUser') ?: getenv('email_SMTPUser') ?: 'project.clearpay@gmail.com';
        $this->SMTPPass = $_ENV['email.SMTPPass'] ?? $_ENV['email_SMTPPass'] ?? getenv('email.SMTPPass') ?: getenv('email_SMTPPass') ?: '';
        $this->SMTPPort = (int)($_ENV['email.SMTPPort'] ?? $_ENV['email_SMTPPort'] ?? getenv('email.SMTPPort') ?: getenv('email_SMTPPort') ?: 587);
        $this->SMTPCrypto = $_ENV['email.SMTPCrypto'] ?? $_ENV['email_SMTPCrypto'] ?? getenv('email.SMTPCrypto') ?: getenv('email_SMTPCrypto') ?: 'tls';
        $this->mailType = $_ENV['email.mailType'] ?? $_ENV['email_mailType'] ?? getenv('email.mailType') ?: getenv('email_mailType') ?: 'html';
    }
}
