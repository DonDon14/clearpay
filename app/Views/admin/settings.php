<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
// Dummy data for UI development - replace with actual controller data later
$systemInfo = [
    'version' => 'ClearPay v1.0.0',
    'php_version' => phpversion(),
    'framework' => 'CodeIgniter 4.x',
    'database' => 'MySQL',
    'last_backup' => date('M j, Y g:i A', strtotime('-2 hours')),
    'uptime' => '24 days, 6 hours',
    'status' => 'online'
];

// Settings dummy data
$settings = [
    'maintenance_mode' => false,
    'auto_backups' => true,
    'backup_frequency' => 'daily',
    'backup_retention' => '14',
    'qr_generation' => true,
    'payment_notifications' => true,
    'partial_threshold' => '25',
    'due_period' => '30',
    'two_factor_auth' => false,
    'session_timeout' => '30',
    'password_policy' => true,
    'activity_logging' => true,
    'email_notifications' => true,
    'default_theme' => 'light',
    'default_language' => 'en',
    'timezone' => 'Asia/Manila',
    'default_currency' => 'PHP'
];
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">System Settings</h1>
                    <p class="mb-0 text-muted">Manage system configurations and preferences</p>
                </div>
                <div>
                    <span class="badge bg-success fs-6">
                        <i class="fas fa-circle me-1"></i>System Online
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Settings Grid -->
    <div class="row">
        <!-- System Configuration Card -->
        <div class="col-lg-6 mb-4">
            <?= view('partials/container-card', [
                'title' => 'System Configuration',
                'subtitle' => 'Core system settings and maintenance options',
                'content' => '
                    <div class="settings-list">
                        <div class="d-flex justify-content-between align-items-center py-3 border-bottom">
                            <div class="d-flex align-items-center gap-3">
                                <div class="setting-icon bg-warning bg-opacity-10 text-warning rounded p-2">
                                    <i class="fas fa-shield-alt"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1 fw-semibold">Maintenance Mode</h6>
                                    <small class="text-muted">Enable to restrict system access during updates</small>
                                </div>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="maintenanceMode" ' . ($settings['maintenance_mode'] ? 'checked' : '') . '>
                                <label class="form-check-label" for="maintenanceMode"></label>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center py-3 border-bottom">
                            <div class="d-flex align-items-center gap-3">
                                <div class="setting-icon bg-info bg-opacity-10 text-info rounded p-2">
                                    <i class="fas fa-database"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1 fw-semibold">Automatic Backups</h6>
                                    <small class="text-muted">Schedule regular database backups</small>
                                </div>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="autoBackups" ' . ($settings['auto_backups'] ? 'checked' : '') . '>
                                <label class="form-check-label" for="autoBackups"></label>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center py-3 border-bottom">
                            <div class="d-flex align-items-center gap-3">
                                <div class="setting-icon bg-primary bg-opacity-10 text-primary rounded p-2">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1 fw-semibold">Backup Frequency</h6>
                                    <small class="text-muted">How often to create backups</small>
                                </div>
                            </div>
                            <div style="min-width: 120px;">
                                <select class="form-select form-select-sm" id="backupFrequency">
                                    <option value="daily" ' . ($settings['backup_frequency'] === 'daily' ? 'selected' : '') . '>Daily</option>
                                    <option value="weekly" ' . ($settings['backup_frequency'] === 'weekly' ? 'selected' : '') . '>Weekly</option>
                                    <option value="monthly" ' . ($settings['backup_frequency'] === 'monthly' ? 'selected' : '') . '>Monthly</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center py-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="setting-icon bg-secondary bg-opacity-10 text-secondary rounded p-2">
                                    <i class="fas fa-history"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1 fw-semibold">Backup Retention</h6>
                                    <small class="text-muted">Number of backups to keep</small>
                                </div>
                            </div>
                            <div style="min-width: 120px;">
                                <select class="form-select form-select-sm" id="backupRetention">
                                    <option value="7" ' . ($settings['backup_retention'] === '7' ? 'selected' : '') . '>7 backups</option>
                                    <option value="14" ' . ($settings['backup_retention'] === '14' ? 'selected' : '') . '>14 backups</option>
                                    <option value="30" ' . ($settings['backup_retention'] === '30' ? 'selected' : '') . '>30 backups</option>
                                </select>
                            </div>
                        </div>
                    </div>
                '
            ]) ?>
        </div>

        <!-- Payment Settings Card -->
        <div class="col-lg-6 mb-4">
            <?= view('partials/container-card', [
                'title' => 'Payment Settings',
                'subtitle' => 'Configure payment processing options',
                'content' => '
                    <div class="settings-list">
                        <div class="d-flex justify-content-between align-items-center py-3 border-bottom">
                            <div class="d-flex align-items-center gap-3">
                                <div class="setting-icon bg-success bg-opacity-10 text-success rounded p-2">
                                    <i class="fas fa-qrcode"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1 fw-semibold">QR Code Generation</h6>
                                    <small class="text-muted">Enable QR receipt generation for payments</small>
                                </div>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="qrGeneration" ' . ($settings['qr_generation'] ? 'checked' : '') . '>
                                <label class="form-check-label" for="qrGeneration"></label>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center py-3 border-bottom">
                            <div class="d-flex align-items-center gap-3">
                                <div class="setting-icon bg-info bg-opacity-10 text-info rounded p-2">
                                    <i class="fas fa-bell"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1 fw-semibold">Payment Notifications</h6>
                                    <small class="text-muted">Send notifications for payment events</small>
                                </div>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="paymentNotifications" ' . ($settings['payment_notifications'] ? 'checked' : '') . '>
                                <label class="form-check-label" for="paymentNotifications"></label>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center py-3 border-bottom">
                            <div class="d-flex align-items-center gap-3">
                                <div class="setting-icon bg-warning bg-opacity-10 text-warning rounded p-2">
                                    <i class="fas fa-percent"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1 fw-semibold">Partial Payment Threshold</h6>
                                    <small class="text-muted">Minimum percentage for partial payments</small>
                                </div>
                            </div>
                            <div style="min-width: 100px;">
                                <select class="form-select form-select-sm" id="partialThreshold">
                                    <option value="10" ' . ($settings['partial_threshold'] === '10' ? 'selected' : '') . '>10%</option>
                                    <option value="25" ' . ($settings['partial_threshold'] === '25' ? 'selected' : '') . '>25%</option>
                                    <option value="50" ' . ($settings['partial_threshold'] === '50' ? 'selected' : '') . '>50%</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center py-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="setting-icon bg-danger bg-opacity-10 text-danger rounded p-2">
                                    <i class="fas fa-calendar"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1 fw-semibold">Payment Due Period</h6>
                                    <small class="text-muted">Days before payment is considered overdue</small>
                                </div>
                            </div>
                            <div style="min-width: 100px;">
                                <select class="form-select form-select-sm" id="duePeriod">
                                    <option value="7" ' . ($settings['due_period'] === '7' ? 'selected' : '') . '>7 days</option>
                                    <option value="14" ' . ($settings['due_period'] === '14' ? 'selected' : '') . '>14 days</option>
                                    <option value="30" ' . ($settings['due_period'] === '30' ? 'selected' : '') . '>30 days</option>
                                </select>
                            </div>
                        </div>
                    </div>
                '
            ]) ?>
        </div>

        <!-- Security Settings Card -->
        <div class="col-lg-6 mb-4">
            <?= view('partials/container-card', [
                'title' => 'Security Settings',
                'subtitle' => 'Configure system security and authentication',
                'content' => '
                    <div class="settings-list">
                        <div class="d-flex justify-content-between align-items-center py-3 border-bottom">
                            <div class="d-flex align-items-center gap-3">
                                <div class="setting-icon bg-primary bg-opacity-10 text-primary rounded p-2">
                                    <i class="fas fa-user-shield"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1 fw-semibold">Two-Factor Authentication</h6>
                                    <small class="text-muted">Require 2FA for admin access</small>
                                </div>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="twoFactorAuth" ' . ($settings['two_factor_auth'] ? 'checked' : '') . '>
                                <label class="form-check-label" for="twoFactorAuth"></label>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center py-3 border-bottom">
                            <div class="d-flex align-items-center gap-3">
                                <div class="setting-icon bg-warning bg-opacity-10 text-warning rounded p-2">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1 fw-semibold">Session Timeout</h6>
                                    <small class="text-muted">Automatic logout after inactivity</small>
                                </div>
                            </div>
                            <div style="min-width: 130px;">
                                <select class="form-select form-select-sm" id="sessionTimeout">
                                    <option value="15" ' . ($settings['session_timeout'] === '15' ? 'selected' : '') . '>15 minutes</option>
                                    <option value="30" ' . ($settings['session_timeout'] === '30' ? 'selected' : '') . '>30 minutes</option>
                                    <option value="60" ' . ($settings['session_timeout'] === '60' ? 'selected' : '') . '>1 hour</option>
                                    <option value="120" ' . ($settings['session_timeout'] === '120' ? 'selected' : '') . '>2 hours</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center py-3 border-bottom">
                            <div class="d-flex align-items-center gap-3">
                                <div class="setting-icon bg-success bg-opacity-10 text-success rounded p-2">
                                    <i class="fas fa-key"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1 fw-semibold">Password Policy</h6>
                                    <small class="text-muted">Enforce strong password requirements</small>
                                </div>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="passwordPolicy" ' . ($settings['password_policy'] ? 'checked' : '') . '>
                                <label class="form-check-label" for="passwordPolicy"></label>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center py-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="setting-icon bg-info bg-opacity-10 text-info rounded p-2">
                                    <i class="fas fa-history"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1 fw-semibold">Activity Logging</h6>
                                    <small class="text-muted">Log all user activities and changes</small>
                                </div>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="activityLogging" ' . ($settings['activity_logging'] ? 'checked' : '') . '>
                                <label class="form-check-label" for="activityLogging"></label>
                            </div>
                        </div>
                    </div>
                '
            ]) ?>
        </div>

        <!-- Email Settings Card -->
        <div class="col-lg-6 mb-4">
            <?= view('partials/container-card', [
                'title' => 'Email Settings',
                'subtitle' => 'Configure email notifications and SMTP',
                'content' => '
                    <div class="settings-list">
                        <div class="d-flex justify-content-between align-items-center py-3 border-bottom">
                            <div class="d-flex align-items-center gap-3">
                                <div class="setting-icon bg-primary bg-opacity-10 text-primary rounded p-2">
                                    <i class="fas fa-server"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1 fw-semibold">SMTP Configuration</h6>
                                    <small class="text-muted">Email server settings</small>
                                </div>
                            </div>
                            <button class="btn btn-outline-primary btn-sm" onclick="configureEmail()">
                                <i class="fas fa-cog"></i> Configure
                            </button>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center py-3 border-bottom">
                            <div class="d-flex align-items-center gap-3">
                                <div class="setting-icon bg-success bg-opacity-10 text-success rounded p-2">
                                    <i class="fas fa-paper-plane"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1 fw-semibold">Email Notifications</h6>
                                    <small class="text-muted">Send email alerts for system events</small>
                                </div>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="emailNotifications" ' . ($settings['email_notifications'] ? 'checked' : '') . '>
                                <label class="form-check-label" for="emailNotifications"></label>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center py-3 border-bottom">
                            <div class="d-flex align-items-center gap-3">
                                <div class="setting-icon bg-info bg-opacity-10 text-info rounded p-2">
                                    <i class="fas fa-envelope-open"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1 fw-semibold">Email Templates</h6>
                                    <small class="text-muted">Customize notification email templates</small>
                                </div>
                            </div>
                            <button class="btn btn-outline-info btn-sm" onclick="manageTemplates()">
                                <i class="fas fa-edit"></i> Manage
                            </button>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center py-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="setting-icon bg-warning bg-opacity-10 text-warning rounded p-2">
                                    <i class="fas fa-vial"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1 fw-semibold">Test Email</h6>
                                    <small class="text-muted">Send a test email to verify configuration</small>
                                </div>
                            </div>
                            <button class="btn btn-primary btn-sm" onclick="testEmail()">
                                <i class="fas fa-paper-plane"></i> Send Test
                            </button>
                        </div>
                    </div>
                '
            ]) ?>
        </div>

        <!-- Application Settings Card -->
        <div class="col-lg-6 mb-4">
            <?= view('partials/container-card', [
                'title' => 'Application Settings',
                'subtitle' => 'Configure application preferences and defaults',
                'content' => '
                    <div class="settings-list">
                        <div class="d-flex justify-content-between align-items-center py-3 border-bottom">
                            <div class="d-flex align-items-center gap-3">
                                <div class="setting-icon bg-primary bg-opacity-10 text-primary rounded p-2">
                                    <i class="fas fa-palette"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1 fw-semibold">Default Theme</h6>
                                    <small class="text-muted">System-wide default theme</small>
                                </div>
                            </div>
                            <div style="min-width: 100px;">
                                <select class="form-select form-select-sm" id="defaultTheme">
                                    <option value="light" ' . ($settings['default_theme'] === 'light' ? 'selected' : '') . '>Light</option>
                                    <option value="dark" ' . ($settings['default_theme'] === 'dark' ? 'selected' : '') . '>Dark</option>
                                    <option value="auto" ' . ($settings['default_theme'] === 'auto' ? 'selected' : '') . '>Auto</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center py-3 border-bottom">
                            <div class="d-flex align-items-center gap-3">
                                <div class="setting-icon bg-success bg-opacity-10 text-success rounded p-2">
                                    <i class="fas fa-language"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1 fw-semibold">Default Language</h6>
                                    <small class="text-muted">System default language</small>
                                </div>
                            </div>
                            <div style="min-width: 120px;">
                                <select class="form-select form-select-sm" id="defaultLanguage">
                                    <option value="en" ' . ($settings['default_language'] === 'en' ? 'selected' : '') . '>English</option>
                                    <option value="fil" ' . ($settings['default_language'] === 'fil' ? 'selected' : '') . '>Filipino</option>
                                    <option value="es" ' . ($settings['default_language'] === 'es' ? 'selected' : '') . '>Spanish</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center py-3 border-bottom">
                            <div class="d-flex align-items-center gap-3">
                                <div class="setting-icon bg-info bg-opacity-10 text-info rounded p-2">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1 fw-semibold">Timezone</h6>
                                    <small class="text-muted">System timezone setting</small>
                                </div>
                            </div>
                            <div style="min-width: 150px;">
                                <select class="form-select form-select-sm" id="timezone">
                                    <option value="Asia/Manila" ' . ($settings['timezone'] === 'Asia/Manila' ? 'selected' : '') . '>Asia/Manila</option>
                                    <option value="UTC" ' . ($settings['timezone'] === 'UTC' ? 'selected' : '') . '>UTC</option>
                                    <option value="America/New_York" ' . ($settings['timezone'] === 'America/New_York' ? 'selected' : '') . '>Eastern Time</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center py-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="setting-icon bg-warning bg-opacity-10 text-warning rounded p-2">
                                    <i class="fas fa-peso-sign"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1 fw-semibold">Default Currency</h6>
                                    <small class="text-muted">System currency format</small>
                                </div>
                            </div>
                            <div style="min-width: 120px;">
                                <select class="form-select form-select-sm" id="defaultCurrency">
                                    <option value="PHP" ' . ($settings['default_currency'] === 'PHP' ? 'selected' : '') . '>PHP (₱)</option>
                                    <option value="USD" ' . ($settings['default_currency'] === 'USD' ? 'selected' : '') . '>USD ($)</option>
                                    <option value="EUR" ' . ($settings['default_currency'] === 'EUR' ? 'selected' : '') . '>EUR (€)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                '
            ]) ?>
        </div>

        <!-- System Information Card -->
        <div class="col-lg-6 mb-4">
            <?= view('partials/container-card', [
                'title' => 'System Information',
                'subtitle' => 'System status and maintenance tools',
                'content' => '
                    <div class="p-3 bg-light rounded mb-3">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">System Version:</span>
                                    <strong>' . $systemInfo['version'] . '</strong>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">PHP Version:</span>
                                    <strong>' . $systemInfo['php_version'] . '</strong>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Framework:</span>
                                    <strong>' . $systemInfo['framework'] . '</strong>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Database:</span>
                                    <strong>' . $systemInfo['database'] . '</strong>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Last Backup:</span>
                                    <strong>' . $systemInfo['last_backup'] . '</strong>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Uptime:</span>
                                    <strong>' . $systemInfo['uptime'] . '</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="settings-list">
                        <div class="d-flex justify-content-between align-items-center py-3 border-bottom">
                            <div class="d-flex align-items-center gap-3">
                                <div class="setting-icon bg-info bg-opacity-10 text-info rounded p-2">
                                    <i class="fas fa-download"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1 fw-semibold">System Logs</h6>
                                    <small class="text-muted">Download system log files for analysis</small>
                                </div>
                            </div>
                            <button class="btn btn-outline-info btn-sm" onclick="downloadLogs()">
                                <i class="fas fa-download"></i> Download
                            </button>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center py-3 border-bottom">
                            <div class="d-flex align-items-center gap-3">
                                <div class="setting-icon bg-success bg-opacity-10 text-success rounded p-2">
                                    <i class="fas fa-database"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1 fw-semibold">Create Backup</h6>
                                    <small class="text-muted">Manually create a database backup</small>
                                </div>
                            </div>
                            <button class="btn btn-success btn-sm" onclick="createBackup()">
                                <i class="fas fa-plus"></i> Create Backup
                            </button>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center py-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="setting-icon bg-danger bg-opacity-10 text-danger rounded p-2">
                                    <i class="fas fa-trash"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1 fw-semibold">Clear Cache</h6>
                                    <small class="text-muted">Clear system cache and temporary files</small>
                                </div>
                            </div>
                            <button class="btn btn-outline-danger btn-sm" onclick="clearCache()">
                                <i class="fas fa-broom"></i> Clear Cache
                            </button>
                        </div>
                    </div>
                '
            ]) ?>
        </div>
    </div>

    <!-- Quick Actions -->
    <?= view('partials/container-card', [
        'title' => 'Quick System Actions',
        'subtitle' => 'Common system administration tasks',
        'content' => '
            <div class="row">
                <div class="col-md-3 col-sm-6 mb-3">
                    ' . view('partials/quick-action', [
                        'icon' => 'fa-database',
                        'title' => 'Backup Database',
                        'subtitle' => 'Create a backup of the database',
                        'bgColor' => 'success',
                        'action' => 'onclick="createBackup()"'
                    ]) . '
                </div>
                <div class="col-md-3 col-sm-6 mb-3">
                    ' . view('partials/quick-action', [
                        'icon' => 'fa-broom',
                        'title' => 'Clear Cache',
                        'subtitle' => 'Clear system cache and temp files',
                        'bgColor' => 'warning',
                        'action' => 'onclick="clearCache()"'
                    ]) . '
                </div>
                <div class="col-md-3 col-sm-6 mb-3">
                    ' . view('partials/quick-action', [
                        'icon' => 'fa-download',
                        'title' => 'Download Logs',
                        'subtitle' => 'Download system logs',
                        'bgColor' => 'info',
                        'action' => 'onclick="downloadLogs()"'
                    ]) . '
                </div>
                <div class="col-md-3 col-sm-6 mb-3">
                    ' . view('partials/quick-action', [
                        'icon' => 'fa-paper-plane',
                        'title' => 'Test Email',
                        'subtitle' => 'Send a test email',
                        'bgColor' => 'primary',
                        'action' => 'onclick="testEmail()"'
                    ]) . '
                </div>
            </div>
        '
    ]) ?>
</div>

<script>
// Settings change handlers
document.addEventListener('change', function(e) {
    if (e.target.type === 'checkbox' || e.target.tagName === 'SELECT') {
        showNotification('Setting updated successfully', 'success');
    }
});

// Action functions
function configureEmail() {
    showNotification('Email configuration opened', 'info');
}

function manageTemplates() {
    showNotification('Template management opened', 'info');
}

function testEmail() {
    showNotification('Test email sent successfully', 'success');
}

function downloadLogs() {
    showNotification('System logs download started', 'info');
}

function createBackup() {
    const button = event.target.closest('button');
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating...';
    button.disabled = true;
    
    setTimeout(() => {
        showNotification('Database backup created successfully', 'success');
        button.innerHTML = originalText;
        button.disabled = false;
    }, 2000);
}

function clearCache() {
    const button = event.target.closest('button');
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Clearing...';
    button.disabled = true;
    
    setTimeout(() => {
        showNotification('System cache cleared successfully', 'success');
        button.innerHTML = originalText;
        button.disabled = false;
    }, 1500);
}

// Notification function
function showNotification(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show`;
    toast.style.position = 'fixed';
    toast.style.top = '20px';
    toast.style.right = '20px';
    toast.style.zIndex = '9999';
    toast.style.minWidth = '300px';
    toast.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'} me-2"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        if (toast.parentElement) {
            toast.remove();
        }
    }, 5000);
}

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    console.log('System Settings page loaded');
});
</script>
<?= $this->endSection() ?>