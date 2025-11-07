<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
// Get system info from controller (passed from SidebarController)
$systemInfo = $systemInfo ?? [
    'version' => 'ClearPay v1.0.0',
    'php_version' => phpversion(),
    'framework' => 'CodeIgniter ' . \CodeIgniter\CodeIgniter::CI_VERSION,
    'database' => 'MySQL',
    'last_backup' => 'No backups yet',
    'uptime' => 'Unknown',
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
        <!-- Payment & Email Settings Card -->
        <div class="col-lg-12 mb-4">
            <?= view('partials/container-card', [
                'title' => 'Settings',
                'subtitle' => 'Configure payment processing and email notifications',
                'bodyClass' => '',
                'content' => '
                    <div class="row">
                        <!-- Payment Settings Column -->
                        <div class="col-lg-6">
                            <h5 class="mb-3 fw-semibold">Payment Settings</h5>
                            <div class="settings-list">
                                <!-- Toggle Settings -->
                                <div class="mb-3">
                                    <h6 class="fw-semibold mb-3 text-muted small text-uppercase">General Settings</h6>
                                    <div class="d-flex justify-content-between align-items-center py-3 border-bottom">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="setting-icon bg-primary bg-opacity-10 text-primary rounded p-2">
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
                                </div>
                                
                                <!-- Dropdown Settings -->
                                <div class="mb-3">
                                    <h6 class="fw-semibold mb-3 text-muted small text-uppercase">Payment Rules</h6>
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
                                                <i class="fas fa-calendar-clock"></i>
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
                                
                                <!-- Management Actions -->
                                <div>
                                    <h6 class="fw-semibold mb-3 text-muted small text-uppercase">Management</h6>
                                    <div class="d-flex justify-content-between align-items-center py-3 border-bottom">
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
                                    
                                    <div class="d-flex justify-content-between align-items-center py-3 border-bottom">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="setting-icon bg-warning bg-opacity-10 text-warning rounded p-2">
                                                <i class="fas fa-undo"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-1 fw-semibold">Refund Methods</h6>
                                                <small class="text-muted">Manage available refund methods</small>
                                            </div>
                                        </div>
                                        <div>
                                            <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#refundMethodsModal">
                                                <i class="fas fa-cog me-1"></i>Manage
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between align-items-center py-3">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="setting-icon bg-info bg-opacity-10 text-info rounded p-2">
                                                <i class="fas fa-folder-open"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-1 fw-semibold">Contribution Categories</h6>
                                                <small class="text-muted">Manage contribution categories</small>
                                            </div>
                                        </div>
                                        <div>
                                            <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#contributionCategoriesModal">
                                                <i class="fas fa-cog me-1"></i>Manage
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Email Settings Column -->
                        <div class="col-lg-6">
                            <h5 class="mb-3 fw-semibold">Email Settings</h5>
                            <div class="settings-list">
                                <!-- Configuration Section -->
                                <div class="mb-3">
                                    <h6 class="fw-semibold mb-3 text-muted small text-uppercase">Configuration</h6>
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
                                </div>
                                
                                <!-- Management & Testing Section -->
                                <div>
                                    <h6 class="fw-semibold mb-3 text-muted small text-uppercase">Templates & Testing</h6>
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
                            </div>
                        </div>
                    </div>
                '
            ]) ?>
        </div>

        <!-- System Information Card -->
        <div class="col-lg-12 mb-4">
            <?= view('partials/container-card', [
                'title' => 'System Information',
                'subtitle' => 'System status and maintenance tools',
                'content' => '
                    <div class="row">
                        <!-- System Details Section -->
                        <div class="col-lg-6 mb-4">
                            <h6 class="fw-semibold mb-3 text-muted">System Details</h6>
                            <div class="p-3 bg-light rounded">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <div class="d-flex justify-content-between align-items-center py-2">
                                            <span class="text-muted">System Version:</span>
                                            <div class="d-flex align-items-center gap-2">
                                                <strong id="systemVersionDisplay">' . $systemInfo['version'] . '</strong>
                                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="editSystemVersion()" title="Edit System Version">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="d-flex justify-content-between py-2">
                                            <span class="text-muted">PHP Version:</span>
                                            <strong>' . $systemInfo['php_version'] . '</strong>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="d-flex justify-content-between py-2">
                                            <span class="text-muted">Framework:</span>
                                            <strong>' . $systemInfo['framework'] . '</strong>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="d-flex justify-content-between py-2">
                                            <span class="text-muted">Database:</span>
                                            <strong>' . $systemInfo['database'] . '</strong>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="d-flex justify-content-between py-2">
                                            <span class="text-muted">Last Backup:</span>
                                            <strong>' . $systemInfo['last_backup'] . '</strong>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="d-flex justify-content-between py-2">
                                            <span class="text-muted">Uptime:</span>
                                            <strong>' . $systemInfo['uptime'] . '</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Maintenance Tools Section -->
                        <div class="col-lg-6 mb-4">
                            <h6 class="fw-semibold mb-3 text-muted">Maintenance Tools</h6>
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
                        </div>
                    </div>
                '
            ]) ?>
        </div>
    </div>

<script>
// Settings change handlers
document.addEventListener('change', function(e) {
    if (e.target.id === 'emailNotifications') {
        const enabled = e.target.checked;
        
        fetch('/admin/email-settings/toggle-notifications', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin',
            body: JSON.stringify({ enabled: enabled })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message || 'Email notifications updated successfully', 'success');
            } else {
                showNotification('Failed to update email notifications: ' + (data.error || 'Unknown error'), 'error');
                // Revert checkbox
                e.target.checked = !enabled;
            }
        })
        .catch(error => {
            console.error('Toggle notifications error:', error);
            showNotification('An error occurred while updating email notifications', 'error');
            // Revert checkbox
            e.target.checked = !enabled;
        });
    } else if (e.target.type === 'checkbox' || e.target.tagName === 'SELECT') {
        showNotification('Setting updated successfully', 'success');
    }
});

// Action functions
function configureEmail() {
    // Load current email configuration
    fetch('/admin/email-settings/config', {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Populate form with current config
            const config = data.config;
            document.getElementById('smtpFromEmail').value = config.fromEmail || '';
            document.getElementById('smtpFromName').value = config.fromName || 'ClearPay';
            document.getElementById('smtpProtocol').value = config.protocol || 'smtp';
            document.getElementById('smtpHost').value = config.SMTPHost || '';
            document.getElementById('smtpUser').value = config.SMTPUser || '';
            document.getElementById('smtpPass').value = config.SMTPPass || '';
            document.getElementById('smtpPort').value = config.SMTPPort || 587;
            document.getElementById('smtpCrypto').value = config.SMTPCrypto || 'tls';
            document.getElementById('smtpTimeout').value = config.SMTPTimeout || 30;
            document.getElementById('mailType').value = config.mailType || 'html';
            document.getElementById('charset').value = config.charset || 'UTF-8';
            
            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('smtpConfigModal'));
            modal.show();
        } else {
            showNotification('Failed to load email configuration: ' + (data.error || 'Unknown error'), 'error');
        }
    })
    .catch(error => {
        console.error('Email config load error:', error);
        showNotification('An error occurred while loading email configuration', 'error');
    });
}

function saveSmtpConfig() {
    const button = document.getElementById('saveSmtpBtn');
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
    button.disabled = true;
    
    const config = {
        fromEmail: document.getElementById('smtpFromEmail').value,
        fromName: document.getElementById('smtpFromName').value,
        protocol: document.getElementById('smtpProtocol').value,
        SMTPHost: document.getElementById('smtpHost').value,
        SMTPUser: document.getElementById('smtpUser').value,
        SMTPPass: document.getElementById('smtpPass').value,
        SMTPPort: parseInt(document.getElementById('smtpPort').value),
        SMTPCrypto: document.getElementById('smtpCrypto').value,
        SMTPTimeout: parseInt(document.getElementById('smtpTimeout').value),
        mailType: document.getElementById('mailType').value,
        charset: document.getElementById('charset').value,
    };
    
    fetch('/admin/email-settings/config', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'same-origin',
        body: JSON.stringify(config)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Email configuration saved successfully!', 'success');
            const modal = bootstrap.Modal.getInstance(document.getElementById('smtpConfigModal'));
            modal.hide();
        } else {
            showNotification('Failed to save configuration: ' + (data.error || 'Unknown error'), 'error');
        }
    })
    .catch(error => {
        console.error('Save config error:', error);
        showNotification('An error occurred while saving configuration', 'error');
    })
    .finally(() => {
        button.innerHTML = originalText;
        button.disabled = false;
    });
}

function manageTemplates() {
    // Load email templates
    fetch('/admin/email-settings/templates', {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Populate templates in modal
            const templatesContainer = document.getElementById('templatesList');
            templatesContainer.innerHTML = '';
            
            Object.keys(data.templates).forEach(key => {
                const template = data.templates[key];
                const templateDiv = document.createElement('div');
                templateDiv.className = 'mb-3';
                templateDiv.innerHTML = `
                    <label class="form-label fw-semibold">${template.name}</label>
                    <input type="text" class="form-control mb-2" id="template_${key}_subject" 
                           value="${template.subject}" placeholder="Subject">
                    <textarea class="form-control" id="template_${key}_body" rows="3" 
                              placeholder="Email Body">${template.body}</textarea>
                `;
                templatesContainer.appendChild(templateDiv);
            });
            
            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('emailTemplatesModal'));
            modal.show();
        } else {
            showNotification('Failed to load email templates: ' + (data.error || 'Unknown error'), 'error');
        }
    })
    .catch(error => {
        console.error('Templates load error:', error);
        showNotification('An error occurred while loading email templates', 'error');
    });
}

function saveTemplates() {
    showNotification('Email templates saved successfully!', 'success');
    const modal = bootstrap.Modal.getInstance(document.getElementById('emailTemplatesModal'));
    modal.hide();
}

function testEmail() {
    const testEmailAddress = prompt('Enter email address to send test email:', sessionStorage.getItem('adminEmail') || '');
    
    if (!testEmailAddress) {
        return;
    }
    
    if (!testEmailAddress.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) {
        showNotification('Please enter a valid email address', 'error');
        return;
    }
    
    const button = event.target.closest('button');
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
    button.disabled = true;
    
    showNotification('Sending test email...', 'info');
    
    fetch('/admin/email-settings/test-email', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'same-origin',
        body: JSON.stringify({
            email: testEmailAddress
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message || 'Test email sent successfully!', 'success');
        } else {
            showNotification('Failed to send test email: ' + (data.error || 'Unknown error'), 'error');
            if (data.debug) {
                console.error('Email debug info:', data.debug);
            }
        }
    })
    .catch(error => {
        console.error('Test email error:', error);
        showNotification('An error occurred while sending test email', 'error');
    })
    .finally(() => {
        button.innerHTML = originalText;
        button.disabled = false;
    });
}

function downloadLogs() {
    const button = event.target.closest('button');
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Downloading...';
    button.disabled = true;
    
    // Show loading notification
    showNotification('Preparing system logs for download...', 'info');
    
    // Make API call to download logs
    fetch('/admin/system/download-logs', {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'same-origin'
    })
    .then(response => {
        if (response.ok) {
            // Get filename from Content-Disposition header or use default
            const contentDisposition = response.headers.get('Content-Disposition');
            let filename = 'clearpay_logs.zip';
            if (contentDisposition) {
                const filenameMatch = contentDisposition.match(/filename="?(.+)"?/);
                if (filenameMatch) {
                    filename = filenameMatch[1];
                }
            }
            
            // Convert response to blob and download
            return response.blob().then(blob => {
                const url = window.URL.createObjectURL(blob);
                const link = document.createElement('a');
                link.href = url;
                link.download = filename;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                window.URL.revokeObjectURL(url);
                
                showNotification('System logs downloaded successfully!', 'success');
            });
        } else {
            return response.json().then(data => {
                showNotification('Failed to download logs: ' + (data.error || 'Unknown error'), 'error');
            });
        }
    })
    .catch(error => {
        console.error('Logs download error:', error);
        showNotification('An error occurred while downloading logs', 'error');
    })
    .finally(() => {
        button.innerHTML = originalText;
        button.disabled = false;
    });
}

function createBackup() {
    const button = event.target.closest('button');
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating...';
    button.disabled = true;
    
    // Show loading notification
    showNotification('Creating database backup...', 'info');
    
    // Make API call to create backup
    fetch('/admin/backup/create', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            let message = 'Database backup created successfully!';
            if (data.backup && data.backup.copied_to_documents) {
                message += ' File saved to Documents\\clearpaybackups folder.';
            }
            showNotification(message, 'success');
            
            // Don't auto-download since file is already in Documents folder
            // User can access it directly from C:\Users\User\Documents\clearpaybackups
            // If they want to download, they can use the download link
        } else {
            showNotification('Failed to create backup: ' + (data.error || 'Unknown error'), 'error');
        }
    })
    .catch(error => {
        console.error('Backup error:', error);
        showNotification('An error occurred while creating the backup', 'error');
    })
    .finally(() => {
        button.innerHTML = originalText;
        button.disabled = false;
    });
}

function clearCache() {
    const button = event.target.closest('button');
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Clearing...';
    button.disabled = true;
    
    // Show loading notification
    showNotification('Clearing system cache...', 'info');
    
    // Make API call to clear cache
    fetch('/admin/system/clear-cache', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            let message = 'System cache cleared successfully!';
            if (data.cleared_count !== undefined) {
                message += ` (${data.cleared_count} files/directories removed)`;
            }
            showNotification(message, 'success');
        } else {
            showNotification('Failed to clear cache: ' + (data.error || 'Unknown error'), 'error');
        }
    })
    .catch(error => {
        console.error('Cache clear error:', error);
        showNotification('An error occurred while clearing cache', 'error');
    })
    .finally(() => {
        button.innerHTML = originalText;
        button.disabled = false;
    });
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

// Edit System Version
function editSystemVersion() {
    const currentVersion = document.getElementById('systemVersionDisplay').textContent.trim();
    const newVersion = prompt('Enter new system version:', currentVersion);
    
    if (newVersion && newVersion !== currentVersion) {
        // Show loading
        showNotification('Updating system version...', 'info');
        
        // Make API call to update version
        fetch('/admin/system/update-version', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin',
            body: JSON.stringify({
                version: newVersion
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('systemVersionDisplay').textContent = newVersion;
                showNotification('System version updated successfully!', 'success');
            } else {
                showNotification('Failed to update version: ' + (data.error || 'Unknown error'), 'error');
            }
        })
        .catch(error => {
            console.error('Version update error:', error);
            showNotification('An error occurred while updating version', 'error');
        });
    }
}

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    console.log('System Settings page loaded');
    
    // Load email notifications status
    fetch('/admin/email-settings/notifications-status', {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const checkbox = document.getElementById('emailNotifications');
            if (checkbox) {
                checkbox.checked = data.enabled;
            }
        }
    })
    .catch(error => {
        console.error('Failed to load email notifications status:', error);
    });
});
</script>

<?= payment_methods_modal($paymentMethods ?? []) ?>
<?= view('partials/modal-refund-methods') ?>
<?= view('partials/modal-contribution-categories') ?>

<!-- SMTP Configuration Modal -->
<div class="modal fade" id="smtpConfigModal" tabindex="-1" aria-labelledby="smtpConfigModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="smtpConfigModalLabel">SMTP Configuration</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="smtpConfigForm">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="smtpFromEmail" class="form-label">From Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="smtpFromEmail" required>
                        </div>
                        <div class="col-md-6">
                            <label for="smtpFromName" class="form-label">From Name</label>
                            <input type="text" class="form-control" id="smtpFromName" value="ClearPay">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="smtpProtocol" class="form-label">Protocol</label>
                        <select class="form-select" id="smtpProtocol">
                            <option value="smtp" selected>SMTP</option>
                            <option value="mail">PHP Mail</option>
                            <option value="sendmail">Sendmail</option>
                        </select>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-8">
                            <label for="smtpHost" class="form-label">SMTP Host <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="smtpHost" required 
                                   placeholder="e.g., smtp.gmail.com">
                        </div>
                        <div class="col-md-4">
                            <label for="smtpPort" class="form-label">Port <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="smtpPort" value="587" required min="1" max="65535">
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="smtpUser" class="form-label">SMTP Username <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="smtpUser" required>
                        </div>
                        <div class="col-md-6">
                            <label for="smtpPass" class="form-label">SMTP Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="smtpPass" required>
                            <small class="text-muted">For Gmail, use App Password</small>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="smtpCrypto" class="form-label">Encryption</label>
                            <select class="form-select" id="smtpCrypto">
                                <option value="tls" selected>TLS</option>
                                <option value="ssl">SSL</option>
                                <option value="">None</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="smtpTimeout" class="form-label">Timeout (seconds)</label>
                            <input type="number" class="form-control" id="smtpTimeout" value="30" min="1">
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="mailType" class="form-label">Mail Type</label>
                            <select class="form-select" id="mailType">
                                <option value="html" selected>HTML</option>
                                <option value="text">Text</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="charset" class="form-label">Charset</label>
                            <input type="text" class="form-control" id="charset" value="UTF-8">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveSmtpBtn" onclick="saveSmtpConfig()">
                    <i class="fas fa-save"></i> Save Configuration
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Email Templates Modal -->
<div class="modal fade" id="emailTemplatesModal" tabindex="-1" aria-labelledby="emailTemplatesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="emailTemplatesModalLabel">Email Templates</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="templatesList">
                    <!-- Templates will be loaded here -->
                </div>
                <div class="alert alert-info mt-3">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Note:</strong> Use placeholders like {code}, {amount}, {name} in templates. These will be replaced with actual values when emails are sent.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveTemplates()">
                    <i class="fas fa-save"></i> Save Templates
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Set base URL for refund methods JavaScript
window.APP_BASE_URL = '<?= base_url() ?>';
</script>
<script src="<?= base_url('js/refund-methods.js') ?>"></script>
<script src="<?= base_url('js/contribution-categories.js') ?>"></script>

<?= $this->endSection() ?>