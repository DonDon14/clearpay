<?= $this->extend('layouts/payer-layout') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-hand-holding-usd me-2"></i>Active Contributions</h5>
                    <div class="search-container">
                        <div class="input-group" style="width: 300px;">
                            <span class="input-group-text">
                                <i class="fas fa-search"></i>
                            </span>
                            <input type="text" class="form-control" id="contributionSearch" placeholder="Search contributions...">
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (empty($contributions)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                            <h5 class="text-muted">No Active Contributions</h5>
                            <p class="text-muted">There are currently no active contributions</p>
                        </div>
                    <?php else: ?>
                        <div class="row">
                            <?php foreach ($contributions as $contribution): ?>
                                <div class="col-lg-6 mb-3">
                                    <div class="card contribution-card h-100" style="cursor: pointer;" 
                                         data-contribution='<?= json_encode($contribution) ?>'>
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <h6 class="card-title mb-0">
                                                    <i class="fas fa-file-invoice-dollar text-primary me-2"></i>
                                                    <?= esc($contribution['title']) ?>
                                                </h6>
                                                <span class="badge bg-<?= $contribution['payment_status'] === 'fully paid' ? 'success' : ($contribution['payment_status'] === 'partial' ? 'warning' : 'danger') ?>">
                                                    <?= esc(ucfirst($contribution['payment_status'])) ?>
                                                </span>
                                            </div>
                                            
                                            <p class="card-text text-muted mb-2" style="font-size: 0.9rem;">
                                                <?= esc(substr($contribution['description'], 0, 100)) ?><?= strlen($contribution['description']) > 100 ? '...' : '' ?>
                                            </p>
                                            
                                            <div class="row mb-2">
                                                <div class="col-6">
                                                    <small class="text-muted">Amount</small>
                                                    <div class="fw-bold">₱<?= number_format($contribution['amount'], 2) ?></div>
                                                </div>
                                                <div class="col-6">
                                                    <small class="text-muted">Paid</small>
                                                    <div class="fw-bold text-success">₱<?= number_format($contribution['total_paid'], 2) ?></div>
                                                </div>
                                            </div>
                                            
                                            <?php if ($contribution['remaining_balance'] > 0): ?>
                                                <div class="mb-2">
                                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                                        <small class="text-muted">Progress</small>
                                                        <small class="text-muted"><?= round(($contribution['total_paid'] / $contribution['amount']) * 100, 1) ?>%</small>
                                                    </div>
                                                    <div class="progress" style="height: 6px;">
                                                        <div class="progress-bar bg-<?= $contribution['payment_status'] === 'fully paid' ? 'success' : 'warning' ?>" 
                                                             role="progressbar" 
                                                             style="width: <?= ($contribution['total_paid'] / $contribution['amount']) * 100 ?>%">
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <div class="d-flex justify-content-between align-items-center">
                                                <small class="text-muted">
                                                    <i class="fas fa-tag me-1"></i>
                                                    <?= esc($contribution['category'] ?? 'General') ?>
                                                </small>
                                                <small class="text-muted">
                                                    <i class="fas fa-clock me-1"></i>
                                                    <?= date('M d, Y', strtotime($contribution['created_at'])) ?>
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Contribution Details Modal -->
<div class="modal fade" id="contributionModal" tabindex="-1" aria-labelledby="contributionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="contributionModalLabel">
                    <i class="fas fa-file-invoice-dollar text-primary me-2"></i>
                    <span id="modalTitle">Contribution Details</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-8">
                        <h6 class="text-muted mb-3">Description</h6>
                        <p id="modalDescription" class="mb-4"></p>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <h6 class="text-muted mb-1">Total Amount</h6>
                                        <h4 class="text-primary mb-0" id="modalTotalAmount"></h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <h6 class="text-muted mb-1">Amount Paid</h6>
                                        <h4 class="text-success mb-0" id="modalAmountPaid"></h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <h6 class="text-muted mb-1">Remaining Balance</h6>
                                        <h4 class="text-danger mb-0" id="modalRemainingBalance"></h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <h6 class="text-muted mb-1">Payment Status</h6>
                                        <span id="modalPaymentStatus" class="badge fs-6"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <h6 class="text-muted mb-2">Payment Progress</h6>
                            <div class="progress" style="height: 10px;">
                                <div id="modalProgressBar" class="progress-bar" role="progressbar" style="width: 0%"></div>
                            </div>
                            <div class="d-flex justify-content-between mt-1">
                                <small class="text-muted">0%</small>
                                <small class="text-muted" id="modalProgressText">0%</small>
                                <small class="text-muted">100%</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">Contribution Info</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <strong>Category:</strong><br>
                                    <span id="modalCategory" class="text-muted"></span>
                                </div>
                                <div class="mb-3">
                                    <strong>Created:</strong><br>
                                    <span id="modalCreatedDate" class="text-muted"></span>
                                </div>
                                <div class="mb-3">
                                    <strong>Status:</strong><br>
                                    <span id="modalStatus" class="badge"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="viewPaymentHistoryBtn">
                    <i class="fas fa-history me-2"></i>View Payment History
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Payment History Modal -->
<div class="modal fade" id="paymentHistoryModal" tabindex="-1" aria-labelledby="paymentHistoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="paymentHistoryModalLabel">
                    <i class="fas fa-history text-primary me-2"></i>
                    <span id="paymentHistoryTitle">Payment History</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="paymentHistoryContent">
                    <!-- Payment history will be loaded here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- QR Receipt Modal -->
<?= $this->include('partials/qr-receipt-modal') ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const contributionCards = document.querySelectorAll('.contribution-card');
    const contributionModal = new bootstrap.Modal(document.getElementById('contributionModal'));
    const paymentHistoryModal = new bootstrap.Modal(document.getElementById('paymentHistoryModal'));
    const qrReceiptModal = new bootstrap.Modal(document.getElementById('qrReceiptModal'));
    
    let currentContribution = null;
    
    // Search functionality
    const searchInput = document.getElementById('contributionSearch');
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const cards = document.querySelectorAll('.contribution-card');
        
        cards.forEach(card => {
            const title = card.querySelector('.card-title').textContent.toLowerCase();
            const description = card.querySelector('.card-text').textContent.toLowerCase();
            const category = card.querySelector('.text-muted').textContent.toLowerCase();
            
            if (title.includes(searchTerm) || description.includes(searchTerm) || category.includes(searchTerm)) {
                card.closest('.col-lg-6').style.display = 'block';
            } else {
                card.closest('.col-lg-6').style.display = 'none';
            }
        });
    });
    
    // Contribution card click
    contributionCards.forEach(card => {
        card.addEventListener('click', function() {
            const contributionData = JSON.parse(this.getAttribute('data-contribution'));
            currentContribution = contributionData;
            showContributionDetails(contributionData);
            contributionModal.show();
        });
    });
    
    // View payment history button
    document.getElementById('viewPaymentHistoryBtn').addEventListener('click', function() {
        if (currentContribution) {
            showPaymentHistory(currentContribution);
            contributionModal.hide();
            paymentHistoryModal.show();
        }
    });
    
    function showContributionDetails(contribution) {
        // Update modal title
        document.getElementById('modalTitle').textContent = contribution.title;
        
        // Update description
        document.getElementById('modalDescription').textContent = contribution.description || 'No description available.';
        
        // Update amounts
        document.getElementById('modalTotalAmount').textContent = '₱' + parseFloat(contribution.amount).toLocaleString('en-US', {minimumFractionDigits: 2});
        document.getElementById('modalAmountPaid').textContent = '₱' + parseFloat(contribution.total_paid).toLocaleString('en-US', {minimumFractionDigits: 2});
        document.getElementById('modalRemainingBalance').textContent = '₱' + parseFloat(contribution.remaining_balance).toLocaleString('en-US', {minimumFractionDigits: 2});
        
        // Update payment status
        const statusBadge = document.getElementById('modalPaymentStatus');
        statusBadge.textContent = contribution.payment_status.charAt(0).toUpperCase() + contribution.payment_status.slice(1);
        statusBadge.className = 'badge fs-6 bg-' + (contribution.payment_status === 'fully paid' ? 'success' : (contribution.payment_status === 'partial' ? 'warning' : 'danger'));
        
        // Update progress bar
        const progressPercentage = Math.round((contribution.total_paid / contribution.amount) * 100);
        const progressBar = document.getElementById('modalProgressBar');
        progressBar.style.width = progressPercentage + '%';
        progressBar.className = 'progress-bar bg-' + (contribution.payment_status === 'fully paid' ? 'success' : 'warning');
        document.getElementById('modalProgressText').textContent = progressPercentage + '%';
        
        // Update other info
        document.getElementById('modalCategory').textContent = contribution.category || 'General';
        document.getElementById('modalCreatedDate').textContent = new Date(contribution.created_at).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
        
        const statusBadgeInfo = document.getElementById('modalStatus');
        statusBadgeInfo.textContent = contribution.status.charAt(0).toUpperCase() + contribution.status.slice(1);
        statusBadgeInfo.className = 'badge bg-' + (contribution.status === 'active' ? 'success' : 'secondary');
    }
    
    function showPaymentHistory(contribution) {
        document.getElementById('paymentHistoryTitle').textContent = `Payment History - ${contribution.title}`;
        
        // Fetch payment history for this specific contribution
        fetch(`<?= base_url('payer/get-contribution-payments') ?>/${contribution.id}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayPaymentHistory(data.payments);
                } else {
                    document.getElementById('paymentHistoryContent').innerHTML = `
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            ${data.message || 'No payment history found for this contribution.'}
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error('Error fetching payment history:', error);
                document.getElementById('paymentHistoryContent').innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        Error loading payment history. Please try again.
                    </div>
                `;
            });
    }
    
    function displayPaymentHistory(payments) {
        if (payments.length === 0) {
            document.getElementById('paymentHistoryContent').innerHTML = `
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                    <h5 class="text-muted">No Payment Records</h5>
                    <p class="text-muted">No payments have been made for this contribution yet.</p>
                </div>
            `;
            return;
        }
        
        let html = `
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>Reference Number</th>
                            <th>Payment Method</th>
                            <th>Status</th>
                            <th>Receipt</th>
                        </tr>
                    </thead>
                    <tbody>
        `;
        
        payments.forEach(payment => {
            html += `
                <tr>
                    <td>${new Date(payment.payment_date || payment.created_at).toLocaleDateString('en-US', {
                        year: 'numeric',
                        month: 'short',
                        day: 'numeric'
                    })}</td>
                    <td><strong>₱${parseFloat(payment.amount_paid).toLocaleString('en-US', {minimumFractionDigits: 2})}</strong></td>
                    <td><code>${payment.reference_number || 'N/A'}</code></td>
                    <td>${payment.payment_method ? payment.payment_method.charAt(0).toUpperCase() + payment.payment_method.slice(1) : 'N/A'}</td>
                    <td>
                        <span class="badge bg-${payment.payment_status === 'fully paid' ? 'success' : 'warning'}">
                            ${payment.payment_status ? payment.payment_status.charAt(0).toUpperCase() + payment.payment_status.slice(1) : 'Pending'}
                        </span>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary view-receipt-btn" data-payment='${JSON.stringify(payment)}'>
                            <i class="fas fa-qrcode"></i> View QR
                        </button>
                    </td>
                </tr>
            `;
        });
        
        html += `
                    </tbody>
                </table>
            </div>
        `;
        
        document.getElementById('paymentHistoryContent').innerHTML = html;
        
        // Add event listeners for receipt buttons
        document.querySelectorAll('.view-receipt-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const paymentData = JSON.parse(this.getAttribute('data-payment'));
                console.log('Payment data being passed to showQRReceipt:', paymentData);
                showQRReceipt(paymentData);
                paymentHistoryModal.hide();
                qrReceiptModal.show();
            });
        });
    }
    
    // View QR Receipt functionality
    function showQRReceipt(payment) {
        console.log('showQRReceipt called with payment:', payment);
        
        // Wait for modal elements to be available
        setTimeout(function() {
            const qrReceiptTitle = document.getElementById('qrReceiptTitle');
            const qrAmountPaid = document.getElementById('qrAmountPaid');
            const qrReferenceNumber = document.getElementById('qrReferenceNumber');
            const qrPaymentDate = document.getElementById('qrPaymentDate');
            const qrPaymentMethod = document.getElementById('qrPaymentMethod');
            const qrPayerName = document.getElementById('qrPayerName');
            const qrPayerId = document.getElementById('qrPayerId');
            const qrPayerContact = document.getElementById('qrPayerContact');
            const qrPayerEmail = document.getElementById('qrPayerEmail');
            const qrContributionTitle = document.getElementById('qrContributionTitle');
            const qrRecordedBy = document.getElementById('qrRecordedBy');
            const qrIssueDate = document.getElementById('qrIssueDate');
            const qrPaymentStatus = document.getElementById('qrPaymentStatus');
            const qrReceiptContent = document.getElementById('qrReceiptContent');
            
            // Check if all elements exist
            if (!qrReceiptTitle || !qrAmountPaid || !qrReferenceNumber || !qrPaymentDate || 
                !qrPaymentMethod || !qrPayerName || !qrPayerId || !qrPayerContact || 
                !qrPayerEmail || !qrContributionTitle || !qrRecordedBy || !qrIssueDate || 
                !qrPaymentStatus || !qrReceiptContent) {
                console.error('QR Receipt modal elements not found');
                console.log('Missing elements:', {
                    qrReceiptTitle: !!qrReceiptTitle,
                    qrAmountPaid: !!qrAmountPaid,
                    qrReferenceNumber: !!qrReferenceNumber,
                    qrPaymentDate: !!qrPaymentDate,
                    qrPaymentMethod: !!qrPaymentMethod,
                    qrPayerName: !!qrPayerName,
                    qrPayerId: !!qrPayerId,
                    qrPayerContact: !!qrPayerContact,
                    qrPayerEmail: !!qrPayerEmail,
                    qrContributionTitle: !!qrContributionTitle,
                    qrRecordedBy: !!qrRecordedBy,
                    qrIssueDate: !!qrIssueDate,
                    qrPaymentStatus: !!qrPaymentStatus,
                    qrReceiptContent: !!qrReceiptContent
                });
                return;
            }
            
            console.log('All elements found, updating modal with payment data');
            
            // Update modal title
            qrReceiptTitle.textContent = `QR Receipt - ${payment.reference_number || 'Payment'}`;
            
            // Update QR reference display
            const qrReferenceDisplay = document.getElementById('qrReferenceDisplay');
            if (qrReferenceDisplay) {
                qrReferenceDisplay.textContent = payment.reference_number || 'N/A';
            }
            
            // Update payment details
            qrAmountPaid.textContent = '₱' + parseFloat(payment.amount_paid || 0).toLocaleString('en-US', {minimumFractionDigits: 2});
            qrReferenceNumber.textContent = payment.reference_number || 'N/A';
            qrPaymentDate.textContent = payment.payment_date ? new Date(payment.payment_date).toLocaleDateString('en-US') : (payment.created_at ? new Date(payment.created_at).toLocaleDateString('en-US') : 'N/A');
            qrPaymentMethod.textContent = payment.payment_method ? payment.payment_method.charAt(0).toUpperCase() + payment.payment_method.slice(1) : 'N/A';
            
            // Update payer details
            qrPayerName.textContent = payment.payer_name || 'N/A';
            qrPayerId.textContent = payment.payer_id || 'N/A';
            qrPayerContact.textContent = payment.contact_number || 'N/A';
            qrPayerEmail.textContent = payment.email_address || 'N/A';
            
            // Update contribution details
            qrContributionTitle.textContent = payment.contribution_title || 'N/A';
            
            // Update issued by details
            qrRecordedBy.textContent = payment.recorded_by_name || 'System Administrator';
            qrIssueDate.textContent = new Date().toLocaleDateString('en-US');
            
            // Update payment status
            const status = payment.payment_status || 'pending';
            const statusText = status === 'fully paid' ? 'Completed' : (status === 'partial' ? 'Partial' : 'Pending');
            qrPaymentStatus.innerHTML = `<span class="badge bg-${status === 'fully paid' ? 'success' : (status === 'partial' ? 'warning' : 'secondary')}">${statusText}</span>`;
            
            console.log('Modal updated, generating QR code');
            
            // Generate QR code
            generateQRCode(payment);
            
            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('qrReceiptModal'));
            modal.show();
            
            // Clean up modal backdrop when closed
            document.getElementById('qrReceiptModal').addEventListener('hidden.bs.modal', function() {
                // Remove any lingering backdrop
                const backdrops = document.querySelectorAll('.modal-backdrop');
                backdrops.forEach(backdrop => backdrop.remove());
                
                // Remove modal-open class from body
                document.body.classList.remove('modal-open');
                
                // Reset body padding if needed
                document.body.style.paddingRight = '';
            });
        }, 100);
    }
    
    function generateQRCode(payment) {
        console.log('generateQRCode called with payment:', payment);
        
        const qrContainer = document.getElementById('qrReceiptContent');
        if (!qrContainer) {
            console.error('QR container not found');
            return;
        }
        
        // QR code data
        const qrText = `${payment.receipt_number || payment.id}|${payment.payer_name || 'Payer'}|${payment.amount_paid || 0}|${payment.payment_date || payment.created_at}|${payment.reference_number || 'N/A'}`;
        const qrApiUrl = `https://api.qrserver.com/v1/create-qr-code/?size=120x120&ecc=H&data=${encodeURIComponent(qrText)}`;
        
        console.log('QR Text:', qrText);
        console.log('QR API URL:', qrApiUrl);
        
        // Show loading state
        qrContainer.innerHTML = '<div class="text-center"><div class="spinner-border spinner-border-sm text-primary mb-1" role="status" style="width: 1rem; height: 1rem;"><span class="visually-hidden">Loading...</span></div><p class="text-muted mb-0" style="font-size: 0.7rem;">Generating...</p></div>';
        
        // Create QR image
        const qrImage = document.createElement('img');
        qrImage.style.cssText = 'max-width: 120px; max-height: 120px; width: auto; height: auto; border: 1px solid #0d6efd; border-radius: 4px; padding: 2px; background: white;';
        qrImage.alt = 'QR Receipt for Payment #' + payment.id;
        qrImage.crossOrigin = 'anonymous';
        
        qrImage.onload = function() {
            console.log('QR code loaded successfully');
            qrContainer.innerHTML = '';
            qrContainer.appendChild(qrImage);
        };
        
        qrImage.onerror = function() {
            console.log('Primary QR API failed, trying fallback');
            // Fallback to Google Charts API
            const fallbackUrl = `https://chart.googleapis.com/chart?chs=120x120&cht=qr&chl=${encodeURIComponent(qrText)}&choe=UTF-8`;
            qrImage.src = fallbackUrl;
        };
        
        qrImage.src = qrApiUrl;
    }
    
    // Make functions globally accessible
    window.showQRReceipt = showQRReceipt;
    window.generateQRCode = generateQRCode;
    
});
</script>
<?= $this->endSection() ?>
