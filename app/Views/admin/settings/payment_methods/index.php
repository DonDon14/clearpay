<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-gray-800"><?= esc($pageTitle) ?></h1>
                    <p class="text-muted mb-0"><?= esc($pageSubtitle) ?></p>
                </div>
                <div>
                    <a href="<?= base_url('admin/settings/payment-methods/create') ?>" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Add New Payment Method
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Flash Messages -->
    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            <?= session()->getFlashdata('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            <?= session()->getFlashdata('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Payment Methods Table -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-credit-card me-2 text-primary"></i>
                        Payment Methods
                    </h5>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($paymentMethods)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-credit-card fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No Payment Methods Found</h5>
                            <p class="text-muted">Get started by adding your first payment method.</p>
                            <a href="<?= base_url('admin/settings/payment-methods/create') ?>" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>Add Payment Method
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="border-0">Name</th>
                                        <th class="border-0">Description</th>
                                        <th class="border-0">Account Details</th>
                                        <th class="border-0">Status</th>
                                        <th class="border-0">Created</th>
                                        <th class="border-0 text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($paymentMethods as $method): ?>
                                        <tr>
                                            <td class="align-middle">
                                                <div class="d-flex align-items-center">
                                                    <div class="me-3">
                                                        <i class="fas fa-credit-card text-primary"></i>
                                                    </div>
                                                    <div>
                                                        <strong><?= esc($method['name']) ?></strong>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="align-middle">
                                                <?php if (!empty($method['description'])): ?>
                                                    <span class="text-muted"><?= esc($method['description']) ?></span>
                                                <?php else: ?>
                                                    <span class="text-muted fst-italic">No description</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="align-middle">
                                                <?php if (!empty($method['account_details'])): ?>
                                                    <span class="text-muted"><?= esc($method['account_details']) ?></span>
                                                <?php else: ?>
                                                    <span class="text-muted fst-italic">No details</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="align-middle">
                                                <?php if ($method['status'] === 'active'): ?>
                                                    <span class="badge bg-success">
                                                        <i class="fas fa-check-circle me-1"></i>Active
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">
                                                        <i class="fas fa-times-circle me-1"></i>Inactive
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="align-middle">
                                                <small class="text-muted">
                                                    <?= date('M j, Y', strtotime($method['created_at'])) ?>
                                                </small>
                                            </td>
                                            <td class="align-middle text-center">
                                                <div class="btn-group" role="group">
                                                    <a href="<?= base_url('admin/settings/payment-methods/edit/' . $method['id']) ?>" 
                                                       class="btn btn-sm btn-outline-primary" 
                                                       title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="<?= base_url('admin/settings/payment-methods/toggle-status/' . $method['id']) ?>" 
                                                       class="btn btn-sm btn-outline-<?= $method['status'] === 'active' ? 'warning' : 'success' ?>" 
                                                       title="<?= $method['status'] === 'active' ? 'Deactivate' : 'Activate' ?>"
                                                       onclick="return confirm('Are you sure you want to <?= $method['status'] === 'active' ? 'deactivate' : 'activate' ?> this payment method?')">
                                                        <i class="fas fa-<?= $method['status'] === 'active' ? 'pause' : 'play' ?>"></i>
                                                    </a>
                                                    <a href="<?= base_url('admin/settings/payment-methods/delete/' . $method['id']) ?>" 
                                                       class="btn btn-sm btn-outline-danger" 
                                                       title="Delete"
                                                       onclick="return confirm('Are you sure you want to delete this payment method? This action cannot be undone.')">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </div>
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
    </div>
</div>

<?= $this->endSection() ?>
