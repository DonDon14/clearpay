<?= $this->extend('layouts/payer-layout') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-undo me-2"></i>Refund Requests</h5>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#requestRefundModal">
                        <i class="fas fa-plus me-1"></i>Request Refund
                    </button>
                </div>
                <div class="card-body">
                    <?php if (empty($refundRequests)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                            <h5 class="text-muted">No Refund Requests</h5>
                            <p class="text-muted">You haven't submitted any refund requests yet.</p>
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#requestRefundModal">
                                <i class="fas fa-plus me-2"></i>Submit Your First Refund Request
                            </button>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Request Date</th>
                                        <th>Contribution</th>
                                        <th>Payment Receipt</th>
                                        <th>Refund Amount</th>
                                        <th>Refund Method</th>
                                        <th>Status</th>
                                        <th>Reference</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($refundRequests as $request): ?>
                                        <tr>
                                            <td>
                                                <small class="text-muted">
                                                    <?= date('M d, Y', strtotime($request['requested_at'])) ?><br>
                                                    <?= date('g:i A', strtotime($request['requested_at'])) ?>
                                                </small>
                                            </td>
                                            <td><?= esc($request['contribution_title'] ?? 'N/A') ?></td>
                                            <td><code><?= esc($request['receipt_number'] ?? 'N/A') ?></code></td>
                                            <td><strong class="text-primary">₱<?= number_format($request['refund_amount'], 2) ?></strong></td>
                                            <td><?= esc(ucfirst(str_replace('_', ' ', $request['refund_method'] ?? 'N/A'))) ?></td>
                                            <td>
                                                <?php
                                                $statusClass = 'bg-secondary';
                                                $statusText = strtoupper($request['status'] ?? 'pending');
                                                
                                                switch($request['status']) {
                                                    case 'pending':
                                                        $statusClass = 'bg-warning text-dark';
                                                        break;
                                                    case 'processing':
                                                        $statusClass = 'bg-info text-white';
                                                        break;
                                                    case 'completed':
                                                        $statusClass = 'bg-success text-white';
                                                        break;
                                                    case 'rejected':
                                                        $statusClass = 'bg-danger text-white';
                                                        break;
                                                    case 'cancelled':
                                                        $statusClass = 'bg-secondary text-white';
                                                        break;
                                                }
                                                ?>
                                                <span class="badge <?= $statusClass ?>"><?= $statusText ?></span>
                                            </td>
                                            <td>
                                                <code class="small"><?= esc($request['refund_reference'] ?? 'N/A') ?></code>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-info" 
                                                        onclick="viewRefundDetails(<?= $request['id'] ?>)">
                                                    <i class="fas fa-eye me-1"></i>View
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Request Refund Modal -->
<div class="modal fade" id="requestRefundModal" tabindex="-1" aria-labelledby="requestRefundModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="requestRefundModalLabel">
                    <i class="fas fa-undo me-2"></i>Request Refund
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="refundRequestForm">
                    <div class="mb-3">
                        <label for="payment_id" class="form-label">Select Payment <span class="text-danger">*</span></label>
                        <select class="form-select" id="payment_id" name="payment_id" required>
                            <option value="">-- Select Payment --</option>
                            <?php foreach ($refundablePayments as $payment): ?>
                                <option value="<?= $payment['id'] ?>" 
                                        data-amount="<?= $payment['amount_paid'] ?>"
                                        data-available="<?= $payment['available_refund'] ?>"
                                        data-refund-status="<?= $payment['refund_status'] ?>">
                                    <?= esc($payment['contribution_title']) ?> - 
                                    Receipt: <?= esc($payment['receipt_number'] ?? 'N/A') ?> - 
                                    Amount: ₱<?= number_format($payment['amount_paid'], 2) ?> 
                                    (Available: ₱<?= number_format($payment['available_refund'], 2) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">Only payments with available refund amounts are shown</small>
                    </div>

                    <div class="mb-3" id="paymentInfoSection" style="display: none;">
                        <div class="card bg-light p-3">
                            <div class="row">
                                <div class="col-md-6">
                                    <small class="text-muted">Original Amount:</small>
                                    <div class="fw-bold" id="originalAmount">₱0.00</div>
                                </div>
                                <div class="col-md-6">
                                    <small class="text-muted">Available for Refund:</small>
                                    <div class="fw-bold text-success" id="availableAmount">₱0.00</div>
                                </div>
                            </div>
                            <div class="mt-2">
                                <small class="text-muted">Refund Status:</small>
                                <div id="refundStatusBadge"></div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="refund_amount" class="form-label">Refund Amount <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">₱</span>
                            <input type="number" class="form-control" id="refund_amount" name="refund_amount" 
                                   step="0.01" min="0.01" required>
                        </div>
                        <small class="text-muted">Maximum available: <span id="maxRefundAmount">₱0.00</span></small>
                    </div>

                    <div class="mb-3">
                        <label for="refund_method" class="form-label">Refund Method <span class="text-danger">*</span></label>
                        <select class="form-select" id="refund_method" name="refund_method" required>
                            <option value="">-- Select Refund Method --</option>
                            <?php if (!empty($refundMethods)): ?>
                                <?php foreach ($refundMethods as $method): ?>
                                    <option value="<?= esc($method['code']) ?>"><?= esc($method['name']) ?></option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option value="original_method">Original Payment Method</option>
                                <option value="cash">Cash</option>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="gcash">GCash</option>
                                <option value="paymaya">PayMaya</option>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="refund_reason" class="form-label">Reason for Refund</label>
                        <textarea class="form-control" id="refund_reason" name="refund_reason" 
                                  rows="3" placeholder="Please provide a reason for requesting this refund..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="submitRefundRequest()">
                    <i class="fas fa-paper-plane me-1"></i>Submit Request
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Refund Details Modal -->
<div class="modal fade" id="refundDetailsModal" tabindex="-1" aria-labelledby="refundDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="refundDetailsModalLabel">
                    <i class="fas fa-info-circle me-2"></i>Refund Request Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="refundDetailsContent">
                <div class="text-center">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const paymentSelect = document.getElementById('payment_id');
    const refundAmountInput = document.getElementById('refund_amount');
    
    if (paymentSelect) {
        paymentSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            
            if (selectedOption.value) {
                const available = parseFloat(selectedOption.getAttribute('data-available')) || 0;
                const original = parseFloat(selectedOption.getAttribute('data-amount')) || 0;
                const refundStatus = selectedOption.getAttribute('data-refund-status') || 'no_refund';
                
                // Show payment info section
                document.getElementById('paymentInfoSection').style.display = 'block';
                document.getElementById('originalAmount').textContent = '₱' + original.toFixed(2);
                document.getElementById('availableAmount').textContent = '₱' + available.toFixed(2);
                document.getElementById('maxRefundAmount').textContent = '₱' + available.toFixed(2);
                
                // Set refund status badge
                const statusBadge = document.getElementById('refundStatusBadge');
                let badgeClass = 'badge bg-secondary';
                let statusText = 'No Refund';
                
                if (refundStatus === 'partially_refunded') {
                    badgeClass = 'badge bg-warning text-dark';
                    statusText = 'Partially Refunded';
                } else if (refundStatus === 'fully_refunded') {
                    badgeClass = 'badge bg-danger';
                    statusText = 'Fully Refunded';
                }
                
                statusBadge.innerHTML = '<span class="' + badgeClass + '">' + statusText + '</span>';
                
                // Set max refund amount
                refundAmountInput.max = available;
                refundAmountInput.value = available > 0 ? available.toFixed(2) : '';
            } else {
                document.getElementById('paymentInfoSection').style.display = 'none';
                refundAmountInput.value = '';
            }
        });
    }
    
    // Validate refund amount on input
    if (refundAmountInput) {
        refundAmountInput.addEventListener('input', function() {
            const maxAmount = parseFloat(this.max) || 0;
            const currentValue = parseFloat(this.value) || 0;
            
            if (currentValue > maxAmount) {
                this.setCustomValidity('Refund amount cannot exceed available amount (₱' + maxAmount.toFixed(2) + ')');
                this.classList.add('is-invalid');
            } else {
                this.setCustomValidity('');
                this.classList.remove('is-invalid');
            }
        });
    }
});

function submitRefundRequest() {
    const form = document.getElementById('refundRequestForm');
    
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    
    const formData = new FormData(form);
    
    // Show loading state
    const submitBtn = event.target;
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Submitting...';
    
    fetch('<?= base_url('payer/submit-refund-request') ?>', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message
            alert(data.message);
            // Close modal and reload page
            const modal = bootstrap.Modal.getInstance(document.getElementById('requestRefundModal'));
            modal.hide();
            window.location.reload();
        } else {
            alert('Error: ' + data.message);
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while submitting the refund request. Please try again.');
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    });
}

function viewRefundDetails(refundId) {
    // Fetch refund details via AJAX
    fetch('<?= base_url('admin/refunds/get-details') ?>?refund_id=' + refundId, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const refund = data.refund;
            const content = document.getElementById('refundDetailsContent');
            
            let statusClass = 'bg-secondary';
            let statusText = refund.status ? refund.status.toUpperCase() : 'PENDING';
            
            switch(refund.status) {
                case 'pending':
                    statusClass = 'bg-warning text-dark';
                    break;
                case 'processing':
                    statusClass = 'bg-info text-white';
                    break;
                case 'completed':
                    statusClass = 'bg-success text-white';
                    break;
                case 'rejected':
                    statusClass = 'bg-danger text-white';
                    break;
            }
            
            content.innerHTML = `
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Refund Reference:</strong><br>
                        <code>${refund.refund_reference || 'N/A'}</code>
                    </div>
                    <div class="col-md-6">
                        <strong>Status:</strong><br>
                        <span class="badge ${statusClass}">${statusText}</span>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Contribution:</strong><br>
                        ${refund.contribution_title || 'N/A'}
                    </div>
                    <div class="col-md-6">
                        <strong>Payment Receipt:</strong><br>
                        <code>${refund.receipt_number || 'N/A'}</code>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Refund Amount:</strong><br>
                        <span class="fw-bold text-primary">₱${parseFloat(refund.refund_amount || 0).toFixed(2)}</span>
                    </div>
                    <div class="col-md-6">
                        <strong>Refund Method:</strong><br>
                        ${refund.refund_method ? refund.refund_method.replace('_', ' ').toUpperCase() : 'N/A'}
                    </div>
                </div>
                <div class="mb-3">
                    <strong>Request Date:</strong><br>
                    ${refund.requested_at ? new Date(refund.requested_at).toLocaleString() : 'N/A'}
                </div>
                ${refund.processed_at ? `
                <div class="mb-3">
                    <strong>Processed Date:</strong><br>
                    ${new Date(refund.processed_at).toLocaleString()}
                </div>
                ` : ''}
                ${refund.refund_reason ? `
                <div class="mb-3">
                    <strong>Reason:</strong><br>
                    ${refund.refund_reason}
                </div>
                ` : ''}
                ${refund.admin_notes ? `
                <div class="mb-3">
                    <strong>Admin Notes:</strong><br>
                    <div class="bg-light p-2 rounded">${refund.admin_notes}</div>
                </div>
                ` : ''}
            `;
            
            const modal = new bootstrap.Modal(document.getElementById('refundDetailsModal'));
            modal.show();
        } else {
            alert('Error loading refund details: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while loading refund details.');
    });
}
</script>

<?= $this->endSection() ?>

