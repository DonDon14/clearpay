<?= $this->extend('layouts/payer-layout') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-bullhorn me-2"></i>Announcements</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($announcements)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-bell-slash fa-4x text-muted mb-3"></i>
                            <h5 class="text-muted">No Announcements</h5>
                            <p class="text-muted">Check back later for updates</p>
                        </div>
                    <?php else: ?>
                        <div class="row">
                            <?php foreach ($announcements as $announcement): ?>
                                <div class="col-md-6 mb-4">
                                    <div class="card h-100 border-left-primary">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <h6 class="card-title mb-0">
                                                    <i class="fas fa-<?= $announcement['type'] === 'urgent' ? 'exclamation-triangle text-warning' : 'info-circle text-info' ?> me-2"></i>
                                                    <?= esc($announcement['title']) ?>
                                                </h6>
                                                <span class="badge bg-<?= $announcement['priority'] === 'critical' ? 'danger' : ($announcement['priority'] === 'high' ? 'warning' : 'info') ?>">
                                                    <?= esc(ucfirst($announcement['priority'])) ?>
                                                </span>
                                            </div>
                                            <p class="card-text text-muted">
                                                <?= esc($announcement['text']) ?>
                                            </p>
                                            <small class="text-muted">
                                                <i class="fas fa-calendar me-1"></i>
                                                <?= date('M d, Y g:i A', strtotime($announcement['created_at'])) ?>
                                            </small>
                                        </div>
                                    </div>
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
