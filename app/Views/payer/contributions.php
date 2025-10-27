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
                                                       <span class="badge payment-status-badge" data-total-paid="<?= $contribution['total_paid'] ?>" data-total-amount="<?= $contribution['amount'] ?>">
                                                           <!-- Status will be calculated by JavaScript -->
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
                                                    <small class="text-muted">Total Paid</small>
                                                    <div class="fw-bold text-success">₱<?= number_format($contribution['total_paid'], 2) ?></div>
                                                </div>
                                            </div>
                                            
                                            <!-- Payment Groups Section -->
                                            <?php if (!empty($contribution['payment_groups'])): ?>
                                                <div class="mb-2">
                                                    <small class="text-muted">Payment Groups:</small>
                                                    <div class="mt-1">
                                                        <?php foreach ($contribution['payment_groups'] as $group): ?>
                                                            <div class="payment-group-item mb-1 p-2 border rounded" 
                                                                 style="cursor: pointer; background-color: #f8f9fa;"
                                                                 data-contribution-id="<?= $contribution['id'] ?>"
                                                                 data-payment-sequence="<?= $group['payment_sequence'] ?>"
                                                                 data-group-data='<?= json_encode($group) ?>'>
                                                                <div class="d-flex justify-content-between align-items-center">
                                                                    <div>
                                                                        <small class="fw-bold">
                                                                            Group <?= $group['payment_sequence'] ?>
                                                                            <?php if ($group['computed_status'] === 'fully paid'): ?>
                                                                                <span class="badge bg-success ms-1">Completed</span>
                                                                            <?php else: ?>
                                                                                <span class="badge bg-warning ms-1">Partial</span>
                                                                            <?php endif; ?>
                                                                        </small>
                                                                    </div>
                                                                    <div class="text-end">
                                                                        <small class="fw-bold text-success">₱<?= number_format($group['total_paid'], 2) ?></small>
                                                                        <?php if ($group['computed_status'] !== 'fully paid'): ?>
                                                                            <br><small class="text-muted">Remaining: ₱<?= number_format($group['remaining_balance'], 2) ?></small>
                                                                        <?php endif; ?>
                                                                    </div>
                                                                </div>
                                                                <div class="mt-1">
                                                                    <small class="text-muted">
                                                                        <?= $group['payment_count'] ?> payment<?= $group['payment_count'] > 1 ? 's' : '' ?> • 
                                                                        Last: <?= date('M d, Y', strtotime($group['last_payment_date'])) ?>
                                                                    </small>
                                                                </div>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                            
                                                   <?php if ($contribution['remaining_balance'] > 0): ?>
                                                       <div class="mb-2">
                                                           <div class="d-flex justify-content-between align-items-center mb-1">
                                                               <small class="text-muted">Progress</small>
                                                               <small class="text-muted progress-percentage"><?= round(($contribution['total_paid'] / $contribution['amount']) * 100, 1) ?>%</small>
                                                           </div>
                                                           <div class="progress" style="height: 6px;">
                                                               <div class="progress-bar progress-bar-dynamic" 
                                                                    role="progressbar" 
                                                                    data-total-paid="<?= $contribution['total_paid'] ?>"
                                                                    data-total-amount="<?= $contribution['amount'] ?>"
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
                <button type="button" class="btn btn-success" id="requestPaymentBtn" style="display: none;">
                    <i class="fas fa-paper-plane me-2"></i>Request Payment
                </button>
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
<?= $this->include('partials/modal-qr-receipt') ?>

<script>
       document.addEventListener('DOMContentLoaded', function() {
           const contributionCards = document.querySelectorAll('.contribution-card');
           const contributionModal = new bootstrap.Modal(document.getElementById('contributionModal'));
           const paymentHistoryModal = new bootstrap.Modal(document.getElementById('paymentHistoryModal'));
           
           let currentContribution = null;
           
           // Calculate payment status dynamically for all contribution cards
           calculatePaymentStatuses();
           
           // Function to calculate payment status based on total paid vs contribution amount
           function calculatePaymentStatuses() {
               const statusBadges = document.querySelectorAll('.payment-status-badge');
               const progressBars = document.querySelectorAll('.progress-bar-dynamic');
               
               statusBadges.forEach(badge => {
                   const totalPaid = parseFloat(badge.getAttribute('data-total-paid')) || 0;
                   const totalAmount = parseFloat(badge.getAttribute('data-total-amount')) || 0;
                   
                   let status, statusClass, statusText;
                   
                   if (totalPaid === 0) {
                       status = 'unpaid';
                       statusClass = 'bg-secondary text-white';
                       statusText = 'UNPAID';
                   } else if (totalPaid >= totalAmount) {
                       status = 'fully paid';
                       statusClass = 'bg-primary text-white';
                       statusText = 'COMPLETED';
                   } else {
                       status = 'partial';
                       statusClass = 'bg-warning text-dark';
                       statusText = 'PARTIAL';
                   }
                   
                   // Update badge appearance
                   badge.className = `badge ${statusClass}`;
                   badge.textContent = statusText;
                   
                   // Store calculated status for modal use
                   badge.setAttribute('data-calculated-status', status);
               });
               
               // Update progress bar colors
               progressBars.forEach(progressBar => {
                   const totalPaid = parseFloat(progressBar.getAttribute('data-total-paid')) || 0;
                   const totalAmount = parseFloat(progressBar.getAttribute('data-total-amount')) || 0;
                   
                   let progressClass;
                   if (totalPaid >= totalAmount) {
                       progressClass = 'bg-success';
                   } else {
                       progressClass = 'bg-warning';
                   }
                   
                   progressBar.className = `progress-bar ${progressClass}`;
               });
           }
    
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
               
               // Calculate payment status dynamically
               const totalPaid = parseFloat(contribution.total_paid) || 0;
               const totalAmount = parseFloat(contribution.amount) || 0;
               
               let status, statusClass, statusText;
               
               if (totalPaid === 0) {
                   status = 'unpaid';
                   statusClass = 'bg-danger';
                   statusText = 'Unpaid';
               } else if (totalPaid >= totalAmount) {
                   status = 'fully paid';
                   statusClass = 'bg-success';
                   statusText = 'Fully paid';
               } else {
                   status = 'partial';
                   statusClass = 'bg-warning text-dark';
                   statusText = 'Partial';
               }
               
               // Update payment status
               const statusBadge = document.getElementById('modalPaymentStatus');
               statusBadge.textContent = statusText;
               statusBadge.className = `badge fs-6 ${statusClass}`;
        
               // Update progress bar
               const progressPercentage = Math.round((contribution.total_paid / contribution.amount) * 100);
               const progressBar = document.getElementById('modalProgressBar');
               progressBar.style.width = progressPercentage + '%';
               progressBar.className = 'progress-bar bg-' + (status === 'fully paid' ? 'success' : 'warning');
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
        
        // Show/hide Request Payment button based on remaining balance
        const requestPaymentBtn = document.getElementById('requestPaymentBtn');
        const remainingBalance = parseFloat(contribution.remaining_balance) || 0;
        
        if (remainingBalance > 0) {
            requestPaymentBtn.style.display = 'inline-block';
            requestPaymentBtn.onclick = function() {
                // Close current modal
                contributionModal.hide();
                // Open payment request modal with contribution data
                if (typeof window.openPaymentRequestModal === 'function') {
                    window.openPaymentRequestModal(contribution);
                } else {
                    console.error('openPaymentRequestModal function not available');
                }
            };
        } else {
            requestPaymentBtn.style.display = 'none';
        }
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
                            <i class="fas fa-qrcode"></i> View Receipt
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
                
                // Don't hide payment history modal - keep it open
                // Use the global showQRReceipt function from modal-qr-receipt.php
                if (typeof window.showQRReceipt === 'function') {
                    window.showQRReceipt(paymentData);
                } else {
                    console.error('showQRReceipt function not available');
                    alert('QR receipt functionality not available. Please refresh the page.');
                }
            });
        });
    }
    
    // Handle payment group clicks
    $(document).on('click', '.payment-group-item', function() {
        const contributionId = $(this).data('contribution-id');
        const paymentSequence = $(this).data('payment-sequence');
        const groupData = $(this).data('group-data');
        
        // Show payment progress modal for this group
        showPaymentProgressModal(contributionId, paymentSequence, groupData);
    });
    
    // Function to show payment progress modal
    function showPaymentProgressModal(contributionId, paymentSequence, groupData) {
        // Fetch individual payments for this group
        fetch(`<?= base_url('payer/get-contribution-payments') ?>/${contributionId}?sequence=${paymentSequence}`, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Create and show payment progress modal
                const modalHtml = `
                    <div class="modal fade" id="paymentProgressModal" tabindex="-1" aria-labelledby="paymentProgressModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="paymentProgressModalLabel">
                                        <i class="fas fa-file-invoice-dollar me-2"></i>${groupData.contribution_title || 'Payment Group'}
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="row mb-4">
                                        <div class="col-md-3">
                                            <div class="card bg-primary text-white">
                                                <div class="card-body text-center">
                                                    <h6 class="card-title">Total Amount</h6>
                                                    <h4>₱${parseFloat(groupData.contribution_amount || 0).toFixed(2)}</h4>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="card bg-success text-white">
                                                <div class="card-body text-center">
                                                    <h6 class="card-title">Amount Paid</h6>
                                                    <h4>₱${parseFloat(groupData.total_paid || 0).toFixed(2)}</h4>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="card bg-danger text-white">
                                                <div class="card-body text-center">
                                                    <h6 class="card-title">Remaining Balance</h6>
                                                    <h4>₱${parseFloat(groupData.remaining_balance || 0).toFixed(2)}</h4>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="card bg-info text-white">
                                                <div class="card-body text-center">
                                                    <h6 class="card-title">Payment Status</h6>
                                                    <h4><span class="badge bg-${groupData.computed_status === 'fully paid' ? 'success' : 'warning'}">${groupData.computed_status === 'fully paid' ? 'Fully paid' : 'Partial'}</span></h4>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <h6>Payment Progress</h6>
                                        <div class="progress" style="height: 25px;">
                                            <div class="progress-bar ${groupData.computed_status === 'fully paid' ? 'bg-success' : 'bg-warning'}" 
                                                 role="progressbar" 
                                                 style="width: ${Math.min(100, (groupData.total_paid / groupData.contribution_amount) * 100)}%"
                                                 aria-valuenow="${(groupData.total_paid / groupData.contribution_amount) * 100}" 
                                                 aria-valuemin="0" 
                                                 aria-valuemax="100">
                                                ${Math.round((groupData.total_paid / groupData.contribution_amount) * 100)}%
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-8">
                                            <h6>Individual Payments</h6>
                                            <div class="table-responsive">
                                                <table class="table table-striped">
                                                    <thead>
                                                        <tr>
                                                            <th>Date</th>
                                                            <th>Amount</th>
                                                            <th>Method</th>
                                                            <th>Status</th>
                                                            <th>Receipt</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        ${data.payments.map(payment => `
                                                            <tr>
                                                                <td>${new Date(payment.payment_date).toLocaleDateString()}</td>
                                                                <td><strong>₱${parseFloat(payment.amount_paid).toFixed(2)}</strong></td>
                                                                <td>${payment.payment_method.charAt(0).toUpperCase() + payment.payment_method.slice(1)}</td>
                                                                <td><span class="badge bg-${payment.payment_status === 'fully paid' ? 'success' : 'warning'}">${payment.payment_status.charAt(0).toUpperCase() + payment.payment_status.slice(1)}</span></td>
                                                                <td>
                                                                    <button class="btn btn-sm btn-outline-primary view-receipt-btn" data-payment='${JSON.stringify(payment)}'>
                                                                        <i class="fas fa-qrcode"></i>
                                                                    </button>
                                                                </td>
                                                            </tr>
                                                        `).join('')}
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <h6>Contribution Info</h6>
                                            <div class="card">
                                                <div class="card-body">
                                                    <p><strong>Category:</strong> ${groupData.contribution_category || 'other'}</p>
                                                    <p><strong>Created:</strong> ${new Date(groupData.contribution_created_at || new Date()).toLocaleDateString()}</p>
                                                    <p><strong>Status:</strong> <span class="badge bg-success">Active</span></p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    <button type="button" class="btn btn-primary" onclick="viewPaymentHistory(${contributionId})">
                                        <i class="fas fa-history me-2"></i>View Payment History
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                
                // Remove existing modal if any
                $('#paymentProgressModal').remove();
                
                // Add modal to body
                $('body').append(modalHtml);
                
                // Show modal
                $('#paymentProgressModal').modal('show');
                
                // Add event listeners for receipt buttons
                $('#paymentProgressModal').on('click', '.view-receipt-btn', function() {
                    const paymentData = JSON.parse($(this).attr('data-payment'));
                    if (typeof window.showQRReceipt === 'function') {
                        window.showQRReceipt(paymentData);
                    }
                });
                
                // Clean up modal when hidden
                $('#paymentProgressModal').on('hidden.bs.modal', function() {
                    $(this).remove();
                });
                
            } else {
                alert('Error loading payment details: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while loading payment details.');
        });
    }
    
    // QR Receipt functionality now uses the global showQRReceipt function from modal-qr-receipt.php
    
});
</script>

<!-- Include Payment Request Modal -->
<?= $this->include('partials/modal-payment-request') ?>

<?= $this->endSection() ?>
