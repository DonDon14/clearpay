<?= $this->extend('layouts/super-admin') ?>

<?= $this->section('content') ?>
<?php
$renderOfficerAvatar = static function (array $officer): string {
    if (!empty($officer['profile_picture']) && trim((string) $officer['profile_picture']) !== '') {
        $officerPicUrl = (strpos((string) $officer['profile_picture'], 'res.cloudinary.com') !== false)
            ? $officer['profile_picture']
            : base_url($officer['profile_picture']);

        return '<img src="' . esc($officerPicUrl) . '" alt="' . esc($officer['name'] ?? $officer['username']) . '" class="rounded-circle ui-avatar-40">';
    }

    return '<div class="rounded-circle d-flex align-items-center justify-content-center bg-secondary text-white ui-avatar-fallback-40"><i class="fas fa-user"></i></div>';
};
?>
<div class="container-fluid ui-page-shell">

    <div class="ui-page-intro">
        <div>
            <h6>Super Admin Operations</h6>
            <p>Review officer onboarding, monitor account quality, and manage access from a single control surface.</p>
        </div>
        <div class="ui-actions-stack">
            <a href="<?= base_url('super-admin/user-activity-history') ?>" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-history me-2"></i>Activity History
            </a>
            <a href="#pending-approvals" class="btn btn-primary btn-sm">
                <i class="fas fa-user-check me-2"></i>Review Approvals
            </a>
        </div>
    </div>

    <div class="ui-pill-group mb-4">
        <span class="ui-inline-pill"><i class="fas fa-users text-primary"></i> Approved <?= number_format((int) ($totalOfficers ?? 0)) ?></span>
        <span class="ui-inline-pill"><i class="fas fa-user-check text-success"></i> Online <?= number_format((int) ($onlineOfficers ?? 0)) ?></span>
        <span class="ui-inline-pill"><i class="fas fa-user-clock text-warning"></i> Pending <?= number_format((int) ($totalPending ?? 0)) ?></span>
        <span class="ui-inline-pill"><i class="fas fa-user-slash text-danger"></i> Inactive <?= number_format((int) ($inactiveOfficers ?? 0)) ?></span>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <?= view('partials/card', [
                'title' => 'Pending Approvals',
                'text' => number_format($totalPending ?? 0),
                'subtitle' => 'Officer accounts awaiting review',
                'icon' => 'fas fa-user-clock',
                'iconColor' => 'text-warning'
            ]) ?>
        </div>
        <div class="col-xl-3 col-md-6">
            <?= view('partials/card', [
                'title' => 'Approved Officers',
                'text' => number_format($totalOfficers ?? 0),
                'subtitle' => 'Currently approved accounts',
                'icon' => 'fas fa-users',
                'iconColor' => 'text-primary'
            ]) ?>
        </div>
        <div class="col-xl-3 col-md-6">
            <?= view('partials/card', [
                'title' => 'Online Officers',
                'text' => number_format($onlineOfficers ?? 0),
                'subtitle' => 'Active in the last 15 minutes',
                'icon' => 'fas fa-user-check',
                'iconColor' => 'text-success'
            ]) ?>
        </div>
        <div class="col-xl-3 col-md-6">
            <?= view('partials/card', [
                'title' => 'Inactive Officers',
                'text' => number_format($inactiveOfficers ?? 0),
                'subtitle' => 'Accounts currently disabled',
                'icon' => 'fas fa-user-slash',
                'iconColor' => 'text-danger'
            ]) ?>
        </div>
        <div class="col-xl-4 col-md-6">
            <?= view('partials/card', [
                'title' => 'Stale Pending Reviews',
                'text' => number_format((int) ($insights['stale_pending_count'] ?? 0)),
                'subtitle' => 'Waiting for more than 24 hours',
                'icon' => 'fas fa-hourglass-half',
                'iconColor' => 'text-warning'
            ]) ?>
        </div>
        <div class="col-xl-4 col-md-6">
            <?= view('partials/card', [
                'title' => 'Incomplete Profiles',
                'text' => number_format((int) ($insights['incomplete_profile_count'] ?? 0)),
                'subtitle' => 'Approved officers missing key details',
                'icon' => 'fas fa-id-card',
                'iconColor' => 'text-info'
            ]) ?>
        </div>
        <div class="col-xl-4 col-md-12">
            <?= view('partials/card', [
                'title' => 'Recent Joiners',
                'text' => number_format((int) ($insights['recent_joiners_count'] ?? 0)),
                'subtitle' => 'Created in the last 7 days',
                'icon' => 'fas fa-user-plus',
                'iconColor' => 'text-success'
            ]) ?>
        </div>
    </div>

    <?php if (!empty($priorityFlags)): ?>
        <div class="ui-priority-grid mb-4">
            <?php foreach ($priorityFlags as $flag): ?>
                <div class="ui-priority-flag ui-priority-<?= esc($flag['tone']) ?>">
                    <div class="d-flex align-items-start gap-3">
                        <i class="<?= esc($flag['icon']) ?> mt-1"></i>
                        <div>
                            <h6><?= esc($flag['title']) ?></h6>
                            <p><?= esc($flag['text']) ?></p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($pendingOfficers)): ?>
    <div class="alert alert-warning alert-dismissible fade show mb-4" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <strong>New Signup Requests:</strong> You have <strong><?= count($pendingOfficers) ?></strong> pending officer signup<?= count($pendingOfficers) > 1 ? 's' : '' ?> awaiting your approval. Please review and approve or reject them below.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>
    
    <div class="card shadow-sm ui-data-shell mb-4 border-warning" id="pending-approvals">
        <div class="card-header bg-warning bg-opacity-10 d-flex justify-content-between align-items-center">
            <div>
                <h5 class="ui-section-title mb-0">
                    <i class="fas fa-user-plus text-warning me-2"></i>
                    User Requests (<?= count($pendingOfficers ?? []) ?>)
                </h5>
                <p class="ui-section-subtitle mb-0">New officer signups awaiting approval</p>
            </div>
            <span class="badge bg-warning text-dark"><?= number_format((int) ($insights['stale_pending_count'] ?? 0)) ?> stale</span>
        </div>
        <div class="card-body">
            <?php if (!empty($pendingOfficers)): ?>
            <div class="table-responsive ui-table-wrap">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Profile</th>
                            <th>Officer</th>
                            <th>Contact</th>
                            <th>Date Joined</th>
                            <th>Priority</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pendingOfficers as $officer): ?>
                            <?php
                            $createdAt = !empty($officer['created_at']) ? strtotime($officer['created_at']) : false;
                            $isStale = $createdAt !== false && $createdAt <= strtotime('-24 hours');
                            ?>
                            <tr>
                                <td>
                                    <?= $renderOfficerAvatar($officer) ?>
                                </td>
                                <td>
                                    <div class="fw-semibold"><?= esc($officer['name'] ?? $officer['username']) ?></div>
                                    <div class="text-muted small">@<?= esc($officer['username']) ?></div>
                                </td>
                                <td>
                                    <div><?= esc($officer['email'] ?? 'No email') ?></div>
                                    <div class="text-muted small"><?= esc($officer['phone'] ?? 'No phone') ?></div>
                                </td>
                                <td>
                                    <?php if ($createdAt !== false): ?>
                                        <div><?= date('M d, Y', $createdAt) ?></div>
                                        <div class="text-muted small"><?= date('g:i A', $createdAt) ?></div>
                                    <?php else: ?>
                                        <small class="text-muted">N/A</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($isStale): ?>
                                        <span class="badge bg-warning text-dark">Over 24h</span>
                                    <?php else: ?>
                                        <span class="badge bg-light text-dark">Fresh</span>
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
                                                class="btn btn-sm btn-outline-danger reject-btn" 
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
            <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                <p class="text-muted mb-0">No pending signup requests at this time.</p>
                <small class="text-muted">New officer signups will appear here for approval.</small>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="card shadow-sm ui-data-shell mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="ui-section-title mb-0">
                    <i class="fas fa-users me-2"></i>
                    All Officers
                </h5>
                <p class="ui-section-subtitle mb-0">Manage approved officers, review online presence, and control access.</p>
            </div>
        </div>
        <div class="card-body">
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
                                    <option value="active">Active</option>
                                    <option value="inactive">Deactivated</option>
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
            
            <div class="table-responsive ui-table-wrap">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Account Status</th>
                            <th>Online Status</th>
                            <th>Profile</th>
                            <th>Name</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Date Joined</th>
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
                                    data-online="<?= $officer['is_online'] ? 'online' : 'offline' ?>"
                                    data-active="<?= ($officer['is_active'] === true) ? 'active' : 'inactive' ?>"
                                    data-created="<?= !empty($officer['created_at']) ? strtotime($officer['created_at']) : 0 ?>">
                                    <td>
                                        <?php 
                                        $status = $officer['status'] ?? 'approved';
                                        // is_active is already normalized to boolean in controller
                                        $isActive = $officer['is_active'] === true;
                                        
                                        if ($status === 'pending'): ?>
                                            <span class="badge bg-warning">
                                                <i class="fas fa-clock"></i> Pending Approval
                                            </span>
                                        <?php elseif ($status === 'rejected'): ?>
                                            <span class="badge bg-danger">
                                                <i class="fas fa-times-circle"></i> Rejected
                                            </span>
                                        <?php elseif (!$isActive): ?>
                                            <span class="badge bg-danger">
                                                <i class="fas fa-ban"></i> Deactivated
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-success">
                                                <i class="fas fa-check-circle"></i> Active
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($status === 'approved' && $isActive): ?>
                                            <?php if ($officer['is_online'] ?? false): ?>
                                                <span class="badge bg-success">
                                                    <i class="fas fa-circle me-1" style="font-size: 0.55rem;"></i>Online
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">
                                                    <i class="fas fa-circle me-1" style="font-size: 0.55rem; opacity: 0.5;"></i>Offline
                                                </span>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="text-muted small">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?= $renderOfficerAvatar($officer) ?>
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
                                        <div class="btn-group-vertical btn-group-sm" role="group">
                                            <?php if (($officer['status'] ?? 'approved') === 'pending'): ?>
                                                <div class="btn-group" role="group">
                                                    <button type="button" 
                                                            class="btn btn-success approve-btn" 
                                                            data-user-id="<?= $officer['id'] ?>"
                                                            data-user-name="<?= esc($officer['name'] ?? $officer['username']) ?>">
                                                        <i class="fas fa-check"></i> Approve
                                                    </button>
                                                    <button type="button" 
                                                            class="btn btn-danger reject-btn" 
                                                            data-user-id="<?= $officer['id'] ?>"
                                                            data-user-name="<?= esc($officer['name'] ?? $officer['username']) ?>">
                                                        <i class="fas fa-times"></i> Reject
                                                    </button>
                                                </div>
                                            <?php elseif (($officer['status'] ?? 'approved') === 'rejected'): ?>
                                                <button type="button" 
                                                        class="btn btn-success approve-btn" 
                                                        data-user-id="<?= $officer['id'] ?>"
                                                        data-user-name="<?= esc($officer['name'] ?? $officer['username']) ?>">
                                                    <i class="fas fa-check"></i> Approve
                                                </button>
                                            <?php else: ?>
                                                <?php 
                                                // For approved officers, show deactivate/reactivate based on is_active status
                                                // is_active is already normalized to boolean in controller
                                                $isActive = $officer['is_active'] === true;
                                                if ($isActive): ?>
                                                    <button type="button" 
                                                            class="btn btn-warning btn-sm deactivate-btn" 
                                                            data-user-id="<?= $officer['id'] ?>"
                                                            data-user-name="<?= esc($officer['name'] ?? $officer['username']) ?>"
                                                            title="Deactivate this officer - they will be logged out immediately">
                                                        <i class="fas fa-ban"></i> Deactivate
                                                    </button>
                                                <?php else: ?>
                                                    <button type="button" 
                                                            class="btn btn-success btn-sm reactivate-btn" 
                                                            data-user-id="<?= $officer['id'] ?>"
                                                            data-user-name="<?= esc($officer['name'] ?? $officer['username']) ?>"
                                                            title="Reactivate this officer - they will be able to log in again">
                                                        <i class="fas fa-check-circle"></i> Reactivate
                                                    </button>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                            <button type="button"
                                                    class="btn btn-danger btn-sm mt-2 delete-officer-btn"
                                                    data-user-id="<?= $officer['id'] ?>"
                                                    data-user-name="<?= esc($officer['name'] ?? $officer['username']) ?>"
                                                    title="Permanently delete this officer account">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="text-center text-muted">No officers found</td>
                            </tr>
                        <?php endif; ?>
                        <tr id="noResultsRow" style="display: none;">
                            <td colspan="9" class="text-center text-muted py-4">
                                <i class="fas fa-search fa-2x mb-2 d-block"></i>
                                <p class="mb-0">No officers found matching your search criteria</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card shadow-sm ui-data-shell mb-4 border-danger">
        <div class="card-header bg-danger bg-opacity-10 d-flex justify-content-between align-items-center">
            <div>
                <h5 class="ui-section-title mb-0">
                    <i class="fas fa-user-times text-danger me-2"></i>
                    Declined Requests (<?= count($rejectedOfficers ?? []) ?>)
                </h5>
                <p class="ui-section-subtitle mb-0">Officer signups that have been rejected and can still be reconsidered.</p>
            </div>
        </div>
        <div class="card-body">
            <?php if (!empty($rejectedOfficers)): ?>
            <div class="table-responsive ui-table-wrap">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Status</th>
                            <th>Profile</th>
                            <th>Name</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Date Joined</th>
                            <th>Date Rejected</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rejectedOfficers as $officer): ?>
                            <tr>
                                <td>
                                    <span class="badge bg-danger">
                                        <i class="fas fa-times-circle"></i> Rejected
                                    </span>
                                </td>
                                <td>
                                    <?= $renderOfficerAvatar($officer) ?>
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
                                    <?php if (!empty($officer['updated_at'])): ?>
                                        <?= date('M d, Y', strtotime($officer['updated_at'])) ?>
                                    <?php else: ?>
                                        N/A
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button type="button" 
                                            class="btn btn-success btn-sm approve-btn" 
                                            data-user-id="<?= $officer['id'] ?>"
                                            data-user-name="<?= esc($officer['name'] ?? $officer['username']) ?>"
                                            title="Approve this previously rejected officer">
                                        <i class="fas fa-check"></i> Approve
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                <p class="text-muted mb-0">No declined requests at this time.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Deactivate Reason Modal -->
<div class="modal fade" id="deactivateModal" tabindex="-1" aria-labelledby="deactivateModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deactivateModalLabel">Deactivate Officer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to deactivate <strong id="deactivateOfficerName"></strong>?</p>
                <div class="mb-3">
                    <label for="deactivateReason" class="form-label">Reason (optional)</label>
                    <textarea class="form-control" id="deactivateReason" rows="3" placeholder="Enter reason for deactivation..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" id="confirmDeactivateBtn">Deactivate</button>
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
    let currentDeactivateUserId = null;

    function notify(message, type = 'info') {
        if (typeof showNotification === 'function') {
            showNotification(message, type);
            return;
        }

        alert(message);
    }
    
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
                const response = await fetch('<?= base_url('super-admin/portal/approve') ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `user_id=${userId}`
                });
                
                const result = await response.json();
                
                if (result.success) {
                    notify(result.message || 'Officer approved successfully!', 'success');
                    location.reload();
                } else {
                    notify(result.error || 'Failed to approve officer.', 'danger');
                    this.disabled = false;
                    this.innerHTML = originalText;
                }
            } catch (error) {
                console.error('Error:', error);
                notify('An error occurred. Please try again.', 'danger');
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
    
    // Deactivate button handler
    document.querySelectorAll('.deactivate-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            currentDeactivateUserId = this.dataset.userId;
            const userName = this.dataset.userName;
            document.getElementById('deactivateOfficerName').textContent = userName;
            const deactivateModal = new bootstrap.Modal(document.getElementById('deactivateModal'));
            deactivateModal.show();
        });
    });
    
    // Reactivate button handler
    document.querySelectorAll('.reactivate-btn').forEach(btn => {
        btn.addEventListener('click', async function() {
            const userId = this.dataset.userId;
            const userName = this.dataset.userName;
            
            if (!confirm(`Are you sure you want to reactivate ${userName}?`)) {
                return;
            }
            
            const originalText = this.innerHTML;
            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
            
            try {
                const response = await fetch('<?= base_url('super-admin/portal/reactivate') ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `user_id=${userId}`
                });
                
                const result = await response.json();
                
                if (result.success) {
                    notify(result.message || 'Officer reactivated successfully!', 'success');
                    location.reload();
                } else {
                    notify(result.error || 'Failed to reactivate officer.', 'danger');
                    this.disabled = false;
                    this.innerHTML = originalText;
                }
            } catch (error) {
                console.error('Error:', error);
                notify('An error occurred. Please try again.', 'danger');
                this.disabled = false;
                this.innerHTML = originalText;
            }
        });
    });
    
    // Delete officer handler
    document.querySelectorAll('.delete-officer-btn').forEach(btn => {
        btn.addEventListener('click', async function() {
            const userId = this.dataset.userId;
            const userName = this.dataset.userName;

            if (!confirm(`This will permanently delete ${userName}'s account. Continue?`)) {
                return;
            }

            const originalText = this.innerHTML;
            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Deleting...';

            try {
                const response = await fetch('<?= base_url('super-admin/portal/delete') ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `user_id=${userId}`
                });

                const result = await response.json();

                if (result.success) {
                    notify(result.message || 'Officer deleted successfully!', 'success');
                    location.reload();
                } else {
                    notify(result.error || 'Failed to delete officer.', 'danger');
                    this.disabled = false;
                    this.innerHTML = originalText;
                }
            } catch (error) {
                console.error('Error:', error);
                notify('An error occurred. Please try again.', 'danger');
                this.disabled = false;
                this.innerHTML = originalText;
            }
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
            const response = await fetch('<?= base_url('super-admin/portal/reject') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `user_id=${currentRejectUserId}&reason=${encodeURIComponent(reason)}`
            });
            
            const result = await response.json();
            
            if (result.success) {
                bootstrap.Modal.getInstance(document.getElementById('rejectModal')).hide();
                notify(result.message || 'Officer rejected successfully!', 'success');
                location.reload();
            } else {
                notify(result.error || 'Failed to reject officer.', 'danger');
                this.disabled = false;
                this.innerHTML = originalText;
            }
        } catch (error) {
            console.error('Error:', error);
            notify('An error occurred. Please try again.', 'danger');
            this.disabled = false;
            this.innerHTML = originalText;
        }
    });
    
    // Confirm deactivate button
    document.getElementById('confirmDeactivateBtn').addEventListener('click', async function() {
        if (!currentDeactivateUserId) return;
        
        const reason = document.getElementById('deactivateReason').value;
        const originalText = this.innerHTML;
        this.disabled = true;
        this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
        
        try {
            const response = await fetch('<?= base_url('super-admin/portal/deactivate') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `user_id=${currentDeactivateUserId}&reason=${encodeURIComponent(reason)}`
            });
            
            const result = await response.json();
            
            if (result.success) {
                bootstrap.Modal.getInstance(document.getElementById('deactivateModal')).hide();
                notify(result.message || 'Officer deactivated successfully!', 'success');
                location.reload();
            } else {
                notify(result.error || 'Failed to deactivate officer.', 'danger');
                this.disabled = false;
                this.innerHTML = originalText;
            }
        } catch (error) {
            console.error('Error:', error);
            notify('An error occurred. Please try again.', 'danger');
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
            const online = row.dataset.online || '';
            const active = row.dataset.active || '';
            
            const matchesSearch = !searchTerm || 
                name.includes(searchTerm) || 
                username.includes(searchTerm) || 
                email.includes(searchTerm);
            
            let matchesStatus = true;
            if (statusFilter === 'online' || statusFilter === 'offline') {
                matchesStatus = online === statusFilter;
            } else if (statusFilter === 'active' || statusFilter === 'inactive') {
                matchesStatus = active === statusFilter;
            }
            
            return matchesSearch && matchesStatus;
        });

        if (sortValue === 'date_desc' || sortValue === 'date_asc') {
            visibleRows.sort((a, b) => {
                const aCreated = parseInt(a.dataset.created || '0', 10);
                const bCreated = parseInt(b.dataset.created || '0', 10);
                return sortValue === 'date_desc' ? (bCreated - aCreated) : (aCreated - bCreated);
            });
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

        const tbody = allOfficerRows[0]?.parentElement;
        if (!tbody) {
            return;
        }

        allOfficerRows.forEach(row => row.style.display = 'none');
        visibleRows.forEach(row => {
            row.style.display = '';
            tbody.appendChild(row);
        });

        noResultsRow.style.display = visibleRows.length === 0 ? '' : 'none';
    }
    
    searchInput.addEventListener('input', filterAndSort);
    filterStatus.addEventListener('change', filterAndSort);
    sortBy.addEventListener('change', filterAndSort);
    
    filterAndSort();
});
</script>

<?= $this->endSection() ?>
