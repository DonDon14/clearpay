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

<?= $this->include('partials/modal-request-refund') ?>

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

// Note: submitRefundRequest() is defined in modal-request-refund.php
// This function is not used - kept for backward compatibility only

function viewRefundDetails(refundId) {
    // Show loading state
    const content = document.getElementById('refundDetailsContent');
    if (content) {
        content.innerHTML = '<div class="text-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>';
    }
    
    // Fetch refund details via AJAX (payer-specific endpoint)
    fetch('<?= base_url('payer/refund-details') ?>?refund_id=' + refundId, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
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
            const content = document.getElementById('refundDetailsContent');
            if (content) {
                content.innerHTML = '<div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-2"></i>Error loading refund details: ' + (data.message || 'Unknown error') + '</div>';
            } else {
                alert('Error loading refund details: ' + (data.message || 'Unknown error'));
            }
        }
    })
    .catch(error => {
        const content = document.getElementById('refundDetailsContent');
        if (content) {
            content.innerHTML = '<div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-2"></i>An error occurred while loading refund details. Please try again.</div>';
        } else {
            alert('An error occurred while loading refund details. Please try again.');
        }
    });
}
</script>

<?= $this->endSection() ?>

