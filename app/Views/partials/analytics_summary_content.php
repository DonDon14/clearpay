<div class="row g-4">
    <!-- Top Payers -->
    <div class="col-lg-6">
        <h6 class="mb-3 fw-semibold">Top Payers</h6>
        <div class="activity-list">
            <?php if (!empty($payments['top_payers'])): ?>
                <?php foreach (array_slice($payments['top_payers'], 0, 5) as $index => $payer): ?>
                    <div class="activity-item d-flex align-items-center gap-3 p-3 mb-2 bg-light rounded">
                        <div class="activity-icon">
                            <span class="badge bg-success rounded-pill"><?= $index + 1 ?></span>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-1 fw-semibold"><?= esc($payer['payer_name']) ?></h6>
                            <p class="mb-0 small text-muted">ID: <?= esc($payer['payer_id_number']) ?></p>
                        </div>
                        <div class="activity-meta text-end">
                            <div class="fw-semibold text-success">₱<?= number_format($payer['total_paid'], 2) ?></div>
                            <small class="text-muted"><?= $payer['total_transactions'] ?> transactions</small>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-center py-4 text-muted">
                    <i class="fas fa-users fs-1 mb-3"></i>
                    <p>No payer data available</p>
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
                            <div class="fw-semibold text-success">₱<?= number_format($contribution['profit_amount'] ?? 0, 2) ?></div>
                            <small class="text-muted"><?= number_format($contribution['profit_margin'] ?? 0, 1) ?>% margin</small>
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