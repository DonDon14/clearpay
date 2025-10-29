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
                            <div class="mb-4">
                                <label for="refund_type" class="form-label fw-bold">Refund Type <span class="text-danger">*</span></label>
                                <select class="form-select form-select-lg" id="refund_type" name="refund_type" required>
                                    <option value="">-- Select Refund Type --</option>
                                    <option value="custom">Custom Refund (Any Amount from Payment Group)</option>
                                    <option value="group">Group Refund (All Payments in Sequence)</option>
                                    <option value="sequence">Sequence Refund (Select Specific Payments)</option>
                                </select>
                                <small class="text-muted d-block mt-2">
                                    <strong>Custom:</strong> Refund any custom amount from a payment sequence (e.g., ₱1, ₱20, ₱35) | 
                                    <strong>Group:</strong> Refund full amount of all payments in a payment sequence | 
                                    <strong>Sequence:</strong> Select specific payments from a sequence to refund
                                </small>
                            </div>

                            <div class="row">
                                <!-- Selection Panel -->
                                <div class="col-lg-6">
                                    <!-- Custom Refund View -->
                                    <div id="customRefundView" class="refund-view" style="display: none;">
                                        <h5 class="mb-3">Select Payment Group for Custom Refund</h5>
                                        <div class="mb-3">
                                            <label for="customSearch" class="form-label">Search Payment Groups</label>
                                            <input type="text" class="form-control" id="customSearch" placeholder="Search by payer name or contribution...">
                                        </div>
                                        <div class="alert alert-info">
                                            <i class="fas fa-info-circle me-2"></i>
                                            <strong>Custom Refund:</strong> Select a payment group and enter any amount you want to refund (e.g., ₱1, ₱20, ₱35, ₱123.50). 
                                            The amount will be distributed proportionally across payments in the group.
                                        </div>
                                        <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                                            <table class="table table-hover table-sm">
                                                <thead class="sticky-top bg-light">
                                                    <tr>
                                                        <th>Payer</th>
                                                        <th>Contribution</th>
                                                        <th>Sequence</th>
                                                        <th>Total</th>
                                                        <th>Payments</th>
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="customPaymentsList">
                                                    <?php foreach ($groupedPayments as $group): ?>
                                                        <tr>
                                                            <td><?= esc($group['payer_name']) ?></td>
                                                            <td><?= esc($group['contribution_title']) ?></td>
                                                            <td><span class="badge bg-info">#<?= $group['payment_sequence'] ?? '1' ?></span></td>
                                                            <td>₱<?= number_format($group['total_paid'], 2) ?></td>
                                                            <td><?= $group['payment_count'] ?> payment(s)</td>
                                                            <td>
                                                                <button class="btn btn-sm btn-primary select-custom-group-btn" 
                                                                        data-payer-id="<?= $group['payer_id'] ?>"
                                                                        data-contribution-id="<?= $group['contribution_id'] ?>"
                                                                        data-sequence="<?= $group['payment_sequence'] ?? '1' ?>">
                                                                    <i class="fas fa-edit"></i> Select for Custom Refund
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    <!-- Group Refund View -->
                                    <div id="groupRefundView" class="refund-view" style="display: none;">
                                        <h5 class="mb-3">Select Payment Group</h5>
                                        <div class="mb-3">
                                            <label for="groupSearch" class="form-label">Search Groups</label>
                                            <input type="text" class="form-control" id="groupSearch" placeholder="Search by payer name or contribution...">
                                        </div>
                                        <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                                            <table class="table table-hover table-sm">
                                                <thead class="sticky-top bg-light">
                                                    <tr>
                                                        <th>Payer</th>
                                                        <th>Contribution</th>
                                                        <th>Sequence</th>
                                                        <th>Total</th>
                                                        <th>Payments</th>
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="groupsList">
                                                    <?php foreach ($groupedPayments as $group): ?>
                                                        <tr>
                                                            <td><?= esc($group['payer_name']) ?></td>
                                                            <td><?= esc($group['contribution_title']) ?></td>
                                                            <td><span class="badge bg-info">#<?= $group['payment_sequence'] ?? '1' ?></span></td>
                                                            <td>₱<?= number_format($group['total_paid'], 2) ?></td>
                                                            <td><?= $group['payment_count'] ?> payment(s)</td>
                                                            <td>
                                                                <button class="btn btn-sm btn-primary select-group-btn" 
                                                                        data-payer-id="<?= $group['payer_id'] ?>"
                                                                        data-contribution-id="<?= $group['contribution_id'] ?>"
                                                                        data-sequence="<?= $group['payment_sequence'] ?? '1' ?>">
                                                                    <i class="fas fa-undo"></i> Select Group
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    <!-- Sequence Refund View -->
                                    <div id="sequenceRefundView" class="refund-view" style="display: none;">
                                        <h5 class="mb-3">Select Payment Sequence</h5>
                                        <div class="mb-3">
                                            <label for="sequenceSearch" class="form-label">Search Sequences</label>
                                            <input type="text" class="form-control" id="sequenceSearch" placeholder="Search by payer name or contribution...">
                                        </div>
                                        <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                                            <table class="table table-hover table-sm">
                                                <thead class="sticky-top bg-light">
                                                    <tr>
                                                        <th>Payer</th>
                                                        <th>Contribution</th>
                                                        <th>Sequence</th>
                                                        <th>Total</th>
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="sequencesList">
                                                    <?php foreach ($groupedPayments as $group): ?>
                                                        <tr>
                                                            <td><?= esc($group['payer_name']) ?></td>
                                                            <td><?= esc($group['contribution_title']) ?></td>
                                                            <td><span class="badge bg-info">#<?= $group['payment_sequence'] ?? '1' ?></span></td>
                                                            <td>₱<?= number_format($group['total_paid'], 2) ?></td>
                                                            <td>
                                                                <button class="btn btn-sm btn-primary select-sequence-btn" 
                                                                        data-payer-id="<?= $group['payer_id'] ?>"
                                                                        data-contribution-id="<?= $group['contribution_id'] ?>"
                                                                        data-sequence="<?= $group['payment_sequence'] ?? '1' ?>">
                                                                    <i class="fas fa-list"></i> Select Payments
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <!-- Refund Details Panel -->
                                <div class="col-lg-6">
                                    <h5 class="mb-3">Refund Details</h5>
                                    <form id="processRefundForm">
                                        <input type="hidden" id="refund_type_field" name="refund_type">
                                        <input type="hidden" id="refund_payment_id" name="payment_id">
                                        <input type="hidden" id="refund_payment_ids" name="payment_ids">
                                        <input type="hidden" id="refund_payment_sequence" name="payment_sequence">
                                        <input type="hidden" id="refund_payer_id" name="payer_id">
                                        <input type="hidden" id="refund_contribution_id" name="contribution_id">
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Payment Information</label>
                                            <div class="card bg-light p-3" id="paymentInfoDisplay">
                                                <p class="text-muted mb-0">Select a refund type and payment(s) above</p>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="refund_amount" class="form-label">Refund Amount <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <span class="input-group-text">₱</span>
                                                <input type="number" class="form-control" id="refund_amount" name="refund_amount" step="0.01" min="0.01" required>
                                            </div>
                                            <small class="text-muted">Available for refund: <span id="available_amount">₱0.00</span></small>
                                        </div>

                                        <div class="mb-3">
                                            <label for="refund_method" class="form-label">Refund Method <span class="text-danger">*</span></label>
                                            <select class="form-select" id="refund_method" name="refund_method" required>
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
                                            <label for="refund_reference" class="form-label">Refund Reference Number</label>
                                            <input type="text" class="form-control" id="refund_reference" name="refund_reference" placeholder="Optional reference number">
                                        </div>

                                        <div class="mb-3">
                                            <label for="refund_reason" class="form-label">Refund Reason</label>
                                            <textarea class="form-control" id="refund_reason" name="refund_reason" rows="3" placeholder="Enter reason for refund"></textarea>
                                        </div>

                                        <div class="mb-3">
                                            <label for="admin_notes" class="form-label">Admin Notes</label>
                                            <textarea class="form-control" id="admin_notes" name="admin_notes" rows="2" placeholder="Optional admin notes"></textarea>
                                        </div>

                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-check me-2"></i>Process Refund
                                        </button>
                                        <button type="reset" class="btn btn-secondary">
                                            <i class="fas fa-times me-2"></i>Reset
                                        </button>
                                    </form>
                                </div>
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
                                                                <img src="<?= base_url($request['profile_picture']) ?>" 
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
                                                        <button type="button" class="btn btn-sm btn-success approve-request-btn" data-id="<?= $request['id'] ?>" title="Approve">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-danger reject-request-btn" data-id="<?= $request['id'] ?>" title="Reject">
                                                            <i class="fas fa-times"></i>
                                                        </button>
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
                    <div class="mb-3">
                        <label for="modal_admin_notes" class="form-label">Admin Notes</label>
                        <textarea class="form-control" id="modal_admin_notes" name="admin_notes" rows="3"></textarea>
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

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    let selectedSequencePayments = [];
    
    // Refund type change handler
    $('#refund_type').on('change', function() {
        const refundType = $(this).val();
        
        // Hide all views
        $('.refund-view').hide();
        
        // Clear form
        $('#processRefundForm')[0].reset();
        $('#paymentInfoDisplay').html('<p class="text-muted mb-0">Select a refund type and payment(s) above</p>');
        $('#available_amount').text('₱0.00');
        
        // Show appropriate view
        if (refundType === 'custom') {
            $('#customRefundView').show();
        } else if (refundType === 'group') {
            $('#groupRefundView').show();
        } else if (refundType === 'sequence') {
            $('#sequenceRefundView').show();
        }
        
        $('#refund_type_field').val(refundType);
    });

    // Search custom payments
    $('#customSearch').on('keyup', function() {
        const search = $(this).val().toLowerCase();
        $('#customPaymentsList tr').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(search) > -1);
        });
    });

    // Search groups
    $('#groupSearch').on('keyup', function() {
        const search = $(this).val().toLowerCase();
        $('#groupsList tr').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(search) > -1);
        });
    });

    // Search sequences
    $('#sequenceSearch').on('keyup', function() {
        const search = $(this).val().toLowerCase();
        $('#sequencesList tr').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(search) > -1);
        });
    });

    // Select payment group for custom refund (any amount)
    $(document).on('click', '.select-custom-group-btn', function() {
        const payerId = $(this).data('payer-id');
        const contributionId = $(this).data('contribution-id');
        const sequence = $(this).data('sequence');
        
        $.ajax({
            url: `${window.APP_BASE_URL}/admin/refunds/get-payment-group-details`,
            method: 'GET',
            data: { 
                payer_id: payerId,
                contribution_id: contributionId,
                payment_sequence: sequence
            },
            success: function(response) {
                if (response.success) {
                    const group = response.group;
                    
                    if (!group || !group.payments || group.payments.length === 0) {
                        showNotification('No payments found in this group', 'error');
                        return;
                    }
                    
                    // Fill form fields - for custom refund, we use the sequence approach
                    $('#refund_payer_id').val(payerId);
                    $('#refund_contribution_id').val(contributionId);
                    $('#refund_payment_sequence').val(sequence);
                    $('#refund_payment_id').val('');
                    // For custom refund, include all payments in the sequence
                    const allPaymentIds = group.payments.map(p => p.id);
                    $('#refund_payment_ids').val(JSON.stringify(allPaymentIds));
                    
                    // Set initial amount to 0 - user enters custom amount
                    $('#refund_amount').val('0.00');
                    $('#available_amount').text('₱' + parseFloat(group.available_for_refund || 0).toFixed(2));
                    
                    // Get payer and contribution names from first payment if not in group
                    const payerName = group.payer_name || (group.payments[0] && group.payments[0].payer_name) || 'Unknown Payer';
                    const contributionTitle = group.contribution_title || (group.payments[0] && group.payments[0].contribution_title) || 'Unknown Contribution';
                    
                    // Display group info with explanation
                    let paymentsHtml = '<small class="text-muted">Payments in this group:</small><ul class="list-unstyled mt-2 mb-2">';
                    group.payments.forEach(function(payment) {
                        paymentsHtml += `<li>• Payment #${payment.id}: ₱${parseFloat(payment.amount_paid || 0).toFixed(2)} (${payment.receipt_number || 'No receipt'})</li>`;
                    });
                    paymentsHtml += '</ul>';
                    paymentsHtml += '<div class="alert alert-info mt-2 mb-0"><small><strong>Note:</strong> Enter any amount you want to refund (e.g., ₱1, ₱20, ₱35.50). The system will distribute it proportionally across the payments above.</small></div>';
                    
                    const infoHtml = `
                        <strong>${payerName}</strong><br>
                        <small>Sequence #${sequence} | ${contributionTitle}</small><br>
                        <span class="text-muted">Group Total: ₱${parseFloat(group.total_amount || 0).toFixed(2)}</span><br>
                        <span class="text-danger">Already Refunded: ₱${parseFloat(group.total_refunded || 0).toFixed(2)}</span><br>
                        ${paymentsHtml}
                    `;
                    $('#paymentInfoDisplay').html(infoHtml);
                } else {
                    showNotification(response.message || 'Failed to load payment group details', 'error');
                }
            },
            error: function(xhr) {
                let message = 'An error occurred while loading payment group details';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                showNotification(message, 'error');
            }
        });
    });

    // Select group for group refund
    $(document).on('click', '.select-group-btn', function() {
        const payerId = $(this).data('payer-id');
        const contributionId = $(this).data('contribution-id');
        const sequence = $(this).data('sequence');
        
        $.ajax({
            url: `${window.APP_BASE_URL}/admin/refunds/get-payment-group-details`,
            method: 'GET',
            data: { 
                payer_id: payerId,
                contribution_id: contributionId,
                payment_sequence: sequence
            },
            success: function(response) {
                if (response.success) {
                    const group = response.group;
                    
                    if (!group || !group.payments || group.payments.length === 0) {
                        showNotification('No payments found in this group', 'error');
                        return;
                    }
                    
                    // Fill form fields
                    $('#refund_payer_id').val(payerId);
                    $('#refund_contribution_id').val(contributionId);
                    $('#refund_payment_sequence').val(sequence);
                    $('#refund_payment_id').val('');
                    $('#refund_payment_ids').val('');
                    $('#refund_amount').val(group.available_for_refund || 0);
                    $('#available_amount').text('₱' + parseFloat(group.available_for_refund || 0).toFixed(2));
                    
                    // Get payer and contribution names from first payment if not in group
                    const payerName = group.payer_name || (group.payments[0] && group.payments[0].payer_name) || 'Unknown Payer';
                    const contributionTitle = group.contribution_title || (group.payments[0] && group.payments[0].contribution_title) || 'Unknown Contribution';
                    
                    // Display group info
                    let paymentsHtml = '<small class="text-muted">Payments in group:</small><ul class="list-unstyled mt-2 mb-0">';
                    group.payments.forEach(function(payment) {
                        paymentsHtml += `<li>• Payment #${payment.id}: ₱${parseFloat(payment.amount_paid || 0).toFixed(2)} (${payment.receipt_number || 'No receipt'})</li>`;
                    });
                    paymentsHtml += '</ul>';
                    
                    const infoHtml = `
                        <strong>${payerName}</strong><br>
                        <small>Sequence #${sequence} | ${contributionTitle}</small><br>
                        <span class="text-muted">Group Total: ₱${parseFloat(group.total_amount || 0).toFixed(2)}</span><br>
                        <span class="text-danger">Already Refunded: ₱${parseFloat(group.total_refunded || 0).toFixed(2)}</span><br>
                        ${paymentsHtml}
                    `;
                    $('#paymentInfoDisplay').html(infoHtml);
                } else {
                    showNotification(response.message || 'Failed to load payment group details', 'error');
                }
            },
            error: function(xhr) {
                let message = 'An error occurred while loading payment group details';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                showNotification(message, 'error');
            }
        });
    });

    // Select sequence for sequence refund
    $(document).on('click', '.select-sequence-btn', function() {
        const payerId = $(this).data('payer-id');
        const contributionId = $(this).data('contribution-id');
        const sequence = $(this).data('sequence');
        
        $.ajax({
            url: `${window.APP_BASE_URL}/admin/refunds/get-payment-group-details`,
            method: 'GET',
            data: { 
                payer_id: payerId,
                contribution_id: contributionId,
                payment_sequence: sequence
            },
            success: function(response) {
                if (response.success) {
                    const group = response.group;
                    selectedSequencePayments = group.payments || [];
                    
                    // Fill form fields
                    $('#refund_payer_id').val(payerId);
                    $('#refund_contribution_id').val(contributionId);
                    $('#refund_payment_sequence').val(sequence);
                    $('#refund_payment_id').val('');
                    
                    // Calculate available amount for selected payments (will be calculated when payments are selected)
                    let availableTotal = 0;
                    group.payments.forEach(function(payment) {
                        availableTotal += parseFloat(payment.amount_paid);
                    });
                    $('#refund_amount').val(availableTotal);
                    $('#available_amount').text('₱' + parseFloat(group.available_for_refund).toFixed(2));
                    
                    // Display sequence info with checkboxes
                    let paymentsHtml = '<small class="text-muted">Select payments to refund:</small><div class="mt-2">';
                    group.payments.forEach(function(payment, index) {
                        paymentsHtml += `
                            <div class="form-check">
                                <input class="form-check-input sequence-payment-check" type="checkbox" 
                                       value="${payment.id}" id="pay_${payment.id}" 
                                       data-amount="${payment.amount_paid}" checked>
                                <label class="form-check-label" for="pay_${payment.id}">
                                    Payment #${payment.id}: ₱${parseFloat(payment.amount_paid).toFixed(2)} 
                                    (${payment.receipt_number || 'No receipt'}) - ${payment.payment_date ? new Date(payment.payment_date).toLocaleDateString() : ''}
                                </label>
                            </div>
                        `;
                    });
                    paymentsHtml += '</div>';
                    
                    // Get payer and contribution names from first payment if not in group
                    const payerName = group.payer_name || (group.payments[0] && group.payments[0].payer_name) || 'Unknown Payer';
                    const contributionTitle = group.contribution_title || (group.payments[0] && group.payments[0].contribution_title) || 'Unknown Contribution';
                    
                    const infoHtml = `
                        <strong>${payerName}</strong><br>
                        <small>Sequence #${sequence} | ${contributionTitle}</small><br>
                        <span class="text-muted">Group Total: ₱${parseFloat(group.total_amount || 0).toFixed(2)}</span><br>
                        <span class="text-danger">Already Refunded: ₱${parseFloat(group.total_refunded || 0).toFixed(2)}</span><br>
                        ${paymentsHtml}
                    `;
                    $('#paymentInfoDisplay').html(infoHtml);
                    
                    // Update selected payments when checkboxes change
                    updateSequencePaymentSelection();
                } else {
                    showNotification(response.message || 'Failed to load payment group details', 'error');
                }
            },
            error: function(xhr) {
                let message = 'An error occurred while loading payment group details';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                showNotification(message, 'error');
            }
        });
    });

    // Update sequence payment selection
    function updateSequencePaymentSelection() {
        const selectedIds = [];
        let selectedTotal = 0;
        
        $('.sequence-payment-check:checked').each(function() {
            const paymentId = $(this).val();
            const amount = parseFloat($(this).data('amount'));
            selectedIds.push(paymentId);
            selectedTotal += amount;
        });
        
        $('#refund_payment_ids').val(JSON.stringify(selectedIds));
        $('#refund_amount').val(selectedTotal.toFixed(2));
    }

    $(document).on('change', '.sequence-payment-check', function() {
        updateSequencePaymentSelection();
    });

    // Process refund form submission
    $('#processRefundForm').on('submit', function(e) {
        e.preventDefault();
        
        const refundType = $('#refund_type_field').val();
        if (!refundType) {
            showNotification('Please select a refund type', 'error');
            return;
        }

        // Validate required fields
        const refundAmount = parseFloat($('#refund_amount').val() || 0);
        if (refundAmount <= 0) {
            showNotification('Please enter a refund amount greater than 0', 'error');
            return;
        }

        if (!confirm(`Are you sure you want to process this ${refundType} refund of ₱${refundAmount.toFixed(2)}?`)) {
            return;
        }

        const formData = $(this).serialize();
        
        // Show loading indicator
        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.html();
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Processing...');
        
        $.ajax({
            url: `${window.APP_BASE_URL}/admin/refunds/process`,
            method: 'POST',
            data: formData,
            success: function(response) {
                submitBtn.prop('disabled', false).html(originalText);
                
                if (response.success) {
                    let message = 'Refund processed successfully';
                    if (response.payment_count && response.payment_count > 1) {
                        message += ` (${response.payment_count} payments, Total: ₱${parseFloat(response.total_refunded || 0).toFixed(2)})`;
                    }
                    showNotification(message, 'success');
                    $('#processRefundForm')[0].reset();
                    $('#refund_type').val('').trigger('change');
                    $('#paymentInfoDisplay').html('<p class="text-muted mb-0">Select a refund type and payment(s) above</p>');
                    // Refresh page after 2 seconds
                    setTimeout(() => location.reload(), 2000);
                } else {
                    let errorMessage = response.message || 'Failed to process refund';
                    if (response.errors && typeof response.errors === 'object') {
                        const errorList = Object.values(response.errors).flat();
                        if (errorList.length > 0) {
                            errorMessage += ': ' + errorList.join(', ');
                        }
                    }
                    showNotification(errorMessage, 'error');
                }
            },
            error: function(xhr) {
                submitBtn.prop('disabled', false).html(originalText);
                
                let message = 'An error occurred while processing the refund';
                if (xhr.responseJSON) {
                    if (xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }
                    if (xhr.responseJSON.errors && typeof xhr.responseJSON.errors === 'object') {
                        const errorList = Object.values(xhr.responseJSON.errors).flat();
                        if (errorList.length > 0) {
                            message += ': ' + errorList.join(', ');
                        }
                    }
                } else if (xhr.status === 0) {
                    message = 'Network error. Please check your connection.';
                } else if (xhr.status >= 500) {
                    message = 'Server error. Please try again later.';
                }
                console.error('Refund processing error:', xhr);
                showNotification(message, 'error');
            }
        });
    });

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
                            </div>
                        </div>
                        ${refund.refund_reason ? `<div class="mt-3"><h6>Reason:</h6><p>${refund.refund_reason}</p></div>` : ''}
                        ${refund.admin_notes ? `<div class="mt-3"><h6>Admin Notes:</h6><p>${refund.admin_notes}</p></div>` : ''}
                        ${refund.payer_notes ? `<div class="mt-3"><h6>Payer Notes:</h6><p>${refund.payer_notes}</p></div>` : ''}
                    `;
                    $('#refundDetailsContent').html(html);
                    new bootstrap.Modal(document.getElementById('refundDetailsModal')).show();
                }
            }
        });
    });

    // Approve request
    $('.approve-request-btn').on('click', function() {
        const refundId = $(this).data('id');
        $('#modal_refund_id').val(refundId);
        $('#approveRejectModalTitle').text('Approve Refund Request');
        $('#modalActionBtn').removeClass('btn-danger').addClass('btn-success').text('Approve').off('click').on('click', function() {
            approveRefundRequest(refundId);
        });
        new bootstrap.Modal(document.getElementById('approveRejectModal')).show();
    });

    // Reject request
    $('.reject-request-btn').on('click', function() {
        const refundId = $(this).data('id');
        $('#modal_refund_id').val(refundId);
        $('#approveRejectModalTitle').text('Reject Refund Request');
        $('#modalActionBtn').removeClass('btn-success').addClass('btn-danger').text('Reject').off('click').on('click', function() {
            rejectRefundRequest(refundId);
        });
        new bootstrap.Modal(document.getElementById('approveRejectModal')).show();
    });

    function approveRefundRequest(refundId) {
        const adminNotes = $('#modal_admin_notes').val();
        
        $.ajax({
            url: `${window.APP_BASE_URL}/admin/refunds/approve`,
            method: 'POST',
            data: {
                refund_id: refundId,
                admin_notes: adminNotes
            },
            success: function(response) {
                if (response.success) {
                    showNotification('Refund request approved successfully', 'success');
                    bootstrap.Modal.getInstance(document.getElementById('approveRejectModal')).hide();
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
<?= $this->endSection() ?>

<?= $this->endSection() ?>
