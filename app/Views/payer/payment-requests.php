<?= $this->extend('layouts/payer-layout') ?>
<?php $peso = '&#8369;'; ?>

<?= $this->section('content') ?>
<div class="container-fluid ui-page-shell payer-page-shell">
    <?= view('partials/payer-page-intro', [
        'title' => 'Your Payment Requests',
        'subtitle' => 'Track submitted requests, view proof of payment, and check admin feedback.',
        'actionsHtml' => '
            <button type="button" class="btn btn-outline-primary btn-sm" onclick="refreshPaymentRequests()">
                <i class="fas fa-sync-alt me-1"></i>Refresh
            </button>
            <a href="' . base_url('payer/products') . '" class="btn btn-primary btn-sm">
                <i class="fas fa-plus me-1"></i>Request New Payment
            </a>
        ',
    ]) ?>

    <div class="row">
        <div class="col-12">
            <div class="card border-0 ui-surface-card">
                <div class="card-header ui-surface-card-header">
                    <h5 class="mb-0"><i class="fas fa-paper-plane me-2"></i>Your Payment Requests</h5>
                </div>
                <div class="card-body ui-surface-card-body">
                    <?php if (empty($paymentRequests)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                            <h5 class="text-muted">No Payment Requests</h5>
                            <p class="text-muted">You haven't submitted any payment requests yet.</p>
                            <a href="<?= base_url('payer/products') ?>" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>Submit Your First Payment Request
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive ui-table-wrap">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Date</th>
                                        <th>Item</th>
                                        <th>Amount</th>
                                        <th>Method</th>
                                        <th>Status</th>
                                        <th>Reference</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($paymentRequests as $request): ?>
                                        <tr>
                                            <td>
                                                <small class="text-muted">
                                                    <?= date('M d, Y', strtotime($request['requested_at'])) ?><br>
                                                    <?= date('g:i A', strtotime($request['requested_at'])) ?>
                                                </small>
                                            </td>
                                            <td>
                                                <strong><?= esc($request['item_title']) ?></strong>
                                                <span class="badge <?= ($request['item_type'] ?? 'contribution') === 'product' ? 'bg-primary' : 'bg-success' ?> ms-1">
                                                    <?= ucfirst($request['item_type'] ?? 'contribution') ?>
                                                </span><br>
                                                <?php if (($request['item_type'] ?? 'contribution') === 'product'): ?>
                                                    <small class="text-muted">Qty: <?= (int)($request['quantity'] ?? 1) ?></small><br>
                                                <?php endif; ?>
                                                <small class="text-muted"><?= esc(substr($request['item_description'] ?? '', 0, 50)) ?><?= strlen($request['item_description'] ?? '') > 50 ? '...' : '' ?></small>
                                            </td>
                                            <td>
                                                <strong><?= $peso ?><?= number_format($request['requested_amount'], 2) ?></strong>
                                            </td>
                                            <td>
                                                <span class="badge bg-info"><?= ucfirst(str_replace('_', ' ', $request['payment_method'])) ?></span>
                                            </td>
                                            <td>
                                                <?php
                                                $statusClass = 'bg-secondary';
                                                $statusText = ucfirst($request['status']);

                                                switch ($request['status']) {
                                                    case 'pending':
                                                        $statusClass = 'bg-warning text-dark';
                                                        break;
                                                    case 'approved':
                                                        $statusClass = 'bg-success';
                                                        break;
                                                    case 'rejected':
                                                        $statusClass = 'bg-danger';
                                                        break;
                                                    case 'processed':
                                                        $statusClass = 'bg-primary';
                                                        break;
                                                }
                                                ?>
                                                <span class="badge <?= $statusClass ?>"><?= $statusText ?></span>
                                            </td>
                                            <td>
                                                <code class="small"><?= esc($request['reference_number']) ?></code>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <?php if ($request['proof_of_payment_path']): ?>
                                                        <button type="button" class="btn btn-outline-info" onclick="viewProofOfPayment('<?= $request['proof_of_payment_path'] ?>', '<?= $request['reference_number'] ?>')" title="View Proof of Payment">
                                                            <i class="fas fa-image"></i>
                                                        </button>
                                                    <?php endif; ?>

                                                    <?php if ($request['admin_notes']): ?>
                                                        <button type="button" class="btn btn-outline-warning" onclick="viewAdminNotes('<?= esc($request['admin_notes']) ?>')" title="View Admin Notes">
                                                            <i class="fas fa-sticky-note"></i>
                                                        </button>
                                                    <?php endif; ?>
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

<div class="modal fade" id="proofOfPaymentModal" tabindex="-1" aria-labelledby="proofOfPaymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="proofOfPaymentModalLabel">
                    <i class="fas fa-image me-2"></i>Proof of Payment
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <img id="proofImage" src="" alt="Proof of Payment" class="img-fluid" style="max-height: 500px;">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <a id="downloadProofBtn" href="" download class="btn btn-primary">
                    <i class="fas fa-download me-2"></i>Download
                </a>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="adminNotesModal" tabindex="-1" aria-labelledby="adminNotesModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="adminNotesModalLabel">
                    <i class="fas fa-sticky-note me-2"></i>Admin Notes
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p id="adminNotesContent"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
function refreshPaymentRequests() {
    location.reload();
}

function viewProofOfPayment(imagePath, referenceNumber) {
    const modal = new bootstrap.Modal(document.getElementById('proofOfPaymentModal'));
    const imgElement = document.getElementById('proofImage');
    imgElement.src = imagePath;
    imgElement.onerror = function() {
        this.src = '<?= base_url('assets/img/placeholder-image.png') ?>';
        this.onerror = null;
    };
    document.getElementById('downloadProofBtn').href = imagePath;
    document.getElementById('downloadProofBtn').download = `proof_of_payment_${referenceNumber}.jpg`;
    modal.show();
}

function viewAdminNotes(notes) {
    const modal = new bootstrap.Modal(document.getElementById('adminNotesModal'));
    document.getElementById('adminNotesContent').textContent = notes;
    modal.show();
}
</script>
<?= $this->endSection() ?>
