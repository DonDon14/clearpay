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
                    <div class="text-success mb-2">
                        <i class="fas fa-check-circle fa-2x"></i>
                    </div>
                    <h5 class="card-title">Approved</h5>
                    <p class="card-text text-muted"><?= $stats['approved'] ?></p>
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
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="text-primary mb-2">
                        <i class="fas fa-check-double fa-2x"></i>
                    </div>
                    <h5 class="card-title">Processed</h5>
                    <p class="card-text text-muted"><?= $stats['processed'] ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Payment Requests Management -->
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Pending Requests Table -->
            <div class="card mb-4">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="fas fa-clock me-2"></i>Pending Payment Requests</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($pendingRequests)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <h6 class="text-muted">No Pending Requests</h6>
                            <p class="text-muted">Pending payment requests will appear here</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover" id="pendingTable">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Payer</th>
                                        <th>Contribution</th>
                                        <th>Amount</th>
                                        <th>Method</th>
                                        <th>Reference</th>
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
                                                        $profileUrl = (strpos($request['profile_picture'], 'res.cloudinary.com') !== false) 
                                                            ? $request['profile_picture'] 
                                                            : base_url($request['profile_picture']);
                                                        ?>
                                                        <img src="<?= $profileUrl ?>" 
                                                             alt="Profile" class="rounded-circle me-2" 
                                                             style="width: 32px; height: 32px; object-fit: cover;"
                                                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                                        <div class="bg-primary text-white rounded-circle me-2 d-flex align-items-center justify-content-center" 
                                                             style="width: 32px; height: 32px; display: none;">
                                                            <i class="fas fa-user"></i>
                                                        </div>
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
                                                    <div class="fw-bold"><?= esc($request['contribution_title']) ?></div>
                                                    <small class="text-muted"><?= esc(substr($request['contribution_description'], 0, 50)) ?>...</small>
                                                </div>
                                            </td>
                                            <td>₱<?= number_format($request['requested_amount'], 2) ?></td>
                                            <td><?= esc(ucfirst(str_replace('_', ' ', $request['payment_method']))) ?></td>
                                            <td><?= esc($request['reference_number']) ?></td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-info view-details-btn" data-id="<?= $request['id'] ?>" title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-danger delete-request-btn" data-id="<?= $request['id'] ?>" title="Delete Request">
                                                    <i class="fas fa-trash"></i>
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

            <!-- Approved Requests Table -->
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-check-circle me-2"></i>Approved Payment Requests</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($approvedRequests)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-check-circle fa-3x text-muted mb-3"></i>
                            <h6 class="text-muted">No Approved Requests</h6>
                            <p class="text-muted">Approved payment requests will appear here</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover" id="approvedTable">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Payer</th>
                                        <th>Contribution</th>
                                        <th>Amount</th>
                                        <th>Method</th>
                                        <th>Reference</th>
                                        <th>Approved By</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($approvedRequests as $request): ?>
                                        <tr>
                                            <td><?= date('M d, Y H:i', strtotime($request['requested_at'])) ?></td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <?php if (!empty($request['profile_picture'])): ?>
                                                        <?php 
                                                        // Check if it's a Cloudinary URL (full URL) or local path
                                                        $profileUrl = (strpos($request['profile_picture'], 'res.cloudinary.com') !== false) 
                                                            ? $request['profile_picture'] 
                                                            : base_url($request['profile_picture']);
                                                        ?>
                                                        <img src="<?= $profileUrl ?>" 
                                                             alt="Profile" class="rounded-circle me-2" 
                                                             style="width: 32px; height: 32px; object-fit: cover;"
                                                             onerror="this.style.display='none';">
                                                    <?php else: ?>
                                                        <div class="bg-primary text-white rounded-circle me-2 d-flex align-items-center justify-content-center" 
                                                             style="width: 32px; height: 32px;">
                                                            <i class="fas fa-user"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                    <div class="ms-2">
                                                        <div class="fw-bold"><?= esc($request['payer_name']) ?></div>
                                                        <small class="text-muted"><?= esc($request['contact_number']) ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <div class="fw-bold"><?= esc($request['contribution_title']) ?></div>
                                                    <small class="text-muted"><?= esc(substr($request['contribution_description'], 0, 50)) ?>...</small>
                                                </div>
                                            </td>
                                            <td>₱<?= number_format($request['requested_amount'], 2) ?></td>
                                            <td><?= esc(ucfirst(str_replace('_', ' ', $request['payment_method']))) ?></td>
                                            <td><?= esc($request['reference_number']) ?></td>
                                            <td>
                                                <span class="badge bg-success"><?= esc($request['processed_by_name'] ?? 'Admin') ?></span>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-info view-details-btn" data-id="<?= $request['id'] ?>" title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-danger delete-request-btn" data-id="<?= $request['id'] ?>" title="Delete Request">
                                                    <i class="fas fa-trash"></i>
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

            <!-- Rejected Requests Table -->
            <div class="card mb-4">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0"><i class="fas fa-times-circle me-2"></i>Rejected Payment Requests</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($rejectedRequests)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-times-circle fa-3x text-muted mb-3"></i>
                            <h6 class="text-muted">No Rejected Requests</h6>
                            <p class="text-muted">Rejected payment requests will appear here</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover" id="rejectedTable">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Payer</th>
                                        <th>Contribution</th>
                                        <th>Amount</th>
                                        <th>Method</th>
                                        <th>Reference</th>
                                        <th>Rejected By</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($rejectedRequests as $request): ?>
                                        <tr>
                                            <td><?= date('M d, Y H:i', strtotime($request['requested_at'])) ?></td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <?php if (!empty($request['profile_picture'])): ?>
                                                        <?php 
                                                        // Check if it's a Cloudinary URL (full URL) or local path
                                                        $profileUrl = (strpos($request['profile_picture'], 'res.cloudinary.com') !== false) 
                                                            ? $request['profile_picture'] 
                                                            : base_url($request['profile_picture']);
                                                        ?>
                                                        <img src="<?= $profileUrl ?>" 
                                                             alt="Profile" class="rounded-circle me-2" 
                                                             style="width: 32px; height: 32px; object-fit: cover;"
                                                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                                        <div class="bg-primary text-white rounded-circle me-2 d-flex align-items-center justify-content-center" 
                                                             style="width: 32px; height: 32px; display: none;">
                                                            <i class="fas fa-user"></i>
                                                        </div>
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
                                                    <div class="fw-bold"><?= esc($request['contribution_title']) ?></div>
                                                    <small class="text-muted"><?= esc(substr($request['contribution_description'], 0, 50)) ?>...</small>
                                                </div>
                                            </td>
                                            <td>₱<?= number_format($request['requested_amount'], 2) ?></td>
                                            <td><?= esc(ucfirst(str_replace('_', ' ', $request['payment_method']))) ?></td>
                                            <td><?= esc($request['reference_number']) ?></td>
                                            <td>
                                                <span class="badge bg-danger"><?= esc($request['processed_by_name'] ?? 'Admin') ?></span>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-info view-details-btn" data-id="<?= $request['id'] ?>" title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-danger delete-request-btn" data-id="<?= $request['id'] ?>" title="Delete Request">
                                                    <i class="fas fa-trash"></i>
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

<!-- Request Details Modal -->
<div class="modal fade" id="requestDetailsModal" tabindex="-1" aria-labelledby="requestDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="requestDetailsModalLabel">
                    <i class="fas fa-eye me-2"></i>Payment Request Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="requestDetailsContent">
                <!-- Content will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-success" id="approveFromDetailsBtn" style="display: none;">
                    <i class="fas fa-check me-2"></i>Approve
                </button>
                <button type="button" class="btn btn-danger" id="rejectFromDetailsBtn" style="display: none;">
                    <i class="fas fa-times me-2"></i>Reject
                </button>
                <button type="button" class="btn btn-primary" id="processFromDetailsBtn" style="display: none;">
                    <i class="fas fa-cog me-2"></i>Process
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Action Confirmation Modal -->
<div class="modal fade" id="actionModal" tabindex="-1" aria-labelledby="actionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="actionModalLabel">Confirm Action</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="actionModalBody">
                <!-- Content will be loaded here -->
                <div id="adminNotesSection" style="display: none;" class="mt-3">
                    <div class="mb-3">
                        <label for="modal_admin_notes" class="form-label">Reason for Rejection <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="modal_admin_notes" name="admin_notes" rows="3" 
                                  placeholder="Please provide a reason for rejecting this payment request..."></textarea>
                        <small class="text-muted">This message will be sent to the payer</small>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmActionBtn">Confirm</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteModalLabel">
                    <i class="fas fa-exclamation-triangle me-2"></i>Delete Payment Request
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Warning!</strong> This action cannot be undone.
                </div>
                <p>Are you sure you want to delete this payment request?</p>
                <div id="deleteRequestInfo">
                    <!-- Request details will be loaded here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                    <i class="fas fa-trash me-2"></i>Delete Request
                </button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    let currentRequestId = null;
    let currentAction = null;

    // Initialize DataTables
    $('#pendingTable').DataTable({
        "order": [[0, "desc"]], // Order by date descending
        "pageLength": 10
    });
    
    $('#approvedTable').DataTable({
        "order": [[0, "desc"]], // Order by date descending
        "pageLength": 10
    });
    
    $('#rejectedTable').DataTable({
        "order": [[0, "desc"]], // Order by date descending
        "pageLength": 10
    });

    // Handle view details button click
    $(document).on('click', '.view-details-btn', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const requestId = $(this).data('id');
        viewRequestDetails(requestId);
    });

    // Handle delete button click
    $(document).on('click', '.delete-request-btn', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const requestId = $(this).data('id');
        showDeleteModal(requestId);
    });

    // Handle approve from details modal
    $('#approveFromDetailsBtn').click(function() {
        showActionModal('Approve Payment Request', 'Are you sure you want to approve this payment request? This will create a payment record.');
        currentAction = 'approve';
    });

    // Handle reject from details modal
    $('#rejectFromDetailsBtn').click(function() {
        currentAction = 'reject';
        showActionModal('Reject Payment Request', 'Are you sure you want to reject this payment request? Please provide a reason below.');
    });

    // Handle process from details modal
    $('#processFromDetailsBtn').click(function() {
        showActionModal('Process Payment Request', 'Are you sure you want to process this payment request? This will create a payment record.');
        currentAction = 'process';
    });

    // Handle confirm action
    $('#confirmActionBtn').click(function() {
        if (currentAction && currentRequestId) {
            executeAction(currentAction, currentRequestId);
        }
    });

    // Handle confirm delete
    $('#confirmDeleteBtn').click(function() {
        if (currentRequestId) {
            deletePaymentRequest(currentRequestId);
        }
    });

    function viewRequestDetails(requestId) {
        fetch('<?= base_url('admin/get-payment-request-details') ?>?request_id=' + requestId, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const request = data.request;
                currentRequestId = requestId;
                
                let statusBadge = '';
                switch(request.status) {
                    case 'pending':
                        statusBadge = '<span class="badge bg-warning text-dark">Pending</span>';
                        break;
                    case 'approved':
                        statusBadge = '<span class="badge bg-success">Approved</span>';
                        break;
                    case 'rejected':
                        statusBadge = '<span class="badge bg-danger">Rejected</span>';
                        break;
                    case 'processed':
                        statusBadge = '<span class="badge bg-info">Processed</span>';
                        break;
                }

                const content = `
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-primary">Request Information</h6>
                            <p><strong>Reference Number:</strong> ${request.reference_number}</p>
                            <p><strong>Status:</strong> ${statusBadge}</p>
                            <p><strong>Requested Amount:</strong> ₱${parseFloat(request.requested_amount).toLocaleString('en-US', {minimumFractionDigits: 2})}</p>
                            <p><strong>Payment Method:</strong> ${request.payment_method.replace('_', ' ').toUpperCase()}</p>
                            <p><strong>Requested At:</strong> ${new Date(request.requested_at).toLocaleString()}</p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-primary">Payer Information</h6>
                            <p><strong>Name:</strong> ${request.payer_name}</p>
                            <p><strong>Contact:</strong> ${request.contact_number}</p>
                            <p><strong>Email:</strong> ${request.email_address}</p>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <h6 class="text-primary">Contribution Details</h6>
                            <p><strong>Title:</strong> ${request.contribution_title}</p>
                            ${request.contribution_code ? `<p><strong>Code:</strong> <code>${request.contribution_code}</code></p>` : ''}
                            <p><strong>Description:</strong> ${request.contribution_description}</p>
                            <p><strong>Total Amount:</strong> ₱${parseFloat(request.contribution_amount).toLocaleString('en-US', {minimumFractionDigits: 2})}</p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-primary">Additional Information</h6>
                            <p><strong>Notes:</strong> ${request.notes || 'None'}</p>
                            ${request.admin_notes ? `<p><strong>Admin Notes:</strong> ${request.admin_notes}</p>` : ''}
                            ${request.processed_by_name ? `<p><strong>Processed By:</strong> ${request.processed_by_name}</p>` : ''}
                        </div>
                    </div>
                    ${request.proof_of_payment_path ? `
                    <div class="row mt-3">
                        <div class="col-12">
                            <h6 class="text-primary">Proof of Payment</h6>
                            <div class="text-center">
                                <img src="${request.proof_of_payment_path.startsWith('http') ? request.proof_of_payment_path : '<?= base_url() ?>' + request.proof_of_payment_path}" alt="Proof of Payment" class="img-fluid mb-3" style="max-height: 300px;" onerror="this.src='<?= base_url('assets/img/placeholder-image.png') ?>'; this.onerror=null;">
                                <div>
                                    <a href="${request.proof_of_payment_path.startsWith('http') ? request.proof_of_payment_path : '<?= base_url() ?>' + request.proof_of_payment_path}" download="proof_of_payment_${request.reference_number}.jpg" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-download me-1"></i>Download Proof
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    ` : ''}
                `;
                
                $('#requestDetailsContent').html(content);
                
                // Show/hide action buttons based on status
                $('#approveFromDetailsBtn, #rejectFromDetailsBtn, #processFromDetailsBtn').hide();
                
                if (request.status === 'pending') {
                    $('#approveFromDetailsBtn, #rejectFromDetailsBtn').show();
                } else if (request.status === 'approved') {
                    $('#processFromDetailsBtn').show();
                }
                
                $('#requestDetailsModal').modal('show');
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while fetching request details.');
        });
    }

    function showActionModal(title, message) {
        $('#actionModalLabel').text(title);
        
        // Show admin notes section only for reject action
        if (currentAction === 'reject') {
            // Set message
            $('#actionModalBody').html(`<p>${message}</p>`);
            // Append admin notes section if it doesn't exist or was removed
            if ($('#adminNotesSection').length === 0 || $('#adminNotesSection').parent('#actionModalBody').length === 0) {
                $('#actionModalBody').append(`
                    <div id="adminNotesSection" class="mt-3">
                        <div class="mb-3">
                            <label for="modal_admin_notes" class="form-label">Reason for Rejection <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="modal_admin_notes" name="admin_notes" rows="3" 
                                      placeholder="Please provide a reason for rejecting this payment request..."></textarea>
                            <small class="text-muted">This message will be sent to the payer</small>
                        </div>
                    </div>
                `);
            }
            $('#adminNotesSection').show();
            $('#modal_admin_notes').val('').attr('required', true);
            // Focus on the textarea after modal is shown
            setTimeout(() => {
                $('#modal_admin_notes').focus();
            }, 300);
        } else {
            $('#actionModalBody').html(`<p>${message}</p>`);
            $('#adminNotesSection').hide();
            $('#modal_admin_notes').val('').removeAttr('required');
        }
        
        $('#actionModal').modal('show');
    }

    function executeAction(action, requestId) {
        let url = '';
        let successMessage = '';
        
        switch(action) {
            case 'approve':
                url = '<?= base_url('admin/approve-payment-request') ?>';
                successMessage = 'Payment request approved successfully!';
                break;
            case 'reject':
                url = '<?= base_url('admin/reject-payment-request') ?>';
                successMessage = 'Payment request rejected successfully!';
                break;
            case 'process':
                url = '<?= base_url('admin/process-payment-request') ?>';
                successMessage = 'Payment request processed successfully!';
                break;
        }

        // Get admin notes from modal (required for reject action)
        const adminNotes = $('#modal_admin_notes').val() || '';
        
        // Validate admin notes for reject action
        if (action === 'reject' && !adminNotes.trim()) {
            alert('Please provide a reason for rejecting this payment request.');
            $('#modal_admin_notes').focus();
            return;
        }

        fetch(url, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/x-www-form-urlencoded',
                'Accept': 'application/json'
            },
            body: new URLSearchParams({
                request_id: requestId,
                admin_notes: adminNotes
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(successMessage);
                $('#actionModal').modal('hide');
                $('#requestDetailsModal').modal('hide');
                // Reset admin notes section
                $('#adminNotesSection').hide();
                $('#modal_admin_notes').val('').removeAttr('required');
                location.reload(); // Reload page to show updated data
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while processing the request.');
        });
    }

    function showDeleteModal(requestId) {
        // Get request details for display
        fetch('<?= base_url('admin/get-payment-request-details') ?>?request_id=' + requestId, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const request = data.request;
                currentRequestId = requestId;
                
                const info = `
                    <div class="border p-3 rounded bg-light">
                        <p><strong>Reference:</strong> ${request.reference_number}</p>
                        <p><strong>Payer:</strong> ${request.payer_name}</p>
                        <p><strong>Amount:</strong> ₱${parseFloat(request.requested_amount).toLocaleString('en-US', {minimumFractionDigits: 2})}</p>
                        <p><strong>Status:</strong> ${request.status.charAt(0).toUpperCase() + request.status.slice(1)}</p>
                    </div>
                `;
                
                $('#deleteRequestInfo').html(info);
                $('#deleteModal').modal('show');
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while fetching request details.');
        });
    }

    function deletePaymentRequest(requestId) {
        fetch('<?= base_url('admin/delete-payment-request') ?>', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/x-www-form-urlencoded',
                'Accept': 'application/json'
            },
            body: new URLSearchParams({
                request_id: requestId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Payment request deleted successfully!');
                $('#deleteModal').modal('hide');
                location.reload(); // Reload page to show updated data
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while deleting the request.');
        });
    }
});
</script>
<?= $this->endSection() ?>