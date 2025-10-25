<!-- All Payments Modal -->
<div class="modal fade" id="allPaymentsModal" tabindex="-1" aria-labelledby="allPaymentsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="allPaymentsModalLabel">All Payments</h5>
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
                                    <tr>
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
});
</script>