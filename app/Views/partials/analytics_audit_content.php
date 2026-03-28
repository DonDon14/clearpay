<div class="row g-4">
    <div class="col-lg-6">
        <h6 class="mb-3 fw-semibold">Suspicious Records</h6>
        <div class="activity-list">
            <?php if (!empty($payments['suspicious'])): ?>
                <?php foreach (array_slice($payments['suspicious'], 0, 5) as $item): ?>
                    <div class="activity-item p-3 mb-2 bg-light rounded border-start border-warning border-4">
                        <div class="d-flex justify-content-between align-items-start gap-3">
                            <div>
                                <h6 class="mb-1 fw-semibold"><?= esc($item['payer_name'] ?? 'Unknown Payer') ?></h6>
                                <p class="mb-1 small text-muted">
                                    <?= esc($item['contribution_title'] ?? 'Unknown Contribution') ?>
                                    · ID: <?= esc($item['payer_id_number'] ?? '-') ?>
                                </p>
                                <small class="text-warning-emphasis"><?= esc($item['reason'] ?? 'Flagged by Python analytics') ?></small>
                            </div>
                            <div class="text-end">
                                <div class="fw-semibold text-warning">&#8369;<?= number_format($item['amount_paid'] ?? 0, 2) ?></div>
                                <small class="text-muted"><?= esc($item['payment_day'] ?? '') ?></small>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-center py-4 text-muted">
                    <i class="fas fa-shield-alt fs-1 mb-3"></i>
                    <p class="mb-0">No suspicious records detected by Python analytics</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="col-lg-6">
        <h6 class="mb-3 fw-semibold">Duplicate Records</h6>
        <div class="activity-list">
            <?php if (!empty($payments['duplicates'])): ?>
                <?php foreach (array_slice($payments['duplicates'], 0, 5) as $item): ?>
                    <div class="activity-item p-3 mb-2 bg-light rounded border-start border-danger border-4">
                        <div class="d-flex justify-content-between align-items-start gap-3">
                            <div>
                                <h6 class="mb-1 fw-semibold"><?= esc($item['payer_name'] ?? 'Unknown Payer') ?></h6>
                                <p class="mb-1 small text-muted">
                                    <?= esc($item['contribution_title'] ?? 'Unknown Contribution') ?>
                                    · Receipt: <?= esc($item['receipt_number'] ?? '-') ?>
                                </p>
                                <small class="text-danger-emphasis"><?= esc($item['duplicate_reason'] ?? 'Duplicate pattern detected') ?></small>
                            </div>
                            <div class="text-end">
                                <div class="fw-semibold text-danger">&#8369;<?= number_format($item['amount_paid'] ?? 0, 2) ?></div>
                                <small class="text-muted"><?= esc($item['payment_day'] ?? '') ?></small>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-center py-4 text-muted">
                    <i class="fas fa-copy fs-1 mb-3"></i>
                    <p class="mb-0">No duplicate records detected by Python analytics</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
