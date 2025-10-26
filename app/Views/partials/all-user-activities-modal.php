<!-- All User Activities Modal -->
<div class="modal fade" id="allUserActivitiesModal" tabindex="-1" aria-labelledby="allUserActivitiesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <div>
                    <h5 class="modal-title" id="allUserActivitiesModalLabel">All User Activities</h5>
                    <small class="text-white-50">
                        <i class="fas fa-info-circle me-1"></i>
                        Complete activity history
                    </small>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <?php if (!empty($allUserActivities)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th style="width: 60px;">Icon</th>
                                    <th>User</th>
                                    <th>Activity</th>
                                    <th>Entity Type</th>
                                    <th>Timestamp</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($allUserActivities as $activity): ?>
                                    <tr>
                                        <td>
                                            <?php 
                                                $activityIcon = match($activity['activity_type']) {
                                                    'create' => 'fa-plus-circle',
                                                    'update' => 'fa-edit',
                                                    'delete' => 'fa-trash',
                                                    'login' => 'fa-sign-in-alt',
                                                    'logout' => 'fa-sign-out-alt',
                                                    default => 'fa-circle'
                                                };
                                                $activityColor = match($activity['activity_type']) {
                                                    'create' => 'bg-success',
                                                    'update' => 'bg-info',
                                                    'delete' => 'bg-danger',
                                                    'login' => 'bg-primary',
                                                    'logout' => 'bg-secondary',
                                                    default => 'bg-secondary'
                                                };
                                            ?>
                                            <div class="<?= $activityColor ?> text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                <i class="fas <?= $activityIcon ?>"></i>
                                            </div>
                                        </td>
                                        <td>
                                            <strong><?= esc($activity['user_name']) ?? esc($activity['username']) ?></strong>
                                            <br>
                                            <small class="text-muted"><?= esc($activity['username'] ?? 'N/A') ?></small>
                                        </td>
                                        <td>
                                            <div class="activity-description">
                                                <?= esc($activity['description'] ?? 'No description') ?>
                                            </div>
                                        </td>
                                        <td>
                                            <?php if (!empty($activity['entity_type'])): ?>
                                                <span class="badge bg-secondary"><?= esc(strtoupper($activity['entity_type'])) ?></span>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                <?= date('M d, Y h:i A', strtotime($activity['created_at'])) ?>
                                            </small>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center text-muted py-5">
                        <i class="fas fa-inbox fa-3x mb-3"></i>
                        <p>No user activities found.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
.activity-description {
    max-width: 400px;
    word-wrap: break-word;
}

.table th {
    background-color: #f8f9fa;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.85rem;
    letter-spacing: 0.5px;
}

.table tbody tr {
    transition: background-color 0.2s;
}

.table tbody tr:hover {
    background-color: #f8f9fa;
}
</style>
