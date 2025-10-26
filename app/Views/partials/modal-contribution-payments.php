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
                        Click any payment to view QR receipt
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
                                    <th>Amount Paid</th>
                                    <th>Payment Date</th>
                                    <th>Status</th>
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
</style>

<script>
// Function to show contribution payments modal
function showContributionPayments(contributionId, contributionTitle, contributionAmount) {
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
                document.getElementById('contributionPaymentsContent').style.display = 'block';
                
                const tbody = document.getElementById('contributionPaymentsTableBody');
                tbody.innerHTML = '';
                
                data.payments.forEach(payment => {
                    const row = document.createElement('tr');
                    row.className = 'contribution-payment-row';
                    row.setAttribute('data-payment-id', payment.id);
                    
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
                    } else if (payment.payment_status === 'partial') {
                        statusBadge = '<span class="badge bg-warning">PARTIAL</span>';
                    } else {
                        statusBadge = '<span class="badge bg-danger">PENDING</span>';
                    }
                    
                    row.innerHTML = `
                        <td>${payment.payer_student_id || payment.payer_id || 'N/A'}</td>
                        <td>${payment.payer_name || 'N/A'}</td>
                        <td class="fw-semibold">₱${parseFloat(payment.amount_paid).toFixed(2)}</td>
                        <td>${formattedDate}</td>
                        <td>${statusBadge}</td>
                    `;
                    
                    // Add click event to show QR receipt
                    row.addEventListener('click', function() {
                        // Show QR receipt
                        if (typeof showQRReceipt === 'function') {
                            showQRReceipt(payment);
                        } else {
                            console.error('showQRReceipt function not found');
                            showNotification('QR Receipt modal not available', 'danger');
                        }
                    });
                    
                    tbody.appendChild(row);
                });
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
</script>
