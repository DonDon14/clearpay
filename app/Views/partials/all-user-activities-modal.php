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
                                    <?php
                                        // Determine click URL based on entity_type and activity_type
                                        $clickUrl = null;
                                        $entityType = strtolower($activity['entity_type'] ?? '');
                                        $activityType = strtolower($activity['activity_type'] ?? ''); // This is the action: create, update, delete, etc.
                                        
                                        // For user_activities table, entity_type determines the redirect
                                        if ($entityType === 'payment_request') {
                                            $clickUrl = base_url('payment-requests');
                                        } elseif ($entityType === 'refund') {
                                            // For refunds, redirect to history if rejected/processed
                                            if (in_array($activityType, ['rejected', 'completed', 'processed'])) {
                                                $clickUrl = base_url('refunds') . '#history';
                                            } else {
                                                $clickUrl = base_url('refunds');
                                            }
                                        } elseif ($entityType === 'payment') {
                                            $clickUrl = base_url('payments');
                                        } elseif ($entityType === 'contribution') {
                                            $clickUrl = base_url('contributions');
                                        } elseif ($entityType === 'announcement') {
                                            $clickUrl = base_url('announcements');
                                        } elseif ($entityType === 'payer') {
                                            $clickUrl = base_url('payers');
                                        } elseif ($entityType === 'user') {
                                            $clickUrl = base_url('settings/users');
                                        }
                                        
                                        $isClickable = $clickUrl !== null;
                                        $rowStyle = $isClickable ? 'cursor: pointer;' : '';
                                    ?>
                                    <tr onclick="<?= $isClickable ? "window.location.href='{$clickUrl}'" : '' ?>" 
                                        style="<?= $rowStyle ?>"
                                        onmouseover="<?= $isClickable ? "this.style.backgroundColor='#f8f9fa'" : '' ?>"
                                        onmouseout="<?= $isClickable ? "this.style.backgroundColor=''" : '' ?>">
                                        <td>
                                            <?php 
                                                $activityIcon = match($activity['activity_type'] ?? '') {
                                                    'create' => 'fa-plus-circle',
                                                    'update' => 'fa-edit',
                                                    'delete' => 'fa-trash',
                                                    'login' => 'fa-sign-in-alt',
                                                    'logout' => 'fa-sign-out-alt',
                                                    'approved' => 'fa-check-circle',
                                                    'rejected' => 'fa-times-circle',
                                                    'processed' => 'fa-check-double',
                                                    'completed' => 'fa-check-double',
                                                    default => 'fa-circle'
                                                };
                                                $activityColor = match($activity['activity_type'] ?? '') {
                                                    'create' => 'bg-success',
                                                    'update' => 'bg-info',
                                                    'delete' => 'bg-danger',
                                                    'login' => 'bg-primary',
                                                    'logout' => 'bg-secondary',
                                                    'approved' => 'bg-success',
                                                    'rejected' => 'bg-danger',
                                                    'processed', 'completed' => 'bg-primary',
                                                    default => 'bg-secondary'
                                                };
                                            ?>
                                            <?php if (!empty($activity['profile_picture'])): ?>
                                                <?php 
                                                // Check if it's a Cloudinary URL (full URL) or local path
                                                $modalActivityPicUrl = (strpos($activity['profile_picture'], 'res.cloudinary.com') !== false || 
                                                                       strpos($activity['profile_picture'], 'http://') === 0 || 
                                                                       strpos($activity['profile_picture'], 'https://') === 0)
                                                    ? $activity['profile_picture'] 
                                                    : base_url($activity['profile_picture']);
                                                ?>
                                                <img src="<?= $modalActivityPicUrl ?>" 
                                                     alt="Profile Picture" 
                                                     class="rounded-circle" 
                                                     style="width: 40px; height: 40px; object-fit: cover; border: 2px solid #e9ecef;"
                                                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                            <?php endif; ?>
                                            <div class="<?= $activityColor ?> text-white rounded-circle d-flex align-items-center justify-content-center" 
                                                 style="width: 40px; height: 40px; <?= !empty($activity['profile_picture']) ? 'display: none;' : '' ?>">
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
                                                <?php if ($isClickable): ?>
                                                    <i class="fas fa-chevron-right text-muted ms-2"></i>
                                                <?php endif; ?>
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

.user-activity-clickable {
    cursor: pointer;
    transition: background-color 0.2s;
}

.user-activity-clickable:hover {
    background-color: #f8f9fa;
}
</style>
