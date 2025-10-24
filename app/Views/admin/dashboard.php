<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<!-- Dashboard Content -->
<!-- Stats Cards -->
<div class="container-fluid mb-4">
    <div class="row g-3">
        <div class="col-xl-3 col-lg-6 col-md-6">
            <?= view('partials/card', [
                'icon' => 'fas fa-database',
                'iconColor' => 'text-primary',
                'title' => 'Total Collections',
                'text' => '₱150,000.00'
            ]) ?>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6">
            <?= view('partials/card', [
                'icon' => 'fas fa-check-square',
                'iconColor' => 'text-success',
                'title' => 'Verified Payments',
                'text' => '0'
            ]) ?>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6">
            <?= view('partials/card', [
                'icon' => 'fas fa-clock',
                'iconColor' => 'text-warning',
                'title' => 'Pending Payments',
                'text' => '0'
            ]) ?>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6">
            <?= view('partials/card', [
                'icon' => 'fas fa-calendar-alt',
                'iconColor' => 'text-info',
                'title' => 'Today\'s Payments',
                'text' => '0'
            ]) ?>
        </div>
    </div>
</div>
<!--End Stats Cards -->

<!-- Detailed Stats and Welcome Message -->
<div class="container-fluid">
    <!-- Recent Payments and System Status -->
    <div class="row mb-4">
        <!-- Quick Actions -->
        <div class="col-lg-4 col-md-6">
            <div class="card h-100 shadow-sm">
                <div class="card-header">
                    <h5 class="card-title mb-0">Quick Actions</h5>
                    <small class="text-muted">Frequently used operations</small>
                </div>
                <div class="card-body p-2">
                    <div class="row g-2">
                        <?= view('partials/quick-action', [
                            'icon' => 'fas fa-plus-circle',
                            'title' => 'Record Payment',
                            'subtitle' => 'Add new payment record',
                            'bgColor' => 'bg-primary',
                            'link' => '/payments/add',
                            'colClass' => 'col-6'
                        ]) ?>
                        <?= view('partials/quick-action', [
                            'icon' => 'fas fa-check-square',
                            'title' => 'Verify Payments',
                            'subtitle' => 'Scan QR codes to verify',
                            'bgColor' => 'bg-success',
                            'link' => '/payments/verify',
                            'colClass' => 'col-6'
                        ]) ?>
                        <?= view('partials/quick-action', [
                            'icon' => 'fas fa-hand-holding-usd',
                            'title' => 'Manage Contributions',
                            'subtitle' => 'Add or edit fee types',
                            'bgColor' => 'bg-info',
                            'link' => '/contributions',
                            'colClass' => 'col-6'
                        ]) ?>
                        <?= view('partials/quick-action', [
                            'icon' => 'fas fa-chart-bar',
                            'title' => 'View Analytics',
                            'subtitle' => 'System performance reports',
                            'bgColor' => 'bg-secondary',
                            'link' => '/analytics',
                            'colClass' => 'col-6'
                        ]) ?>
                        <?= view('partials/quick-action', [
                            'icon' => 'fas fa-wallet',
                            'title' => 'Partial Payments',
                            'subtitle' => 'View installment records',
                            'bgColor' => 'bg-warning',
                            'link' => '/partial-payments',
                            'colClass' => 'col-6'
                        ]) ?>
                        <?= view('partials/quick-action', [
                            'icon' => 'fas fa-bullhorn',
                            'title' => 'Add Announcement',
                            'subtitle' => 'Create system announcements',
                            'bgColor' => 'bg-purple',
                            'link' => '/announcements/add',
                            'colClass' => 'col-6'
                        ]) ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Payments -->
        <div class="col-lg-4 col-md-6">
            <div class="card h-100 shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title mb-0">Recent Payments</h5>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <button class="btn btn-sm btn-outline-secondary" title="Refresh">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                        <button class="btn btn-sm btn-primary">View All</button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <!-- Payment Item 1 -->
                    <div class="d-flex align-items-center p-3 border-bottom">
                        <div class="me-3">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="fas fa-user"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-1 fw-semibold">Thirdyx</h6>
                            <small class="text-muted">Uniform</small>
                            <div class="text-muted small">Oct 22, 2025 1:38 AM</div>
                        </div>
                        <div class="text-end">
                            <div class="fw-bold">₱1,000.00</div>
                            <span class="badge bg-success small">FULLY_PAID</span>
                        </div>
                        <div class="ms-2">
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="dropdown">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="#"><i class="fas fa-eye me-2"></i>View</a></li>
                                    <li><a class="dropdown-item" href="#"><i class="fas fa-edit me-2"></i>Edit</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Item 2 -->
                    <div class="d-flex align-items-center p-3 border-bottom">
                        <div class="me-3">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="fas fa-user"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-1 fw-semibold">Floro</h6>
                            <small class="text-muted">Uniform</small>
                            <div class="text-muted small">Oct 21, 2025 2:44 PM</div>
                        </div>
                        <div class="text-end">
                            <div class="fw-bold">₱1,000.00</div>
                            <span class="badge bg-success small">FULLY_PAID</span>
                        </div>
                        <div class="ms-2">
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="dropdown">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="#"><i class="fas fa-eye me-2"></i>View</a></li>
                                    <li><a class="dropdown-item" href="#"><i class="fas fa-edit me-2"></i>Edit</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Item 3 -->
                    <div class="d-flex align-items-center p-3">
                        <div class="me-3">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="fas fa-user"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-1 fw-semibold">Floro</h6>
                            <small class="text-muted">Feast</small>
                            <div class="text-muted small">Oct 21, 2025 2:40 PM</div>
                        </div>
                        <div class="text-end">
                            <div class="fw-bold">₱500.00</div>
                            <span class="badge bg-success small">FULLY_PAID</span>
                        </div>
                        <div class="ms-2">
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="dropdown">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="#"><i class="fas fa-eye me-2"></i>View</a></li>
                                    <li><a class="dropdown-item" href="#"><i class="fas fa-edit me-2"></i>Edit</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Status -->
        <div class="col-lg-4 col-md-12">
            <div class="card h-100 shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">System Status</h5>
                    <span class="badge bg-success">
                        <i class="fas fa-circle me-1" style="font-size: 8px;"></i>
                        ONLINE
                    </span>
                </div>
                <div class="card-body">
                    <!-- Database Status -->
                    <div class="d-flex align-items-center mb-3">
                        <div class="me-3">
                            <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="fas fa-database"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-1 fw-semibold">Database</h6>
                            <small class="text-muted">Connected and operational</small>
                        </div>
                        <span class="badge bg-success">HEALTHY</span>
                    </div>

                    <!-- QR Generation -->
                    <div class="d-flex align-items-center mb-3">
                        <div class="me-3">
                            <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="fas fa-qrcode"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-1 fw-semibold">QR Generation</h6>
                            <small class="text-muted">QR extension active</small>
                        </div>
                        <span class="badge bg-success">ACTIVE</span>
                    </div>

                    <!-- Backup System -->
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <div class="bg-warning text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="fas fa-cloud-upload-alt"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-1 fw-semibold">Backup System</h6>
                            <small class="text-muted">Last backup: 2 hours ago</small>
                        </div>
                        <span class="badge bg-warning">SCHEDULED</span>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="card-title mb-0">Welcome, <?= esc($username) ?>!</h5>
                </div>
                <div class="card-body">
                    <p class="card-text">You are successfully logged in to the ClearPay admin dashboard.</p>
                    <p class="text-muted">Use the sidebar navigation to access different sections of the application.</p>
                    <?= view('partials/quick-action', [
                            'icon' => 'fas fa-bullhorn',
                            'title' => 'Add Announcement',
                            'subtitle' => 'Create system announcements',
                            'bgColor' => 'bg-purple',
                            'link' => '/announcements/add',
                            'colClass' => 'col-6'
                        ]) ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.icon-circle {
    width: 45px;
    height: 45px;
    background: rgba(255,255,255,0.2);
    border-radius: 50%;
    flex-shrink: 0;
    font-size: 1.25rem;
}

.hover-scale:hover {
    transform: translateY(-3px) scale(1.03);
    box-shadow: 0 8px 20px rgba(0,0,0,0.2);
}

.text-white-75 {
    color: rgba(255,255,255,0.75) !important;
}

.bg-purple {
    background-color: #6f42c1 !important;
}

/* Custom badges */
.badge {
    font-size: 0.7rem;
}

/* Payment item hover effect */
.card-body .border-bottom:hover {
    background-color: #f8f9fa;
}

/* Status indicators */
.badge .fas.fa-circle {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { opacity: 1; }
    50% { opacity: 0.5; }
    100% { opacity: 1; }
}
</style>

<?= $this->endSection() ?>
