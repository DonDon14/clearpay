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
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPayerModal">
                    <i class="fas fa-plus"></i> Add New Payer
                </button>
            </div>
        </div>
        <div class="card-body">
            <!-- Search Bar -->
            <div class="mb-3">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-search"></i>
                            </span>
                            <input type="text" 
                                   class="form-control" 
                                   id="searchPayerInput" 
                                   placeholder="Search by Student ID, Name, Email, or Contact Number..."
                                   autocomplete="off">
                            <button class="btn btn-outline-secondary" type="button" id="clearSearchBtn" style="display: none;">
                                <i class="fas fa-times"></i> Clear
                            </button>
                        </div>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <small class="text-muted" id="searchResultsCount">
                            Showing <?= !empty($payers) ? count($payers) : '0' ?> of <?= !empty($payers) ? count($payers) : '0' ?> payers
                        </small>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-2">
                        <label for="filterCourse" class="form-label small text-muted mb-1">Filter by Course/Department</label>
                        <select class="form-select form-select-sm" id="filterCourse">
                            <option value="">All Courses/Departments</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-2">
                        <label for="sortBy" class="form-label small text-muted mb-1">Sort By</label>
                        <select class="form-select form-select-sm" id="sortBy">
                            <option value="name_asc">Name (A-Z)</option>
                            <option value="name_desc">Name (Z-A)</option>
                            <option value="amount_desc">Payment Total (High to Low)</option>
                            <option value="amount_asc">Payment Total (Low to High)</option>
                            <option value="course_asc">Course/Department (A-Z)</option>
                            <option value="payments_desc">Total Payments (High to Low)</option>
                            <option value="payments_asc">Total Payments (Low to High)</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-2">
                        <label for="filterStatus" class="form-label small text-muted mb-1">Filter by Status</label>
                        <select class="form-select form-select-sm" id="filterStatus">
                            <option value="">All Status</option>
                            <option value="active">Active</option>
                            <option value="pending">Pending</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="table-responsive">
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
                                    data-status="<?= esc($payer['status']) ?>">
                                    <td><strong><?= esc($payer['payer_id']) ?></strong></td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <?php if (!empty($payer['profile_picture']) && trim($payer['profile_picture']) !== ''): ?>
                                                <img src="<?= base_url($payer['profile_picture']) ?>" 
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
                        <tr id="noResultsRow" style="display: none;">
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

<script>
// Search, Filter, and Sort functionality for payers list
(function() {
    'use strict';
    
    const searchInput = document.getElementById('searchPayerInput');
    const clearBtn = document.getElementById('clearSearchBtn');
    const searchResultsCount = document.getElementById('searchResultsCount');
    const paginationInfo = document.getElementById('paginationInfo');
    const noResultsRow = document.getElementById('noResultsRow');
    const filterCourse = document.getElementById('filterCourse');
    const filterStatus = document.getElementById('filterStatus');
    const sortBy = document.getElementById('sortBy');
    const tbody = document.querySelector('tbody');
    const totalPayers = <?= !empty($payers) ? count($payers) : 0 ?>;
    
    if (!searchInput || !tbody) return;
    
    // Populate course/department filter dropdown
    function populateCourseFilter() {
        const courses = new Set();
        document.querySelectorAll('.payer-row').forEach(row => {
            const course = row.getAttribute('data-course')?.trim();
            if (course && course !== '' && course !== 'n/a') {
                courses.add(course);
            }
        });
        
        // Clear existing options except "All"
        while (filterCourse.options.length > 1) {
            filterCourse.remove(1);
        }
        
        // Add unique courses sorted alphabetically
        Array.from(courses).sort().forEach(course => {
            const option = document.createElement('option');
            option.value = course;
            option.textContent = course.charAt(0).toUpperCase() + course.slice(1);
            filterCourse.appendChild(option);
        });
    }
    
    // Get all visible rows
    function getVisibleRows() {
        return Array.from(document.querySelectorAll('.payer-row')).filter(row => {
            return row.style.display !== 'none';
        });
    }
    
    // Function to sort rows
    function sortRows() {
        const sortValue = sortBy.value;
        const rows = getVisibleRows();
        const tbody = document.querySelector('tbody');
        
        rows.sort((a, b) => {
            switch (sortValue) {
                case 'name_asc':
                    return (a.getAttribute('data-payer-name') || '').localeCompare(b.getAttribute('data-payer-name') || '');
                case 'name_desc':
                    return (b.getAttribute('data-payer-name') || '').localeCompare(a.getAttribute('data-payer-name') || '');
                case 'amount_desc':
                    return parseFloat(b.getAttribute('data-total-paid') || 0) - parseFloat(a.getAttribute('data-total-paid') || 0);
                case 'amount_asc':
                    return parseFloat(a.getAttribute('data-total-paid') || 0) - parseFloat(b.getAttribute('data-total-paid') || 0);
                case 'course_asc':
                    const courseA = a.getAttribute('data-course') || '';
                    const courseB = b.getAttribute('data-course') || '';
                    return courseA.localeCompare(courseB);
                case 'payments_desc':
                    return parseInt(b.getAttribute('data-total-payments') || 0) - parseInt(a.getAttribute('data-total-payments') || 0);
                case 'payments_asc':
                    return parseInt(a.getAttribute('data-total-payments') || 0) - parseInt(b.getAttribute('data-total-payments') || 0);
                default:
                    return 0;
            }
        });
        
        // Reorder rows in DOM
        rows.forEach(row => tbody.appendChild(row));
    }
    
    // Function to filter and search payers
    function filterPayers() {
        const searchTerm = searchInput.value.trim().toLowerCase();
        const filterCourseValue = filterCourse.value.toLowerCase();
        const filterStatusValue = filterStatus.value.toLowerCase();
        const payerRows = document.querySelectorAll('.payer-row');
        let visibleCount = 0;
        
        payerRows.forEach(row => {
            const payerId = row.getAttribute('data-payer-id') || '';
            const payerName = row.getAttribute('data-payer-name') || '';
            const email = row.getAttribute('data-email') || '';
            const contact = row.getAttribute('data-contact') || '';
            const course = row.getAttribute('data-course') || '';
            const status = row.getAttribute('data-status') || '';
            
            // Search filter
            const matchesSearch = searchTerm === '' || 
                payerId.includes(searchTerm) || 
                payerName.includes(searchTerm) || 
                email.includes(searchTerm) || 
                contact.includes(searchTerm) ||
                course.includes(searchTerm);
            
            // Course filter
            const matchesCourse = filterCourseValue === '' || course === filterCourseValue;
            
            // Status filter
            const matchesStatus = filterStatusValue === '' || status === filterStatusValue;
            
            if (matchesSearch && matchesCourse && matchesStatus) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });
        
        // Show/hide no results message
        if (visibleCount === 0) {
            noResultsRow.style.display = '';
        } else {
            noResultsRow.style.display = 'none';
        }
        
        // Show/hide clear button
        if (searchTerm !== '' || filterCourseValue !== '' || filterStatusValue !== '') {
            clearBtn.style.display = 'inline-block';
        } else {
            clearBtn.style.display = 'none';
        }
        
        updateCounts(visibleCount);
        sortRows(); // Re-sort after filtering
    }
    
    // Function to update result counts
    function updateCounts(count) {
        if (searchResultsCount) {
            searchResultsCount.textContent = `Showing ${count} of ${totalPayers} payers`;
        }
        if (paginationInfo) {
            paginationInfo.textContent = `Showing ${count > 0 ? '1 to ' + count : '0'} of ${totalPayers} entries`;
        }
    }
    
    // Event listeners
    searchInput.addEventListener('input', filterPayers);
    searchInput.addEventListener('keyup', function(e) {
        if (e.key === 'Escape') {
            searchInput.value = '';
            filterPayers();
        }
    });
    
    clearBtn.addEventListener('click', function() {
        searchInput.value = '';
        filterCourse.value = '';
        filterStatus.value = '';
        filterPayers();
        searchInput.focus();
    });
    
    filterCourse.addEventListener('change', filterPayers);
    filterStatus.addEventListener('change', filterPayers);
    sortBy.addEventListener('change', sortRows);
    
    // Initialize course filter dropdown
    populateCourseFilter();
    
    // Initial sort
    sortRows();
})();

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
        console.error('Error deleting payer:', error);
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
</script>

<?= $this->endSection() ?>