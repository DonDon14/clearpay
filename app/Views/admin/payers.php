<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
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
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="card-title mb-0">Payers</h5>
                <p class="text-muted mb-0 small">Complete list of all registered payers</p>
            </div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPayerModal">
                <i class="fas fa-plus"></i> Add New Payer
            </button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Payer ID</th>
                            <th>Payer Name</th>
                            <th>Email</th>
                            <th>Contact Number</th>
                            <th>Total Payments</th>
                            <th>Total Amount</th>
                            <th>Last Payment</th>
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
                                    <td><?= esc($payer['contact_number'] ?? 'N/A') ?></td>
                                    <td><?= number_format($payer['total_payments']) ?></td>
                                    <td>₱<?= number_format($payer['total_paid'], 2) ?></td>
                                    <td><?= $payer['last_payment'] ? date('M j, Y', strtotime($payer['last_payment'])) : 'Never' ?></td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-primary" title="View Details" onclick="viewPayerDetails(<?= $payer['id'] ?>)">
                                                <i class="fas fa-eye"></i>
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

<!-- Include Modals -->
<?= view('partials/modal-view-payer-details') ?>
<?= view('partials/modal-edit-payer') ?>
<?= view('partials/modal-qr-receipt') ?>

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
    
    // Fetch payer details and payment history
    fetch(`<?= base_url('payers/get-details/') ?>${payerId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const payer = data.payer;
                const payments = data.payments || [];
                
                // Populate payer information
                document.getElementById('viewPayerId').textContent = payer.payer_id || '-';
                document.getElementById('viewPayerName').textContent = payer.payer_name || '-';
                document.getElementById('viewPayerEmail').textContent = payer.email_address || 'N/A';
                document.getElementById('viewPayerContact').textContent = payer.contact_number || 'N/A';
                
                // Populate profile picture
                const profilePicture = document.getElementById('viewPayerProfilePicture');
                const profileIcon = document.getElementById('viewPayerProfileIcon');
                
                if (payer.profile_picture && payer.profile_picture.trim() !== '') {
                    profilePicture.src = `<?= base_url() ?>${payer.profile_picture}`;
                    profilePicture.style.display = 'block';
                    profileIcon.style.display = 'none';
                } else {
                    profilePicture.style.display = 'none';
                    profileIcon.style.display = 'block';
                }
                
                // Calculate and display totals
                const totalPaid = payments.reduce((sum, p) => sum + parseFloat(p.amount_paid || 0), 0);
                const totalPayments = payments.length;
                const lastPayment = payments.length > 0 ? payments[0].payment_date : null;
                
                document.getElementById('viewTotalPaid').textContent = '₱' + totalPaid.toFixed(2);
                document.getElementById('viewTotalPayments').textContent = totalPayments;
                document.getElementById('viewLastPayment').textContent = lastPayment 
                    ? new Date(lastPayment).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
                    : 'Never';
                
                // Populate payment history
                const historyTbody = document.getElementById('viewPaymentHistory');
                if (payments.length > 0) {
                    historyTbody.innerHTML = payments.map((payment, index) => {
                        const status = payment.computed_status || payment.payment_status || 'unknown';
                        const statusBadge = status === 'fully paid' 
                            ? '<span class="badge bg-primary">Completed</span>'
                            : status === 'partial'
                            ? '<span class="badge bg-warning text-dark">Partial</span>'
                            : '<span class="badge bg-secondary">Unpaid</span>';
                        
                        return `
                            <tr style="cursor: pointer;" onclick="viewPaymentReceiptFromPayer(${payment.id})" 
                                title="Click to view receipt" onmouseover="this.style.backgroundColor='#f8f9fa'" 
                                onmouseout="this.style.backgroundColor=''">
                                <td>${new Date(payment.payment_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}</td>
                                <td>${payment.contribution_title || 'N/A'}</td>
                                <td>₱${parseFloat(payment.amount_paid).toFixed(2)}</td>
                                <td>${payment.payment_method || 'N/A'}</td>
                                <td>${statusBadge}</td>
                            </tr>
                        `;
                    }).join('');
                } else {
                    historyTbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-4">No payment records found</td></tr>';
                }
                
                // Store payer ID for edit button
                document.getElementById('editPayerFromViewBtn').setAttribute('onclick', `editPayer(${payerId})`);
            
                
                // Show modal
                const modal = new bootstrap.Modal(document.getElementById('viewPayerDetailsModal'));
                modal.show();
            } else {
                showNotification(data.message || 'Error loading payer details', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error loading payer details', 'error');
        });
}

function editPayer(payerId) {
    console.log('Edit payer:', payerId);
    
    // Fetch payer details
    fetch(`<?= base_url('payers/get/') ?>${payerId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.payer) {
                const payer = data.payer;
                
                // Populate form fields
                document.getElementById('editPayerId').value = payer.id;
                document.getElementById('editPayerIdField').value = payer.payer_id || '';
                document.getElementById('editPayerName').value = payer.payer_name || '';
                document.getElementById('editContactNumber').value = payer.contact_number || '';
                document.getElementById('editEmailAddress').value = payer.email_address || '';
                
                // Show modal
                const viewModal = bootstrap.Modal.getInstance(document.getElementById('viewPayerDetailsModal'));
                if (viewModal) {
                    viewModal.hide();
                }
                
                const editModal = new bootstrap.Modal(document.getElementById('editPayerModal'));
                editModal.show();
            } else {
                showNotification(data.message || 'Error loading payer information', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error loading payer information', 'error');
        });
}

function saveEditedPayer(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    const payerId = document.getElementById('editPayerId').value;
    
    fetch(`<?= base_url('payers/update/') ?>${payerId}`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Payer updated successfully!', 'success');
            const modal = bootstrap.Modal.getInstance(document.getElementById('editPayerModal'));
            modal.hide();
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showNotification(data.message || 'Error updating payer', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error updating payer', 'error');
    });
}

function exportPayerPDF(payerId) {
    console.log('Export payer PDF:', payerId);
    
    // Show loading notification
    showNotification('Generating PDF...', 'info');
    
    // Redirect to PDF export endpoint
    window.location.href = `<?= base_url('payers/export-pdf/') ?>${payerId}`;
}

function viewPaymentReceiptFromPayer(paymentId) {
    console.log('View payment receipt from payer:', paymentId);
    
    // Check if viewPaymentReceipt function exists (from payments page or modal)
    if (typeof window.viewPaymentReceipt !== 'undefined') {
        // Call the existing function if available
        window.viewPaymentReceipt(paymentId);
    } else {
        // Fetch payment data and show QR receipt
        fetch(`<?= base_url('payments/get-details/') ?>${paymentId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.payment) {
                    // Check if showQRReceipt function exists
                    if (typeof window.showQRReceipt !== 'undefined') {
                        window.showQRReceipt(data.payment);
                    } else {
                        showNotification('QR Receipt functionality not available', 'error');
                    }
                } else {
                    showNotification('Error loading payment details', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Error loading payment details', 'error');
            });
    }
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