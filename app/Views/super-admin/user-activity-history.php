<?= $this->extend('layouts/super-admin') ?>

<?= $this->section('content') ?>
<div class="container-fluid">

    <!-- User Filter -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="row align-items-end">
                <div class="col-md-6">
                    <label for="userFilter" class="form-label">Filter by User</label>
                    <select class="form-select" id="userFilter">
                        <option value="">All Users</option>
                        <?php foreach ($allUsers as $user): ?>
                            <option value="<?= $user['id'] ?>" <?= ($selectedUserId == $user['id']) ? 'selected' : '' ?>>
                                <?= esc($user['name'] ?? $user['username']) ?> 
                                (<?= esc($user['role']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-primary" id="applyFilter">
                            <i class="fas fa-filter me-1"></i>
                            Apply Filter
                        </button>
                        <?php if ($selectedUserId): ?>
                            <a href="<?= base_url('super-admin/user-activity-history') ?>" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i>
                                Clear
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Selected User Info -->
    <?php if ($selectedUser): ?>
    <div class="card shadow-sm mb-4 border-info">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h5 class="mb-1">
                        <i class="fas fa-user me-2"></i>
                        <?= esc($selectedUser['name'] ?? $selectedUser['username']) ?>
                    </h5>
                    <p class="text-muted mb-0">
                        <span class="badge bg-<?= $selectedUser['role'] === 'admin' ? 'danger' : 'primary' ?> me-2">
                            <?= esc(ucfirst($selectedUser['role'])) ?>
                        </span>
                        <span class="me-2">Username: <?= esc($selectedUser['username']) ?></span>
                        <span>Email: <?= esc($selectedUser['email'] ?? 'N/A') ?></span>
                    </p>
                </div>
                <div class="col-md-4 text-end">
                    <div class="text-muted small">
                        <div>Total Activities: <strong><?= $totalActivities ?></strong></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Activities Table -->
    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-list me-2"></i>
                    Activities
                    <?php if ($selectedUser): ?>
                        <span class="badge bg-primary"><?= $totalActivities ?></span>
                    <?php endif; ?>
                </h5>
                <div class="text-muted small">
                    Showing <?= count($activities) ?> most recent activities
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <?php if (!empty($activities)): ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 180px;">Date & Time</th>
                            <th style="width: 150px;">Actor</th>
                            <th style="width: 120px;">Action</th>
                            <th>Title</th>
                            <th>Description</th>
                            <th style="width: 100px;">Type</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($activities as $activity): ?>
                            <tr>
                                <td>
                                    <?php if (!empty($activity['created_at'])): ?>
                                        <?php 
                                        $date = new \DateTime($activity['created_at']);
                                        $now = new \DateTime();
                                        $diff = $now->diff($date);
                                        
                                        if ($diff->days == 0) {
                                            if ($diff->h == 0 && $diff->i < 5) {
                                                echo '<span class="text-success">Just now</span>';
                                            } else {
                                                echo $date->format('H:i');
                                            }
                                        } else if ($diff->days == 1) {
                                            echo 'Yesterday ' . $date->format('H:i');
                                        } else if ($diff->days < 7) {
                                            echo $date->format('D H:i');
                                        } else {
                                            echo $date->format('M d, Y H:i');
                                        }
                                        ?>
                                        <br>
                                        <small class="text-muted"><?= $date->format('M d, Y') ?></small>
                                    <?php else: ?>
                                        <span class="text-muted">N/A</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (isset($activity['actor_name'])): ?>
                                        <div class="d-flex align-items-center">
                                            <span class="badge bg-<?= $activity['actor_role'] === 'admin' ? 'danger' : ($activity['actor_role'] === 'officer' ? 'primary' : 'secondary') ?> me-2">
                                                <?= esc(ucfirst($activity['actor_role'])) ?>
                                            </span>
                                            <span><?= esc($activity['actor_name']) ?></span>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">System</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php 
                                    $action = $activity['action'] ?? 'unknown';
                                    $actionColors = [
                                        'created' => 'success',
                                        'updated' => 'info',
                                        'deleted' => 'danger',
                                        'approved' => 'success',
                                        'rejected' => 'danger',
                                        'deactivated' => 'warning',
                                        'reactivated' => 'success',
                                        'login' => 'primary',
                                        'logout' => 'secondary'
                                    ];
                                    $color = $actionColors[$action] ?? 'secondary';
                                    ?>
                                    <span class="badge bg-<?= $color ?>">
                                        <?= esc(ucfirst($action)) ?>
                                    </span>
                                </td>
                                <td>
                                    <strong><?= esc($activity['title'] ?? 'N/A') ?></strong>
                                </td>
                                <td>
                                    <div class="text-muted small" style="max-width: 400px; overflow: hidden; text-overflow: ellipsis;">
                                        <?= esc($activity['description'] ?? 'No description') ?>
                                    </div>
                                </td>
                                <td>
                                    <?php 
                                    $activityType = $activity['activity_type'] ?? 'unknown';
                                    $typeColors = [
                                        'user' => 'primary',
                                        'user_activity' => 'info',
                                        'payment' => 'success',
                                        'contribution' => 'warning',
                                        'payer' => 'secondary'
                                    ];
                                    $typeColor = $typeColors[$activityType] ?? 'secondary';
                                    ?>
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
                <p class="text-muted">No activities found</p>
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
    // User filter
    document.getElementById('applyFilter').addEventListener('click', function() {
        const userId = document.getElementById('userFilter').value;
        if (userId) {
            window.location.href = '<?= base_url('super-admin/user-activity-history') ?>?user_id=' + userId;
        } else {
            window.location.href = '<?= base_url('super-admin/user-activity-history') ?>';
        }
    });
    
    // Allow Enter key to apply filter
    document.getElementById('userFilter').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            document.getElementById('applyFilter').click();
        }
    });
</script>

<?= $this->endSection() ?>

