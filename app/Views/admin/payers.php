<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<link rel="stylesheet" href="<?= base_url('css/dashboard.css') ?>" />
<div class="container-fluid ui-page-shell">
    <div class="ui-page-intro">
        <div>
            <h6>Payers</h6>
            <p>Browse registered payers, filter by status or course, and jump into exports or account management quickly.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#importPayersCsvModal">
                <i class="fas fa-file-import"></i> Import CSV
            </button>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPayerModal">
                <i class="fas fa-plus"></i> Add New Payer
            </button>
        </div>
    </div>
    <!-- Statistics Cards -->
    <div class="row mb-4 ui-stats-row">
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
    <div class="card shadow-sm mb-4 ui-data-shell">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="ui-section-title">Payers</h5>
                <p class="ui-section-subtitle mb-0">Complete list of all registered payers</p>
            </div>
            <div class="d-flex gap-2">
                <div class="btn-group">
                    <button type="button" class="btn btn-success dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-file-export me-1"></i>Export
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#" onclick="exportPayers('csv'); return false;">
                            <i class="fas fa-file-csv me-2"></i>Export to CSV
                        </a></li>
                        <li><a class="dropdown-item" href="#" onclick="exportPayers('pdf'); return false;">
                            <i class="fas fa-file-pdf me-2"></i>Export to PDF
                        </a></li>
                    </ul>
                </div>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPayerModal">
                    <i class="fas fa-plus"></i> Add New Payer
                </button>
                <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#importPayersCsvModal">
                    <i class="fas fa-file-import"></i> Import CSV
                </button>
            </div>
        </div>
        <div class="card-body">
            <?php
            $payerCourses = [];
            foreach (($payers ?? []) as $payerForCourse) {
                $course = trim((string) ($payerForCourse['course_department'] ?? ''));
                if ($course !== '') {
                    $payerCourses[strtolower($course)] = $course;
                }
            }
            asort($payerCourses);
            ?>
            <?= view('partials/list-controls', [
                'controlId' => 'payerControls',
                'searchId' => 'searchPayerInput',
                'searchLabel' => 'Search payers',
                'placeholder' => 'ID, name, email, contact...',
                'resultId' => 'searchResultsCount',
                'chipsId' => 'payerFilterChips',
                'clearId' => 'clearSearchBtn',
                'filters' => [
                    [
                        'id' => 'filterCourse',
                        'label' => 'Course',
                        'options' => ['' => 'All courses'] + $payerCourses,
                    ],
                    [
                        'id' => 'filterStatus',
                        'label' => 'Status',
                        'options' => ['' => 'All statuses', 'active' => 'Active', 'pending' => 'Pending', 'inactive' => 'Inactive'],
                    ],
                ],
                'sort' => [
                    'id' => 'sortBy',
                    'label' => 'Sort',
                    'options' => [
                        'name_asc' => 'Name A-Z',
                        'name_desc' => 'Name Z-A',
                        'amount_desc' => 'Amount high-low',
                        'amount_asc' => 'Amount low-high',
                        'course_asc' => 'Course A-Z',
                        'payments_desc' => 'Payments high-low',
                        'payments_asc' => 'Payments low-high',
                    ],
                ],
            ]) ?>
            
            <div class="table-responsive ui-table-wrap">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Payer ID</th>
                            <th>Payer Name</th>
                            <th>Course/Department</th>
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
                                <tr class="payer-row" 
                                    data-payer-id="<?= esc(strtolower($payer['payer_id'])) ?>"
                                    data-payer-db-id="<?= $payer['id'] ?>"
                                    data-payer-name="<?= esc(strtolower($payer['payer_name'])) ?>"
                                    data-email="<?= esc(strtolower($payer['email_address'] ?? '')) ?>"
                                    data-contact="<?= esc($payer['contact_number'] ?? '') ?>"
                                    data-course="<?= esc(strtolower($payer['course_department'] ?? '')) ?>"
                                    data-total-paid="<?= $payer['total_paid'] ?>"
                                    data-total-payments="<?= $payer['total_payments'] ?>"
                                    data-status="<?= esc($payer['status']) ?>"
                                    data-search="<?= esc(strtolower(trim(($payer['payer_id'] ?? '') . ' ' . ($payer['payer_name'] ?? '') . ' ' . ($payer['email_address'] ?? '') . ' ' . ($payer['contact_number'] ?? '') . ' ' . ($payer['course_department'] ?? '') . ' ' . ($payer['status'] ?? '')))) ?>">
                                    <td><strong><?= esc($payer['payer_id']) ?></strong></td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <?php if (!empty($payer['profile_picture']) && trim($payer['profile_picture']) !== ''): ?>
                                                <?php 
                                                // Check if it's a Cloudinary URL (full URL) or local path
                                                $payerPicUrl = (strpos($payer['profile_picture'], 'res.cloudinary.com') !== false) 
                                                    ? $payer['profile_picture'] 
                                                    : base_url($payer['profile_picture']);
                                                ?>
                                                <img src="<?= $payerPicUrl ?>" 
                                                     alt="<?= esc($payer['payer_name']) ?>"
                                                     class="rounded-circle"
                                                     style="width: 40px; height: 40px; object-fit: cover; border: 2px solid #e9ecef;">
                                            <?php else: ?>
                                                <div class="rounded-circle d-flex align-items-center justify-content-center bg-secondary text-white"
                                                     style="width: 40px; height: 40px; flex-shrink: 0;">
                                                    <i class="fas fa-user"></i>
                                                </div>
                                            <?php endif; ?>
                                            <span><?= esc($payer['payer_name']) ?></span>
                                        </div>
                                    </td>
                                    <td><?= esc($payer['course_department'] ?? 'N/A') ?></td>
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
                                            <button type="button" class="btn btn-outline-danger delete-payer-btn" 
                                                    title="Delete Payer" 
                                                    data-payer-id="<?= $payer['id'] ?>"
                                                    data-payer-name="<?= esc($payer['payer_name']) ?>"
                                                    data-payment-count="<?= $payer['total_payments'] ?>">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="text-center text-muted">No payers found</td>
                            </tr>
                        <?php endif; ?>
                        <!-- No Results Row (hidden by default) -->
                        <tr id="noResultsRow" class="d-none">
                            <td colspan="9" class="text-center text-muted py-4">
                                <i class="fas fa-search fa-2x mb-2 d-block"></i>
                                <p class="mb-0">No payers found matching your search criteria</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div class="text-muted" id="paginationInfo">
                    Showing <?= !empty($payers) ? '1 to ' . count($payers) . ' of ' . count($payers) : '0' ?> entries
                </div>
            </div>
        </div>
    </div>

<!-- Include Modals -->
<?= view('partials/modal-add-payer') ?>
<?= view('partials/modal-view-payer-details') ?>
<?= view('partials/modal-edit-payer') ?>
<?= view('partials/modal-qr-receipt') ?>

<!-- Delete Payer Confirmation Modal -->
<div class="modal fade" id="deletePayerModal" tabindex="-1" aria-labelledby="deletePayerModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deletePayerModalLabel">
                    <i class="fas fa-exclamation-triangle me-2"></i>Delete Payer
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
                <div class="modal-body">
                <div class="alert alert-warning" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Warning:</strong> This action cannot be undone!
                    </div>
                <p id="deletePayerMessage"></p>
                <div id="deletePayerWarning" class="alert alert-danger mt-3" style="display: none;">
                    <i class="fas fa-ban me-2"></i>
                    <strong>Cannot Delete:</strong> This payer has <span id="paymentCount"></span> payment(s) associated with them. Please remove or reassign payments before deleting this payer.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeletePayerBtn" onclick="deletePayer()">
                    <i class="fas fa-trash me-2"></i>Delete Payer
                    </button>
                </div>
        </div>
    </div>
</div>

<div class="modal fade" id="importPayersCsvModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Import Payers from CSV</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="small text-muted mb-2">Required columns: <code>payer_id,payer_name,email_address,contact_number</code></p>
                <p class="small text-muted">Optional column: <code>course_department</code></p>
                <input type="file" class="form-control" id="payersCsvFile" accept=".csv">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="importPayersCsvBtn" onclick="importPayersCsv()">Import</button>
            </div>
        </div>
    </div>
</div>

<script src="<?= base_url('js/list-controls.js') ?>"></script>
<script>
// Search, Filter, and Sort functionality for payers list
(function() {
    'use strict';

    const paginationInfo = document.getElementById('paginationInfo');
    window.payerListControls = createListControls({
        searchId: 'searchPayerInput',
        resultId: 'searchResultsCount',
        chipsId: 'payerFilterChips',
        clearId: 'clearSearchBtn',
        itemSelector: '.payer-row',
        containerSelector: 'tbody',
        emptySelector: '#noResultsRow',
        label: 'payers',
        filters: [
            { id: 'filterCourse', key: 'course', label: 'Course', attribute: 'data-course' },
            { id: 'filterStatus', key: 'status', label: 'Status', attribute: 'data-status' },
        ],
        sort: {
            id: 'sortBy',
            options: {
                name_asc: { attribute: 'data-payer-name', direction: 'asc' },
                name_desc: { attribute: 'data-payer-name', direction: 'desc' },
                amount_desc: { attribute: 'data-total-paid', direction: 'desc', type: 'number' },
                amount_asc: { attribute: 'data-total-paid', direction: 'asc', type: 'number' },
                course_asc: { attribute: 'data-course', direction: 'asc' },
                payments_desc: { attribute: 'data-total-payments', direction: 'desc', type: 'number' },
                payments_asc: { attribute: 'data-total-payments', direction: 'asc', type: 'number' },
            },
        },
        onAfterApply: function({ visible, total }) {
        if (paginationInfo) {
                paginationInfo.textContent = `Showing ${visible > 0 ? '1 to ' + visible : '0'} of ${total} entries`;
        }
        },
    });
})();

(function focusPayerFromQueryParam() {
    const params = new URLSearchParams(window.location.search || '');
    const focusPayerId = parseInt(params.get('focus_payer') || '0', 10);
    if (!focusPayerId) return;

    const searchInput = document.getElementById('searchPayerInput');
    const rowSelector = `tr[data-payer-db-id="${focusPayerId}"]`;

    const highlightRow = () => {
        const row = document.querySelector(rowSelector);
        if (!row) return;
        row.style.outline = '2px solid #0d6efd';
        row.style.boxShadow = '0 0 0 0.25rem rgba(13, 110, 253, 0.2)';
        row.scrollIntoView({ behavior: 'smooth', block: 'center' });
        setTimeout(() => {
            row.style.outline = '';
            row.style.boxShadow = '';
        }, 4500);
    };

    const row = document.querySelector(rowSelector);
    if (row) {
        highlightRow();
        return;
    }

    if (searchInput) {
        searchInput.value = String(focusPayerId);
        searchInput.dispatchEvent(new Event('input', { bubbles: true }));
        setTimeout(highlightRow, 200);
    }
})();

function viewPayerDetails(payerId) {
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
                document.getElementById('viewPayerCourse').textContent = payer.course_department || 'N/A';
                
                // Populate profile picture
                const profilePicture = document.getElementById('viewPayerProfilePicture');
                const profileIcon = document.getElementById('viewPayerProfileIcon');
                
                if (payer.profile_picture && payer.profile_picture.trim() !== '') {
                    // Ensure path doesn't already start with http or base_url
                    let profilePath = payer.profile_picture;
                    if (!profilePath.startsWith('http') && !profilePath.startsWith('<?= base_url() ?>')) {
                        profilePath = `<?= base_url() ?>${profilePath}`;
                    }
                    profilePicture.src = profilePath;
                    profilePicture.style.display = 'block';
                    profileIcon.style.display = 'none';
                    profilePicture.onerror = function() {
                        this.style.display = 'none';
                        profileIcon.style.display = 'block';
                        this.onerror = null;
                    };
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
            showNotification('Error loading payer details', 'error');
        });
}

function editPayer(payerId) {
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
                document.getElementById('editCourseDepartment').value = payer.course_department || '';
                
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
        showNotification('Error updating payer', 'error');
    });
}

function exportPayerPDF(payerId) {
    // Show loading notification
    showNotification('Generating PDF...', 'info');
    
    // Redirect to PDF export endpoint
    window.location.href = `<?= base_url('payers/export-pdf/') ?>${payerId}`;
}

function viewPaymentReceiptFromPayer(paymentId) {
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

// Delete Payer Functions
let currentPayerIdToDelete = null;

// Add event listeners to delete buttons
document.addEventListener('DOMContentLoaded', function() {
    // Use event delegation for dynamically loaded content
    document.addEventListener('click', function(e) {
        if (e.target.closest('.delete-payer-btn')) {
            e.preventDefault();
            e.stopPropagation();
            const btn = e.target.closest('.delete-payer-btn');
            const payerId = btn.getAttribute('data-payer-id');
            const payerName = btn.getAttribute('data-payer-name');
            const paymentCount = parseInt(btn.getAttribute('data-payment-count') || '0');
            confirmDeletePayer(payerId, payerName, paymentCount);
        }
    });
});

function confirmDeletePayer(payerId, payerName, paymentCount) {
    currentPayerIdToDelete = payerId;
    const deleteModal = new bootstrap.Modal(document.getElementById('deletePayerModal'));
    const messageEl = document.getElementById('deletePayerMessage');
    const warningEl = document.getElementById('deletePayerWarning');
    const paymentCountEl = document.getElementById('paymentCount');
    const confirmBtn = document.getElementById('confirmDeletePayerBtn');
    
    // Set message
    messageEl.textContent = `Are you sure you want to delete "${payerName}"?`;
    
    // Show warning if payer has payments
    if (paymentCount > 0) {
        warningEl.style.display = 'block';
        paymentCountEl.textContent = paymentCount;
        confirmBtn.disabled = true;
        confirmBtn.innerHTML = '<i class="fas fa-ban me-2"></i>Cannot Delete';
    } else {
        warningEl.style.display = 'none';
        confirmBtn.disabled = false;
        confirmBtn.innerHTML = '<i class="fas fa-trash me-2"></i>Delete Payer';
    }
    
    deleteModal.show();
}

function deletePayer() {
    if (!currentPayerIdToDelete) return;
    
    const confirmBtn = document.getElementById('confirmDeletePayerBtn');
    const originalText = confirmBtn.innerHTML;
    confirmBtn.disabled = true;
    confirmBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Deleting...';
    
    fetch(`<?= base_url('payers/delete/') ?>${currentPayerIdToDelete}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Close modal
            const deleteModal = bootstrap.Modal.getInstance(document.getElementById('deletePayerModal'));
            deleteModal.hide();
            
            // Show success notification
            if (typeof window.showNotification === 'function') {
                window.showNotification(data.message || 'Payer deleted successfully', 'success');
            } else {
                showNotification(data.message || 'Payer deleted successfully', 'success');
            }
            
            // Remove the row from table
            const row = document.querySelector(`tr[data-payer-db-id="${currentPayerIdToDelete}"]`);
            if (row) {
                row.style.transition = 'opacity 0.3s';
                row.style.opacity = '0';
                setTimeout(() => {
                    row.remove();
                    // Update counts
                    const visibleRows = document.querySelectorAll('.payer-row:not([style*="display: none"])');
                    if (visibleRows.length === 0) {
                        document.getElementById('noResultsRow').style.display = '';
                    }
                }, 300);
            }
            
            // Reload page after a short delay to refresh the list
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            // Show error notification
            if (typeof window.showNotification === 'function') {
                window.showNotification(data.message || 'Failed to delete payer', 'error');
            } else {
                showNotification(data.message || 'Failed to delete payer', 'error');
            }
            
            confirmBtn.disabled = false;
            confirmBtn.innerHTML = originalText;
        }
    })
    .catch(error => {
        if (typeof window.showNotification === 'function') {
            window.showNotification('An error occurred while deleting the payer', 'error');
        } else {
            showNotification('An error occurred while deleting the payer', 'error');
        }
        confirmBtn.disabled = false;
        confirmBtn.innerHTML = originalText;
    });
}

// Export payers function
function exportPayers(format) {
    // Get current filter values
    const searchTerm = document.getElementById('searchPayerInput').value;
    const courseFilter = document.getElementById('filterCourse').value;
    const statusFilter = document.getElementById('filterStatus').value;
    
    // Build query string with filters
    const params = new URLSearchParams();
    if (searchTerm) params.append('search', searchTerm);
    if (courseFilter && courseFilter !== 'all') params.append('course', courseFilter);
    if (statusFilter && statusFilter !== 'all') params.append('status', statusFilter);
    
    // Build URL based on format
    const baseUrl = window.APP_BASE_URL || '';
    let url = '';
    
    if (format === 'csv') {
        url = `${baseUrl}/payers/export/csv`;
    } else if (format === 'pdf') {
        url = `${baseUrl}/payers/export/pdf`;
    } else {
        showNotification('Invalid export format', 'error');
        return;
    }
    
    // Add query parameters if any
    if (params.toString()) {
        url += '?' + params.toString();
    }
    
    // Redirect to export endpoint
    window.location.href = url;
}

function importPayersCsv() {
    const fileInput = document.getElementById('payersCsvFile');
    const button = document.getElementById('importPayersCsvBtn');
    if (!fileInput || !fileInput.files || fileInput.files.length === 0) {
        showNotification('Please choose a CSV file first.', 'error');
        return;
    }

    const formData = new FormData();
    formData.append('csv_file', fileInput.files[0]);
    button.disabled = true;
    const originalText = button.innerHTML;
    button.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Importing...';

    fetch(`<?= base_url('payers/import/csv') ?>`, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message || 'CSV import complete.', 'success');
            setTimeout(() => window.location.reload(), 1200);
            return;
        }
        showNotification(data.message || 'CSV import failed.', 'error');
    })
    .catch(() => showNotification('CSV import failed.', 'error'))
    .finally(() => {
        button.disabled = false;
        button.innerHTML = originalText;
    });
}
</script>

<?= $this->endSection() ?>
