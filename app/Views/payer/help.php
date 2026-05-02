<?= $this->extend('layouts/payer-layout') ?>

<?= $this->section('content') ?>
<div class="container-fluid ui-page-shell payer-page-shell">
    <?= view('partials/payer-page-intro', [
        'title' => $pageTitle ?? 'Help & Support',
        'subtitle' => 'Simple guides for daily use',
    ]) ?>

    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 ui-surface-card">
                <div class="card-body ui-surface-card-body">
                    <h5 class="card-title mb-3">Search Help</h5>
                    <div class="input-group input-group-lg">
                        <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" class="form-control border-start-0" id="helpSearch" placeholder="Try: login, payment, refund, profile">
                        <button class="btn btn-primary" type="button" onclick="searchHelp()">Search</button>
                    </div>
                    <div id="searchResults" class="mt-3" style="display:none;"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4" id="common-tasks">
        <div class="col-12">
            <div class="card border-0 ui-surface-card">
                <div class="card-body ui-surface-card-body">
                    <h4 class="mb-3">Most Common Tasks</h4>
                    <div class="accordion" id="commonTasksAccordion">
                        <div class="accordion-item border-0 shadow-sm mb-3">
                            <h2 class="accordion-header"><button class="accordion-button" data-bs-toggle="collapse" data-bs-target="#task1">1) Create an account</button></h2>
                            <div id="task1" class="accordion-collapse collapse show" data-bs-parent="#commonTasksAccordion"><div class="accordion-body">
                                <ol class="mb-0">
                                    <li>Open the signup page.</li>
                                    <li>Enter your Student ID, name, and password.</li>
                                    <li>Add email and contact number if available.</li>
                                    <li>Click <strong>Sign Up</strong>.</li>
                                </ol>
                            </div></div>
                        </div>
                        <div class="accordion-item border-0 shadow-sm mb-3">
                            <h2 class="accordion-header"><button class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#task2">2) Log in</button></h2>
                            <div id="task2" class="accordion-collapse collapse" data-bs-parent="#commonTasksAccordion"><div class="accordion-body">
                                <ol class="mb-0">
                                    <li>Go to the login page.</li>
                                    <li>Enter Student ID and password.</li>
                                    <li>Click <strong>Login</strong>.</li>
                                    <li>If needed, use <strong>Forgot Password</strong>.</li>
                                </ol>
                            </div></div>
                        </div>
                        <div class="accordion-item border-0 shadow-sm mb-3">
                            <h2 class="accordion-header"><button class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#task3">3) Send a payment request</button></h2>
                            <div id="task3" class="accordion-collapse collapse" data-bs-parent="#commonTasksAccordion"><div class="accordion-body">
                                <ol class="mb-0">
                                    <li>Open <strong>Payment Requests</strong>.</li>
                                    <li>Choose the contribution or product.</li>
                                    <li>Enter amount and payment method.</li>
                                    <li>Upload proof of payment.</li>
                                    <li>Click <strong>Submit</strong>.</li>
                                </ol>
                            </div></div>
                        </div>
                        <div class="accordion-item border-0 shadow-sm mb-3">
                            <h2 class="accordion-header"><button class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#task4">4) Request a refund</button></h2>
                            <div id="task4" class="accordion-collapse collapse" data-bs-parent="#commonTasksAccordion"><div class="accordion-body">
                                <ol class="mb-0">
                                    <li>Open <strong>Refund Requests</strong>.</li>
                                    <li>Select a payment to refund.</li>
                                    <li>Enter reason and refund details.</li>
                                    <li>Click <strong>Submit Refund Request</strong>.</li>
                                </ol>
                            </div></div>
                        </div>
                        <div class="accordion-item border-0 shadow-sm mb-3">
                            <h2 class="accordion-header"><button class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#task5">5) Update my profile</button></h2>
                            <div id="task5" class="accordion-collapse collapse" data-bs-parent="#commonTasksAccordion"><div class="accordion-body">
                                <ol class="mb-0">
                                    <li>Go to <strong>My Data</strong>.</li>
                                    <li>Tap or click <strong>Edit Profile</strong>.</li>
                                    <li>Update your details.</li>
                                    <li>Click <strong>Save Changes</strong>.</li>
                                </ol>
                            </div></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4" id="faq">
        <div class="col-12">
            <div class="card border-0 ui-surface-card">
                <div class="card-body ui-surface-card-body">
                    <h4 class="mb-3">Frequently Asked Questions</h4>
                    <div class="accordion" id="faqAccordion">
                        <div class="accordion-item border-0 shadow-sm mb-3">
                            <h2 class="accordion-header"><button class="accordion-button" data-bs-toggle="collapse" data-bs-target="#faq1">Why can\'t I log in?</button></h2>
                            <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion"><div class="accordion-body">Check your Student ID and password. If still not working, use <strong>Forgot Password</strong> or contact your admin.</div></div>
                        </div>
                        <div class="accordion-item border-0 shadow-sm mb-3">
                            <h2 class="accordion-header"><button class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#faq2">How do I know if my payment was accepted?</button></h2>
                            <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion"><div class="accordion-body">Open <strong>Payment Requests</strong> or <strong>Payment History</strong>. The status shows if your request is pending, approved, or rejected.</div></div>
                        </div>
                        <div class="accordion-item border-0 shadow-sm mb-3">
                            <h2 class="accordion-header"><button class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#faq3">What if my payment request was rejected?</button></h2>
                            <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion"><div class="accordion-body">Check the reason in the request details, correct the information, then submit a new request.</div></div>
                        </div>
                        <div class="accordion-item border-0 shadow-sm mb-3">
                            <h2 class="accordion-header"><button class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#faq4">How long does approval take?</button></h2>
                            <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion"><div class="accordion-body">It depends on your organization. Most requests are reviewed within the same day or next business day.</div></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4" id="contact">
        <div class="col-12">
            <div class="card border-0 ui-surface-card">
                <div class="card-body ui-surface-card-body">
                    <h4 class="mb-3">Need More Help?</h4>
                    <p class="mb-2">When asking for support, include:</p>
                    <ul>
                        <li>The page where the issue happened</li>
                        <li>What you clicked</li>
                        <li>What you expected</li>
                        <li>What happened instead</li>
                        <li>A screenshot (if possible)</li>
                    </ul>
                    <div class="alert alert-info mb-0">Clear details help support solve your issue faster.</div>
                </div>
            </div>
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
