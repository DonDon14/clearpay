<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<link rel="stylesheet" href="<?= base_url('css/dashboard.css') ?>" />

<div class="container-fluid mb-4">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-1 fw-semibold"><?= $pageTitle ?? 'User Manual' ?></h1>
                    <p class="text-muted mb-0"><?= $pageSubtitle ?? 'Complete guide to using ClearPay' ?></p>
                </div>
                <div>
                    <a href="<?= base_url('help') ?>" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>Back to Help
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Table of Contents -->
    <div class="row mb-4">
        <div class="col-12">
            <?= view('partials/container-card', [
                'title' => 'Table of Contents',
                'subtitle' => 'Quick navigation to all sections',
                'bodyClass' => '',
                'content' => '
                    <div class="list-group list-group-flush">
                        <a href="#introduction" class="list-group-item list-group-item-action">
                            <i class="fas fa-book me-2 text-primary"></i>1. Introduction
                        </a>
                        <a href="#getting-started" class="list-group-item list-group-item-action">
                            <i class="fas fa-play-circle me-2 text-success"></i>2. Getting Started
                        </a>
                        <a href="#dashboard" class="list-group-item list-group-item-action">
                            <i class="fas fa-home me-2 text-info"></i>3. Dashboard
                        </a>
                        <a href="#contributions" class="list-group-item list-group-item-action">
                            <i class="fas fa-hand-holding-usd me-2 text-warning"></i>4. Managing Contributions
                        </a>
                        <a href="#payers" class="list-group-item list-group-item-action">
                            <i class="fas fa-users me-2 text-danger"></i>5. Managing Payers
                        </a>
                        <a href="#payments" class="list-group-item list-group-item-action">
                            <i class="fas fa-credit-card me-2 text-primary"></i>6. Processing Payments
                        </a>
                        <a href="#payment-requests" class="list-group-item list-group-item-action">
                            <i class="fas fa-paper-plane me-2 text-success"></i>7. Payment Requests
                        </a>
                        <a href="#refunds" class="list-group-item list-group-item-action">
                            <i class="fas fa-undo me-2 text-info"></i>8. Managing Refunds
                        </a>
                        <a href="#announcements" class="list-group-item list-group-item-action">
                            <i class="fas fa-bullhorn me-2 text-warning"></i>9. Announcements
                        </a>
                        <a href="#analytics" class="list-group-item list-group-item-action">
                            <i class="fas fa-chart-bar me-2 text-danger"></i>10. Analytics & Reports
                        </a>
                        <a href="#settings" class="list-group-item list-group-item-action">
                            <i class="fas fa-cog me-2 text-primary"></i>11. Settings & Configuration
                        </a>
                        <a href="#troubleshooting" class="list-group-item list-group-item-action">
                            <i class="fas fa-wrench me-2 text-secondary"></i>12. Troubleshooting
                        </a>
                    </div>
                '
            ]) ?>
        </div>
    </div>

    <!-- Introduction -->
    <div class="row mb-4" id="introduction">
        <div class="col-12">
            <?= view('partials/container-card', [
                'title' => '1. Introduction',
                'subtitle' => 'Welcome to ClearPay',
                'bodyClass' => '',
                'content' => '
                    <div class="content-section">
                        <h4 class="mb-3">What is ClearPay?</h4>
                        <p>ClearPay is a comprehensive payment management system designed for educational institutions to efficiently manage student contributions, payments, and refunds. It provides a centralized platform for administrators to track financial transactions and for students (payers) to submit payments and requests.</p>
                        
                        <h5 class="mt-4 mb-3">Key Features</h5>
                        <ul>
                            <li><strong>Contribution Management:</strong> Create and manage student contribution campaigns</li>
                            <li><strong>Payment Processing:</strong> Record and track payments from students</li>
                            <li><strong>Payment Requests:</strong> Approve or reject online payment requests from students</li>
                            <li><strong>Refund Management:</strong> Process refunds for completed payments</li>
                            <li><strong>Analytics & Reports:</strong> Generate financial reports and analytics</li>
                            <li><strong>Announcements:</strong> Send announcements to students</li>
                            <li><strong>QR Code Receipts:</strong> Generate QR code receipts for payments</li>
                            <li><strong>Mobile App:</strong> Flutter mobile app for students</li>
                        </ul>
                    </div>
                '
            ]) ?>
        </div>
    </div>

    <!-- Getting Started -->
    <div class="row mb-4" id="getting-started">
        <div class="col-12">
            <?= view('partials/container-card', [
                'title' => '2. Getting Started',
                'subtitle' => 'First steps with ClearPay',
                'bodyClass' => '',
                'content' => '
                    <div class="content-section">
                        <h4 class="mb-3">Initial Setup</h4>
                        <ol>
                            <li class="mb-3">
                                <strong>Login to Admin Panel</strong>
                                <p class="mt-2 mb-0">Access the admin panel using your credentials at <code>' . base_url() . '</code></p>
                            </li>
                            <li class="mb-3">
                                <strong>Configure Settings</strong>
                                <p class="mt-2 mb-0">Go to <strong>Settings</strong> and configure:
                                    <ul class="mt-2">
                                        <li>Payment methods</li>
                                        <li>Refund methods</li>
                                        <li>Contribution categories</li>
                                        <li>Email settings (SMTP configuration)</li>
                                        <li>System settings</li>
                                    </ul>
                                </p>
                            </li>
                            <li class="mb-3">
                                <strong>Add Payers</strong>
                                <p class="mt-2 mb-0">Navigate to <strong>Payers</strong> and add student records with their information</p>
                            </li>
                            <li class="mb-3">
                                <strong>Create Contributions</strong>
                                <p class="mt-2 mb-0">Go to <strong>Contributions</strong> and create your first contribution campaign</p>
                            </li>
                        </ol>
                    </div>
                '
            ]) ?>
        </div>
    </div>

    <!-- Dashboard -->
    <div class="row mb-4" id="dashboard">
        <div class="col-12">
            <?= view('partials/container-card', [
                'title' => '3. Dashboard',
                'subtitle' => 'Overview of your system',
                'bodyClass' => '',
                'content' => '
                    <div class="content-section">
                        <h4 class="mb-3">Dashboard Overview</h4>
                        <p>The dashboard provides a comprehensive overview of your payment system, including key metrics and recent activities.</p>
                        
                        <h5 class="mt-4 mb-3">Key Metrics</h5>
                        <ul>
                            <li><strong>Total Collections:</strong> Total amount collected from all payments</li>
                            <li><strong>Completed Payments:</strong> Number of fully paid contributions</li>
                            <li><strong>Partial Payments:</strong> Number of partially paid contributions</li>
                            <li><strong>Today\'s Payments:</strong> Payments received today</li>
                            <li><strong>Pending Payment Requests:</strong> Number of payment requests awaiting approval</li>
                        </ul>
                        
                        <h5 class="mt-4 mb-3">Recent Activities</h5>
                        <p>The dashboard displays recent payment activities, allowing you to quickly see the latest transactions and system events.</p>
                        
                        <h5 class="mt-4 mb-3">Quick Actions</h5>
                        <p>From the dashboard, you can quickly access:
                            <ul>
                                <li>Add new payment</li>
                                <li>Create new contribution</li>
                                <li>View pending payment requests</li>
                                <li>Access settings</li>
                            </ul>
                        </p>
                    </div>
                '
            ]) ?>
        </div>
    </div>

    <!-- Contributions -->
    <div class="row mb-4" id="contributions">
        <div class="col-12">
            <?= view('partials/container-card', [
                'title' => '4. Managing Contributions',
                'subtitle' => 'Create and manage contribution campaigns',
                'bodyClass' => '',
                'content' => '
                    <div class="content-section">
                        <h4 class="mb-3">Creating a Contribution</h4>
                        <ol>
                            <li class="mb-3">
                                <strong>Navigate to Contributions</strong>
                                <p class="mt-2 mb-0">Click on <strong>Contributions</strong> in the sidebar</p>
                            </li>
                            <li class="mb-3">
                                <strong>Click "Add New Contribution"</strong>
                                <p class="mt-2 mb-0">Fill in the contribution details:
                                    <ul class="mt-2">
                                        <li><strong>Title:</strong> Name of the contribution</li>
                                        <li><strong>Description:</strong> Detailed description</li>
                                        <li><strong>Contribution Code:</strong> Unique identifier</li>
                                        <li><strong>Grand Total:</strong> Total amount to be collected</li>
                                        <li><strong>Number of Payers:</strong> Total number of students who need to pay</li>
                                        <li><strong>Amount per Payer:</strong> Automatically calculated (Grand Total / Number of Payers)</li>
                                        <li><strong>Due Date:</strong> Payment deadline</li>
                                        <li><strong>Category:</strong> Contribution category</li>
                                    </ul>
                                </p>
                            </li>
                            <li class="mb-3">
                                <strong>Save the Contribution</strong>
                                <p class="mt-2 mb-0">Click <strong>Save</strong> to create the contribution. The status will be set to "Active" by default.</p>
                            </li>
                        </ol>
                        
                        <h5 class="mt-4 mb-3">Editing a Contribution</h5>
                        <p>To edit a contribution:
                            <ol>
                                <li>Click on the contribution you want to edit</li>
                                <li>Click the <strong>Edit</strong> button</li>
                                <li>Modify the details as needed</li>
                                <li>Click <strong>Update</strong> to save changes</li>
                            </ol>
                        </p>
                        
                        <h5 class="mt-4 mb-3">Managing Contribution Status</h5>
                        <p>You can toggle a contribution\'s status between Active and Inactive:
                            <ul>
                                <li><strong>Active:</strong> Contribution is open for payments</li>
                                <li><strong>Inactive:</strong> Contribution is closed (no new payments accepted)</li>
                            </ul>
                        </p>
                    </div>
                '
            ]) ?>
        </div>
    </div>

    <!-- Payers -->
    <div class="row mb-4" id="payers">
        <div class="col-12">
            <?= view('partials/container-card', [
                'title' => '5. Managing Payers',
                'subtitle' => 'Add and manage student records',
                'bodyClass' => '',
                'content' => '
                    <div class="content-section">
                        <h4 class="mb-3">Adding a New Payer</h4>
                        <ol>
                            <li class="mb-3">
                                <strong>Navigate to Payers</strong>
                                <p class="mt-2 mb-0">Click on <strong>Payers</strong> in the sidebar</p>
                            </li>
                            <li class="mb-3">
                                <strong>Click "Add New Payer"</strong>
                                <p class="mt-2 mb-0">Fill in the payer information:
                                    <ul class="mt-2">
                                        <li><strong>Student ID:</strong> Unique student identifier (required)</li>
                                        <li><strong>Full Name:</strong> Student\'s full name</li>
                                        <li><strong>Email:</strong> Student\'s email address</li>
                                        <li><strong>Contact Number:</strong> Student\'s phone number</li>
                                        <li><strong>Course/Department:</strong> Student\'s course or department</li>
                                        <li><strong>Profile Picture:</strong> Optional profile picture upload</li>
                                    </ul>
                                </p>
                            </li>
                            <li class="mb-3">
                                <strong>Save the Payer</strong>
                                <p class="mt-2 mb-0">Click <strong>Save</strong> to add the payer to the system</p>
                            </li>
                        </ol>
                        
                        <h5 class="mt-4 mb-3">Editing Payer Information</h5>
                        <p>To edit a payer:
                            <ol>
                                <li>Click on the payer you want to edit</li>
                                <li>Click the <strong>Edit</strong> button</li>
                                <li>Update the information as needed</li>
                                <li>Click <strong>Update</strong> to save changes</li>
                            </ol>
                        </p>
                        
                        <h5 class="mt-4 mb-3">Exporting Payer Data</h5>
                        <p>You can export payer data in PDF or CSV format:
                            <ul>
                                <li><strong>PDF Export:</strong> Generates a formatted PDF document</li>
                                <li><strong>CSV Export:</strong> Generates a CSV file for Excel or other spreadsheet applications</li>
                            </ul>
                        </p>
                    </div>
                '
            ]) ?>
        </div>
    </div>

    <!-- Payments -->
    <div class="row mb-4" id="payments">
        <div class="col-12">
            <?= view('partials/container-card', [
                'title' => '6. Processing Payments',
                'subtitle' => 'Record and manage payments',
                'bodyClass' => '',
                'content' => '
                    <div class="content-section">
                        <h4 class="mb-3">Adding a Payment</h4>
                        <ol>
                            <li class="mb-3">
                                <strong>Navigate to Payments</strong>
                                <p class="mt-2 mb-0">Click on <strong>Payments</strong> in the sidebar</p>
                            </li>
                            <li class="mb-3">
                                <strong>Click "Add Payment"</strong>
                                <p class="mt-2 mb-0">Fill in the payment details:
                                    <ul class="mt-2">
                                        <li><strong>Payer:</strong> Select the student making the payment</li>
                                        <li><strong>Contribution:</strong> Select the contribution being paid</li>
                                        <li><strong>Amount:</strong> Payment amount</li>
                                        <li><strong>Payment Method:</strong> Select the payment method used</li>
                                        <li><strong>Payment Date:</strong> Date of payment</li>
                                        <li><strong>Reference Number:</strong> Optional reference number</li>
                                        <li><strong>Notes:</strong> Optional payment notes</li>
                                    </ul>
                                </p>
                            </li>
                            <li class="mb-3">
                                <strong>Save the Payment</strong>
                                <p class="mt-2 mb-0">Click <strong>Save</strong> to record the payment. The system will automatically calculate the payment status (Fully Paid, Partially Paid, or Unpaid).</p>
                            </li>
                        </ol>
                        
                        <h5 class="mt-4 mb-3">Payment Status</h5>
                        <p>The system automatically calculates payment status:
                            <ul>
                                <li><strong>Fully Paid:</strong> Total payments equal or exceed the contribution amount</li>
                                <li><strong>Partially Paid:</strong> Some payment has been made but not the full amount</li>
                                <li><strong>Unpaid:</strong> No payments have been recorded</li>
                            </ul>
                        </p>
                        
                        <h5 class="mt-4 mb-3">Generating QR Receipts</h5>
                        <p>After recording a payment, you can generate a QR code receipt:
                            <ol>
                                <li>Click on the payment record</li>
                                <li>Click <strong>Generate QR Receipt</strong></li>
                                <li>The QR code can be scanned to verify the payment</li>
                            </ol>
                        </p>
                        
                        <h5 class="mt-4 mb-3">Viewing Payment History</h5>
                        <p>The Payments page displays all payment records with filtering options to help you find specific payments quickly.</p>
                    </div>
                '
            ]) ?>
        </div>
    </div>

    <!-- Payment Requests -->
    <div class="row mb-4" id="payment-requests">
        <div class="col-12">
            <?= view('partials/container-card', [
                'title' => '7. Payment Requests',
                'subtitle' => 'Manage online payment requests from students',
                'bodyClass' => '',
                'content' => '
                    <div class="content-section">
                        <h4 class="mb-3">Approving Payment Requests</h4>
                        <ol>
                            <li class="mb-3">
                                <strong>Navigate to Payment Requests</strong>
                                <p class="mt-2 mb-0">Click on <strong>Payment Requests</strong> in the sidebar. You\'ll see a badge with the number of pending requests.</p>
                            </li>
                            <li class="mb-3">
                                <strong>Review the Request</strong>
                                <p class="mt-2 mb-0">Click on a pending request to view details:
                                    <ul class="mt-2">
                                        <li>Payer information</li>
                                        <li>Contribution details</li>
                                        <li>Requested amount</li>
                                        <li>Payment method</li>
                                        <li>Proof of payment (if uploaded)</li>
                                        <li>Reference number</li>
                                    </ul>
                                </p>
                            </li>
                            <li class="mb-3">
                                <strong>Approve or Reject</strong>
                                <p class="mt-2 mb-0">
                                    <ul>
                                        <li><strong>Approve:</strong> Click <strong>Approve</strong> to accept the payment request. The payment will be automatically recorded.</li>
                                        <li><strong>Reject:</strong> Click <strong>Reject</strong> and provide a reason for rejection. The student will be notified.</li>
                                    </ul>
                                </p>
                            </li>
                        </ol>
                        
                        <h5 class="mt-4 mb-3">Processing Payment Requests</h5>
                        <p>After approving a payment request, you can process it to mark it as completed. This will update the payment status in the system.</p>
                    </div>
                '
            ]) ?>
        </div>
    </div>

    <!-- Refunds -->
    <div class="row mb-4" id="refunds">
        <div class="col-12">
            <?= view('partials/container-card', [
                'title' => '8. Managing Refunds',
                'subtitle' => 'Process refunds for completed payments',
                'bodyClass' => '',
                'content' => '
                    <div class="content-section">
                        <h4 class="mb-3">Processing a Refund</h4>
                        <ol>
                            <li class="mb-3">
                                <strong>Navigate to Refunds</strong>
                                <p class="mt-2 mb-0">Click on <strong>Refunds</strong> in the sidebar. You\'ll see a badge with the number of pending refund requests.</p>
                            </li>
                            <li class="mb-3">
                                <strong>Select Payment to Refund</strong>
                                <p class="mt-2 mb-0">Choose from:
                                    <ul class="mt-2">
                                        <li><strong>Recent Payments:</strong> Select a recently paid contribution</li>
                                        <li><strong>Refund Requests:</strong> Process refund requests from students</li>
                                    </ul>
                                </p>
                            </li>
                            <li class="mb-3">
                                <strong>Process Refund</strong>
                                <p class="mt-2 mb-0">Fill in refund details:
                                    <ul class="mt-2">
                                        <li><strong>Refund Amount:</strong> Amount to be refunded</li>
                                        <li><strong>Refund Method:</strong> How the refund will be processed</li>
                                        <li><strong>Refund Reason:</strong> Reason for the refund</li>
                                        <li><strong>Refund Reference:</strong> Reference number for tracking</li>
                                    </ul>
                                </p>
                            </li>
                            <li class="mb-3">
                                <strong>Complete Refund</strong>
                                <p class="mt-2 mb-0">Click <strong>Complete Refund</strong> to process the refund. The system will update the payment status accordingly.</p>
                            </li>
                        </ol>
                        
                        <h5 class="mt-4 mb-3">Refund Requests from Students</h5>
                        <p>Students can submit refund requests through the mobile app:
                            <ol>
                                <li>Review the refund request</li>
                                <li>Approve or reject the request</li>
                                <li>If approved, process the refund with the appropriate refund method</li>
                                <li>Provide admin notes if rejecting</li>
                            </ol>
                        </p>
                    </div>
                '
            ]) ?>
        </div>
    </div>

    <!-- Announcements -->
    <div class="row mb-4" id="announcements">
        <div class="col-12">
            <?= view('partials/container-card', [
                'title' => '9. Announcements',
                'subtitle' => 'Send announcements to students',
                'bodyClass' => '',
                'content' => '
                    <div class="content-section">
                        <h4 class="mb-3">Creating an Announcement</h4>
                        <ol>
                            <li class="mb-3">
                                <strong>Navigate to Announcements</strong>
                                <p class="mt-2 mb-0">Click on <strong>Announcements</strong> in the sidebar</p>
                            </li>
                            <li class="mb-3">
                                <strong>Click "Add New Announcement"</strong>
                                <p class="mt-2 mb-0">Fill in announcement details:
                                    <ul class="mt-2">
                                        <li><strong>Title:</strong> Announcement title</li>
                                        <li><strong>Message:</strong> Announcement content</li>
                                        <li><strong>Priority:</strong> High, Medium, or Low</li>
                                        <li><strong>Type:</strong> General, Payment, or System</li>
                                        <li><strong>Target Audience:</strong> All payers or specific groups</li>
                                    </ul>
                                </p>
                            </li>
                            <li class="mb-3">
                                <strong>Publish Announcement</strong>
                                <p class="mt-2 mb-0">Click <strong>Publish</strong> to send the announcement. Students will receive a popup notification in the mobile app.</p>
                            </li>
                        </ol>
                    </div>
                '
            ]) ?>
        </div>
    </div>

    <!-- Analytics -->
    <div class="row mb-4" id="analytics">
        <div class="col-12">
            <?= view('partials/container-card', [
                'title' => '10. Analytics & Reports',
                'subtitle' => 'Generate reports and view analytics',
                'bodyClass' => '',
                'content' => '
                    <div class="content-section">
                        <h4 class="mb-3">Viewing Analytics</h4>
                        <p>The Analytics page provides comprehensive insights into your payment system:
                            <ul>
                                <li><strong>Revenue Trends:</strong> Track revenue over time</li>
                                <li><strong>Payment Status:</strong> View distribution of payment statuses</li>
                                <li><strong>Contribution Performance:</strong> See which contributions are performing well</li>
                                <li><strong>Payer Statistics:</strong> Analyze payer behavior and payment patterns</li>
                            </ul>
                        </p>
                        
                        <h5 class="mt-4 mb-3">Generating Reports</h5>
                        <p>You can generate various reports:
                            <ul>
                                <li><strong>Payment Reports:</strong> Detailed payment transaction reports</li>
                                <li><strong>Contribution Reports:</strong> Reports on contribution collections</li>
                                <li><strong>Payer Reports:</strong> Individual payer payment history</li>
                            </ul>
                        </p>
                    </div>
                '
            ]) ?>
        </div>
    </div>

    <!-- Settings -->
    <div class="row mb-4" id="settings">
        <div class="col-12">
            <?= view('partials/container-card', [
                'title' => '11. Settings & Configuration',
                'subtitle' => 'Configure system settings',
                'bodyClass' => '',
                'content' => '
                    <div class="content-section">
                        <h4 class="mb-3">Payment Settings</h4>
                        <ul>
                            <li><strong>QR Code Generation:</strong> Enable/disable QR receipt generation</li>
                            <li><strong>Payment Notifications:</strong> Enable/disable payment notifications</li>
                            <li><strong>Partial Payment Threshold:</strong> Set minimum percentage for partial payments</li>
                            <li><strong>Payment Due Period:</strong> Set days before payment is considered overdue</li>
                        </ul>
                        
                        <h5 class="mt-4 mb-3">Email Settings</h5>
                        <ul>
                            <li><strong>SMTP Configuration:</strong> Configure email server settings</li>
                            <li><strong>Email Notifications:</strong> Enable/disable email alerts</li>
                            <li><strong>Email Templates:</strong> Customize email templates</li>
                            <li><strong>Test Email:</strong> Send test emails to verify configuration</li>
                        </ul>
                        
                        <h5 class="mt-4 mb-3">System Information</h5>
                        <ul>
                            <li><strong>System Version:</strong> View and update system version</li>
                            <li><strong>System Logs:</strong> Download system log files</li>
                            <li><strong>Clear Cache:</strong> Clear system cache and temporary files</li>
                            <li><strong>Database Backup:</strong> Create and download database backups</li>
                        </ul>
                        
                        <h5 class="mt-4 mb-3">Managing Payment Methods</h5>
                        <p>Configure available payment methods that students can use:
                            <ol>
                                <li>Go to Settings → Payment Settings</li>
                                <li>Click <strong>Manage</strong> next to Payment Methods</li>
                                <li>Add, edit, or deactivate payment methods</li>
                            </ol>
                        </p>
                        
                        <h5 class="mt-4 mb-3">Managing Refund Methods</h5>
                        <p>Configure available refund methods:
                            <ol>
                                <li>Go to Settings → Payment Settings</li>
                                <li>Click <strong>Manage</strong> next to Refund Methods</li>
                                <li>Add, edit, or deactivate refund methods</li>
                            </ol>
                        </p>
                    </div>
                '
            ]) ?>
        </div>
    </div>

    <!-- Troubleshooting -->
    <div class="row mb-4" id="troubleshooting">
        <div class="col-12">
            <?= view('partials/container-card', [
                'title' => '12. Troubleshooting',
                'subtitle' => 'Common issues and solutions',
                'bodyClass' => '',
                'content' => '
                    <div class="content-section">
                        <h4 class="mb-3">Common Issues</h4>
                        
                        <h5 class="mt-4 mb-3">Payment Not Showing</h5>
                        <p><strong>Issue:</strong> Payment is not appearing in the system</p>
                        <p><strong>Solution:</strong>
                            <ul>
                                <li>Check if the payment was saved successfully</li>
                                <li>Refresh the page</li>
                                <li>Check the payment filters</li>
                                <li>Verify the payer and contribution are correct</li>
                            </ul>
                        </p>
                        
                        <h5 class="mt-4 mb-3">Email Not Sending</h5>
                        <p><strong>Issue:</strong> Email notifications are not being sent</p>
                        <p><strong>Solution:</strong>
                            <ul>
                                <li>Check SMTP configuration in Settings → Email Settings</li>
                                <li>Test email configuration using the "Send Test" button</li>
                                <li>Verify email server credentials</li>
                                <li>Check if email notifications are enabled</li>
                            </ul>
                        </p>
                        
                        <h5 class="mt-4 mb-3">Backup Not Creating</h5>
                        <p><strong>Issue:</strong> Database backup is not being created</p>
                        <p><strong>Solution:</strong>
                            <ul>
                                <li>Check if the backup directory has write permissions</li>
                                <li>Verify database connection</li>
                                <li>Check system logs for errors</li>
                                <li>Ensure sufficient disk space</li>
                            </ul>
                        </p>
                        
                        <h5 class="mt-4 mb-3">QR Code Not Generating</h5>
                        <p><strong>Issue:</strong> QR code receipt is not generating</p>
                        <p><strong>Solution:</strong>
                            <ul>
                                <li>Ensure QR code generation is enabled in Settings</li>
                                <li>Check if the payment has been saved</li>
                                <li>Verify the payment ID is valid</li>
                                <li>Check system logs for errors</li>
                            </ul>
                        </p>
                        
                        <h5 class="mt-4 mb-3">Performance Issues</h5>
                        <p><strong>Issue:</strong> System is running slowly</p>
                        <p><strong>Solution:</strong>
                            <ul>
                                <li>Clear system cache in Settings → System Information</li>
                                <li>Check database performance</li>
                                <li>Review system logs for errors</li>
                                <li>Consider database optimization</li>
                            </ul>
                        </p>
                    </div>
                '
            ]) ?>
        </div>
    </div>
</div>

<style>
.content-section {
    line-height: 1.8;
}

.content-section h4 {
    color: #333;
    border-bottom: 2px solid #e9ecef;
    padding-bottom: 0.5rem;
}

.content-section h5 {
    color: #495057;
    margin-top: 1.5rem;
}

.content-section code {
    background-color: #f8f9fa;
    padding: 2px 6px;
    border-radius: 3px;
    font-size: 0.9em;
}

.content-section ul, .content-section ol {
    padding-left: 1.5rem;
}

.content-section li {
    margin-bottom: 0.5rem;
}
</style>

<script>
// Smooth scroll for anchor links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({ behavior: 'smooth', block: 'start', offset: -100 });
        }
    });
});
</script>

<?= $this->endSection() ?>

