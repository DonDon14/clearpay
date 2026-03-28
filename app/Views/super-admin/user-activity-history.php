<?= $this->extend('layouts/super-admin') ?>

<?= $this->section('content') ?>
<?php
$uniqueActors = [];
$adminActions = 0;
$officerActions = 0;
$recentActions = 0;

foreach ($activities as $activity) {
    $actorName = $activity['actor_name'] ?? null;
    if (!empty($actorName)) {
        $uniqueActors[$actorName] = true;
    }

    $actorRole = $activity['actor_role'] ?? 'unknown';
    if ($actorRole === 'admin') {
        $adminActions++;
    } elseif ($actorRole === 'officer') {
        $officerActions++;
    }

    if (!empty($activity['created_at']) && strtotime($activity['created_at']) >= strtotime('-24 hours')) {
        $recentActions++;
    }
}
?>

<div class="container-fluid ui-page-shell">
    <div class="ui-page-intro">
        <div>
            <h6>Activity History</h6>
            <p>Review system actions by admins and officers, filter by actor, and inspect the latest operational changes.</p>
        </div>
        <div class="ui-actions-stack">
            <?php if ($selectedUserId): ?>
                <a href="<?= base_url('super-admin/user-activity-history') ?>" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-times me-2"></i>Clear Filter
                </a>
            <?php endif; ?>
            <a href="<?= base_url('super-admin/portal') ?>" class="btn btn-primary btn-sm">
                <i class="fas fa-shield-alt me-2"></i>Back To Portal
            </a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <?= view('partials/card', [
                'title' => 'Visible Activities',
                'text' => number_format((int) ($totalActivities ?? 0)),
                'subtitle' => 'Current filtered results',
                'icon' => 'fas fa-stream',
                'iconColor' => 'text-primary',
            ]) ?>
        </div>
        <div class="col-xl-3 col-md-6">
            <?= view('partials/card', [
                'title' => 'Unique Actors',
                'text' => number_format(count($uniqueActors)),
                'subtitle' => 'Users represented in this view',
                'icon' => 'fas fa-users',
                'iconColor' => 'text-info',
            ]) ?>
        </div>
        <div class="col-xl-3 col-md-6">
            <?= view('partials/card', [
                'title' => 'Admin Actions',
                'text' => number_format($adminActions),
                'subtitle' => 'Performed by admin accounts',
                'icon' => 'fas fa-user-shield',
                'iconColor' => 'text-danger',
            ]) ?>
        </div>
        <div class="col-xl-3 col-md-6">
            <?= view('partials/card', [
                'title' => 'Last 24 Hours',
                'text' => number_format($recentActions),
                'subtitle' => 'Recent actions in the last day',
                'icon' => 'fas fa-clock',
                'iconColor' => 'text-warning',
            ]) ?>
        </div>
    </div>

    <div class="card shadow-sm ui-data-shell mb-4">
        <div class="card-header">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                <div>
                    <h5 class="ui-section-title mb-0">Filters</h5>
                    <small class="ui-section-subtitle">Limit the audit trail to one actor when you need a focused review.</small>
                </div>
                <div class="row g-2 flex-grow-1 justify-content-end" style="max-width: 640px;">
                    <div class="col-md-8">
                        <label for="userFilter" class="form-label visually-hidden">Filter by user</label>
                        <select class="form-select" id="userFilter">
                            <option value="">All Users</option>
                            <?php foreach ($allUsers as $user): ?>
                                <option value="<?= $user['id'] ?>" <?= ($selectedUserId == $user['id']) ? 'selected' : '' ?>>
                                    <?= esc($user['name'] ?? $user['username']) ?> (<?= esc($user['role']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <button type="button" class="btn btn-primary w-100" id="applyFilter">
                            <i class="fas fa-filter me-2"></i>Apply Filter
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if ($selectedUser): ?>
        <div class="card shadow-sm ui-data-shell mb-4 border-info">
            <div class="card-header bg-info bg-opacity-10">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div>
                        <h5 class="ui-section-title mb-0">
                            <i class="fas fa-user me-2"></i><?= esc($selectedUser['name'] ?? $selectedUser['username']) ?>
                        </h5>
                        <small class="ui-section-subtitle">Focused activity review for the selected account.</small>
                    </div>
                    <span class="badge bg-<?= $selectedUser['role'] === 'admin' ? 'danger' : 'primary' ?>">
                        <?= esc(ucfirst($selectedUser['role'])) ?>
                    </span>
                </div>
            </div>
            <div class="card-body">
                <div class="ui-pill-group">
                    <span class="ui-inline-pill"><i class="fas fa-at text-primary"></i><?= esc($selectedUser['username']) ?></span>
                    <span class="ui-inline-pill"><i class="fas fa-envelope text-info"></i><?= esc($selectedUser['email'] ?? 'No email') ?></span>
                    <span class="ui-inline-pill"><i class="fas fa-list text-success"></i><?= number_format((int) $totalActivities) ?> activities</span>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm ui-data-shell">
        <div class="card-header d-flex justify-content-between align-items-center gap-3">
            <div>
                <h5 class="ui-section-title mb-0">Audit Trail</h5>
                <small class="ui-section-subtitle">Showing the most recent actions from the current result set.</small>
            </div>
            <span class="text-muted small">Showing <?= count($activities) ?> entries</span>
        </div>
        <div class="card-body p-0">
            <?php if (!empty($activities)): ?>
                <div class="table-responsive ui-table-wrap">
                    <table class="table table-hover mb-0 align-middle">
                        <thead>
                            <tr>
                                <th>Date &amp; Time</th>
                                <th>Actor</th>
                                <th>Action</th>
                                <th>Title</th>
                                <th>Description</th>
                                <th>Type</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($activities as $activity): ?>
                                <?php
                                $date = !empty($activity['created_at']) ? new \DateTime($activity['created_at']) : null;
                                $now = new \DateTime();
                                $diff = $date ? $now->diff($date) : null;

                                $action = $activity['action'] ?? 'unknown';
                                $actionColors = [
                                    'created' => 'success',
                                    'update' => 'info',
                                    'updated' => 'info',
                                    'deleted' => 'danger',
                                    'approved' => 'success',
                                    'rejected' => 'danger',
                                    'deactivated' => 'warning',
                                    'reactivated' => 'success',
                                    'login' => 'primary',
                                    'logout' => 'secondary',
                                ];
                                $actionColor = $actionColors[$action] ?? 'secondary';

                                $activityType = $activity['activity_type'] ?? 'unknown';
                                $typeColors = [
                                    'user' => 'primary',
                                    'user_activity' => 'info',
                                    'payment' => 'success',
                                    'contribution' => 'warning',
                                    'payer' => 'secondary',
                                ];
                                $typeColor = $typeColors[$activityType] ?? 'secondary';
                                ?>
                                <tr>
                                    <td>
                                        <?php if ($date): ?>
                                            <?php if ($diff->days === 0 && $diff->h === 0 && $diff->i < 5): ?>
                                                <div class="text-success fw-semibold">Just now</div>
                                            <?php elseif ($diff->days === 0): ?>
                                                <div><?= $date->format('g:i A') ?></div>
                                            <?php elseif ($diff->days === 1): ?>
                                                <div>Yesterday <?= $date->format('g:i A') ?></div>
                                            <?php elseif ($diff->days < 7): ?>
                                                <div><?= $date->format('D g:i A') ?></div>
                                            <?php else: ?>
                                                <div><?= $date->format('M d, Y g:i A') ?></div>
                                            <?php endif; ?>
                                            <div class="text-muted small"><?= $date->format('M d, Y') ?></div>
                                        <?php else: ?>
                                            <span class="text-muted">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge bg-<?= ($activity['actor_role'] ?? '') === 'admin' ? 'danger' : (($activity['actor_role'] ?? '') === 'officer' ? 'primary' : 'secondary') ?>">
                                                <?= esc(ucfirst($activity['actor_role'] ?? 'system')) ?>
                                            </span>
                                            <span><?= esc($activity['actor_name'] ?? 'System') ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= $actionColor ?>">
                                            <?= esc(ucfirst(str_replace('_', ' ', $action))) ?>
                                        </span>
                                    </td>
                                    <td><strong><?= esc($activity['title'] ?? 'N/A') ?></strong></td>
                                    <td>
                                        <div class="text-muted small ui-description-cell">
                                            <?= esc($activity['description'] ?? 'No description') ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= $typeColor ?>">
                                            <?= esc(ucfirst(str_replace('_', ' ', $activityType))) ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-history fa-3x text-muted mb-3"></i>
                    <p class="text-muted mb-1">No activities found.</p>
                    <?php if ($selectedUserId): ?>
                        <a href="<?= base_url('super-admin/user-activity-history') ?>" class="btn btn-outline-primary">
                            View All Activities
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const applyFilterBtn = document.getElementById('applyFilter');
    const userFilter = document.getElementById('userFilter');

    if (applyFilterBtn && userFilter) {
        applyFilterBtn.addEventListener('click', function() {
            const userId = userFilter.value;
            if (userId) {
                window.location.href = '<?= base_url('super-admin/user-activity-history') ?>?user_id=' + userId;
            } else {
                window.location.href = '<?= base_url('super-admin/user-activity-history') ?>';
            }
        });

        userFilter.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                applyFilterBtn.click();
            }
        });
    }
});
</script>

<?= $this->endSection() ?>
