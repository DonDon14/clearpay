<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<link rel="stylesheet" href="<?= base_url('css/dashboard.css') ?>" />

<!-- Stats Cards -->
<div class="container-fluid mb-4">
    <div class="row g-3">
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="text-warning mb-2">
                        <i class="fas fa-clock fa-2x"></i>
                    </div>
                    <h5 class="card-title">Pending Requests</h5>
                    <p class="card-text text-muted"><?= $stats['pending'] ?></p>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="text-info mb-2">
                        <i class="fas fa-spinner fa-2x"></i>
                    </div>
                    <h5 class="card-title">Processing</h5>
                    <p class="card-text text-muted"><?= $stats['processing'] ?></p>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="text-success mb-2">
                        <i class="fas fa-check-circle fa-2x"></i>
                    </div>
                    <h5 class="card-title">Completed</h5>
                    <p class="card-text text-muted"><?= $stats['completed'] ?></p>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="text-danger mb-2">
                        <i class="fas fa-times-circle fa-2x"></i>
                    </div>
                    <h5 class="card-title">Rejected</h5>
                    <p class="card-text text-muted"><?= $stats['rejected'] ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Refunds Management Tabs -->
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header">
                    <ul class="nav nav-tabs card-header-tabs" id="refundsTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="process-tab" data-bs-toggle="tab" data-bs-target="#process" type="button" role="tab">
                                <i class="fas fa-undo me-2"></i>Process Refund
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="requests-tab" data-bs-toggle="tab" data-bs-target="#requests" type="button" role="tab">
                                <i class="fas fa-inbox me-2"></i>Refund Requests
                                <?php if ($stats['pending'] > 0): ?>
                                    <span class="badge bg-warning text-dark ms-2"><?= $stats['pending'] ?></span>
                                <?php endif; ?>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="history-tab" data-bs-toggle="tab" data-bs-target="#history" type="button" role="tab">
                                <i class="fas fa-history me-2"></i>History
                            </button>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content" id="refundsTabContent">
                        <!-- Process Refund Tab -->
                        <div class="tab-pane fade show active" id="process" role="tabpanel">
                            <div class="text-center py-5">
                                <button type="button" class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#refundTransactionModal">
                                    <i class="fas fa-undo me-2"></i>Process Refund Transaction
                                </button>
                                <p class="text-muted mt-3">Click the button above to open the refund transaction modal</p>
                            </div>
                        </div>

                        <!-- Refund Requests Tab -->
                        <div class="tab-pane fade" id="requests" role="tabpanel">
                            <?php if (empty($pendingRequests)): ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                                    <h5 class="text-muted">No Pending Refund Requests</h5>
                                    <p class="text-muted">Refund requests from payers will appear here</p>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover" id="requestsTable">
                                        <thead>
                                            <tr>
                                                <th>Requested Date</th>
                                                <th>Payer</th>
                                                <th>Payment</th>
                                                <th>Contribution</th>
                                                <th>Amount</th>
                                                <th>Reason</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($pendingRequests as $request): ?>
                                                <tr>
                                                    <td><?= date('M d, Y H:i', strtotime($request['requested_at'])) ?></td>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <?php if (!empty($request['profile_picture'])): ?>
                                                                <?php 
                                                                // Check if it's a Cloudinary URL (full URL) or local path
                                                                $refundPicUrl = (strpos($request['profile_picture'], 'res.cloudinary.com') !== false) 
                                                                    ? $request['profile_picture'] 
                                                                    : base_url($request['profile_picture']);
                                                                ?>
                                                                <img src="<?= $refundPicUrl ?>" 
                                                                     alt="Profile" class="rounded-circle me-2" 
                                                                     style="width: 32px; height: 32px; object-fit: cover;">
                                                            <?php else: ?>
                                                                <div class="bg-primary text-white rounded-circle me-2 d-flex align-items-center justify-content-center" 
                                                                     style="width: 32px; height: 32px;">
                                                                    <i class="fas fa-user"></i>
                                                                </div>
                                                            <?php endif; ?>
                                                            <div>
                                                                <div class="fw-bold"><?= esc($request['payer_name']) ?></div>
                                                                <small class="text-muted"><?= esc($request['contact_number']) ?></small>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div>
                                                            <small class="text-muted">Receipt #<?= esc($request['receipt_number']) ?></small><br>
                                                            <small>₱<?= number_format($request['amount_paid'], 2) ?></small>
                                                        </div>
                                                    </td>
                                                    <td><?= esc($request['contribution_title']) ?></td>
                                                    <td class="fw-bold text-danger">₱<?= number_format($request['refund_amount'], 2) ?></td>
                                                    <td>
                                                        <small><?= esc(substr($request['payer_notes'] ?? $request['refund_reason'] ?? 'No reason provided', 0, 50)) ?>...</small>
                                                    </td>
                                                    <td>
                                                        <button type="button" class="btn btn-sm btn-info view-request-btn" data-id="<?= $request['id'] ?>" title="View Details">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- History Tab -->
                        <div class="tab-pane fade" id="history" role="tabpanel">
                            <?php if (empty($refundHistory)): ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-history fa-4x text-muted mb-3"></i>
                                    <h5 class="text-muted">No Refund History</h5>
                                    <p class="text-muted">Completed, rejected, and cancelled refunds will appear here</p>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover" id="historyTable">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Payer</th>
                                                <th>Payment</th>
                                                <th>Contribution</th>
                                                <th>Amount</th>
                                                <th>Method</th>
                                                <th>Status</th>
                                                <th>Processed By</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($refundHistory as $refund): ?>
                                                <tr>
                                                    <td><?= date('M d, Y H:i', strtotime($refund['processed_at'] ?? $refund['requested_at'])) ?></td>
                                                    <td><?= esc($refund['payer_name']) ?></td>
                                                    <td>
                                                        <small class="text-muted">Receipt #<?= esc($refund['receipt_number']) ?></small>
                                                    </td>
                                                    <td><?= esc($refund['contribution_title']) ?></td>
                                                    <td class="fw-bold">₱<?= number_format($refund['refund_amount'], 2) ?></td>
                                                    <td><?= esc(ucfirst(str_replace('_', ' ', $refund['refund_method']))) ?></td>
                                                    <td>
                                                        <?php
                                                        $statusClass = match($refund['status']) {
                                                            'completed' => 'success',
                                                            'rejected' => 'danger',
                                                            'cancelled' => 'secondary',
                                                            default => 'warning'
                                                        };
                                                        ?>
                                                        <span class="badge bg-<?= $statusClass ?>"><?= esc(ucfirst($refund['status'])) ?></span>
                                                    </td>
                                                    <td><?= esc($refund['processed_by_name'] ?? 'N/A') ?></td>
                                                    <td>
                                                        <button type="button" class="btn btn-sm btn-info view-refund-btn" data-id="<?= $refund['id'] ?>" title="View Details">
                                                            <i class="fas fa-eye"></i>
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
    </div>
</div>

<!-- Refund Details Modal -->
<div class="modal fade" id="refundDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Refund Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="refundDetailsContent">
                <!-- Details will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Approve/Reject Modal -->
<div class="modal fade" id="approveRejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="approveRejectModalTitle">Process Refund Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="approveRejectForm">
                    <input type="hidden" id="modal_refund_id" name="refund_id">
                    <div id="approveFields" style="display: none;">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Note:</strong> Approving this refund request will automatically process and complete the refund.
                        </div>
                        <div class="mb-3">
                            <label for="modal_refund_reference" class="form-label">Refund Reference Number</label>
                            <input type="text" class="form-control" id="modal_refund_reference" name="refund_reference" 
                                   placeholder="Optional: Enter refund transaction reference">
                            <small class="text-muted">Leave blank to auto-generate</small>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="modal_admin_notes" class="form-label">Admin Notes</label>
                        <textarea class="form-control" id="modal_admin_notes" name="admin_notes" rows="3" 
                                  placeholder="Optional notes about this action"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn" id="modalActionBtn"></button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    // View refund details
    $('.view-request-btn, .view-refund-btn').on('click', function() {
        const refundId = $(this).data('id');
        
        $.ajax({
            url: `${window.APP_BASE_URL}/admin/refunds/get-details`,
            method: 'GET',
            data: { refund_id: refundId },
            success: function(response) {
                if (response.success) {
                    const refund = response.refund;
                    const html = `
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Refund Information</h6>
                                <p><strong>Refund ID:</strong> #${refund.id}</p>
                                <p><strong>Reference:</strong> ${refund.refund_reference || 'N/A'}</p>
                                <p><strong>Amount:</strong> ₱${parseFloat(refund.refund_amount).toFixed(2)}</p>
                                <p><strong>Method:</strong> ${refund.refund_method.replace('_', ' ')}</p>
                                <p><strong>Status:</strong> <span class="badge bg-${refund.status === 'completed' ? 'success' : refund.status === 'rejected' ? 'danger' : 'warning'}">${refund.status}</span></p>
                            </div>
                            <div class="col-md-6">
                                <h6>Payment Information</h6>
                                    <p><strong>Payer:</strong> ${refund.payer_name}</p>
                                    <p><strong>Receipt #:</strong> ${refund.receipt_number || 'N/A'}</p>
                                    <p><strong>Contribution:</strong> ${refund.contribution_title}</p>
                                    <p><strong>Original Amount:</strong> ₱${parseFloat(refund.amount_paid).toFixed(2)}</p>
                                    ${refund.processed_by_name ? `<p><strong>Processed By:</strong> ${refund.processed_by_name}</p>` : ''}
                                    ${refund.processed_at ? `<p><strong>Processed At:</strong> ${new Date(refund.processed_at).toLocaleString()}</p>` : ''}
                                </div>
                            </div>
                        ${refund.refund_reason ? `<div class="mt-3"><h6>Reason:</h6><p>${refund.refund_reason}</p></div>` : ''}
                        ${refund.admin_notes ? `<div class="mt-3"><h6>Admin Notes:</h6><p>${refund.admin_notes}</p></div>` : ''}
                        ${refund.payer_notes ? `<div class="mt-3"><h6>Payer Notes:</h6><p>${refund.payer_notes}</p></div>` : ''}
                    `;
                    $('#refundDetailsContent').html(html);

                    // Configure actions in the refund details modal
                    const $detailsModal = $('#refundDetailsModal');
                    const $footer = $detailsModal.find('.modal-footer');
                    // Remove any previously injected buttons
                    $footer.find('.details-approve-btn, .details-reject-btn').remove();

                    if (refund.status === 'pending') {
                        // Add Approve and Reject buttons inside the details modal
                        const approveBtn = $('<button>', {
                            class: 'btn btn-success me-2 details-approve-btn',
                            text: 'Approve & Complete'
                        }).on('click', function() {
                            $('#modal_refund_id').val(refund.id);
                            $('#modal_refund_reference').val(refund.refund_reference || '');
                            $('#modal_admin_notes').val('');
                            $('#approveFields').show();
                            $('#approveRejectModalTitle').text('Approve & Complete Refund Request');
                            $('#modalActionBtn').removeClass('btn-danger').addClass('btn-success').text('Approve & Complete').off('click').on('click', function() {
                                approveRefundRequest(refund.id);
                            });
                            bootstrap.Modal.getInstance(document.getElementById('refundDetailsModal')).hide();
                            new bootstrap.Modal(document.getElementById('approveRejectModal')).show();
                        });

                        const rejectBtn = $('<button>', {
                            class: 'btn btn-danger details-reject-btn',
                            text: 'Reject'
                        }).on('click', function() {
                            $('#modal_refund_id').val(refund.id);
                            $('#modal_refund_reference').val('');
                            $('#modal_admin_notes').val('');
                            $('#approveFields').hide();
                            $('#approveRejectModalTitle').text('Reject Refund Request');
                            $('#modalActionBtn').removeClass('btn-success').addClass('btn-danger').text('Reject').off('click').on('click', function() {
                                rejectRefundRequest(refund.id);
                            });
                            bootstrap.Modal.getInstance(document.getElementById('refundDetailsModal')).hide();
                            new bootstrap.Modal(document.getElementById('approveRejectModal')).show();
                        });

                        $footer.prepend(rejectBtn).prepend(approveBtn);
                    }

                    new bootstrap.Modal(document.getElementById('refundDetailsModal')).show();
                }
            }
        });
    });

    // Approve request
    $('.approve-request-btn').on('click', function() {
        const refundId = $(this).data('id');
        $('#modal_refund_id').val(refundId);
        $('#modal_refund_reference').val('');
        $('#modal_admin_notes').val('');
        $('#approveRejectModalTitle').text('Approve & Complete Refund Request');
        $('#approveFields').show();
        $('#modalActionBtn').removeClass('btn-danger').addClass('btn-success').text('Approve & Complete').off('click').on('click', function() {
            approveRefundRequest(refundId);
        });
        new bootstrap.Modal(document.getElementById('approveRejectModal')).show();
    });

    // Reject request
    $('.reject-request-btn').on('click', function() {
        const refundId = $(this).data('id');
        $('#modal_refund_id').val(refundId);
        $('#modal_refund_reference').val('');
        $('#modal_admin_notes').val('');
        $('#approveRejectModalTitle').text('Reject Refund Request');
        $('#approveFields').hide();
        $('#modalActionBtn').removeClass('btn-success').addClass('btn-danger').text('Reject').off('click').on('click', function() {
            rejectRefundRequest(refundId);
        });
        new bootstrap.Modal(document.getElementById('approveRejectModal')).show();
    });

    function approveRefundRequest(refundId) {
        const adminNotes = $('#modal_admin_notes').val();
        const refundReference = $('#modal_refund_reference').val();
        
        $.ajax({
            url: `${window.APP_BASE_URL}/admin/refunds/approve`,
            method: 'POST',
            data: {
                refund_id: refundId,
                admin_notes: adminNotes,
                refund_reference: refundReference
            },
            success: function(response) {
                if (response.success) {
                    showNotification('Refund request approved and completed successfully', 'success');
                    bootstrap.Modal.getInstance(document.getElementById('approveRejectModal')).hide();
                    // Refresh badge immediately
                    if (typeof window.refreshRefundRequestsBadge === 'function') {
                        window.refreshRefundRequestsBadge();
                    }
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showNotification(response.message || 'Failed to approve request', 'error');
                }
            }
        });
    }

    function rejectRefundRequest(refundId) {
        const adminNotes = $('#modal_admin_notes').val();
        
        $.ajax({
            url: `${window.APP_BASE_URL}/admin/refunds/reject`,
            method: 'POST',
            data: {
                refund_id: refundId,
                admin_notes: adminNotes
            },
            success: function(response) {
                if (response.success) {
                    showNotification('Refund request rejected', 'success');
                    bootstrap.Modal.getInstance(document.getElementById('approveRejectModal')).hide();
                    // Refresh badge immediately
                    if (typeof window.refreshRefundRequestsBadge === 'function') {
                        window.refreshRefundRequestsBadge();
                    }
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showNotification(response.message || 'Failed to reject request', 'error');
                }
            }
        });
    }

    // Initialize DataTables if available
    if (typeof $.fn.DataTable !== 'undefined') {
        $('#requestsTable, #historyTable').DataTable({
            order: [[0, 'desc']],
            pageLength: 25
        });
    }
});
</script>

<?= view('partials/modal-refund-transaction', ['refundMethods' => $refundMethods]) ?>

<?= $this->endSection() ?>
