<?= $this->extend('layouts/payer-layout') ?>

<?= $this->section('content') ?>
<div class="container-fluid mb-4">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h1 class="h3 mb-1 fw-semibold"><?= $pageTitle ?? 'Help & Support' ?></h1>
                    <p class="text-muted mb-0"><?= $pageSubtitle ?? 'Get assistance and find answers to common questions' ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Search -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-3">Search Help</h5>
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
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Links -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-3">Quick Links</h5>
                    <div class="row g-3">
                        <div class="col-md-6 col-lg-3">
                            <a href="#getting-started" class="text-decoration-none">
                                <div class="card border-0 shadow-sm h-100 hover-lift">
                                    <div class="card-body text-center p-4">
                                        <div class="text-primary mb-3">
                                            <i class="fas fa-play-circle fa-3x"></i>
                                        </div>
                                        <h5 class="card-title mb-2">Getting Started</h5>
                                        <p class="text-muted small mb-0">New user guide</p>
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
                                        <p class="text-muted small mb-0">Get in touch with us</p>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <a href="#mobile-app" class="text-decoration-none">
                                <div class="card border-0 shadow-sm h-100 hover-lift">
                                    <div class="card-body text-center p-4">
                                        <div class="text-warning mb-3">
                                            <i class="fas fa-mobile-alt fa-3x"></i>
                                        </div>
                                        <h5 class="card-title mb-2">Mobile App</h5>
                                        <p class="text-muted small mb-0">Using the mobile app</p>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Getting Started -->
    <div class="row mb-4" id="getting-started">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="mb-3">Getting Started</h4>
                    <div class="accordion" id="gettingStartedAccordion">
                        <div class="accordion-item border-0 shadow-sm mb-3">
                            <h2 class="accordion-header">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapse1">
                                    <i class="fas fa-user-plus me-2 text-primary"></i>Creating Your Account
                                </button>
                            </h2>
                            <div id="collapse1" class="accordion-collapse collapse show" data-bs-parent="#gettingStartedAccordion">
                                <div class="accordion-body">
                                    <ol>
                                        <li class="mb-2">Go to the Sign Up page</li>
                                        <li class="mb-2">Fill in your information:
                                            <ul class="mt-2">
                                                <li>Student ID (required)</li>
                                                <li>Password</li>
                                                <li>Full Name</li>
                                                <li>Email Address</li>
                                                <li>Contact Number</li>
                                                <li>Course/Department</li>
                                            </ul>
                                        </li>
                                        <li class="mb-2">Click <strong>"Sign Up"</strong></li>
                                        <li class="mb-2">Check your email for the verification code</li>
                                        <li>Enter the verification code to complete registration</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item border-0 shadow-sm mb-3">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse2">
                                    <i class="fas fa-sign-in-alt me-2 text-success"></i>Logging In
                                </button>
                            </h2>
                            <div id="collapse2" class="accordion-collapse collapse" data-bs-parent="#gettingStartedAccordion">
                                <div class="accordion-body">
                                    <ol>
                                        <li class="mb-2">Go to the Login page</li>
                                        <li class="mb-2">Enter your Student ID and Password</li>
                                        <li class="mb-2">Click <strong>"Login"</strong></li>
                                        <li>You will be redirected to your dashboard</li>
                                    </ol>
                                    <p class="mt-3 mb-0"><strong>Forgot Password?</strong> Click on "Forgot Password?" and follow the instructions to reset your password.</p>
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item border-0 shadow-sm mb-3">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse3">
                                    <i class="fas fa-hand-holding-usd me-2 text-info"></i>Viewing Your Contributions
                                </button>
                            </h2>
                            <div id="collapse3" class="accordion-collapse collapse" data-bs-parent="#gettingStartedAccordion">
                                <div class="accordion-body">
                                    <ol>
                                        <li class="mb-2">Navigate to <strong>Contributions</strong> from the sidebar</li>
                                        <li class="mb-2">You will see all active contributions that you need to pay</li>
                                        <li class="mb-2">Each contribution shows:
                                            <ul class="mt-2">
                                                <li>Contribution title and description</li>
                                                <li>Amount due per payer</li>
                                                <li>Due date</li>
                                                <li>Your payment status (Unpaid, Partially Paid, Fully Paid)</li>
                                            </ul>
                                        </li>
                                        <li>Click on a contribution to view details and payment options</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Requests -->
    <div class="row mb-4" id="payment-requests">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="mb-3">Submitting Payment Requests</h4>
                    <div class="accordion" id="paymentRequestsAccordion">
                        <div class="accordion-item border-0 shadow-sm mb-3">
                            <h2 class="accordion-header">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#pr1">
                                    <i class="fas fa-paper-plane me-2 text-primary"></i>How to Submit a Payment Request
                                </button>
                            </h2>
                            <div id="pr1" class="accordion-collapse collapse show" data-bs-parent="#paymentRequestsAccordion">
                                <div class="accordion-body">
                                    <ol>
                                        <li class="mb-2">Go to <strong>Contributions</strong> from the sidebar</li>
                                        <li class="mb-2">Select the contribution you want to pay</li>
                                        <li class="mb-2">Click <strong>"Request Payment"</strong> or <strong>"Submit Payment Request"</strong></li>
                                        <li class="mb-2">Fill in the payment request details:
                                            <ul class="mt-2">
                                                <li>Select payment method (GCash, Bank Transfer, etc.)</li>
                                                <li>Enter the amount you are paying</li>
                                                <li>Enter reference number (transaction ID)</li>
                                                <li>Upload proof of payment (screenshot or photo)</li>
                                                <li>Add any notes (optional)</li>
                                            </ul>
                                        </li>
                                        <li>Click <strong>"Submit"</strong> to send your payment request</li>
                                    </ol>
                                    <p class="mt-3 mb-0"><strong>Note:</strong> Your payment request will be reviewed by the admin. You will receive a notification when it is approved or rejected.</p>
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item border-0 shadow-sm mb-3">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#pr2">
                                    <i class="fas fa-clock me-2 text-warning"></i>Payment Request Status
                                </button>
                            </h2>
                            <div id="pr2" class="accordion-collapse collapse" data-bs-parent="#paymentRequestsAccordion">
                                <div class="accordion-body">
                                    <p>Your payment requests can have the following statuses:</p>
                                    <ul>
                                        <li><strong>Pending:</strong> Your request is waiting for admin approval</li>
                                        <li><strong>Approved:</strong> Your payment has been approved and recorded</li>
                                        <li><strong>Rejected:</strong> Your payment request was rejected. Check admin notes for the reason</li>
                                        <li><strong>Processed:</strong> Your payment has been fully processed</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Refund Requests -->
    <div class="row mb-4" id="refund-requests">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="mb-3">Submitting Refund Requests</h4>
                    <div class="accordion" id="refundRequestsAccordion">
                        <div class="accordion-item border-0 shadow-sm mb-3">
                            <h2 class="accordion-header">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#rr1">
                                    <i class="fas fa-undo me-2 text-primary"></i>How to Request a Refund
                                </button>
                            </h2>
                            <div id="rr1" class="accordion-collapse collapse show" data-bs-parent="#refundRequestsAccordion">
                                <div class="accordion-body">
                                    <ol>
                                        <li class="mb-2">Navigate to <strong>Refund Requests</strong> from the sidebar</li>
                                        <li class="mb-2">Click <strong>"Request Refund"</strong></li>
                                        <li class="mb-2">Select the payment you want to refund</li>
                                        <li class="mb-2">Fill in refund details:
                                            <ul class="mt-2">
                                                <li>Refund amount</li>
                                                <li>Refund method (how you want to receive the refund)</li>
                                                <li>Reason for refund</li>
                                                <li>Additional notes (optional)</li>
                                            </ul>
                                        </li>
                                        <li>Click <strong>"Submit Refund Request"</strong></li>
                                    </ol>
                                    <p class="mt-3 mb-0"><strong>Note:</strong> Refund requests are subject to admin approval. You will be notified of the decision.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment History -->
    <div class="row mb-4" id="payment-history">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="mb-3">Viewing Payment History</h4>
                    <p>To view your payment history:</p>
                    <ol>
                        <li class="mb-2">Navigate to <strong>Payment History</strong> from the sidebar</li>
                        <li class="mb-2">You will see all your payment records</li>
                        <li class="mb-2">Each record shows:
                            <ul class="mt-2">
                                <li>Payment date</li>
                                <li>Contribution name</li>
                                <li>Amount paid</li>
                                <li>Payment method</li>
                                <li>Reference number</li>
                                <li>Payment status</li>
                            </ul>
                        </li>
                        <li>You can filter and search your payment history</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- My Data / Profile -->
    <div class="row mb-4" id="my-data">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="mb-3">Managing Your Profile</h4>
                    <p>To update your profile information:</p>
                    <ol>
                        <li class="mb-2">Navigate to <strong>My Data</strong> from the sidebar</li>
                        <li class="mb-2">Click <strong>"Edit Profile"</strong></li>
                        <li class="mb-2">Update your information:
                            <ul class="mt-2">
                                <li>Full Name</li>
                                <li>Email Address</li>
                                <li>Contact Number</li>
                                <li>Course/Department</li>
                                <li>Profile Picture</li>
                            </ul>
                        </li>
                        <li>Click <strong>"Save Changes"</strong> to update your profile</li>
                    </ol>
                    <p class="mt-3 mb-0"><strong>Note:</strong> Some information (like Student ID) cannot be changed. Contact support if you need to update restricted fields.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Announcements -->
    <div class="row mb-4" id="announcements">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="mb-3">Viewing Announcements</h4>
                    <p>To view announcements:</p>
                    <ol>
                        <li class="mb-2">Navigate to <strong>Announcements</strong> from the sidebar</li>
                        <li class="mb-2">You will see all announcements sent to students</li>
                        <li class="mb-2">Announcements are sorted by date (newest first)</li>
                        <li>Click on an announcement to view full details</li>
                    </ol>
                    <p class="mt-3 mb-0"><strong>Note:</strong> Important announcements may appear as popup notifications when you log in.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Mobile App -->
    <div class="row mb-4" id="mobile-app">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="mb-3">Using the Mobile App</h4>
                    <p>ClearPay has a mobile app available for Android and iOS devices. The mobile app provides the same features as the web portal:</p>
                    <ul>
                        <li>View your dashboard and payment summary</li>
                        <li>View contributions and payment status</li>
                        <li>Submit payment requests</li>
                        <li>View payment history</li>
                        <li>Submit refund requests</li>
                        <li>View announcements</li>
                        <li>Receive push notifications</li>
                        <li>Update your profile</li>
                    </ul>
                    <p class="mt-3 mb-0"><strong>Download:</strong> The mobile app can be downloaded from the app store. Use the same login credentials as the web portal.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- FAQ -->
    <div class="row mb-4" id="faq">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="mb-3">Frequently Asked Questions</h4>
                    <div class="accordion" id="faqAccordion">
                        <div class="accordion-item border-0 shadow-sm mb-3">
                            <h2 class="accordion-header">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                    How long does it take for my payment request to be approved?
                                </button>
                            </h2>
                            <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    <p>Payment requests are typically reviewed within 24-48 hours during business days. You will receive a notification once your request has been processed.</p>
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item border-0 shadow-sm mb-3">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                    What should I do if my payment request is rejected?
                                </button>
                            </h2>
                            <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    <p>If your payment request is rejected, check the admin notes for the reason. Common reasons include:</p>
                                    <ul>
                                        <li>Incorrect reference number</li>
                                        <li>Unclear proof of payment</li>
                                        <li>Payment amount mismatch</li>
                                        <li>Wrong payment method</li>
                                    </ul>
                                    <p class="mt-2 mb-0">You can submit a new payment request with corrected information.</p>
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item border-0 shadow-sm mb-3">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                    How do I know if my payment was received?
                                </button>
                            </h2>
                            <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    <p>Once your payment request is approved, you will:</p>
                                    <ul>
                                        <li>Receive a notification</li>
                                        <li>See the payment in your Payment History</li>
                                        <li>See your contribution status update (Partially Paid or Fully Paid)</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item border-0 shadow-sm mb-3">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                    Can I pay in installments?
                                </button>
                            </h2>
                            <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    <p>Yes, you can make partial payments. The system will track your payment progress and show your status as "Partially Paid" until the full amount is received.</p>
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item border-0 shadow-sm mb-3">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq5">
                                    How do I request a refund?
                                </button>
                            </h2>
                            <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    <p>To request a refund:</p>
                                    <ol>
                                        <li>Go to <strong>Refund Requests</strong></li>
                                        <li>Click <strong>"Request Refund"</strong></li>
                                        <li>Select the payment you want to refund</li>
                                        <li>Fill in the refund details and reason</li>
                                        <li>Submit your request</li>
                                    </ol>
                                    <p class="mt-2 mb-0">Refund requests are subject to admin approval and policy.</p>
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item border-0 shadow-sm mb-3">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq6">
                                    I forgot my password. What should I do?
                                </button>
                            </h2>
                            <div id="faq6" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    <p>To reset your password:</p>
                                    <ol>
                                        <li>Go to the Login page</li>
                                        <li>Click <strong>"Forgot Password?"</strong></li>
                                        <li>Enter your email address</li>
                                        <li>Check your email for the reset code</li>
                                        <li>Enter the reset code and create a new password</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Contact Support -->
    <div class="row mb-4" id="contact">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="mb-3">Contact Support</h4>
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
                                    <p class="mb-0">Send us an email and we'll get back to you within 24 hours.</p>
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
                    </div>
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
        
        // Scroll to first match
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

