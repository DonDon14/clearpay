<div class="row g-4">
    <!-- Recent Payments -->
    <div class="col-lg-6">
        <h6 class="mb-3 fw-semibold">Recent Payments</h6>
        <div class="activity-list">
            <?php if (!empty($payments['recent_payments'])): ?>
                <?php foreach (array_slice($payments['recent_payments'], 0, 5) as $payment): ?>
                    <div class="activity-item d-flex align-items-center gap-3 p-3 mb-2 bg-light rounded">
                        <div class="activity-icon">
                            <i class="fas fa-money-bill-wave text-success"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-1 fw-semibold"><?= esc($payment['student_name']) ?></h6>
                            <p class="mb-0 small text-muted"><?= esc($payment['contribution_title']) ?></p>
                        </div>
                        <div class="activity-meta text-end">
                            <div class="fw-semibold text-success">₱<?= number_format($payment['amount'], 2) ?></div>
                            <small class="text-muted"><?= date('M j', strtotime($payment['created_at'])) ?></small>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-center py-4 text-muted">
                    <i class="fas fa-inbox fs-1 mb-3"></i>
                    <p>No recent payments found</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Top Performing Contributions -->
    <div class="col-lg-6">
        <h6 class="mb-3 fw-semibold">Top Performing Contributions</h6>
        <div class="activity-list">
            <?php if (!empty($contributions['top_profitable'])): ?>
                <?php foreach (array_slice($contributions['top_profitable'], 0, 5) as $index => $contribution): ?>
                    <div class="activity-item d-flex align-items-center gap-3 p-3 mb-2 bg-light rounded">
                        <div class="activity-icon">
                            <span class="badge bg-primary rounded-pill"><?= $index + 1 ?></span>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-1 fw-semibold"><?= esc($contribution['title']) ?></h6>
                            <p class="mb-0 small text-muted"><?= esc($contribution['category'] ?? 'General') ?></p>
                        </div>
                        <div class="activity-meta text-end">
                            <div class="fw-semibold text-success">₱<?= number_format($contribution['profit_amount'], 2) ?></div>
                            <small class="text-muted"><?= number_format($contribution['profit_margin'], 1) ?>% margin</small>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-center py-4 text-muted">
                    <i class="fas fa-chart-line fs-1 mb-3"></i>
                    <p>No contribution data available</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>