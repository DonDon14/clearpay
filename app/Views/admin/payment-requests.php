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

<!-- Payment Requests Table -->
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-paper-plane me-2"></i>Payment Requests Management</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($paymentRequests)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                            <h5 class="text-muted">No Payment Requests</h5>
                            <p class="text-muted">Payment requests from payers will appear here</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Payer</th>
                                        <th>Contribution</th>
                                        <th>Amount</th>
                                        <th>Method</th>
                                        <th>Status</th>
                                        <th>Reference</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($paymentRequests as $request): ?>
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
                                                        <div class="fw-semibold"><?= esc($request['payer_name']) ?></div>
                                                        <small class="text-muted"><?= esc($request['email_address']) ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <div class="fw-semibold"><?= esc($request['contribution_title']) ?></div>
                                                    <small class="text-muted">₱<?= number_format($request['contribution_amount'], 2) ?></small>
                                                </div>
                                            </td>
                                            <td><strong>₱<?= number_format($request['requested_amount'], 2) ?></strong></td>
                                            <td><?= esc(ucfirst(str_replace('_', ' ', $request['payment_method']))) ?></td>
                                            <td>
                                                <?php
                                                $statusClass = match($request['status']) {
                                                    'pending' => 'bg-warning text-dark',
                                                    'approved' => 'bg-success text-white',
                                                    'rejected' => 'bg-danger text-white',
                                                    'processed' => 'bg-primary text-white',
                                                    default => 'bg-secondary text-white'
                                                };
                                                $statusText = match($request['status']) {
                                                    'pending' => 'PENDING',
                                                    'approved' => 'APPROVED',
                                                    'rejected' => 'REJECTED',
                                                    'processed' => 'PROCESSED',
                                                    default => strtoupper($request['status'])
                                                };
                                                ?>
                                                <span class="badge <?= $statusClass ?>"><?= $statusText ?></span>
                                            </td>
                                            <td><code><?= esc($request['reference_number']) ?></code></td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-info" 
                                                        onclick="viewRequestDetails(<?= $request['id'] ?>)">
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

<!-- Request Details Modal -->
<div class="modal fade" id="requestDetailsModal" tabindex="-1" aria-labelledby="requestDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="requestDetailsModalLabel">Payment Request Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="requestDetailsContent">
                <!-- Content will be loaded dynamically -->
            </div>
            <div class="modal-footer" id="requestDetailsFooter">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-success" id="approveFromDetailsBtn" onclick="approveFromDetails()" style="display: none;">
                    <i class="fas fa-check me-1"></i>Approve
                </button>
                <button type="button" class="btn btn-danger" id="rejectFromDetailsBtn" onclick="rejectFromDetails()" style="display: none;">
                    <i class="fas fa-times me-1"></i>Reject
                </button>
                <button type="button" class="btn btn-primary" id="processFromDetailsBtn" onclick="processFromDetails()" style="display: none;">
                    <i class="fas fa-cog me-1"></i>Process
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Action Modal -->
<div class="modal fade" id="actionModal" tabindex="-1" aria-labelledby="actionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="actionModalLabel">Action Required</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="actionForm">
                    <input type="hidden" id="actionRequestId" name="request_id">
                    <input type="hidden" id="actionType" name="action_type">
                    
                    <div class="mb-3">
                        <label for="adminNotes" class="form-label">Admin Notes</label>
                        <textarea class="form-control" id="adminNotes" name="admin_notes" rows="3" 
                                  placeholder="Add notes about this action..."></textarea>
                    </div>
                    
                    <div class="mb-3" id="confirmationMessage">
                        <!-- Confirmation message will be inserted here -->
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn" id="confirmActionBtn" onclick="confirmAction()">
                    <!-- Button text will be set dynamically -->
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let currentRequestId = null;
let currentActionType = null;

function viewRequestDetails(requestId) {
    fetch(`<?= base_url('admin/get-payment-request-details') ?>?request_id=${requestId}`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const request = data.request;
                const content = `
                    <div class="row g-3">
                        <div class="col-md-6">
                            <h6 class="text-primary">Request Information</h6>
                            <p><strong>Reference:</strong> ${request.reference_number}</p>
                            <p><strong>Date:</strong> ${new Date(request.requested_at).toLocaleString()}</p>
                            <p><strong>Amount:</strong> ₱${parseFloat(request.requested_amount).toFixed(2)}</p>
                            <p><strong>Method:</strong> ${request.payment_method.replace('_', ' ').toUpperCase()}</p>
                            <p><strong>Status:</strong> 
                                <span class="badge ${getStatusClass(request.status)}">${request.status.toUpperCase()}</span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-primary">Payer Information</h6>
                            <p><strong>Name:</strong> ${request.payer_name}</p>
                            <p><strong>Email:</strong> ${request.email_address}</p>
                            <p><strong>Contact:</strong> ${request.contact_number}</p>
                        </div>
                        <div class="col-12">
                            <h6 class="text-primary">Contribution Details</h6>
                            <p><strong>Title:</strong> ${request.contribution_title}</p>
                            <p><strong>Amount:</strong> ₱${parseFloat(request.contribution_amount).toFixed(2)}</p>
                        </div>
                        ${request.notes ? `
                        <div class="col-12">
                            <h6 class="text-primary">Payer Notes</h6>
                            <p>${request.notes}</p>
                        </div>
                        ` : ''}
                        ${request.proof_of_payment_path ? `
                        <div class="col-12">
                            <h6 class="text-primary">Proof of Payment</h6>
                            <div class="text-center">
                                <img src="${request.proof_of_payment_path}" alt="Proof of Payment" class="img-fluid mb-3" style="max-height: 300px;">
                                <div>
                                    <a href="${request.proof_of_payment_path}" download="proof_of_payment_${request.reference_number}.jpg" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-download me-1"></i>Download Proof
                                    </a>
                                </div>
                            </div>
                        </div>
                        ` : ''}
                        ${request.admin_notes ? `
                        <div class="col-12">
                            <h6 class="text-primary">Admin Notes</h6>
                            <p>${request.admin_notes}</p>
                        </div>
                        ` : ''}
                        ${request.processed_at ? `
                        <div class="col-12">
                            <h6 class="text-primary">Processing Information</h6>
                            <p><strong>Processed:</strong> ${new Date(request.processed_at).toLocaleString()}</p>
                            <p><strong>By:</strong> ${request.processed_by_name || 'Admin'}</p>
                        </div>
                        ` : ''}
                    </div>
                `;
                
                document.getElementById('requestDetailsContent').innerHTML = content;
                
                // Show/hide action buttons based on status
                const approveBtn = document.getElementById('approveFromDetailsBtn');
                const rejectBtn = document.getElementById('rejectFromDetailsBtn');
                const processBtn = document.getElementById('processFromDetailsBtn');
                
                // Hide all buttons first
                approveBtn.style.display = 'none';
                rejectBtn.style.display = 'none';
                processBtn.style.display = 'none';
                
                // Show appropriate buttons based on status
                if (request.status === 'pending') {
                    approveBtn.style.display = 'inline-block';
                    rejectBtn.style.display = 'inline-block';
                } else if (request.status === 'approved') {
                    processBtn.style.display = 'inline-block';
                }
                
                // Store current request ID for actions
                currentRequestId = requestId;
                
                new bootstrap.Modal(document.getElementById('requestDetailsModal')).show();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while loading request details.');
        });
}

function approveRequest(requestId) {
    currentRequestId = requestId;
    currentActionType = 'approve';
    
    document.getElementById('actionRequestId').value = requestId;
    document.getElementById('actionType').value = 'approve';
    document.getElementById('actionModalLabel').textContent = 'Approve Payment Request';
    document.getElementById('confirmationMessage').innerHTML = 
        '<div class="alert alert-success"><i class="fas fa-check-circle me-2"></i>Are you sure you want to approve this payment request?</div>';
    document.getElementById('confirmActionBtn').textContent = 'Approve Request';
    document.getElementById('confirmActionBtn').className = 'btn btn-success';
    
    new bootstrap.Modal(document.getElementById('actionModal')).show();
}

function rejectRequest(requestId) {
    currentRequestId = requestId;
    currentActionType = 'reject';
    
    document.getElementById('actionRequestId').value = requestId;
    document.getElementById('actionType').value = 'reject';
    document.getElementById('actionModalLabel').textContent = 'Reject Payment Request';
    document.getElementById('confirmationMessage').innerHTML = 
        '<div class="alert alert-danger"><i class="fas fa-times-circle me-2"></i>Are you sure you want to reject this payment request?</div>';
    document.getElementById('confirmActionBtn').textContent = 'Reject Request';
    document.getElementById('confirmActionBtn').className = 'btn btn-danger';
    
    new bootstrap.Modal(document.getElementById('actionModal')).show();
}

function processRequest(requestId) {
    currentRequestId = requestId;
    currentActionType = 'process';
    
    document.getElementById('actionRequestId').value = requestId;
    document.getElementById('actionType').value = 'process';
    document.getElementById('actionModalLabel').textContent = 'Process Payment Request';
    document.getElementById('confirmationMessage').innerHTML = 
        '<div class="alert alert-primary"><i class="fas fa-cog me-2"></i>This will create an actual payment record and mark the request as processed.</div>';
    document.getElementById('confirmActionBtn').textContent = 'Process Request';
    document.getElementById('confirmActionBtn').className = 'btn btn-primary';
    
    new bootstrap.Modal(document.getElementById('actionModal')).show();
}

function confirmAction() {
    const formData = new FormData(document.getElementById('actionForm'));
    const adminNotes = formData.get('admin_notes');
    
    let url = '';
    if (currentActionType === 'approve') {
        url = '<?= base_url('admin/approve-payment-request') ?>';
    } else if (currentActionType === 'reject') {
        url = '<?= base_url('admin/reject-payment-request') ?>';
    } else if (currentActionType === 'process') {
        url = '<?= base_url('admin/process-payment-request') ?>';
    }
    
    fetch(url, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams(formData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            bootstrap.Modal.getInstance(document.getElementById('actionModal')).hide();
            location.reload(); // Reload to show updated status
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while processing the request.');
    });
}

function getStatusClass(status) {
    const classes = {
        'pending': 'bg-warning text-dark',
        'approved': 'bg-success text-white',
        'rejected': 'bg-danger text-white',
        'processed': 'bg-primary text-white'
    };
    return classes[status] || 'bg-secondary text-white';
}

// Functions to handle actions from details modal
function approveFromDetails() {
    currentActionType = 'approve';
    showActionModal('Approve Payment Request', 'Are you sure you want to approve this payment request? This will create a payment record.');
}

function rejectFromDetails() {
    currentActionType = 'reject';
    showActionModal('Reject Payment Request', 'Are you sure you want to reject this payment request?');
}

function processFromDetails() {
    currentActionType = 'process';
    showActionModal('Process Payment Request', 'Are you sure you want to process this payment request? This will mark it as completed.');
}

function showActionModal(title, message) {
    document.getElementById('actionRequestId').value = currentRequestId;
    document.getElementById('actionType').value = currentActionType;
    document.getElementById('actionModalLabel').textContent = title;
    document.getElementById('confirmationMessage').innerHTML = 
        `<div class="alert alert-${currentActionType === 'approve' ? 'success' : currentActionType === 'reject' ? 'danger' : 'primary'}"><i class="fas fa-${currentActionType === 'approve' ? 'check-circle' : currentActionType === 'reject' ? 'times-circle' : 'cog'} me-2"></i>${message}</div>`;
    document.getElementById('confirmActionBtn').textContent = `${currentActionType.charAt(0).toUpperCase() + currentActionType.slice(1)} Request`;
    document.getElementById('confirmActionBtn').className = `btn btn-${currentActionType === 'approve' ? 'success' : currentActionType === 'reject' ? 'danger' : 'primary'}`;
    
    // Close the details modal first
    bootstrap.Modal.getInstance(document.getElementById('requestDetailsModal')).hide();
    
    // Show the action modal
    new bootstrap.Modal(document.getElementById('actionModal')).show();
}
</script>

<?= $this->endSection() ?>
