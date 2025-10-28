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
                        
                        <div class="d-flex justify-content-between align-items-center py-3 border-bottom">
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
                        
                        <div class="d-flex justify-content-between align-items-center py-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="setting-icon bg-primary bg-opacity-10 text-primary rounded p-2">
                                    <i class="fas fa-credit-card"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1 fw-semibold">Payment Methods</h6>
                                    <small class="text-muted">Manage available payment methods</small>
                                </div>
                            </div>
                            <div>
                                <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#paymentMethodsModal">
                                    <i class="fas fa-cog me-1"></i>Manage
                                </button>
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

<!-- Payment Methods Management Modal -->
<div class="modal fade" id="paymentMethodsModal" tabindex="-1" aria-labelledby="paymentMethodsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="paymentMethodsModalLabel">
                    <i class="fas fa-credit-card me-2"></i>Payment Methods Management
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Add New Payment Method Button -->
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0">Available Payment Methods</h6>
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addPaymentMethodModal">
                        <i class="fas fa-plus me-1"></i>Add New Payment Method
                    </button>
                </div>

                <!-- Payment Methods Table -->
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Account Details</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($paymentMethods)): ?>
                                <?php foreach ($paymentMethods as $method): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <?php if (!empty($method['icon']) && file_exists(FCPATH . $method['icon'])): ?>
                                                    <img src="<?= base_url($method['icon']) ?>" alt="<?= esc($method['name']) ?> Icon" style="width: 32px; height: 32px; object-fit: cover; border-radius: 4px;" class="me-2">
                                                <?php else: ?>
                                                    <div class="payment-method-placeholder me-2" style="width: 32px; height: 32px; border-radius: 4px; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 10px;" data-method="<?= esc($method['name']) ?>">
                                                        <?= strtoupper(substr($method['name'], 0, 2)) ?>
                                                    </div>
                                                <?php endif; ?>
                                                <strong><?= esc($method['name']) ?></strong>
                                            </div>
                                        </td>
                                        <td>
                                            <?php if (!empty($method['description'])): ?>
                                                <span class="text-muted"><?= esc($method['description']) ?></span>
                                            <?php else: ?>
                                                <span class="text-muted fst-italic">No description</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($method['account_details'])): ?>
                                                <span class="text-muted"><?= esc($method['account_details']) ?></span>
                                            <?php else: ?>
                                                <span class="text-muted fst-italic">No details</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($method['status'] === 'active'): ?>
                                                <span class="badge bg-success">
                                                    <i class="fas fa-check-circle me-1"></i>Active
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">
                                                    <i class="fas fa-times-circle me-1"></i>Inactive
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="editPaymentMethod(<?= $method['id'] ?>)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-<?= $method['status'] === 'active' ? 'warning' : 'success' ?>" 
                                                        onclick="togglePaymentMethodStatus(<?= $method['id'] ?>, '<?= $method['status'] ?>')">
                                                    <i class="fas fa-<?= $method['status'] === 'active' ? 'pause' : 'play' ?>"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-danger" 
                                                        onclick="deletePaymentMethod(<?= $method['id'] ?>, '<?= esc($method['name']) ?>')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center py-4">
                                        <i class="fas fa-credit-card fa-2x text-muted mb-2"></i>
                                        <p class="text-muted mb-0">No payment methods found</p>
                                        <small class="text-muted">Add your first payment method to get started</small>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Add Payment Method Modal -->
<div class="modal fade" id="addPaymentMethodModal" tabindex="-1" aria-labelledby="addPaymentMethodModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addPaymentMethodModalLabel">
                    <i class="fas fa-plus-circle me-2"></i>Add New Payment Method
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addPaymentMethodForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="paymentMethodName" class="form-label">Payment Method Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="paymentMethodName" name="name" required placeholder="e.g., GCash, PayMaya, Bank Transfer">
                    </div>
                    <div class="mb-3">
                        <label for="paymentMethodIcon" class="form-label">Icon</label>
                        <div class="input-group">
                            <input type="file" class="form-control" id="paymentMethodIcon" name="icon" accept="image/*" onchange="previewIcon(this)">
                            <label class="input-group-text" for="paymentMethodIcon">
                                <i class="fas fa-upload"></i>
                            </label>
                        </div>
                        <div class="form-text">Upload an image file (JPG, PNG, GIF, WebP) - Max 2MB</div>
                        <div id="iconPreview" class="mt-2" style="display: none;">
                            <img id="iconPreviewImg" src="" alt="Icon Preview" style="max-width: 64px; max-height: 64px; border-radius: 4px;">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="paymentMethodDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="paymentMethodDescription" name="description" rows="3" placeholder="Optional description for this payment method"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="paymentMethodAccountDetails" class="form-label">Account Details</label>
                        <input type="text" class="form-control" id="paymentMethodAccountDetails" name="account_details" placeholder="e.g., Account Number, Mobile Number, etc.">
                    </div>
                    <div class="mb-3">
                        <label for="paymentMethodStatus" class="form-label">Status <span class="text-danger">*</span></label>
                        <select class="form-select" id="paymentMethodStatus" name="status" required>
                            <option value="">Select Status</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Create Payment Method
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Payment Method Modal -->
<div class="modal fade" id="editPaymentMethodModal" tabindex="-1" aria-labelledby="editPaymentMethodModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editPaymentMethodModalLabel">
                    <i class="fas fa-edit me-2"></i>Edit Payment Method
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editPaymentMethodForm">
                <input type="hidden" id="editPaymentMethodId" name="id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="editPaymentMethodName" class="form-label">Payment Method Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="editPaymentMethodName" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="editPaymentMethodIcon" class="form-label">Icon</label>
                        <div class="input-group">
                            <input type="file" class="form-control" id="editPaymentMethodIcon" name="icon" accept="image/*" onchange="previewEditIcon(this)">
                            <label class="input-group-text" for="editPaymentMethodIcon">
                                <i class="fas fa-upload"></i>
                            </label>
                        </div>
                        <div class="form-text">Upload an image file (JPG, PNG, GIF, WebP) - Max 2MB</div>
                        <div id="editIconPreview" class="mt-2">
                            <img id="editIconPreviewImg" src="" alt="Current Icon" style="max-width: 64px; max-height: 64px; border-radius: 4px;">
                            <div class="mt-1">
                                <small class="text-muted">Current icon</small>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="editPaymentMethodDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="editPaymentMethodDescription" name="description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="editPaymentMethodAccountDetails" class="form-label">Account Details</label>
                        <input type="text" class="form-control" id="editPaymentMethodAccountDetails" name="account_details">
                    </div>
                    <div class="mb-3">
                        <label for="editPaymentMethodStatus" class="form-label">Status <span class="text-danger">*</span></label>
                        <select class="form-select" id="editPaymentMethodStatus" name="status" required>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Update Payment Method
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.payment-method-placeholder {
    background: linear-gradient(135deg, #007bff, #0056b3);
}

.payment-method-placeholder[data-method="GCash"] {
    background: linear-gradient(135deg, #009639, #007a2e);
}

.payment-method-placeholder[data-method="PayMaya"] {
    background: linear-gradient(135deg, #ffc107, #e0a800);
}

.payment-method-placeholder[data-method="Bank Transfer"] {
    background: linear-gradient(135deg, #007bff, #0056b3);
}

.payment-method-placeholder[data-method="Cash"] {
    background: linear-gradient(135deg, #28a745, #1e7e34);
}

.payment-method-placeholder[data-method="Online Banking"] {
    background: linear-gradient(135deg, #6c757d, #545b62);
}
</style>

<script>
// Payment Methods Management JavaScript
let paymentMethods = <?= json_encode($paymentMethods ?? []) ?>;

// Add Payment Method
document.getElementById('addPaymentMethodForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('<?= base_url('admin/settings/payment-methods/store') ?>', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            showNotification('Payment method created successfully', 'success');
            // Close only the Add Payment Method modal
            bootstrap.Modal.getInstance(document.getElementById('addPaymentMethodModal')).hide();
            // Reset the form
            document.getElementById('addPaymentMethodForm').reset();
            document.getElementById('iconPreview').style.display = 'none';
            // Refresh the payment methods list without closing the main modal
            refreshPaymentMethodsList();
        } else {
            showNotification(result.message || 'Failed to create payment method', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred while creating payment method', 'error');
    });
});

// Edit Payment Method
function editPaymentMethod(id) {
    const method = paymentMethods.find(m => m.id == id);
    if (method) {
        document.getElementById('editPaymentMethodId').value = method.id;
        document.getElementById('editPaymentMethodName').value = method.name;
        document.getElementById('editPaymentMethodDescription').value = method.description || '';
        document.getElementById('editPaymentMethodAccountDetails').value = method.account_details || '';
        document.getElementById('editPaymentMethodStatus').value = method.status;
        
        // Update icon preview
        const iconPreviewImg = document.getElementById('editIconPreviewImg');
        if (method.icon) {
            iconPreviewImg.src = '<?= base_url() ?>' + method.icon;
            iconPreviewImg.style.display = 'block';
        } else {
            iconPreviewImg.style.display = 'none';
        }
        
        new bootstrap.Modal(document.getElementById('editPaymentMethodModal')).show();
    }
}

// Update Payment Method
document.getElementById('editPaymentMethodForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const id = document.getElementById('editPaymentMethodId').value;
    
    fetch(`<?= base_url('admin/settings/payment-methods/update') ?>/${id}`, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            showNotification('Payment method updated successfully', 'success');
            // Close only the Edit Payment Method modal
            bootstrap.Modal.getInstance(document.getElementById('editPaymentMethodModal')).hide();
            // Refresh the payment methods list without closing the main modal
            refreshPaymentMethodsList();
        } else {
            showNotification(result.message || 'Failed to update payment method', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred while updating payment method', 'error');
    });
});

// Toggle Payment Method Status
function togglePaymentMethodStatus(id, currentStatus) {
    const newStatus = currentStatus === 'active' ? 'inactive' : 'active';
    const action = newStatus === 'active' ? 'activate' : 'deactivate';
    
    if (confirm(`Are you sure you want to ${action} this payment method?`)) {
        fetch(`<?= base_url('admin/settings/payment-methods/toggle-status') ?>/${id}`, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                showNotification(`Payment method ${action}d successfully`, 'success');
                // Refresh the payment methods list without closing the main modal
                refreshPaymentMethodsList();
            } else {
                showNotification(result.message || `Failed to ${action} payment method`, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification(`An error occurred while ${action}ing payment method`, 'error');
        });
    }
}

// Delete Payment Method
function deletePaymentMethod(id, name) {
    if (confirm(`Are you sure you want to delete "${name}"? This action cannot be undone.`)) {
        fetch(`<?= base_url('admin/settings/payment-methods/delete') ?>/${id}`, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                showNotification('Payment method deleted successfully', 'success');
                // Refresh the payment methods list without closing the main modal
                refreshPaymentMethodsList();
            } else {
                showNotification(result.message || 'Failed to delete payment method', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('An error occurred while deleting payment method', 'error');
        });
    }
}

// Refresh Payment Methods List
function refreshPaymentMethodsList() {
    fetch('<?= base_url('admin/settings/payment-methods/data') ?>', {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            // Update the paymentMethods array
            paymentMethods = result.data;
            
            // Update the table body
            updatePaymentMethodsTable(result.data);
        } else {
            console.error('Failed to refresh payment methods:', result.message);
            // Fallback to page reload if AJAX fails
            location.reload();
        }
    })
    .catch(error => {
        console.error('Error refreshing payment methods list:', error);
        // Fallback to page reload if AJAX fails
        location.reload();
    });
}

// Update Payment Methods Table
function updatePaymentMethodsTable(methods) {
    const tbody = document.querySelector('#paymentMethodsModal tbody');
    if (!tbody) return;
    
    if (methods.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="5" class="text-center py-4">
                    <i class="fas fa-credit-card fa-2x text-muted mb-2"></i>
                    <p class="text-muted mb-0">No payment methods found</p>
                    <small class="text-muted">Add your first payment method to get started</small>
                </td>
            </tr>
        `;
        return;
    }
    
    let html = '';
    methods.forEach(method => {
        const statusBadge = method.status === 'active' 
            ? '<span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>Active</span>'
            : '<span class="badge bg-secondary"><i class="fas fa-times-circle me-1"></i>Inactive</span>';
        
        const statusAction = method.status === 'active' ? 'pause' : 'play';
        const statusColor = method.status === 'active' ? 'warning' : 'success';
        
        const iconHtml = method.icon && method.icon.trim() !== '' 
            ? `<img src="${'<?= base_url() ?>' + method.icon}" alt="${method.name} Icon" style="width: 32px; height: 32px; object-fit: cover; border-radius: 4px;" class="me-2">`
            : `<div class="payment-method-placeholder me-2" style="width: 32px; height: 32px; border-radius: 4px; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 10px;" data-method="${method.name}">${method.name.substring(0, 2).toUpperCase()}</div>`;
        
        html += `
            <tr>
                <td>
                    <div class="d-flex align-items-center">
                        ${iconHtml}
                        <strong>${method.name}</strong>
                    </div>
                </td>
                <td>
                    ${method.description ? `<span class="text-muted">${method.description}</span>` : '<span class="text-muted fst-italic">No description</span>'}
                </td>
                <td>
                    ${method.account_details ? `<span class="text-muted">${method.account_details}</span>` : '<span class="text-muted fst-italic">No details</span>'}
                </td>
                <td>
                    ${statusBadge}
                </td>
                <td>
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="editPaymentMethod(${method.id})">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-${statusColor}" onclick="togglePaymentMethodStatus(${method.id}, '${method.status}')">
                            <i class="fas fa-${statusAction}"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="deletePaymentMethod(${method.id}, '${method.name}')">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    });
    
    tbody.innerHTML = html;
}

// File Preview Functions
function previewIcon(input) {
    const preview = document.getElementById('iconPreview');
    const previewImg = document.getElementById('iconPreviewImg');
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            previewImg.src = e.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    } else {
        preview.style.display = 'none';
    }
}

function previewEditIcon(input) {
    const previewImg = document.getElementById('editIconPreviewImg');
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            previewImg.src = e.target.result;
            previewImg.style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
<?= $this->endSection() ?>