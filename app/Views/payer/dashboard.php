<?= $this->extend('layouts/payer-layout') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <!-- Welcome Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h2 class="mb-2">Welcome back, <?= esc($payer['payer_name'] ?? 'Payer') ?>!</h2>
                            <p class="text-muted mb-0">Here's your payment summary</p>
                        </div>
                        <div class="text-end">
                            <div class="display-4 text-primary mb-1">₱<?= number_format($totalPaid, 2) ?></div>
                            <small class="text-muted">Total Amount Paid</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <div class="mb-2">
                        <i class="fas fa-receipt fa-2x text-primary"></i>
                    </div>
                    <h3 class="mb-1"><?= count($recentPayments) ?></h3>
                    <p class="text-muted mb-0">Total Payments</p>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <div class="mb-2">
                        <i class="fas fa-check-circle fa-2x text-success"></i>
                    </div>
                    <h3 class="mb-1"><?= count($recentPayments) ?></h3>
                    <p class="text-muted mb-0">Completed</p>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <div class="mb-2">
                        <i class="fas fa-money-bill-wave fa-2x text-info"></i>
                    </div>
                    <h3 class="mb-1">₱<?= number_format($totalPaid, 2) ?></h3>
                    <p class="text-muted mb-0">Total Paid</p>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <div class="mb-2">
                        <i class="fas fa-bullhorn fa-2x text-warning"></i>
                    </div>
                    <h3 class="mb-1"><?= count($announcements) ?></h3>
                    <p class="text-muted mb-0">Announcements</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Recent Payments -->
        <div class="col-lg-8 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-history me-2"></i>Recent Payments</h5>
                    <a href="<?= base_url('payer/payment-history') ?>" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body">
                    <?php if (empty($recentPayments)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No payments found</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Amount</th>
                                        <th>Reference</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentPayments as $payment): ?>
                                        <tr>
                                            <td><?= date('M d, Y', strtotime($payment['payment_date'] ?? $payment['created_at'])) ?></td>
                                            <td>₱<?= number_format($payment['amount_paid'], 2) ?></td>
                                            <td><code><?= esc($payment['reference_number'] ?? 'N/A') ?></code></td>
                                            <td>
                                                <span class="badge bg-<?= ($payment['payment_status'] === 'fully paid') ? 'success' : 'warning' ?>">
                                                    <?= esc(ucfirst($payment['payment_status'] ?? 'pending')) ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Announcements -->
        <div class="col-lg-4 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-bullhorn me-2"></i>Announcements</h5>
                    <a href="<?= base_url('payer/announcements') ?>" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body">
                    <?php if (empty($announcements)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-bell-slash fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No announcements</p>
                        </div>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($announcements as $announcement): ?>
                                <div class="list-group-item px-0">
                                    <h6 class="mb-1"><?= esc($announcement['title']) ?></h6>
                                    <p class="mb-1 text-muted small"><?= esc(substr($announcement['text'], 0, 100)) ?>...</p>
                                    <small class="text-muted"><?= date('M d, Y', strtotime($announcement['created_at'])) ?></small>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
