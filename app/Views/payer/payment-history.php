<?= $this->extend('layouts/payer-layout') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-history me-2"></i>Payment History</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($payments)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                            <h5 class="text-muted">No Payment Records</h5>
                            <p class="text-muted">Your payment history will appear here</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Amount</th>
                                        <th>Reference Number</th>
                                        <th>Payment Method</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($payments as $payment): ?>
                                        <tr class="payment-row" style="cursor: pointer;" 
                                            data-payment='<?= json_encode($payment) ?>'
                                            title="Click to view QR receipt"
                                            onmouseover="this.style.backgroundColor='#f8f9fa'" 
                                            onmouseout="this.style.backgroundColor=''">
                                            <td><?= date('M d, Y', strtotime($payment['payment_date'] ?? $payment['created_at'])) ?></td>
                                            <td><strong>₱<?= number_format($payment['amount_paid'], 2) ?></strong></td>
                                            <td><code><?= esc($payment['reference_number'] ?? 'N/A') ?></code></td>
                                            <td><?= esc(ucfirst($payment['payment_method'] ?? 'N/A')) ?></td>
                                            <td>
                                                <span class="badge payment-status-badge" 
                                                      data-total-paid="<?= $payment['amount_paid'] ?>" 
                                                      data-total-amount="<?= $payment['amount_paid'] ?>">
                                                    <!-- Status will be calculated by JavaScript -->
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
    </div>
</div>

<!-- QR Receipt Modal -->
<?= $this->include('partials/modal-qr-receipt') ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Calculate payment statuses dynamically
    calculatePaymentStatuses();
    
    // Add click handlers to payment rows
    const paymentRows = document.querySelectorAll('.payment-row');
    paymentRows.forEach(row => {
        row.addEventListener('click', function() {
            const paymentData = JSON.parse(this.getAttribute('data-payment'));
            console.log('Payment clicked:', paymentData);
            
            // Show QR receipt modal
            if (typeof window.showQRReceipt === 'function') {
                window.showQRReceipt(paymentData);
            } else {
                console.error('showQRReceipt function not available');
                alert('QR receipt functionality not available. Please refresh the page.');
            }
        });
    });
    
    // Function to calculate payment status dynamically
    function calculatePaymentStatuses() {
        const statusBadges = document.querySelectorAll('.payment-status-badge');
        
        statusBadges.forEach(badge => {
            const totalPaid = parseFloat(badge.getAttribute('data-total-paid')) || 0;
            const totalAmount = parseFloat(badge.getAttribute('data-total-amount')) || 0;
            
            let status, statusClass, statusText;
            
            if (totalPaid === 0) {
                status = 'unpaid';
                statusClass = 'bg-secondary text-white';
                statusText = 'UNPAID';
            } else if (totalPaid >= totalAmount) {
                status = 'fully paid';
                statusClass = 'bg-primary text-white';
                statusText = 'COMPLETED';
            } else {
                status = 'partial';
                statusClass = 'bg-warning text-dark';
                statusText = 'PARTIAL';
            }
            
            // Update badge appearance
            badge.className = `badge ${statusClass}`;
            badge.textContent = statusText;
            
            // Store calculated status for modal use
            badge.setAttribute('data-calculated-status', status);
        });
    }
});
</script>

<?= $this->endSection() ?>
