<?= $this->extend('layouts/payer-layout') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-user-circle me-2"></i>My Information</h5>
                </div>
                <div class="card-body">
                    <?php if ($payer): ?>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <strong><i class="fas fa-id-card text-primary me-2"></i>Payer ID:</strong>
                            </div>
                            <div class="col-md-8">
                                <?= esc($payer['payer_id']) ?>
                            </div>
                        </div>
                        <hr>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <strong><i class="fas fa-user text-primary me-2"></i>Full Name:</strong>
                            </div>
                            <div class="col-md-8">
                                <?= esc($payer['payer_name']) ?>
                            </div>
                        </div>
                        <hr>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <strong><i class="fas fa-envelope text-primary me-2"></i>Email Address:</strong>
                            </div>
                            <div class="col-md-8">
                                <?= esc($payer['email_address']) ?>
                            </div>
                        </div>
                        <hr>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <strong><i class="fas fa-phone text-primary me-2"></i>Contact Number:</strong>
                            </div>
                            <div class="col-md-8">
                                <?= esc($payer['contact_number']) ?>
                            </div>
                        </div>
                        <hr>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <strong><i class="fas fa-calendar text-primary me-2"></i>Member Since:</strong>
                            </div>
                            <div class="col-md-8">
                                <?= date('M d, Y', strtotime($payer['created_at'])) ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>No information available.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
