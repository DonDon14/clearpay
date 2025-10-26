<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPayerModal">
                        <i class="fas fa-plus"></i> Add New Payer
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-4">
            <?= view('partials/card', [
                'title' => 'Total Payers',
                'text' => number_format($payerStats['total_payers']),
                'icon' => 'users',
                'iconColor' => 'text-primary'
            ]) ?>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <?= view('partials/card', [
                'title' => 'Active Payers',
                'text' => number_format($payerStats['active_payers']),
                'icon' => 'user-check',
                'iconColor' => 'text-success'
            ]) ?>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <?= view('partials/card', [
                'title' => 'Total Amount',
                'text' => '₱' . number_format($payerStats['total_amount'], 2),
                'icon' => 'peso-sign',
                'iconColor' => 'text-warning'
            ]) ?>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <?= view('partials/card', [
                'title' => 'Average per Student',
                'text' => '₱' . number_format($payerStats['avg_payment_per_student'], 2),
                'icon' => 'calculator',
                'iconColor' => 'text-info'
            ]) ?>
        </div>
    </div>

    <!-- Payers List -->
    <div class="card shadow-sm mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">Student Payers</h5>
            <p class="text-muted mb-0 small">Complete list of all registered payers</p>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Student ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Total Payments</th>
                            <th>Total Amount</th>
                            <th>Last Payment</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($payers)): ?>
                            <?php foreach ($payers as $payer): ?>
                                <?php 
                                    $statusBadge = match($payer['status']) {
                                        'active' => '<span class="badge bg-success">Active</span>',
                                        'pending' => '<span class="badge bg-warning">Pending</span>',
                                        'inactive' => '<span class="badge bg-secondary">Inactive</span>',
                                        default => '<span class="badge bg-light text-dark">Unknown</span>'
                                    };
                                ?>
                                <tr>
                                    <td><strong><?= esc($payer['payer_id']) ?></strong></td>
                                    <td><?= esc($payer['payer_name']) ?></td>
                                    <td><?= esc($payer['email_address'] ?? 'N/A') ?></td>
                                    <td><?= number_format($payer['total_payments']) ?></td>
                                    <td>₱<?= number_format($payer['total_paid'], 2) ?></td>
                                    <td><?= $payer['last_payment'] ? date('M j, Y', strtotime($payer['last_payment'])) : 'Never' ?></td>
                                    <td><?= $statusBadge ?></td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-primary" title="View Details" onclick="viewPayerDetails(<?= $payer['id'] ?>)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-outline-success" title="Edit" onclick="editPayer(<?= $payer['id'] ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-outline-info" title="Export PDF" onclick="exportPayerPDF(<?= $payer['id'] ?>)">
                                                <i class="fas fa-file-pdf"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted">No payers found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div class="text-muted">
                    Showing <?= !empty($payers) ? '1 to ' . count($payers) . ' of ' . count($payers) : '0' ?> entries
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 col-sm-6 mb-3">
                            <button class="btn btn-outline-primary w-100">
                                <i class="fas fa-user-plus mb-2"></i><br>
                                Add New Payer
                            </button>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3">
                            <button class="btn btn-outline-success w-100">
                                <i class="fas fa-file-export mb-2"></i><br>
                                Export All Data
                            </button>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3">
                            <button class="btn btn-outline-info w-100">
                                <i class="fas fa-chart-bar mb-2"></i><br>
                                Generate Report
                            </button>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3">
                            <button class="btn btn-outline-warning w-100">
                                <i class="fas fa-envelope mb-2"></i><br>
                                Send Reminders
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Payer Modal -->
<div class="modal fade" id="addPayerModal" tabindex="-1" aria-labelledby="addPayerModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addPayerModalLabel">
                    <i class="fas fa-user-plus me-2"></i>Add New Payer
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addPayerForm" onsubmit="savePayer(event)">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="payer_id" class="form-label">Student ID <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="payer_id" name="payer_id" required>
                    </div>
                    <div class="mb-3">
                        <label for="payer_name" class="form-label">Full Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="payer_name" name="payer_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="contact_number" class="form-label">Contact Number</label>
                        <input type="tel" class="form-control" id="contact_number" name="contact_number">
                    </div>
                    <div class="mb-3">
                        <label for="email_address" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="email_address" name="email_address">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Save Payer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function savePayer(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    
    fetch('<?= base_url('payers/save') ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Payer added successfully!', 'success');
            const modal = bootstrap.Modal.getInstance(document.getElementById('addPayerModal'));
            modal.hide();
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showNotification(data.message || 'Error adding payer', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error adding payer', 'error');
    });
}

function viewPayerDetails(payerId) {
    console.log('View payer details:', payerId);
    showNotification('View payer details feature coming soon', 'info');
}

function editPayer(payerId) {
    console.log('Edit payer:', payerId);
    showNotification('Edit payer feature coming soon', 'info');
}

function exportPayerPDF(payerId) {
    console.log('Export payer PDF:', payerId);
    showNotification('Export PDF feature coming soon', 'info');
}

// Helper function for notifications
function showNotification(message, type) {
    // Using bootstrap toast or alert
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type === 'success' ? 'success' : type === 'error' ? 'danger' : 'info'} alert-dismissible fade show position-fixed top-0 end-0 m-3`;
    alertDiv.style.zIndex = '9999';
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.body.appendChild(alertDiv);
    setTimeout(() => {
        alertDiv.remove();
    }, 3000);
}
</script>

<?= $this->endSection() ?>