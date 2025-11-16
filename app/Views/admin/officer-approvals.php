<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-4">
            <?= view('partials/card', [
                'title' => 'Pending Approvals',
                'text' => number_format($totalPending ?? 0),
                'icon' => 'clock',
                'iconColor' => 'text-warning'
            ]) ?>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <?= view('partials/card', [
                'title' => 'Total Officers',
                'text' => number_format($totalOfficers ?? 0),
                'icon' => 'users',
                'iconColor' => 'text-primary'
            ]) ?>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <?= view('partials/card', [
                'title' => 'Online Officers',
                'text' => number_format($onlineOfficers ?? 0),
                'icon' => 'user-check',
                'iconColor' => 'text-success'
            ]) ?>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <?= view('partials/card', [
                'title' => 'Approved',
                'text' => number_format(($totalOfficers ?? 0) - ($totalPending ?? 0)),
                'icon' => 'check-circle',
                'iconColor' => 'text-info'
            ]) ?>
        </div>
    </div>

    <!-- Pending Approvals Section -->
    <?php if (!empty($pendingOfficers)): ?>
    <div class="card shadow-sm mb-4 border-warning">
        <div class="card-header bg-warning bg-opacity-10 d-flex justify-content-between align-items-center">
            <div>
                <h5 class="card-title mb-0">
                    <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                    Pending Approvals (<?= count($pendingOfficers) ?>)
                </h5>
                <p class="text-muted mb-0 small">Review and approve officer signups</p>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Profile</th>
                            <th>Name</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Registered</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pendingOfficers as $officer): ?>
                            <tr>
                                <td>
                                    <?php if (!empty($officer['profile_picture']) && trim($officer['profile_picture']) !== ''): ?>
                                        <?php 
                                        $officerPicUrl = (strpos($officer['profile_picture'], 'res.cloudinary.com') !== false) 
                                            ? $officer['profile_picture'] 
                                            : base_url($officer['profile_picture']);
                                        ?>
                                        <img src="<?= $officerPicUrl ?>" 
                                             alt="<?= esc($officer['name'] ?? $officer['username']) ?>"
                                             class="rounded-circle"
                                             style="width: 40px; height: 40px; object-fit: cover; border: 2px solid #e9ecef;">
                                    <?php else: ?>
                                        <div class="rounded-circle d-flex align-items-center justify-content-center bg-secondary text-white"
                                             style="width: 40px; height: 40px; flex-shrink: 0;">
                                            <i class="fas fa-user"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td><strong><?= esc($officer['name'] ?? $officer['username']) ?></strong></td>
                                <td><?= esc($officer['username']) ?></td>
                                <td><?= esc($officer['email'] ?? 'N/A') ?></td>
                                <td><?= esc($officer['phone'] ?? 'N/A') ?></td>
                                <td>
                                    <?php if (!empty($officer['created_at'])): ?>
                                        <?= date('M d, Y', strtotime($officer['created_at'])) ?>
                                    <?php else: ?>
                                        N/A
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button type="button" 
                                                class="btn btn-sm btn-success approve-btn" 
                                                data-user-id="<?= $officer['id'] ?>"
                                                data-user-name="<?= esc($officer['name'] ?? $officer['username']) ?>">
                                            <i class="fas fa-check"></i> Approve
                                        </button>
                                        <button type="button" 
                                                class="btn btn-sm btn-danger reject-btn" 
                                                data-user-id="<?= $officer['id'] ?>"
                                                data-user-name="<?= esc($officer['name'] ?? $officer['username']) ?>">
                                            <i class="fas fa-times"></i> Reject
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- All Officers List -->
    <div class="card shadow-sm mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="card-title mb-0">All Officers</h5>
                <p class="text-muted mb-0 small">View all officers and their status</p>
            </div>
        </div>
        <div class="card-body">
            <!-- Search and Filters -->
            <div class="mb-3">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-search"></i>
                            </span>
                            <input type="text" 
                                   class="form-control" 
                                   id="searchOfficerInput" 
                                   placeholder="Search by Name, Username, or Email..."
                                   autocomplete="off">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="row">
                            <div class="col-md-6">
                                <select class="form-select form-select-sm" id="filterStatus">
                                    <option value="">All Status</option>
                                    <option value="pending">Pending</option>
                                    <option value="approved">Approved</option>
                                    <option value="rejected">Rejected</option>
                                    <option value="online">Online</option>
                                    <option value="offline">Offline</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <select class="form-select form-select-sm" id="sortBy">
                                    <option value="date_desc">Newest First</option>
                                    <option value="date_asc">Oldest First</option>
                                    <option value="name_asc">Name (A-Z)</option>
                                    <option value="name_desc">Name (Z-A)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Status</th>
                            <th>Profile</th>
                            <th>Name</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Registered</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($allOfficers)): ?>
                            <?php foreach ($allOfficers as $officer): ?>
                                <tr class="officer-row" 
                                    data-officer-id="<?= $officer['id'] ?>"
                                    data-officer-name="<?= esc(strtolower($officer['name'] ?? $officer['username'])) ?>"
                                    data-username="<?= esc(strtolower($officer['username'])) ?>"
                                    data-email="<?= esc(strtolower($officer['email'] ?? '')) ?>"
                                    data-status="<?= esc($officer['status'] ?? 'approved') ?>"
                                    data-online="<?= $officer['is_online'] ? 'online' : 'offline' ?>">
                                    <td>
                                        <?php 
                                        $status = $officer['status'] ?? 'approved';
                                        if ($status === 'pending'): ?>
                                            <span class="badge bg-warning">
                                                <i class="fas fa-clock"></i> Pending
                                            </span>
                                        <?php elseif ($status === 'rejected'): ?>
                                            <span class="badge bg-danger">
                                                <i class="fas fa-times-circle"></i> Rejected
                                            </span>
                                        <?php elseif ($officer['is_online'] ?? false): ?>
                                            <span class="badge bg-success">
                                                <i class="fas fa-circle" style="font-size: 0.6rem; margin-right: 4px;"></i>Online
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">
                                                <i class="fas fa-circle" style="font-size: 0.6rem; margin-right: 4px; opacity: 0.5;"></i>Offline
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($officer['profile_picture']) && trim($officer['profile_picture']) !== ''): ?>
                                            <?php 
                                            $officerPicUrl = (strpos($officer['profile_picture'], 'res.cloudinary.com') !== false) 
                                                ? $officer['profile_picture'] 
                                                : base_url($officer['profile_picture']);
                                            ?>
                                            <img src="<?= $officerPicUrl ?>" 
                                                 alt="<?= esc($officer['name'] ?? $officer['username']) ?>"
                                                 class="rounded-circle"
                                                 style="width: 40px; height: 40px; object-fit: cover; border: 2px solid #e9ecef;">
                                        <?php else: ?>
                                            <div class="rounded-circle d-flex align-items-center justify-content-center bg-secondary text-white"
                                                 style="width: 40px; height: 40px; flex-shrink: 0;">
                                                <i class="fas fa-user"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td><strong><?= esc($officer['name'] ?? $officer['username']) ?></strong></td>
                                    <td><?= esc($officer['username']) ?></td>
                                    <td><?= esc($officer['email'] ?? 'N/A') ?></td>
                                    <td><?= esc($officer['phone'] ?? 'N/A') ?></td>
                                    <td>
                                        <?php if (!empty($officer['created_at'])): ?>
                                            <?= date('M d, Y', strtotime($officer['created_at'])) ?>
                                        <?php else: ?>
                                            N/A
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (($officer['status'] ?? 'approved') === 'pending'): ?>
                                            <div class="btn-group" role="group">
                                                <button type="button" 
                                                        class="btn btn-sm btn-success approve-btn" 
                                                        data-user-id="<?= $officer['id'] ?>"
                                                        data-user-name="<?= esc($officer['name'] ?? $officer['username']) ?>">
                                                    <i class="fas fa-check"></i> Approve
                                                </button>
                                                <button type="button" 
                                                        class="btn btn-sm btn-danger reject-btn" 
                                                        data-user-id="<?= $officer['id'] ?>"
                                                        data-user-name="<?= esc($officer['name'] ?? $officer['username']) ?>">
                                                    <i class="fas fa-times"></i> Reject
                                                </button>
                                            </div>
                                        <?php elseif (($officer['status'] ?? 'approved') === 'rejected'): ?>
                                            <button type="button" 
                                                    class="btn btn-sm btn-success approve-btn" 
                                                    data-user-id="<?= $officer['id'] ?>"
                                                    data-user-name="<?= esc($officer['name'] ?? $officer['username']) ?>">
                                                <i class="fas fa-check"></i> Approve
                                            </button>
                                        <?php else: ?>
                                            <span class="text-muted small">Approved</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted">No officers found</td>
                            </tr>
                        <?php endif; ?>
                        <tr id="noResultsRow" style="display: none;">
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="fas fa-search fa-2x mb-2 d-block"></i>
                                <p class="mb-0">No officers found matching your search criteria</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Reject Reason Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="rejectModalLabel">Reject Officer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to reject <strong id="rejectOfficerName"></strong>?</p>
                <div class="mb-3">
                    <label for="rejectReason" class="form-label">Reason (optional)</label>
                    <textarea class="form-control" id="rejectReason" rows="3" placeholder="Enter reason for rejection..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmRejectBtn">Reject</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchOfficerInput');
    const filterStatus = document.getElementById('filterStatus');
    const sortBy = document.getElementById('sortBy');
    const noResultsRow = document.getElementById('noResultsRow');
    
    let allOfficerRows = Array.from(document.querySelectorAll('.officer-row'));
    let currentRejectUserId = null;
    
    // Approve button handler
    document.querySelectorAll('.approve-btn').forEach(btn => {
        btn.addEventListener('click', async function() {
            const userId = this.dataset.userId;
            const userName = this.dataset.userName;
            
            if (!confirm(`Are you sure you want to approve ${userName}?`)) {
                return;
            }
            
            const originalText = this.innerHTML;
            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
            
            try {
                const response = await fetch('<?= base_url('admin/officer-approvals/approve') ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `user_id=${userId}`
                });
                
                const result = await response.json();
                
                if (result.success) {
                    alert(result.message || 'Officer approved successfully!');
                    location.reload();
                } else {
                    alert(result.error || 'Failed to approve officer.');
                    this.disabled = false;
                    this.innerHTML = originalText;
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
                this.disabled = false;
                this.innerHTML = originalText;
            }
        });
    });
    
    // Reject button handler
    document.querySelectorAll('.reject-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            currentRejectUserId = this.dataset.userId;
            const userName = this.dataset.userName;
            document.getElementById('rejectOfficerName').textContent = userName;
            const rejectModal = new bootstrap.Modal(document.getElementById('rejectModal'));
            rejectModal.show();
        });
    });
    
    // Confirm reject button
    document.getElementById('confirmRejectBtn').addEventListener('click', async function() {
        if (!currentRejectUserId) return;
        
        const reason = document.getElementById('rejectReason').value;
        const originalText = this.innerHTML;
        this.disabled = true;
        this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
        
        try {
            const response = await fetch('<?= base_url('admin/officer-approvals/reject') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `user_id=${currentRejectUserId}&reason=${encodeURIComponent(reason)}`
            });
            
            const result = await response.json();
            
            if (result.success) {
                bootstrap.Modal.getInstance(document.getElementById('rejectModal')).hide();
                alert(result.message || 'Officer rejected successfully!');
                location.reload();
            } else {
                alert(result.error || 'Failed to reject officer.');
                this.disabled = false;
                this.innerHTML = originalText;
            }
        } catch (error) {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
            this.disabled = false;
            this.innerHTML = originalText;
        }
    });
    
    // Filter and search functionality
    function filterAndSort() {
        const searchTerm = searchInput.value.toLowerCase().trim();
        const statusFilter = filterStatus.value;
        const sortValue = sortBy.value;
        
        let visibleRows = allOfficerRows.filter(row => {
            const name = row.dataset.officerName || '';
            const username = row.dataset.username || '';
            const email = row.dataset.email || '';
            const status = row.dataset.status || '';
            const online = row.dataset.online || '';
            
            const matchesSearch = !searchTerm || 
                name.includes(searchTerm) || 
                username.includes(searchTerm) || 
                email.includes(searchTerm);
            
            let matchesStatus = true;
            if (statusFilter) {
                if (statusFilter === 'online' || statusFilter === 'offline') {
                    matchesStatus = online === statusFilter;
                } else {
                    matchesStatus = status === statusFilter;
                }
            }
            
            return matchesSearch && matchesStatus;
        });
        
        // Hide all rows
        allOfficerRows.forEach(row => row.style.display = 'none');
        
        // Sort
        if (sortValue === 'date_desc') {
            visibleRows.sort((a, b) => {
                // Sort by data attributes if available, otherwise keep order
                return 0;
            });
        } else if (sortValue === 'date_asc') {
            visibleRows.reverse();
        } else if (sortValue === 'name_asc') {
            visibleRows.sort((a, b) => {
                const aName = a.dataset.officerName || '';
                const bName = b.dataset.officerName || '';
                return aName.localeCompare(bName);
            });
        } else if (sortValue === 'name_desc') {
            visibleRows.sort((a, b) => {
                const aName = a.dataset.officerName || '';
                const bName = b.dataset.officerName || '';
                return bName.localeCompare(aName);
            });
        }
        
        // Show visible rows
        visibleRows.forEach(row => row.style.display = '');
        
        // Show/hide no results
        noResultsRow.style.display = visibleRows.length === 0 ? '' : 'none';
    }
    
    searchInput.addEventListener('input', filterAndSort);
    filterStatus.addEventListener('change', filterAndSort);
    sortBy.addEventListener('change', filterAndSort);
    
    filterAndSort();
});
</script>

<?= $this->endSection() ?>

