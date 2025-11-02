<!-- Active Contributions List -->
<div class="row g-3" id="contributionsContainer">
    <?php if (!empty($contributions)): ?>
        <?php foreach ($contributions as $contribution): ?>
            <div class="col-12 contribution-item" 
                 id="contribution-<?= $contribution['id'] ?>"
                 data-category="<?= esc($contribution['category'] ?? 'other') ?>" 
                 data-status="<?= esc($contribution['status'] ?? 'active') ?>"
                 data-title="<?= strtolower(esc($contribution['title'])) ?>"
                 data-amount="<?= esc($contribution['amount']) ?>">
                <div class="card border-0 shadow-sm" style="transition: transform 0.2s, box-shadow 0.2s; cursor: pointer;" 
                     onclick="showContributionPayments(<?= $contribution['id'] ?>, '<?= esc($contribution['title']) ?>', <?= esc($contribution['amount']) ?>)">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="contribution-icon me-3">
                                <div class="icon-wrapper d-flex align-items-center justify-content-center rounded-3" style="width: 64px; height: 64px; background: linear-gradient(135deg, #3b82f6, #0ea5e9);">
                                    <i class="fas fa-hand-holding-usd text-white fs-3"></i>
                                </div>
                            </div>
                            
                            <div class="flex-grow-1">
                                <h5 class="mb-1 fw-semibold"><?= esc($contribution['title']) ?></h5>
                                <?php if (!empty($contribution['contribution_code'])): ?>
                                    <p class="text-muted mb-1">
                                        <i class="fas fa-hashtag me-1"></i>
                                        <strong>Code:</strong> <code><?= esc($contribution['contribution_code']) ?></code>
                                    </p>
                                <?php endif; ?>
                                <p class="text-muted mb-2"><?= esc($contribution['description'] ?? 'No description available') ?></p>
                                
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="contribution-amount flex-grow-1">
                                        <div class="mb-2">
                                            <span class="h5 text-primary fw-bold">₱<?= number_format($contribution['amount'], 2) ?></span>
                                            <small class="text-muted d-block">
                                                <span class="badge bg-info me-2"><?= ucfirst(esc($contribution['category'] ?? 'other')) ?></span>
                                                Status: 
                                                <span class="badge <?= $contribution['status'] === 'active' ? 'bg-success' : 'bg-secondary' ?>">
                                                    <?= ucfirst($contribution['status']) ?>
                                                </span>
                                            </small>
                                        </div>
                                        
                                        <!-- Collection Stats -->
                                        <div class="collection-stats mb-2">
                                            <div class="mb-1">
                                                <small class="text-muted d-block mb-1">
                                                    <i class="fas fa-bullseye text-info me-1"></i>
                                                    Target: <strong class="text-info">₱<?= number_format($contribution['target_amount'] ?? $contribution['grand_total'] ?? $contribution['amount'] ?? 0, 2) ?></strong>
                                                    <span class="text-muted">(₱<?= number_format($contribution['amount'] ?? 0, 2) ?> per payer)</span>
                                                </small>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <small class="text-muted">
                                                    <i class="fas fa-check-circle text-success me-1"></i>
                                                    Collected: 
                                                    <strong class="text-success">₱<?= number_format($contribution['total_collected'] ?? 0, 2) ?></strong>
                                                </small>
                                                <small class="text-muted">
                                                    <i class="fas fa-clock text-warning me-1"></i>
                                                    Remaining: 
                                                    <strong class="text-warning">₱<?= number_format($contribution['total_remaining'] ?? 0, 2) ?></strong>
                                                </small>
                                            </div>
                                            
                                            <!-- Progress Bar -->
                                            <div class="progress" style="height: 8px; border-radius: 4px;">
                                                <?php 
                                                $progressPercentage = $contribution['progress_percentage'] ?? 0;
                                                $progressClass = $progressPercentage >= 100 ? 'bg-success' : ($progressPercentage >= 50 ? 'bg-info' : 'bg-warning');
                                                ?>
                                                <div class="progress-bar <?= $progressClass ?>" 
                                                     role="progressbar" 
                                                     style="width: <?= $progressPercentage ?>%" 
                                                     aria-valuenow="<?= $progressPercentage ?>" 
                                                     aria-valuemin="0" 
                                                     aria-valuemax="100"
                                                     title="<?= round($progressPercentage, 1) ?>% Collected">
                                                </div>
                                            </div>
                                            <small class="text-muted d-block mt-1">
                                                <strong><?= round($progressPercentage, 1) ?>%</strong> collected
                                            </small>
                                        </div>
                                    </div>
                                    <div class="contribution-actions" onclick="event.stopPropagation();">
                                        <button class="btn btn-sm btn-outline-warning me-2" 
                                                style="width: 36px; height: 36px;"
                                                onclick="editContribution(<?= $contribution['id'] ?>)"
                                                title="Edit Contribution">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm <?= $contribution['status'] === 'active' ? 'btn-outline-success' : 'btn-outline-secondary' ?> me-2" 
                                                style="width: 36px; height: 36px;"
                                                onclick="toggleContributionStatus(<?= $contribution['id'] ?>, '<?= esc($contribution['status']) ?>')"
                                                title="Toggle Status">
                                            <i class="fas fa-toggle-<?= $contribution['status'] === 'active' ? 'on' : 'off' ?>"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger" 
                                                style="width: 36px; height: 36px;"
                                                onclick="deleteContribution(<?= $contribution['id'] ?>)"
                                                title="Delete Contribution">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="col-12">
            <div class="text-center py-5">
                <div class="mb-3">
                    <i class="fas fa-hand-holding-usd text-muted" style="font-size: 3rem;"></i>
                </div>
                <h5 class="text-muted">No contributions found</h5>
                <p class="text-muted">Start by adding your first contribution type.</p>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
.contribution-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15) !important;
}

.contribution-item .card {
    transition: all 0.3s ease;
}

.contribution-item:hover .card {
    background-color: #f8f9fa;
}

.contribution-actions button {
    transition: all 0.2s ease;
}

.contribution-actions button:hover {
    transform: scale(1.1);
}

</style>