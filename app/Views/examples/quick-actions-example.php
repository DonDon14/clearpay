<?php
// Alternative clean approach for quick actions dashboard
?>

<!-- Quick Actions - Alternative Approach -->
<div class="col-lg-4 col-md-6">
    <?= view('partials/container-card', [
        'title' => 'Quick Actions',
        'subtitle' => 'Manage your tasks efficiently',
        'cardClass' => 'h-100',
        'bodyClass' => 'p-0',
        'content' => '
            <div class="list-group list-group-flush">
                <a href="/payments/add" class="list-group-item list-group-item-action d-flex align-items-center">
                    <div class="me-3">
                        <div class="bg-primary text-white rounded p-2">
                            <i class="fas fa-plus"></i>
                        </div>
                    </div>
                    <div>
                        <h6 class="mb-1">Add Payment</h6>
                        <small class="text-muted">Record new payment</small>
                    </div>
                </a>
                <a href="/analytics" class="list-group-item list-group-item-action d-flex align-items-center">
                    <div class="me-3">
                        <div class="bg-success text-white rounded p-2">
                            <i class="fas fa-chart-bar"></i>
                        </div>
                    </div>
                    <div>
                        <h6 class="mb-1">View Reports</h6>
                        <small class="text-muted">Check analytics</small>
                    </div>
                </a>
                <a href="/students" class="list-group-item list-group-item-action d-flex align-items-center">
                    <div class="me-3">
                        <div class="bg-info text-white rounded p-2">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                    <div>
                        <h6 class="mb-1">Manage Students</h6>
                        <small class="text-muted">Student records</small>
                    </div>
                </a>
                <a href="/settings" class="list-group-item list-group-item-action d-flex align-items-center">
                    <div class="me-3">
                        <div class="bg-secondary text-white rounded p-2">
                            <i class="fas fa-cog"></i>
                        </div>
                    </div>
                    <div>
                        <h6 class="mb-1">Settings</h6>
                        <small class="text-muted">System configuration</small>
                    </div>
                </a>
            </div>
        '
    ]) ?>
</div>