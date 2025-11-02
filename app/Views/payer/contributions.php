<?= $this->extend('layouts/payer-layout') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-hand-holding-usd me-2"></i>Contributions</h5>
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
                            <h5 class="text-muted">No Contributions</h5>
                            <p class="text-muted">There are currently no contributions available</p>
                        </div>
                    <?php else: ?>
                        <div class="row">
                            <?php foreach ($contributions as $contribution): ?>
                                <div class="col-lg-6 mb-3">
                                    <div class="card contribution-card h-100" 
                                         data-contribution='<?= json_encode($contribution) ?>'>
                                        <div class="card-body">
                                                   <div class="d-flex justify-content-between align-items-start mb-2">
                                                       <div class="d-flex align-items-center">
                                                           <h6 class="card-title mb-0">
                                                               <i class="fas fa-file-invoice-dollar text-primary me-2"></i>
                                                               <?= esc($contribution['title']) ?>
                                                           </h6>
                                                           <?php if ($contribution['status'] === 'inactive'): ?>
                                                               <span class="badge bg-secondary ms-2" title="This contribution is inactive">
                                                                   <i class="fas fa-ban me-1"></i>Inactive
                                                               </span>
                                                           <?php endif; ?>
                                                       </div>
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
                                                    <?php if (empty($contribution['payment_groups']) || count($contribution['payment_groups']) <= 1): ?>
                                                        <!-- Only show Total Paid if no groups or single group exists -->
                                                        <small class="text-muted">Total Paid</small>
                                                        <div class="fw-bold text-success">₱<?= number_format($contribution['total_paid'], 2) ?></div>
                                                    <?php else: ?>
                                                        <!-- Show groups count when multiple groups exist -->
                                                        <small class="text-muted">Payment Groups</small>
                                                        <div class="fw-bold text-info"><?= count($contribution['payment_groups']) ?> Group<?= count($contribution['payment_groups']) > 1 ? 's' : '' ?></div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            
                                            <!-- Payment Groups Section -->
                                            <?php if (!empty($contribution['payment_groups'])): ?>
                                                <div class="mb-2">
                                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                                        <small class="text-muted fw-semibold">Payment Groups:</small>
                                                        <button class="btn btn-sm btn-outline-primary" 
                                                                onclick="togglePaymentGroups(<?= $contribution['id'] ?>)"
                                                                id="toggleBtn-<?= $contribution['id'] ?>">
                                                            <i class="fas fa-chevron-down me-1"></i>Show Groups
                                                        </button>
                                                    </div>
                                                    <div class="payment-groups-container" 
                                                         id="paymentGroups-<?= $contribution['id'] ?>" 
                                                         style="display: none;">
                                                        <?php foreach ($contribution['payment_groups'] as $group): ?>
                                                            <div class="payment-group-item mb-1 p-2 border rounded" 
                                                                 style="background-color: #f8f9fa;"
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
                                                                                <span class="badge bg-warning text-dark ms-1">Partial</span>
                                                                            <?php endif; ?>
                                                                        </small>
                                                                        <?php if (isset($group['refund_status']) && $group['refund_status'] !== 'no_refund'): ?>
                                                                            <br>
                                                                            <?php
                                                                            $refundStatus = $group['refund_status'];
                                                                            $refundBadgeClass = 'bg-secondary';
                                                                            $refundBadgeText = 'NO REFUND';
                                                                            
                                                                            if ($refundStatus === 'fully_refunded') {
                                                                                $refundBadgeClass = 'bg-danger';
                                                                                $refundBadgeText = 'FULLY REFUNDED';
                                                                            } elseif ($refundStatus === 'partially_refunded') {
                                                                                $refundBadgeClass = 'bg-warning text-dark';
                                                                                $refundBadgeText = 'PARTIALLY REFUNDED';
                                                                            }
                                                                            ?>
                                                                            <span class="badge <?= $refundBadgeClass ?> small mt-1">
                                                                                <?= $refundBadgeText ?>
                                                                                <?php if (isset($group['total_refunded']) && $group['total_refunded'] > 0): ?>
                                                                                    (₱<?= number_format($group['total_refunded'], 2) ?>)
                                                                                <?php endif; ?>
                                                                            </span>
                                                                        <?php endif; ?>
                                                                    </div>
                                                                    <div class="text-end">
                                                                        <small class="fw-bold text-success">₱<?= number_format($group['total_paid'], 2) ?></small>
                                                                        <?php if ($group['computed_status'] !== 'fully paid'): ?>
                                                                            <br><small class="text-muted">Remaining: ₱<?= number_format($group['remaining_balance'], 2) ?></small>
                                                                        <?php endif; ?>
                                                                    </div>
                                                                </div>
                                                                <?php 
    $groupAmount = isset($group['amount']) ? $group['amount'] : (isset($contribution['amount']) ? $contribution['amount'] : 0);
    $groupProgress = ($groupAmount > 0 && $group['total_paid'] >= $groupAmount) ? 100 : ($groupAmount > 0 ? round(($group['total_paid'] / $groupAmount) * 100, 1) : 0);
?>
<div class="mt-1">
    <div class="d-flex justify-content-between align-items-center">
        <small class="text-muted">Progress</small>
        <small class="text-muted progress-percentage"><?= $groupProgress ?>%</small>
    </div>
    <div class="progress" style="height: 6px;">
        <div class="progress-bar <?= $group['computed_status'] === 'fully paid' ? 'bg-success' : 'bg-warning' ?>"
             role="progressbar"
             style="width: <?= $groupProgress ?>%">
        </div>
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
                                            
                                            <!-- Show Add Payment if no payment groups OR all payment groups are fully paid -->
                                            <!-- BUT disable if contribution is inactive -->
                                            <?php
                                            $showAddPaymentBtn = false;
                                            $isInactive = ($contribution['status'] === 'inactive');
                                            
                                            if (!$isInactive) {
                                                // Only show add payment button for active contributions
                                                if (empty($contribution['payment_groups'])) {
                                                    $showAddPaymentBtn = true;
                                                } else {
                                                    $allGroupsFullyPaid = true;
                                                    foreach ($contribution['payment_groups'] as $group) {
                                                        if ($group['computed_status'] !== 'fully paid') {
                                                            $allGroupsFullyPaid = false;
                                                            break;
                                                        }
                                                    }
                                                    if ($allGroupsFullyPaid) {
                                                        $showAddPaymentBtn = true;
                                                    }
                                                }
                                            }
                                            ?>
                                            <?php if ($showAddPaymentBtn): ?>
                                                <div class="mt-3">
                                                    <button class="btn btn-primary btn-sm w-100 add-payment-btn" 
                                                            data-contribution='<?= json_encode($contribution) ?>'
                                                            onclick="event.stopPropagation();">
                                                        <i class="fas fa-plus me-2"></i>Add Payment
                                                    </button>
                                                </div>
                                            <?php elseif ($isInactive): ?>
                                                <div class="mt-3">
                                                    <button class="btn btn-secondary btn-sm w-100" 
                                                            disabled
                                                            title="This contribution is inactive. You cannot add new payments, but you can view your existing payments and request refunds.">
                                                        <i class="fas fa-ban me-2"></i>Add Payment (Disabled)
                                                    </button>
                                                    <small class="text-muted d-block mt-1 text-center">
                                                        <i class="fas fa-info-circle me-1"></i>Contribution is inactive. You can still view payments and request refunds.
                                                    </small>
                                                </div>
                                            <?php endif; ?>
                                            
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
           
           // Function to toggle payment groups visibility
           window.togglePaymentGroups = function(contributionId) {
               const container = document.getElementById(`paymentGroups-${contributionId}`);
               const toggleBtn = document.getElementById(`toggleBtn-${contributionId}`);
               const icon = toggleBtn.querySelector('i');
               
               if (container.style.display === 'none') {
                   // Show groups
                   container.style.display = 'block';
                   icon.className = 'fas fa-chevron-up me-1';
                   toggleBtn.innerHTML = '<i class="fas fa-chevron-up me-1"></i>Hide Groups';
               } else {
                   // Hide groups
                   container.style.display = 'none';
                   icon.className = 'fas fa-chevron-down me-1';
                   toggleBtn.innerHTML = '<i class="fas fa-chevron-down me-1"></i>Show Groups';
               }
           };
           
           // Add click event listeners to payment group items
           document.addEventListener('click', function(e) {
               const groupItem = e.target.closest('.payment-group-item');
               if (groupItem) {
                   e.stopPropagation(); // Prevent triggering parent card click
                   
                   const contributionId = groupItem.getAttribute('data-contribution-id');
                   const paymentSequence = groupItem.getAttribute('data-payment-sequence');
                   const groupDataStr = groupItem.getAttribute('data-group-data');
                   
                   if (contributionId && paymentSequence && groupDataStr) {
                       try {
                           const groupData = JSON.parse(groupDataStr);
                           // Show payment progress modal when clicking on a group
                           showPaymentProgressModal(contributionId, paymentSequence, groupData);
                       } catch (error) {
                           console.error('Error parsing group data:', error);
                       }
                   }
               }
           });
           
           // Make payment group items visually clickable
           const style = document.createElement('style');
           style.textContent = `
               .payment-group-item {
                   cursor: pointer;
                   transition: all 0.2s ease;
               }
               .payment-group-item:hover {
                   background-color: #e9ecef !important;
                   transform: translateX(2px);
                   box-shadow: 0 2px 4px rgba(0,0,0,0.1);
               }
           `;
           document.head.appendChild(style);
           
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
    
    // Contribution card click - removed to prevent modal from opening
    // Now using collapsible payment groups instead
    
    // View payment history button
    document.getElementById('viewPaymentHistoryBtn').addEventListener('click', function() {
        window.location.href = '<?= base_url('payer/payment-history') ?>';
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
    
    
    
    // Function to open payment request modal for a specific payment group
    function openPaymentRequestForGroup(contributionId, paymentSequence, groupData) {
        // First fetch the contribution details to get the correct data
        fetch(`<?= base_url('payer/get-contribution-payments') ?>/${contributionId}?sequence=${paymentSequence}`, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.payments.length > 0) {
                const firstPayment = data.payments[0];
                
                // Create a contribution object with the correct data
                const contributionData = {
                    id: contributionId,
                    title: firstPayment.contribution_title || 'Contribution',
                    amount: parseFloat(firstPayment.contribution_amount || 0),
                    description: firstPayment.contribution_description || '',
                    remaining_balance: groupData.remaining_balance || 0,
                    payment_sequence: paymentSequence
                };
                
                // Pre-fill the payment request modal with correct data
                if (typeof window.openPaymentRequestModal === 'function') {
                    window.openPaymentRequestModal(contributionData);
                } else {
                    console.error('openPaymentRequestModal function not available');
                    alert('Payment request functionality not available. Please refresh the page.');
                }
            } else {
                alert('Error loading contribution details');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while loading contribution details.');
        });
    }    
    
    // Handle Add Payment button clicks (for contributions with no payment groups)
    document.addEventListener('click', function(e) {
        const addPaymentBtn = e.target.closest('.add-payment-btn');
        if (addPaymentBtn) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation(); // Prevent other event listeners from firing
            
            const contributionData = JSON.parse(addPaymentBtn.getAttribute('data-contribution'));
            
            // Since there are no payment groups, open payment request modal directly
            // This will create the first payment group
            if (typeof window.openPaymentRequestModal === 'function') {
                window.openPaymentRequestModal(contributionData);
            } else {
                console.error('openPaymentRequestModal function not available');
                alert('Payment request functionality not available. Please refresh the page.');
            }
        }
    }, true); // Use capture phase to catch events early
    
    // Function to show payment group selection modal
    function showPaymentGroupSelectionModal(contributionData) {
        const groups = contributionData.payment_groups || [];
        const partialGroups = groups.filter(g => g.computed_status !== 'fully paid');
        
        let groupsHtml = '';
        if (partialGroups.length > 0) {
            groupsHtml = partialGroups.map((group, index) => {
                const groupId = `group-${contributionData.id}-${group.payment_sequence}-${index}`;
                // Store group data in window object to avoid JSON escaping issues
                window[groupId] = {
                    group: group,
                    contribution: contributionData,
                    contributionId: contributionData.id
                };
                
                return `
                    <div class="card mb-2 payment-group-select-item" 
                         style="cursor: pointer; transition: all 0.2s;"
                         data-group-id="${groupId}">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-0">Group ${group.payment_sequence} 
                                        <span class="badge bg-warning ms-1">Partial</span>
                                    </h6>
                                    <small class="text-muted">
                                        ${group.payment_count} payment${group.payment_count > 1 ? 's' : ''} • 
                                        Remaining: ₱${parseFloat(group.remaining_balance || 0).toFixed(2)}
                                    </small>
                                </div>
                                <div class="text-end">
                                    <div class="fw-bold text-success">₱${parseFloat(group.total_paid || 0).toFixed(2)}</div>
                                    <small class="text-muted">Paid</small>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            }).join('');
        } else {
            groupsHtml = '<p class="text-muted">All payment groups are fully paid.</p>';
        }
        
        const contributionId = contributionData.id;
        const contributionDataId = `contribution-${contributionId}`;
        window[contributionDataId] = contributionData;
        
        // Determine if we should allow creating a new group (only if all groups are fully paid)
        const canCreateNewGroup = partialGroups.length === 0;
        
        const modalHtml = `
            <div class="modal fade" id="paymentGroupSelectionModal" tabindex="-1" aria-labelledby="paymentGroupSelectionModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title" id="paymentGroupSelectionModalLabel">
                                <i class="fas fa-folder-open me-2"></i>Select Payment Group - ${contributionData.title}
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                ${partialGroups.length > 0 
                                    ? '<strong>Complete your partial payment groups first</strong> before creating a new group.'
                                    : '<strong>Select a payment group</strong> to add a payment, or create a new group for this contribution.'
                                }
                            </div>
                            
                            ${partialGroups.length > 0 ? `
                                <h6 class="mb-3">Existing Partial Groups:</h6>
                                <div id="existingGroupsContainer">
                                    ${groupsHtml}
                                </div>
                            ` : `
                                <div class="alert alert-success mb-3">
                                    <i class="fas fa-check-circle me-2"></i>
                                    All existing payment groups are completed. You can now create a new payment group.
                                </div>
                                <div id="existingGroupsContainer">
                                    ${groupsHtml}
                                </div>
                            `}
                            
                            ${canCreateNewGroup ? `
                                <hr class="my-4">
                                
                                <div class="text-center">
                                    <button type="button" class="btn btn-outline-primary btn-lg" 
                                            data-contribution-id="${contributionDataId}">
                                        <i class="fas fa-plus-circle me-2"></i>Create New Payment Group
                                    </button>
                                </div>
                                
                                <div class="alert alert-warning mt-3">
                                    <i class="fas fa-lightbulb me-2"></i>
                                    <strong>Note:</strong> Each payment group is independent and tracked separately. 
                                    Creating a new group allows you to make payments for the same contribution type again.
                                </div>
                            ` : `
                                <div class="alert alert-warning mt-3">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <strong>You cannot create a new payment group</strong> while you have partial payment groups. 
                                    Please complete or pay off the remaining balance in your existing partial groups first.
                                </div>
                            `}
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // Remove existing modal if any
        const existingModal = document.getElementById('paymentGroupSelectionModal');
        if (existingModal) {
            existingModal.remove();
        }
        
        // Add modal to body
        document.body.insertAdjacentHTML('beforeend', modalHtml);
        
        // Add click handlers for group selection
        document.querySelectorAll('.payment-group-select-item').forEach(item => {
            item.addEventListener('click', function() {
                const groupId = this.getAttribute('data-group-id');
                if (window[groupId]) {
                    const { group, contribution, contributionId } = window[groupId];
                    selectPaymentGroup(contributionId, group.payment_sequence, group, contribution);
                }
            });
            
            item.addEventListener('mouseenter', function() {
                this.style.backgroundColor = '#f8f9fa';
                this.style.transform = 'translateX(5px)';
            });
            item.addEventListener('mouseleave', function() {
                this.style.backgroundColor = '';
                this.style.transform = '';
            });
        });
        
        // Add click handler for create new group button (only if allowed)
        if (canCreateNewGroup) {
            const createBtn = document.querySelector(`[data-contribution-id="${contributionDataId}"]`);
            if (createBtn) {
                createBtn.addEventListener('click', function() {
                    createNewPaymentGroup(contributionId, contributionData);
                });
            }
        }
        
        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('paymentGroupSelectionModal'));
        modal.show();
        
        // Clean up modal when hidden
        document.getElementById('paymentGroupSelectionModal').addEventListener('hidden.bs.modal', function() {
            // Clean up stored data
            partialGroups.forEach((group, index) => {
                const groupId = `group-${contributionId}-${group.payment_sequence}-${index}`;
                delete window[groupId];
            });
            delete window[contributionDataId];
            this.remove();
        });
    }
    
    // Function to select a payment group
    window.selectPaymentGroup = function(contributionId, paymentSequence, groupData, contributionData) {
        // Close selection modal
        const modal = bootstrap.Modal.getInstance(document.getElementById('paymentGroupSelectionModal'));
        if (modal) {
            modal.hide();
        }
        
        // Prepare contribution data with payment sequence
        const modifiedContributionData = {
            ...contributionData,
            payment_sequence: paymentSequence,
            remaining_balance: groupData.remaining_balance || 0,
            total_paid: groupData.total_paid || 0
        };
        
        // Open payment request modal for this specific group
        if (typeof window.openPaymentRequestModal === 'function') {
            window.openPaymentRequestModal(modifiedContributionData);
        } else {
            console.error('openPaymentRequestModal function not available');
            alert('Payment request functionality not available. Please refresh the page.');
        }
    };
    
    // Function to create new payment group
    window.createNewPaymentGroup = function(contributionId, contributionData) {
        // Close selection modal
        const modal = bootstrap.Modal.getInstance(document.getElementById('paymentGroupSelectionModal'));
        if (modal) {
            modal.hide();
        }
        
        // Prepare contribution data without payment sequence (will create new group)
        const modifiedContributionData = {
            ...contributionData,
            payment_sequence: null, // No sequence means new group
            remaining_balance: contributionData.amount || 0, // Reset to full amount for new group
            total_paid: 0 // Reset for new group
        };
        
        // Open payment request modal for new group
        if (typeof window.openPaymentRequestModal === 'function') {
            window.openPaymentRequestModal(modifiedContributionData);
        } else {
            console.error('openPaymentRequestModal function not available');
            alert('Payment request functionality not available. Please refresh the page.');
        }
    };
    
    // Function to open payment request modal for a contribution
    function openPaymentRequestForContribution(contributionData) {
        // Check if this contribution is fully paid
        const totalPaid = parseFloat(contributionData.total_paid || 0);
        const contributionAmount = parseFloat(contributionData.amount || 0);
        
        if (totalPaid >= contributionAmount) {
            // Contribution is fully paid - show confirmation modal
            showDuplicatePaymentConfirmation(contributionData);
        } else {
            // Contribution is not fully paid - open payment request modal directly
            if (typeof window.openPaymentRequestModal === 'function') {
                window.openPaymentRequestModal(contributionData);
            } else {
                console.error('openPaymentRequestModal function not available');
                alert('Payment request functionality not available. Please refresh the page.');
            }
        }
    }
    
    // Function to show duplicate payment confirmation modal
    function showDuplicatePaymentConfirmation(contributionData) {
        console.log('showDuplicatePaymentConfirmation called with:', contributionData);
        
        // Ensure we have valid data
        const amount = parseFloat(contributionData.amount) || 0;
        const totalPaid = parseFloat(contributionData.total_paid) || 0;
        
        console.log('Parsed values:', { amount, totalPaid });
        
        const modalHtml = `
            <div class="modal fade" id="duplicatePaymentModal" tabindex="-1" aria-labelledby="duplicatePaymentModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header bg-warning text-dark">
                            <h5 class="modal-title" id="duplicatePaymentModalLabel">
                                <i class="fas fa-exclamation-triangle me-2"></i>Contribution Already Fully Paid
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-info">
                                <h6 class="alert-heading"><i class="fas fa-info-circle me-2"></i>Contribution Details</h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <strong>Contribution:</strong> ${contributionData.title}<br>
                                        <strong>Description:</strong> ${contributionData.description || 'No description'}
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Total Amount:</strong> ₱${amount.toFixed(2)}<br>
                                        <strong>Total Paid:</strong> ₱${totalPaid.toFixed(2)}
                                    </div>
                                </div>
                            </div>
                            
                            <p class="mb-3">
                                <strong>⚠️ Contribution Already Fully Paid</strong><br>
                                You already have fully paid contribution groups for "${contributionData.title}" (₱${totalPaid.toFixed(2)} total).
                            </p>
                            
                            <p class="mb-3">
                                <strong>Add another payment group for this contribution?</strong><br>
                                This will create a new, separate payment group for the same contribution type.
                            </p>
                            
                            <div class="alert alert-warning">
                                <i class="fas fa-lightbulb me-2"></i>
                                <strong>Note:</strong> Each payment group is independent and tracked separately in your payment history.
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No, Cancel</button>
                            <button type="button" class="btn btn-primary confirm-duplicate-payment-btn" data-contribution='${JSON.stringify(contributionData)}'>
                                Yes, Add Another Payment Group
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // Remove existing modal if any
        const existingModal = document.getElementById('duplicatePaymentModal');
        if (existingModal) {
            existingModal.remove();
        }
        
        // Add modal to body
        document.body.insertAdjacentHTML('beforeend', modalHtml);
        
        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('duplicatePaymentModal'));
        modal.show();
        
        // Add event listener for confirm button
        document.getElementById('duplicatePaymentModal').addEventListener('click', function(e) {
            if (e.target.closest('.confirm-duplicate-payment-btn')) {
                const btn = e.target.closest('.confirm-duplicate-payment-btn');
                const contributionData = JSON.parse(btn.getAttribute('data-contribution'));
                
                // Close the confirmation modal
                modal.hide();
                
                // Open payment request modal for new payment group
                if (typeof window.openPaymentRequestModal === 'function') {
                    // Set remaining balance to the full contribution amount for new group
                    contributionData.remaining_balance = contributionData.amount;
                    window.openPaymentRequestModal(contributionData);
                } else {
                    console.error('openPaymentRequestModal function not available');
                    alert('Payment request functionality not available. Please refresh the page.');
                }
            }
        });
        
        // Clean up modal when hidden
        document.getElementById('duplicatePaymentModal').addEventListener('hidden.bs.modal', function() {
            this.remove();
        });
    }
    
    
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
            if (data.success && data.payments.length > 0) {
                // Get contribution data from the first payment
                const firstPayment = data.payments[0];
                const contributionAmount = parseFloat(firstPayment.contribution_amount || 0);
                const totalPaid = parseFloat(groupData.total_paid || 0);
                const remainingBalance = parseFloat(groupData.remaining_balance || 0);
                const progressPercentage = contributionAmount > 0 ? Math.round((totalPaid / contributionAmount) * 100) : 0;
                
                // Create and show payment progress modal
                const modalHtml = `
                    <div class="modal fade" id="paymentProgressModal" tabindex="-1" aria-labelledby="paymentProgressModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="paymentProgressModalLabel">
                                        <i class="fas fa-file-invoice-dollar me-2"></i>${firstPayment.contribution_title || 'Payment Group'} - Group ${paymentSequence}
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="row mb-4">
                                        <div class="col-md-3">
                                            <div class="card bg-primary text-white">
                                                <div class="card-body text-center">
                                                    <h6 class="card-title">Total Amount</h6>
                                                    <h4>₱${contributionAmount.toFixed(2)}</h4>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="card bg-success text-white">
                                                <div class="card-body text-center">
                                                    <h6 class="card-title">Amount Paid</h6>
                                                    <h4>₱${totalPaid.toFixed(2)}</h4>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="card bg-danger text-white">
                                                <div class="card-body text-center">
                                                    <h6 class="card-title">Remaining Balance</h6>
                                                    <h4>₱${remainingBalance.toFixed(2)}</h4>
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
                                                 style="width: ${Math.min(100, progressPercentage)}%"
                                                 aria-valuenow="${progressPercentage}" 
                                                 aria-valuemin="0" 
                                                 aria-valuemax="100">
                                                ${progressPercentage}%
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
                                                                <td><strong>₱${(parseFloat(payment.amount_paid) || 0).toFixed(2)}</strong></td>
                                                                <td>${payment.payment_method.charAt(0).toUpperCase() + payment.payment_method.slice(1)}</td>
                                                                <td><span class="badge bg-${groupData.computed_status === 'fully paid' ? 'success' : 'warning'}">${groupData.computed_status === 'fully paid' ? 'Completed' : 'Partial'}</span></td>
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
                                                    <p><strong>Category:</strong> ${firstPayment.contribution_category || 'other'}</p>
                                                    <p><strong>Created:</strong> ${new Date(firstPayment.contribution_created_at || new Date()).toLocaleDateString()}</p>
                                                    <p><strong>Status:</strong> <span class="badge bg-success">Active</span></p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    ${groupData.computed_status !== 'fully paid' ? `
                                        <button type="button" class="btn btn-success request-payment-btn" 
                                                data-contribution-id="${contributionId}"
                                                data-payment-sequence="${paymentSequence}"
                                                data-group-data='${JSON.stringify(groupData)}'>
                                            <i class="fas fa-paper-plane me-2"></i>Request Payment
                                        </button>
                                    ` : ''}
                                    <button type="button" class="btn btn-primary" onclick="viewPaymentHistory(${contributionId})">
                                        <i class="fas fa-history me-2"></i>View Payment History
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                
                // Remove existing modal if any
                const existingModal = document.getElementById('paymentProgressModal');
                if (existingModal) {
                    existingModal.remove();
                }
                
                // Add modal to body
                document.body.insertAdjacentHTML('beforeend', modalHtml);
                
                // Show modal
                const modal = new bootstrap.Modal(document.getElementById('paymentProgressModal'));
                modal.show();
                
                // Add event listeners for receipt buttons and request payment button
                document.getElementById('paymentProgressModal').addEventListener('click', function(e) {
                    if (e.target.closest('.view-receipt-btn')) {
                        const btn = e.target.closest('.view-receipt-btn');
                        const paymentData = JSON.parse(btn.getAttribute('data-payment'));
                        if (typeof window.showQRReceipt === 'function') {
                            window.showQRReceipt(paymentData);
                        }
                    }
                    
                    if (e.target.closest('.request-payment-btn')) {
                        const btn = e.target.closest('.request-payment-btn');
                        const contributionId = btn.getAttribute('data-contribution-id');
                        const paymentSequence = btn.getAttribute('data-payment-sequence');
                        const groupData = JSON.parse(btn.getAttribute('data-group-data'));
                        
                        // Close current modal
                        modal.hide();
                        
                        // Open payment request modal for this specific group
                        openPaymentRequestForGroup(contributionId, paymentSequence, groupData);
                    }
                });
                
                // Clean up modal when hidden
                document.getElementById('paymentProgressModal').addEventListener('hidden.bs.modal', function() {
                    this.remove();
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
    
    // Helper to redirect to the Payment History page from group modal
    window.viewPaymentHistory = function(contributionId) {
        window.location.href = '<?= base_url('payer/payment-history') ?>';
    };
    
});
</script>

<!-- Include Payment Request Modal -->
<?= $this->include('partials/modal-payment-request') ?>

<style>
/* Collapsible Payment Groups Styling */
.payment-groups-container {
    max-height: 300px;
    overflow-y: auto;
    border: 1px solid #e9ecef;
    border-radius: 0.5rem;
    padding: 0.5rem;
    background-color: #ffffff;
}

.payment-group-item {
    border: 1px solid #e9ecef !important;
}

.btn-outline-primary {
    border-color: #0d6efd;
    color: #0d6efd;
    transition: all 0.2s ease;
}

.btn-outline-primary:hover {
    background-color: #0d6efd;
    border-color: #0d6efd;
    color: white;
    transform: translateY(-1px);
}

/* Scrollbar styling for payment groups */
.payment-groups-container::-webkit-scrollbar {
    width: 6px;
}

.payment-groups-container::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

.payment-groups-container::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 3px;
}

.payment-groups-container::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .payment-groups-container {
        max-height: 250px;
    }
    
    .payment-group-item {
        padding: 0.75rem !important;
    }
}
</style>

<?= $this->endSection() ?>
