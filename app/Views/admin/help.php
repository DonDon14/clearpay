<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<link rel="stylesheet" href="<?= base_url('css/dashboard.css') ?>" />

<div class="container-fluid mb-4">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-1 fw-semibold"><?= $pageTitle ?? 'Help & Support' ?></h1>
                    <p class="text-muted mb-0"><?= $pageSubtitle ?? 'Get assistance and find answers to common questions' ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Search -->
    <div class="row mb-4">
        <div class="col-12">
            <?= view('partials/container-card', [
                'title' => 'Search Help',
                'subtitle' => 'Find answers quickly',
                'bodyClass' => '',
                'content' => '
                    <div class="input-group input-group-lg">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                        <input type="text" class="form-control border-start-0" id="helpSearch" placeholder="Search for help topics, FAQs, or guides...">
                        <button class="btn btn-primary" type="button" onclick="searchHelp()">
                            <i class="fas fa-search me-1"></i>Search
                        </button>
                    </div>
                    <div id="searchResults" class="mt-3" style="display: none;"></div>
                '
            ]) ?>
        </div>
    </div>

    <!-- Quick Links -->
    <div class="row mb-4">
        <div class="col-12">
            <?= view('partials/container-card', [
                'title' => 'Quick Links',
                'subtitle' => 'Common resources and guides',
                'bodyClass' => '',
                'content' => '
                    <div class="row g-3">
                        <div class="col-md-6 col-lg-3">
                            <a href="#getting-started" class="text-decoration-none">
                                <div class="card border-0 shadow-sm h-100 hover-lift">
                                    <div class="card-body text-center p-4">
                                        <div class="text-primary mb-3">
                                            <i class="fas fa-play-circle fa-3x"></i>
                                        </div>
                                        <h5 class="card-title mb-2">Getting Started</h5>
                                        <p class="text-muted small mb-0">New user guide and setup</p>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <a href="#faq" class="text-decoration-none">
                                <div class="card border-0 shadow-sm h-100 hover-lift">
                                    <div class="card-body text-center p-4">
                                        <div class="text-info mb-3">
                                            <i class="fas fa-question-circle fa-3x"></i>
                                        </div>
                                        <h5 class="card-title mb-2">FAQ</h5>
                                        <p class="text-muted small mb-0">Frequently asked questions</p>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <a href="#contact" class="text-decoration-none">
                                <div class="card border-0 shadow-sm h-100 hover-lift">
                                    <div class="card-body text-center p-4">
                                        <div class="text-success mb-3">
                                            <i class="fas fa-envelope fa-3x"></i>
                                        </div>
                                        <h5 class="card-title mb-2">Contact Support</h5>
                                        <p class="text-muted small mb-0">Get in touch with our team</p>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <a href="#documentation" class="text-decoration-none">
                                <div class="card border-0 shadow-sm h-100 hover-lift">
                                    <div class="card-body text-center p-4">
                                        <div class="text-warning mb-3">
                                            <i class="fas fa-book fa-3x"></i>
                                        </div>
                                        <h5 class="card-title mb-2">Documentation</h5>
                                        <p class="text-muted small mb-0">Complete system documentation</p>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                '
            ]) ?>
        </div>
    </div>

    <!-- Help Topics -->
    <div class="row mb-4">
        <div class="col-12">
            <?= view('partials/container-card', [
                'title' => 'Help Topics',
                'subtitle' => 'Browse by category',
                'bodyClass' => '',
                'content' => '
                    <div class="row g-3">
                        <div class="col-md-6 col-lg-4">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-start mb-3">
                                        <div class="bg-primary bg-opacity-10 rounded p-3 me-3">
                                            <i class="fas fa-user text-primary fa-2x"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h5 class="card-title mb-2">Account Management</h5>
                                            <p class="text-muted small mb-3">Profile settings, security, and account access</p>
                                            <ul class="list-unstyled mb-0 small">
                                                <li><i class="fas fa-check text-success me-2"></i>Profile settings</li>
                                                <li><i class="fas fa-check text-success me-2"></i>Password management</li>
                                                <li><i class="fas fa-check text-success me-2"></i>Security settings</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-start mb-3">
                                        <div class="bg-success bg-opacity-10 rounded p-3 me-3">
                                            <i class="fas fa-credit-card text-success fa-2x"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h5 class="card-title mb-2">Payments & Transactions</h5>
                                            <p class="text-muted small mb-3">Payment methods, transactions, and receipts</p>
                                            <ul class="list-unstyled mb-0 small">
                                                <li><i class="fas fa-check text-success me-2"></i>Payment processing</li>
                                                <li><i class="fas fa-check text-success me-2"></i>Payment requests</li>
                                                <li><i class="fas fa-check text-success me-2"></i>Receipt generation</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-start mb-3">
                                        <div class="bg-info bg-opacity-10 rounded p-3 me-3">
                                            <i class="fas fa-hand-holding-usd text-info fa-2x"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h5 class="card-title mb-2">Contributions</h5>
                                            <p class="text-muted small mb-3">Managing and tracking student contributions</p>
                                            <ul class="list-unstyled mb-0 small">
                                                <li><i class="fas fa-check text-success me-2"></i>Creating contributions</li>
                                                <li><i class="fas fa-check text-success me-2"></i>Managing payers</li>
                                                <li><i class="fas fa-check text-success me-2"></i>Tracking payments</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-start mb-3">
                                        <div class="bg-warning bg-opacity-10 rounded p-3 me-3">
                                            <i class="fas fa-undo text-warning fa-2x"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h5 class="card-title mb-2">Refunds</h5>
                                            <p class="text-muted small mb-3">Processing and managing refunds</p>
                                            <ul class="list-unstyled mb-0 small">
                                                <li><i class="fas fa-check text-success me-2"></i>Refund requests</li>
                                                <li><i class="fas fa-check text-success me-2"></i>Refund processing</li>
                                                <li><i class="fas fa-check text-success me-2"></i>Refund history</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-start mb-3">
                                        <div class="bg-danger bg-opacity-10 rounded p-3 me-3">
                                            <i class="fas fa-chart-bar text-danger fa-2x"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h5 class="card-title mb-2">Reports & Analytics</h5>
                                            <p class="text-muted small mb-3">Financial reports and data analysis</p>
                                            <ul class="list-unstyled mb-0 small">
                                                <li><i class="fas fa-check text-success me-2"></i>Financial reports</li>
                                                <li><i class="fas fa-check text-success me-2"></i>Data analytics</li>
                                                <li><i class="fas fa-check text-success me-2"></i>Export options</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-start mb-3">
                                        <div class="bg-secondary bg-opacity-10 rounded p-3 me-3">
                                            <i class="fas fa-wrench text-secondary fa-2x"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h5 class="card-title mb-2">Troubleshooting</h5>
                                            <p class="text-muted small mb-3">Common issues and their solutions</p>
                                            <ul class="list-unstyled mb-0 small">
                                                <li><i class="fas fa-check text-success me-2"></i>Common errors</li>
                                                <li><i class="fas fa-check text-success me-2"></i>System issues</li>
                                                <li><i class="fas fa-check text-success me-2"></i>Performance tips</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                '
            ]) ?>
        </div>
    </div>

    <!-- Getting Started -->
    <div class="row mb-4" id="getting-started">
        <div class="col-12">
            <?= view('partials/container-card', [
                'title' => 'Getting Started',
                'subtitle' => 'New to ClearPay? Start here',
                'bodyClass' => '',
                'content' => '
                    <div class="accordion" id="gettingStartedAccordion">
                        <div class="accordion-item border-0 shadow-sm mb-3">
                            <h2 class="accordion-header">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapse1">
                                    <i class="fas fa-user-plus me-2 text-primary"></i>Creating Your First Contribution
                                </button>
                            </h2>
                            <div id="collapse1" class="accordion-collapse collapse show" data-bs-parent="#gettingStartedAccordion">
                                <div class="accordion-body">
                                    <ol>
                                        <li class="mb-2">Navigate to <strong>Contributions</strong> from the sidebar</li>
                                        <li class="mb-2">Click the <strong>"Add New Contribution"</strong> button</li>
                                        <li class="mb-2">Fill in the contribution details:
                                            <ul class="mt-2">
                                                <li>Title and description</li>
                                                <li>Grand total amount</li>
                                                <li>Number of payers</li>
                                                <li>Due date</li>
                                            </ul>
                                        </li>
                                        <li class="mb-2">The system will automatically calculate the amount per payer</li>
                                        <li>Click <strong>"Save"</strong> to create the contribution</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item border-0 shadow-sm mb-3">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse2">
                                    <i class="fas fa-users me-2 text-success"></i>Managing Payers
                                </button>
                            </h2>
                            <div id="collapse2" class="accordion-collapse collapse" data-bs-parent="#gettingStartedAccordion">
                                <div class="accordion-body">
                                    <ol>
                                        <li class="mb-2">Go to <strong>Payers</strong> from the sidebar</li>
                                        <li class="mb-2">Click <strong>"Add New Payer"</strong> to register a new student</li>
                                        <li class="mb-2">Fill in the payer information:
                                            <ul class="mt-2">
                                                <li>Student ID (unique identifier)</li>
                                                <li>Full name</li>
                                                <li>Email address</li>
                                                <li>Contact number</li>
                                                <li>Course/Department</li>
                                            </ul>
                                        </li>
                                        <li>Save the payer information</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item border-0 shadow-sm mb-3">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse3">
                                    <i class="fas fa-credit-card me-2 text-info"></i>Processing Payments
                                </button>
                            </h2>
                            <div id="collapse3" class="accordion-collapse collapse" data-bs-parent="#gettingStartedAccordion">
                                <div class="accordion-body">
                                    <ol>
                                        <li class="mb-2">Navigate to <strong>Payments</strong> from the sidebar</li>
                                        <li class="mb-2">Click <strong>"Add Payment"</strong> button</li>
                                        <li class="mb-2">Select the payer and contribution</li>
                                        <li class="mb-2">Enter payment details:
                                            <ul class="mt-2">
                                                <li>Payment amount</li>
                                                <li>Payment method</li>
                                                <li>Payment date</li>
                                                <li>Reference number (if applicable)</li>
                                            </ul>
                                        </li>
                                        <li>Save the payment to record the transaction</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                '
            ]) ?>
        </div>
    </div>

    <!-- FAQ -->
    <div class="row mb-4" id="faq">
        <div class="col-12">
            <?= view('partials/container-card', [
                'title' => 'Frequently Asked Questions',
                'subtitle' => 'Common questions and answers',
                'bodyClass' => '',
                'content' => '
                    <div class="accordion" id="faqAccordion">
                        <div class="accordion-item border-0 shadow-sm mb-3">
                            <h2 class="accordion-header">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                    How do I approve a payment request?
                                </button>
                            </h2>
                            <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    <p>To approve a payment request:</p>
                                    <ol>
                                        <li>Go to <strong>Payment Requests</strong> from the sidebar</li>
                                        <li>Find the pending request you want to approve</li>
                                        <li>Click on the request to view details</li>
                                        <li>Click the <strong>"Approve"</strong> button</li>
                                        <li>Confirm the action in the modal</li>
                                    </ol>
                                    <p class="mb-0">The payment will be automatically recorded in the system.</p>
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item border-0 shadow-sm mb-3">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                    How do I process a refund?
                                </button>
                            </h2>
                            <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    <p>To process a refund:</p>
                                    <ol>
                                        <li>Navigate to <strong>Refunds</strong> from the sidebar</li>
                                        <li>Select the payment you want to refund</li>
                                        <li>Click <strong>"Process Refund"</strong></li>
                                        <li>Enter refund details:
                                            <ul>
                                                <li>Refund amount</li>
                                                <li>Refund method</li>
                                                <li>Refund reason</li>
                                            </ul>
                                        </li>
                                        <li>Complete the refund process</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item border-0 shadow-sm mb-3">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                    How do I create a backup?
                                </button>
                            </h2>
                            <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    <p>To create a database backup:</p>
                                    <ol>
                                        <li>Go to <strong>Settings</strong> from the sidebar</li>
                                        <li>Scroll to the <strong>System Information</strong> section</li>
                                        <li>In the <strong>Maintenance Tools</strong> section, click <strong>"Create Backup"</strong></li>
                                        <li>Wait for the backup to complete</li>
                                        <li>The backup file will be saved to your Documents folder</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item border-0 shadow-sm mb-3">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                    How do I configure email settings?
                                </button>
                            </h2>
                            <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    <p>To configure email settings:</p>
                                    <ol>
                                        <li>Navigate to <strong>Settings</strong> from the sidebar</li>
                                        <li>Find the <strong>Email Settings</strong> section</li>
                                        <li>Click <strong>"Configure"</strong> next to SMTP Configuration</li>
                                        <li>Enter your SMTP server details:
                                            <ul>
                                                <li>SMTP Host</li>
                                                <li>SMTP Port</li>
                                                <li>Username and Password</li>
                                                <li>Encryption type</li>
                                            </ul>
                                        </li>
                                        <li>Click <strong>"Save Configuration"</strong></li>
                                        <li>Test the email configuration using the <strong>"Send Test"</strong> button</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item border-0 shadow-sm mb-3">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq5">
                                    How do I export payment data?
                                </button>
                            </h2>
                            <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    <p>To export payment data:</p>
                                    <ol>
                                        <li>Go to <strong>Payments</strong> from the sidebar</li>
                                        <li>Use the filters to narrow down the data you want to export</li>
                                        <li>Click the <strong>"Export"</strong> button</li>
                                        <li>Select the export format (PDF, Excel, CSV)</li>
                                        <li>Download the exported file</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                '
            ]) ?>
        </div>
    </div>

    <!-- Contact Support -->
    <div class="row mb-4" id="contact">
        <div class="col-12">
            <?= view('partials/container-card', [
                'title' => 'Contact Support',
                'subtitle' => 'Get in touch with our support team',
                'bodyClass' => '',
                'content' => '
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="bg-primary bg-opacity-10 rounded p-3 me-3">
                                            <i class="fas fa-envelope text-primary fa-2x"></i>
                                        </div>
                                        <div>
                                            <h5 class="card-title mb-1">Email Support</h5>
                                            <p class="text-muted small mb-0">support@clearpay.com</p>
                                        </div>
                                    </div>
                                    <p class="mb-0">Send us an email and we\'ll get back to you within 24 hours.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="bg-success bg-opacity-10 rounded p-3 me-3">
                                            <i class="fas fa-phone text-success fa-2x"></i>
                                        </div>
                                        <div>
                                            <h5 class="card-title mb-1">Phone Support</h5>
                                            <p class="text-muted small mb-0">+63 (0) 123 456 7890</p>
                                        </div>
                                    </div>
                                    <p class="mb-0">Available Monday to Friday, 9:00 AM - 5:00 PM (PHT).</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="bg-info bg-opacity-10 rounded p-3 me-3">
                                            <i class="fas fa-clock text-info fa-2x"></i>
                                        </div>
                                        <div>
                                            <h5 class="card-title mb-1">Response Time</h5>
                                            <p class="text-muted small mb-0">Within 24 hours</p>
                                        </div>
                                    </div>
                                    <p class="mb-0">We typically respond to all inquiries within 24 hours during business days.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="bg-warning bg-opacity-10 rounded p-3 me-3">
                                            <i class="fas fa-headset text-warning fa-2x"></i>
                                        </div>
                                        <div>
                                            <h5 class="card-title mb-1">Live Chat</h5>
                                            <p class="text-muted small mb-0">Coming Soon</p>
                                        </div>
                                    </div>
                                    <p class="mb-0">Live chat support will be available soon for instant assistance.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                '
            ]) ?>
        </div>
    </div>

    <!-- Documentation -->
    <div class="row mb-4" id="documentation">
        <div class="col-12">
            <?= view('partials/container-card', [
                'title' => 'Documentation',
                'subtitle' => 'Complete system documentation and guides',
                'bodyClass' => '',
                'content' => '
                    <div class="row g-3">
                        <div class="col-md-6 col-lg-4">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body p-4">
                                    <h5 class="card-title mb-3">
                                        <i class="fas fa-book text-primary me-2"></i>User Manual
                                    </h5>
                                    <p class="text-muted small mb-3">Complete guide to using ClearPay system features and functionalities.</p>
                                    <a href="' . base_url('help/user-manual') . '" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-book me-1"></i>View Manual
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body p-4">
                                    <h5 class="card-title mb-3">
                                        <i class="fas fa-code text-info me-2"></i>API Documentation
                                    </h5>
                                    <p class="text-muted small mb-3">Technical documentation for API integration and development.</p>
                                    <a href="' . base_url('help/api-documentation') . '" class="btn btn-outline-info btn-sm">
                                        <i class="fas fa-external-link-alt me-1"></i>View API Docs
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body p-4">
                                    <h5 class="card-title mb-3">
                                        <i class="fas fa-video text-success me-2"></i>Video Tutorials
                                    </h5>
                                    <p class="text-muted small mb-3">Step-by-step video guides for common tasks and workflows.</p>
                                    <a href="#" class="btn btn-outline-success btn-sm" onclick="alert(\'Video tutorials coming soon!\'); return false;">
                                        <i class="fas fa-play me-1"></i>Coming Soon
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                '
            ]) ?>
        </div>
    </div>
</div>

<script>
function searchHelp() {
    const searchTerm = document.getElementById('helpSearch').value.trim();
    const resultsDiv = document.getElementById('searchResults');
    
    if (!searchTerm) {
        resultsDiv.style.display = 'none';
        return;
    }
    
    // Simple search implementation
    const allContent = document.body.innerText.toLowerCase();
    const searchLower = searchTerm.toLowerCase();
    
    if (allContent.includes(searchLower)) {
        resultsDiv.innerHTML = `
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                Found results for "<strong>${searchTerm}</strong>". Scroll down to see relevant sections.
            </div>
        `;
        resultsDiv.style.display = 'block';
        
        // Scroll to first match (simple implementation)
        const sections = document.querySelectorAll('[id]');
        for (let section of sections) {
            if (section.textContent.toLowerCase().includes(searchLower)) {
                section.scrollIntoView({ behavior: 'smooth', block: 'start' });
                break;
            }
        }
    } else {
        resultsDiv.innerHTML = `
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle me-2"></i>
                No results found for "<strong>${searchTerm}</strong>". Please try different keywords or contact support.
            </div>
        `;
        resultsDiv.style.display = 'block';
    }
}

// Allow Enter key to trigger search
document.getElementById('helpSearch').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        searchHelp();
    }
});

// Smooth scroll for anchor links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    });
});
</script>

<style>
.hover-lift {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.hover-lift:hover {
    transform: translateY(-5px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}
</style>

<?= $this->endSection() ?>

