<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<!-- Stats Cards Row -->
<div class="container-fluid mb-4">
    <div class="row g-3">
        <div class="col-xl-3 col-lg-6 col-md-6">
            <?= view('partials/card', [
                'icon' => 'fas fa-hourglass-half',
                'iconColor' => 'text-warning',
                'title' => 'Pending',
                'text' => '12'
            ]) ?>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6">
            <?= view('partials/card', [
                'icon' => 'fas fa-clock',
                'iconColor' => 'text-info',
                'title' => 'In Progress',
                'text' => '8'
            ]) ?>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6">
            <?= view('partials/card', [
                'icon' => 'fas fa-dollar-sign',
                'iconColor' => 'text-success',
                'title' => 'Total Collected',
                'text' => '₱45,750.00'
            ]) ?>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6">
            <?= view('partials/card', [
                'icon' => 'fas fa-exclamation-triangle',
                'iconColor' => 'text-danger',
                'title' => 'Outstanding',
                'text' => '₱12,250.00'
            ]) ?>
        </div>
    </div>
</div>

<!-- Quick Actions Section -->
<?= view('partials/container-card', [
    'title' => '<i class="fas fa-bolt me-2"></i>Quick Actions',
    'subtitle' => 'Common tasks for partial payment management',
    'cardClass' => 'shadow-sm border-0',
    'bodyClass' => 'p-0',
    'content' => view('partials/partial_payments_quick_actions')
]) ?>

<!-- Active Partial Payments Section -->
<?= view('partials/container-card', [
    'title' => '<i class="fas fa-clock me-2"></i>Active Partial Payments',
    'subtitle' => 'Students with ongoing installment payments',
    'cardClass' => 'shadow-sm border-0',
    'bodyClass' => 'p-3',
    'content' => view('partials/partial_payments_list', [
        'partialPayments' => $partialPayments ?? []
    ])
]) ?>

<!-- Payment Modal -->
<div id="partialPaymentModal" class="modal fade" tabindex="-1" style="display: none;">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-plus-circle me-2"></i>
                    Add Payment Installment
                </h5>
                <button type="button" class="btn-close" onclick="closePaymentModal()"></button>
            </div>
            <div class="modal-body">
                <!-- Payment Summary Card -->
                <div class="card bg-light border-0 mb-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="student-avatar-large" style="width: 60px; height: 60px; background: linear-gradient(135deg, #3b82f6, #0ea5e9); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.25rem;">
                                <i class="fas fa-user"></i>
                            </div>
                            <div>
                                <h5 id="modalStudentName" class="mb-1"></h5>
                                <p class="text-muted mb-1">ID: <span id="modalStudentId"></span></p>
                                <p class="text-muted mb-0 small">Contribution: <span id="modalContributionTitle"></span></p>
                            </div>
                        </div>
                        
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="text-center p-3 bg-white rounded border">
                                    <div class="h5 text-info mb-1" id="modalTotalDue">₱0.00</div>
                                    <small class="text-muted text-uppercase">Total Due</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-center p-3 bg-white rounded border">
                                    <div class="h5 text-success mb-1" id="modalAlreadyPaid">₱0.00</div>
                                    <small class="text-muted text-uppercase">Already Paid</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-center p-3 bg-warning bg-opacity-10 rounded border border-warning">
                                    <div class="h5 text-warning mb-1" id="modalRemainingBalance">₱0.00</div>
                                    <small class="text-warning text-uppercase">Remaining</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Payment Form -->
                <form id="partialPaymentForm">
                    <input type="hidden" id="hiddenContributionId" name="contribution_id">
                    <input type="hidden" id="hiddenStudentId" name="student_id">
                    <input type="hidden" id="hiddenStudentName" name="student_name">
                    
                    <div class="mb-3">
                        <label for="paymentAmount" class="form-label">Payment Amount (₱)</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-peso-sign"></i></span>
                            <input type="number" id="paymentAmount" name="amount" class="form-control" step="0.01" min="0.01" required placeholder="Enter installment amount">
                        </div>
                        <div class="form-text">Enter the amount for this installment payment</div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="paymentMethodModal" class="form-label">Payment Method</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-credit-card"></i></span>
                            <select id="paymentMethodModal" name="payment_method" class="form-select" required>
                                <option value="cash">Cash</option>
                                <option value="card">Card</option>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="gcash">GCash</option>
                                <option value="mobile_payment">Mobile Payment</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closePaymentModal()">
                    <i class="fas fa-times me-1"></i>Cancel
                </button>
                <button type="submit" form="partialPaymentForm" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i>Record Payment
                </button>
            </div>
        </div>
    </div>
</div>



<script>
// Modal functions
function openPaymentModal(contributionId, studentId, studentName, contributionTitle, totalDue, remainingBalance) {
    const modal = document.getElementById('partialPaymentModal');
    const alreadyPaid = totalDue - remainingBalance;
    
    // Populate modal data
    document.getElementById('modalStudentName').textContent = studentName;
    document.getElementById('modalStudentId').textContent = studentId;
    document.getElementById('modalContributionTitle').textContent = contributionTitle;
    document.getElementById('modalTotalDue').textContent = '₱' + totalDue.toFixed(2);
    document.getElementById('modalAlreadyPaid').textContent = '₱' + alreadyPaid.toFixed(2);
    document.getElementById('modalRemainingBalance').textContent = '₱' + remainingBalance.toFixed(2);
    
    // Populate hidden form fields
    document.getElementById('hiddenContributionId').value = contributionId;
    document.getElementById('hiddenStudentId').value = studentId;
    document.getElementById('hiddenStudentName').value = studentName;
    
    // Set max payment amount
    document.getElementById('paymentAmount').max = remainingBalance.toFixed(2);
    document.getElementById('paymentAmount').placeholder = 'Max: ₱' + remainingBalance.toFixed(2);
    
    // Show modal
    modal.style.display = 'block';
}

function closePaymentModal() {
    const modal = document.getElementById('partialPaymentModal');
    modal.style.display = 'none';
    document.getElementById('partialPaymentForm').reset();
}

// Helper functions
function refreshPayments() {
    window.location.reload();
}

function showPaymentStats() {
    alert('Payment analytics feature coming soon!');
}

function sendReminders() {
    alert('Reminder system coming soon!');
}

function viewPaymentHistory(contributionId, studentId) {
    window.location.href = `<?= base_url('payments/history') ?>?contribution_id=${contributionId}&student_id=${studentId}`;
}

// Form submission
document.getElementById('partialPaymentForm').addEventListener('submit', function(e) {
    e.preventDefault();
    // Add your form submission logic here
    alert('Payment recorded successfully!');
    closePaymentModal();
});
</script>


<?= $this->endSection() ?>