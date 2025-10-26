<!-- All Payments Modal -->
<style>
/* Global fix for backdrop z-index issue */
body.modal-open .modal-backdrop.fade.show {
    z-index: 1040 !important;
}

/* Override for All Payments specifically */
body.modal-open #allPaymentsModal ~ .modal-backdrop.fade.show {
    z-index: 1045 !important;
    /* Ensure backdrop is semi-transparent but visible */
    opacity: 0.5 !important;
}

/* Ensure dashboard content behind modals is not faded */
body.modal-open .content,
body.modal-open .main-content {
    opacity: 1 !important;
}

.payment-row {
    transition: background-color 0.2s ease;
}

.payment-row:hover {
    background-color: #f0f9ff !important;
}

.payment-row:active {
    background-color: #dbeafe !important;
}

/* Ensure All Payments modal is above background but below QR modal */
#allPaymentsModal {
    z-index: 1055 !important;
}

#allPaymentsModal .modal-dialog {
    z-index: 1056 !important;
    margin: 1rem auto !important;
}

#allPaymentsModal .modal-content {
    z-index: 1057 !important;
    position: relative;
}

/* Ensure backdrop is below modal content */
#allPaymentsModal ~ .modal-backdrop {
    z-index: 9999 !important;
}
</style>

<div class="modal fade" id="allPaymentsModal" tabindex="-1" aria-labelledby="allPaymentsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <div>
                    <h5 class="modal-title" id="allPaymentsModalLabel">All Payments</h5>
                    <small class="text-white-50">
                        <i class="fas fa-mouse-pointer me-1"></i>
                        Click any payment to view QR receipt
                    </small>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <?php if (!empty($allPayments)): ?>
                    <!-- Search Input -->
                    <div class="mb-3">
                        <input 
                                type="text"
                                id="searchStudent" 
                                class="form-control" 
                                placeholder="Search student name..."
                                >
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle" id="paymentsTable">
                            <thead>
                                <tr>
                                    <th>Payer Name</th>
                                    <th>Contribution</th>
                                    <th>Amount Paid</th>
                                    <th>Status</th>
                                    <th>Payment Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($allPayments as $payment): ?>
                                    <tr class="payment-row" data-payment-id="<?= esc($payment['id']) ?>" style="cursor: pointer;">
                                        <td><?= esc($payment['payer_name']) ?></td>
                                        <td><?= esc($payment['contribution_title']) ?></td>
                                        <td>₱<?= number_format($payment['amount_paid'], 2) ?></td>
                                        <td>
                                            <span class="badge 
                                                <?= $payment['payment_status'] === 'fully paid' 
                                                    ? 'bg-success' 
                                                    : ($payment['payment_status'] === 'partial' ? 'bg-warning' : 'bg-danger') ?>">
                                                <?= strtoupper($payment['payment_status']) ?>
                                            </span>
                                        </td>
                                        <td><?= date('M d, Y h:i A', strtotime($payment['payment_date'])) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center text-muted">No payment records found.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- 🧠 JavaScript for Prefix-Based Search -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchStudent');

    if (searchInput) {
        searchInput.addEventListener('keyup', function() {
            const searchValue = this.value.toLowerCase().trim();
            const tableRows = document.querySelectorAll('#paymentsTable tbody tr');

            tableRows.forEach(row => {
                const payerName = row.querySelector('td:first-child').textContent.toLowerCase().trim();

                // ✅ Show only if payer name starts with the typed letters
                if (payerName.startsWith(searchValue) || searchValue === '') {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }

    // Handle payment row clicks to show QR receipt
    const paymentRows = document.querySelectorAll('.payment-row');
    paymentRows.forEach(row => {
        row.addEventListener('click', function() {
            const paymentId = this.getAttribute('data-payment-id');
            if (paymentId) {
                // Don't close the all payments modal - just show QR receipt on top
                // Fetch payment data and show QR receipt
                fetch(`${window.APP_BASE_URL}/payments/recent`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.payments) {
                            // Find the specific payment
                            const payment = data.payments.find(p => p.id == paymentId);
                            if (payment) {
                                // Show QR receipt modal (it will overlay on top)
                                if (typeof showQRReceipt === 'function') {
                                    showQRReceipt(payment);
                                } else {
                                    console.error('showQRReceipt function not found');
                                }
                            } else {
                                showNotification('Payment not found', 'warning');
                            }
                        } else {
                            showNotification('Error fetching payment data', 'danger');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showNotification('Error loading payment', 'danger');
                    });
            }
        });
    });
});
</script>