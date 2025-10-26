<!-- Contribution Payments Modal -->
<div class="modal fade" id="contributionPaymentsModal" tabindex="-1" aria-labelledby="contributionPaymentsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <div>
                    <h5 class="modal-title" id="contributionPaymentsModalLabel">
                        <i class="fas fa-hand-holding-usd me-2"></i>
                        <span id="contributionModalTitle">Contribution Payments</span>
                    </h5>
                    <small class="text-white-50">
                        <i class="fas fa-mouse-pointer me-1"></i>
                        Click any payer to view payment history
                    </small>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <!-- Loading State -->
                <div id="contributionPaymentsLoading" class="text-center py-5" style="display: none;">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="text-muted mt-2">Loading payments...</p>
                </div>

                <!-- Payments List -->
                <div id="contributionPaymentsContent">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle" id="contributionPaymentsTable">
                            <thead class="table-light">
                                <tr>
                                    <th>Payer ID</th>
                                    <th>Payer Name</th>
                                    <th>Total Paid</th>
                                    <th>Status</th>
                                    <th>Last Payment</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="contributionPaymentsTableBody">
                                <!-- Payments will be loaded here via JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Empty State -->
                <div id="contributionPaymentsEmpty" class="text-center py-5" style="display: none;">
                    <div class="mb-3">
                        <i class="fas fa-inbox text-muted" style="font-size: 3rem;"></i>
                    </div>
                    <h5 class="text-muted">No payments found</h5>
                    <p class="text-muted">No payments have been recorded for this contribution yet.</p>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Payment History Modal for Individual Payer -->
<div class="modal fade" id="payerPaymentHistoryModal" tabindex="-1" aria-labelledby="payerPaymentHistoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="payerPaymentHistoryModalLabel">
                    <i class="fas fa-history me-2"></i>
                    <span id="payerHistoryTitle">Payment History</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <!-- Loading State -->
                <div id="payerHistoryLoading" class="text-center py-5" style="display: none;">
                    <div class="spinner-border text-info" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="text-muted mt-2">Loading payment history...</p>
                </div>

                <!-- History Content -->
                <div id="payerHistoryContent" style="display: none;">
                    <div class="list-group" id="payerHistoryList">
                        <!-- Payment history items will be loaded here -->
                    </div>
                </div>

                <!-- Empty State -->
                <div id="payerHistoryEmpty" class="text-center py-5" style="display: none;">
                    <div class="mb-3">
                        <i class="fas fa-inbox text-muted" style="font-size: 3rem;"></i>
                    </div>
                    <h5 class="text-muted">No payment history</h5>
                    <p class="text-muted">No payment transactions recorded.</p>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Close
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.contribution-payment-row {
    cursor: pointer;
    transition: background-color 0.2s ease;
}

.contribution-payment-row:hover {
    background-color: #f0f9ff !important;
}

.contribution-payment-row:active {
    background-color: #dbeafe !important;
}

.payment-history-item {
    cursor: pointer;
    transition: all 0.2s ease;
}

.payment-history-item:hover {
    background-color: #f8f9fa !important;
    transform: translateX(5px);
}
</style>

<script>
// Store contribution and payer data (make them global)
window.currentContributionId = null;
window.currentContributionData = {};

// Function to show contribution payments modal with aggregated data
function showContributionPayments(contributionId, contributionTitle, contributionAmount) {
    window.currentContributionId = contributionId;
    window.currentContributionData = {
        id: contributionId,
        title: contributionTitle,
        amount: contributionAmount
    };
    
    // Update modal title
    document.getElementById('contributionModalTitle').textContent = contributionTitle;
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('contributionPaymentsModal'));
    modal.show();
    
    // Reset states
    document.getElementById('contributionPaymentsLoading').style.display = 'block';
    document.getElementById('contributionPaymentsContent').style.display = 'none';
    document.getElementById('contributionPaymentsEmpty').style.display = 'none';
    
    // Clear previous data
    document.getElementById('contributionPaymentsTableBody').innerHTML = '';
    
    // Fetch payments for this contribution
    fetch(`${window.APP_BASE_URL || ''}/payments/by-contribution/${contributionId}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('contributionPaymentsLoading').style.display = 'none';
            
            if (data.success && data.payments && data.payments.length > 0) {
                // Aggregate payments by payer
                const payerMap = {};
                
                data.payments.forEach(payment => {
                    const payerId = payment.payer_id;
                    
                    if (!payerMap[payerId]) {
                        payerMap[payerId] = {
                            payer_id: payerId,
                            payer_student_id: payment.payer_student_id || payment.payer_id || 'N/A',
                            payer_name: payment.payer_name,
                            total_paid: 0,
                            status: 'fully paid',
                            last_payment_date: null,
                            payments: [],
                            contribution_amount: null
                        };
                    }
                    
                    payerMap[payerId].total_paid += parseFloat(payment.amount_paid);
                    payerMap[payerId].payments.push(payment);
                    
                    // Store contribution amount from the first payment
                    if (!payerMap[payerId].contribution_amount && payment.contribution_amount) {
                        payerMap[payerId].contribution_amount = parseFloat(payment.contribution_amount);
                    }
                   
                    // Track latest payment date
                    const paymentDate = new Date(payment.payment_date);
                    if (!payerMap[payerId].last_payment_date || paymentDate > new Date(payerMap[payerId].last_payment_date)) {
                        payerMap[payerId].last_payment_date = payment.payment_date;
                    }
                });
                
                // Determine final status for each payer based on totals
                Object.values(payerMap).forEach(payerData => {
                    // If we have contribution amount, check if fully paid
                    if (payerData.contribution_amount) {
                        if (payerData.total_paid >= payerData.contribution_amount) {
                            payerData.status = 'fully paid';
                        } else {
                            payerData.status = 'partial';
                        }
                    } else {
                        // Fallback: if no contribution amount, check if any payment is partial
                        const hasPartialPayment = payerData.payments.some(p => p.payment_status === 'partial');
                        if (hasPartialPayment) {
                            payerData.status = 'partial';
                        } else {
                            payerData.status = 'fully paid';
                        }
                    }
                });
                
                // Display aggregated data
                const tbody = document.getElementById('contributionPaymentsTableBody');
                tbody.innerHTML = '';
                
                Object.values(payerMap).forEach(payerData => {
                    const row = document.createElement('tr');
                    row.className = 'contribution-payment-row';
                    row.setAttribute('data-payer-id', payerData.payer_id);
                    row.setAttribute('data-payer-data', JSON.stringify(payerData));
                    
                    // Format last payment date
                    let lastPaymentDate = 'N/A';
                    if (payerData.last_payment_date) {
                        const date = new Date(payerData.last_payment_date);
                        lastPaymentDate = date.toLocaleDateString('en-US', { 
                            year: 'numeric', 
                            month: 'short', 
                            day: 'numeric'
                        });
                    }
                    
                    // Status badge
                    let statusBadge = '';
                    if (payerData.status === 'fully paid') {
                        statusBadge = '<span class="badge bg-success">FULLY PAID</span>';
                    } else {
                        statusBadge = '<span class="badge bg-warning">PARTIAL</span>';
                    }
                    
                    // Actions column
                    let actionsHTML = '';
                    if (payerData.status === 'partial' && typeof openAddPaymentToPartialModal === 'function') {
                        // Find a partial payment to pass to the modal
                        const partialPayment = payerData.payments.find(p => p.payment_status === 'partial');
                        if (partialPayment) {
                            // Store payment data in a data attribute instead of inline
                            const paymentDataId = `payment-${partialPayment.id}`;
                            window[paymentDataId] = partialPayment;
                            
                            actionsHTML = `<button class="btn btn-sm btn-info" onclick="event.stopPropagation(); openAddPaymentToPartialModal(window['${paymentDataId}'])">
                                <i class="fas fa-plus me-1"></i>Add Payment
                            </button>`;
                        }
                    }
                    
                    row.innerHTML = `
                        <td>${payerData.payer_student_id}</td>
                        <td>${payerData.payer_name}</td>
                        <td class="fw-semibold">₱${payerData.total_paid.toFixed(2)}</td>
                        <td>${statusBadge}</td>
                        <td>${lastPaymentDate}</td>
                        <td onclick="event.stopPropagation();">${actionsHTML}</td>
                    `;
                    
                    // Add click event to show payment history
                    row.addEventListener('click', function(e) {
                        // Don't trigger if clicking on action buttons
                        if (e.target.closest('button')) {
                            return;
                        }
                        showPayerPaymentHistory(payerData);
                    });
                    
                    tbody.appendChild(row);
                });
                
                document.getElementById('contributionPaymentsContent').style.display = 'block';
            } else {
                document.getElementById('contributionPaymentsEmpty').style.display = 'block';
            }
        })
        .catch(error => {
            console.error('Error fetching contribution payments:', error);
            document.getElementById('contributionPaymentsLoading').style.display = 'none';
            document.getElementById('contributionPaymentsEmpty').style.display = 'block';
            showNotification('Error loading payments', 'danger');
        });
}

// Function to show payment history for a specific payer
function showPayerPaymentHistory(payerData) {
    // Update modal title
    document.getElementById('payerHistoryTitle').textContent = `Payment History - ${payerData.payer_name}`;
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('payerPaymentHistoryModal'));
    modal.show();
    
    // Reset states
    document.getElementById('payerHistoryLoading').style.display = 'block';
    document.getElementById('payerHistoryContent').style.display = 'none';
    document.getElementById('payerHistoryEmpty').style.display = 'none';
    
    // Clear previous data
    document.getElementById('payerHistoryList').innerHTML = '';
    
    // Hide loading and show content
    setTimeout(() => {
        document.getElementById('payerHistoryLoading').style.display = 'none';
        
        if (payerData.payments && payerData.payments.length > 0) {
            // Sort payments by date (newest first)
            const sortedPayments = [...payerData.payments].sort((a, b) => 
                new Date(b.payment_date) - new Date(a.payment_date)
            );
            
            const list = document.getElementById('payerHistoryList');
            
            sortedPayments.forEach(payment => {
                const item = document.createElement('div');
                item.className = 'list-group-item payment-history-item';
                
                // Format payment date
                const paymentDate = new Date(payment.payment_date);
                const formattedDate = paymentDate.toLocaleDateString('en-US', { 
                    year: 'numeric', 
                    month: 'short', 
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });
                
                // Status badge
                let statusBadge = '';
                if (payment.payment_status === 'fully paid') {
                    statusBadge = '<span class="badge bg-success">FULLY PAID</span>';
                } else {
                    statusBadge = '<span class="badge bg-warning">PARTIAL</span>';
                }
                
                item.innerHTML = `
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="mb-1">
                                <i class="fas fa-money-bill-wave me-2 text-primary"></i>
                                ₱${parseFloat(payment.amount_paid).toFixed(2)}
                            </h6>
                            <p class="mb-1 text-muted small">
                                <i class="fas fa-calendar me-1"></i>${formattedDate}
                            </p>
                            <p class="mb-0 small">
                                <i class="fas fa-credit-card me-1"></i>${payment.payment_method.toUpperCase()}
                                ${payment.remaining_balance > 0 ? ` • Remaining: ₱${parseFloat(payment.remaining_balance).toFixed(2)}` : ''}
                            </p>
                        </div>
                        <div class="text-end">
                            ${statusBadge}
                        </div>
                    </div>
                `;
                
                // Add click event to show QR receipt
                item.addEventListener('click', function() {
                    if (typeof showQRReceipt === 'function') {
                        showQRReceipt(payment);
                    } else {
                        showNotification('QR Receipt modal is loading', 'warning');
                    }
                });
                
                list.appendChild(item);
            });
            
            document.getElementById('payerHistoryContent').style.display = 'block';
        } else {
            document.getElementById('payerHistoryEmpty').style.display = 'block';
        }
    }, 300);
}
</script>
