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
     * Constructor - Load from environment variables
     */
    public function __construct()
    {
        parent::__construct();
        
        // Load from environment variables with fallback to defaults
        $this->fromEmail = $_ENV['email.fromEmail'] ?? getenv('email.fromEmail') ?: 'project.clearpay@gmail.com';
        $this->fromName = $_ENV['email.fromName'] ?? getenv('email.fromName') ?: 'ClearPay';
        $this->protocol = $_ENV['email.protocol'] ?? getenv('email.protocol') ?: 'smtp';
        $this->SMTPHost = $_ENV['email.SMTPHost'] ?? getenv('email.SMTPHost') ?: 'smtp.gmail.com';
        $this->SMTPUser = $_ENV['email.SMTPUser'] ?? getenv('email.SMTPUser') ?: 'project.clearpay@gmail.com';
        $this->SMTPPass = $_ENV['email.SMTPPass'] ?? getenv('email.SMTPPass') ?: 'jdab pewu hoqn whho';
        $this->SMTPPort = (int)($_ENV['email.SMTPPort'] ?? getenv('email.SMTPPort') ?: 587);
        $this->SMTPCrypto = $_ENV['email.SMTPCrypto'] ?? getenv('email.SMTPCrypto') ?: 'tls';
        $this->mailType = $_ENV['email.mailType'] ?? getenv('email.mailType') ?: 'html';
    }
}
