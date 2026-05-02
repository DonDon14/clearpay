<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<link rel="stylesheet" href="<?= base_url('css/dashboard.css') ?>" />

<div class="container-fluid mb-4">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-1 fw-semibold"><?= $pageTitle ?? 'Help & Support' ?></h1>
            <p class="text-muted mb-0">Simple guides for daily use</p>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <?= view('partials/container-card', [
                'title' => 'Getting Started (First-Time Setup)',
                'subtitle' => 'Step-by-step onboarding for non-technical users',
                'content' => '
                    <div class="accordion" id="gettingStartedAccordion">
                        <div class="accordion-item border-0 shadow-sm mb-3">
                            <h2 class="accordion-header"><button class="accordion-button" data-bs-toggle="collapse" data-bs-target="#start1">Step 1: Sign in as admin and check system basics</button></h2>
                            <div id="start1" class="accordion-collapse collapse show" data-bs-parent="#gettingStartedAccordion"><div class="accordion-body">
                                <ol class="mb-0">
                                    <li>Log in as <strong>Admin</strong>.</li>
                                    <li>Open <strong>Settings</strong> and verify payment methods and refund methods are listed.</li>
                                    <li>Open <strong>Analytics</strong> and make sure the page loads without errors.</li>
                                </ol>
                            </div></div>
                        </div>
                        <div class="accordion-item border-0 shadow-sm mb-3">
                            <h2 class="accordion-header"><button class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#start2">Step 2: Load initial payer accounts</button></h2>
                            <div id="start2" class="accordion-collapse collapse" data-bs-parent="#gettingStartedAccordion"><div class="accordion-body">
                                <ol class="mb-0">
                                    <li>Go to <strong>Payers</strong>.</li>
                                    <li>Use <strong>Import CSV</strong> for bulk setup, or <strong>Add New Payer</strong> for manual entry.</li>
                                    <li>Confirm no duplicate email or payer ID warnings appear.</li>
                                </ol>
                            </div></div>
                        </div>
                        <div class="accordion-item border-0 shadow-sm mb-3">
                            <h2 class="accordion-header"><button class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#start3">Step 3: Run role-based testing</button></h2>
                            <div id="start3" class="accordion-collapse collapse" data-bs-parent="#gettingStartedAccordion"><div class="accordion-body">
                                <ol class="mb-0">
                                    <li>Admin role: create contributions/products, approve requests, process refunds.</li>
                                    <li>Payer role: sign up, verify email code, submit payment request, submit refund request.</li>
                                    <li>Validate each action appears in history and analytics.</li>
                                </ol>
                            </div></div>
                        </div>
                        <div class="accordion-item border-0 shadow-sm mb-0">
                            <h2 class="accordion-header"><button class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#start4">Step 4: Go-live readiness checklist</button></h2>
                            <div id="start4" class="accordion-collapse collapse" data-bs-parent="#gettingStartedAccordion"><div class="accordion-body">
                                <ul class="mb-0">
                                    <li>OTP/verification email sends successfully to real inboxes</li>
                                    <li>Admin can approve/reject payment and refund requests</li>
                                    <li>Duplicate and suspicious findings are reviewed in Analytics</li>
                                    <li>Backup/export works (CSV/PDF reports)</li>
                                </ul>
                            </div></div>
                        </div>
                    </div>
                '
            ]) ?>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <?= view('partials/container-card', [
                'title' => 'Search Help',
                'subtitle' => 'Type a keyword like payment, refund, or account',
                'content' => '
                    <div class="input-group input-group-lg">
                        <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" class="form-control border-start-0" id="helpSearch" placeholder="What do you need help with?">
                        <button class="btn btn-primary" type="button" onclick="searchHelp()">Search</button>
                    </div>
                    <div id="searchResults" class="mt-3" style="display:none;"></div>
                '
            ]) ?>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <?= view('partials/container-card', [
                'title' => 'Most Common Tasks',
                'subtitle' => 'Quick steps for the things users do most',
                'content' => '
                    <div class="accordion" id="commonTasks">
                        <div class="accordion-item border-0 shadow-sm mb-3">
                            <h2 class="accordion-header"><button class="accordion-button" data-bs-toggle="collapse" data-bs-target="#task1">1) How to log in</button></h2>
                            <div id="task1" class="accordion-collapse collapse show" data-bs-parent="#commonTasks"><div class="accordion-body">
                                <ol class="mb-0">
                                    <li>Open the ClearPay login page.</li>
                                    <li>Enter your username and password.</li>
                                    <li>Click <strong>Login</strong>.</li>
                                    <li>If you forgot your password, click <strong>Forgot Password</strong>.</li>
                                </ol>
                            </div></div>
                        </div>
                        <div class="accordion-item border-0 shadow-sm mb-3">
                            <h2 class="accordion-header"><button class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#task2">2) How to record a payment</button></h2>
                            <div id="task2" class="accordion-collapse collapse" data-bs-parent="#commonTasks"><div class="accordion-body">
                                <ol class="mb-0">
                                    <li>Go to <strong>Payments</strong>.</li>
                                    <li>Click <strong>Add Payment</strong>.</li>
                                    <li>Select the payer and contribution/product.</li>
                                    <li>Enter amount and payment method.</li>
                                    <li>Click <strong>Save</strong>.</li>
                                </ol>
                            </div></div>
                        </div>
                        <div class="accordion-item border-0 shadow-sm mb-3">
                            <h2 class="accordion-header"><button class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#task3">3) How to approve a payment request</button></h2>
                            <div id="task3" class="accordion-collapse collapse" data-bs-parent="#commonTasks"><div class="accordion-body">
                                <ol class="mb-0">
                                    <li>Open <strong>Payment Requests</strong>.</li>
                                    <li>Click a pending request to view details.</li>
                                    <li>Check proof and amount.</li>
                                    <li>Click <strong>Approve</strong> or <strong>Reject</strong>.</li>
                                </ol>
                            </div></div>
                        </div>
                        <div class="accordion-item border-0 shadow-sm mb-3">
                            <h2 class="accordion-header"><button class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#task4">4) How to process a refund</button></h2>
                            <div id="task4" class="accordion-collapse collapse" data-bs-parent="#commonTasks"><div class="accordion-body">
                                <ol class="mb-0">
                                    <li>Go to <strong>Refunds</strong>.</li>
                                    <li>Open the refund request.</li>
                                    <li>Confirm refund amount and method.</li>
                                    <li>Click <strong>Approve</strong> or <strong>Reject</strong>.</li>
                                </ol>
                            </div></div>
                        </div>
                        <div class="accordion-item border-0 shadow-sm mb-3">
                            <h2 class="accordion-header"><button class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#task5">5) How to update your profile</button></h2>
                            <div id="task5" class="accordion-collapse collapse" data-bs-parent="#commonTasks"><div class="accordion-body">
                                <ol class="mb-0">
                                    <li>Go to <strong>Profile</strong>.</li>
                                    <li>Click <strong>Edit</strong>.</li>
                                    <li>Update your details.</li>
                                    <li>Click <strong>Save Changes</strong>.</li>
                                </ol>
                            </div></div>
                        </div>
                    </div>
                '
            ]) ?>
        </div>
    </div>

    <div class="row mb-4" id="faq">
        <div class="col-12">
            <?= view('partials/container-card', [
                'title' => 'Frequently Asked Questions',
                'subtitle' => 'Short answers to common concerns',
                'content' => '
                    <div class="accordion" id="faqAccordion">
                        <div class="accordion-item border-0 shadow-sm mb-3">
                            <h2 class="accordion-header"><button class="accordion-button" data-bs-toggle="collapse" data-bs-target="#faq1">Why can\'t I log in?</button></h2>
                            <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion"><div class="accordion-body">Check your username and password first. If still not working, use <strong>Forgot Password</strong> or ask your admin to check your account status.</div></div>
                        </div>
                        <div class="accordion-item border-0 shadow-sm mb-3">
                            <h2 class="accordion-header"><button class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#faq2">Where can I see payment status?</button></h2>
                            <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion"><div class="accordion-body">Go to <strong>Payment Requests</strong> (or <strong>Payment History</strong>) to see if a request is pending, approved, or rejected.</div></div>
                        </div>
                        <div class="accordion-item border-0 shadow-sm mb-3">
                            <h2 class="accordion-header"><button class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#faq3">What if a payment request is rejected?</button></h2>
                            <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion"><div class="accordion-body">Open the request details, check the rejection reason, fix the issue (amount/proof/reference), then submit again.</div></div>
                        </div>
                        <div class="accordion-item border-0 shadow-sm mb-3">
                            <h2 class="accordion-header"><button class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#faq4">How long before requests are processed?</button></h2>
                            <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion"><div class="accordion-body">This depends on your organization\'s workflow. Most requests are reviewed the same day or next business day.</div></div>
                        </div>
                    </div>
                '
            ]) ?>
        </div>
    </div>

    <div class="row mb-4" id="contact">
        <div class="col-12">
            <?= view('partials/container-card', [
                'title' => 'Need More Help?',
                'subtitle' => 'Contact support using this simple format',
                'content' => '
                    <p class="mb-2">When reporting an issue, include:</p>
                    <ul>
                        <li>What page you were on</li>
                        <li>What you clicked</li>
                        <li>What you expected</li>
                        <li>What happened instead</li>
                        <li>A screenshot (if possible)</li>
                    </ul>
                    <div class="alert alert-info mb-0">
                        <strong>Tip:</strong> Clear, complete reports help support fix issues faster.
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

    const searchLower = searchTerm.toLowerCase();
    const sections = document.querySelectorAll('[id]');
    let found = null;

    for (const section of sections) {
        if ((section.textContent || '').toLowerCase().includes(searchLower)) {
            found = section;
            break;
        }
    }

    if (found) {
        resultsDiv.innerHTML = `<div class="alert alert-info mb-0">Found information for <strong>${searchTerm}</strong>. Jumping to section.</div>`;
        resultsDiv.style.display = 'block';
        found.scrollIntoView({ behavior: 'smooth', block: 'start' });
    } else {
        resultsDiv.innerHTML = `<div class="alert alert-warning mb-0">No matching help topic found for <strong>${searchTerm}</strong>.</div>`;
        resultsDiv.style.display = 'block';
    }
}

document.getElementById('helpSearch').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') searchHelp();
});
</script>

<?= $this->endSection() ?>
