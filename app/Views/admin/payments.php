<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

  <div class="container-fluid">
    <div class="row mb-4">
      <div class="col-12">
        <div class="card shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center">
             <h5 class="card-title mb-0">All Payments</h5>
             <button 
               class="btn btn-primary btn-sm"
               data-bs-toggle="modal"
               data-bs-target="#addPaymentModal"
           >
               <i class="fas fa-plus me-2"></i>Add Payment
           </button>
           </div>
           <div class="card-body">
             <!-- Search and Filter Row -->
             <div class="row mb-3">
                 <div class="col-md-6">
                     <input type="text" id="searchStudentName" class="form-control" placeholder="Search student name or ID...">
                 </div>
                 <div class="col-md-4">
                     <select id="statusFilter" class="form-select">
                         <option value="">All Status</option>
                         <option value="fully paid">Fully Paid</option>
                         <option value="partial">Partial</option>
                     </select>
                 </div>
             </div>
             
             <!-- Table with scrollable body -->
             <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
               <table class="table table-hover">
                 <thead class="table-light sticky-top">
                   <tr>
                     <th>Student ID</th>
                     <th>Student Name</th>
                     <th>Amount</th>
                     <th>Payment Type</th>
                     <th>Date</th>
                     <th>Status</th>
                     <th>Actions</th>
                   </tr>
                 </thead>
                 <tbody id="paymentsTableBody">
                                         <?php if (!empty($recentPayments)): ?>
                         <?php foreach ($recentPayments as $payment): ?>
                             <tr data-payment-status="<?= esc($payment['payment_status']) ?>">
                                 <td><?= esc($payment['payer_id']) ?></td>
                                 <td><?= esc($payment['payer_name']) ?></td>
                                 <td>₱<?= number_format($payment['amount_paid'], 2) ?></td>
                                 <td><?= esc($payment['contribution_title'] ?? 'N/A') ?></td>
                                 <td><?= date('M d, Y', strtotime($payment['payment_date'])) ?></td>
                                 <td>
                                     <?php if ($payment['payment_status'] === 'fully paid'): ?>
                                         <span class="badge bg-success">Fully Paid</span>
                                     <?php elseif ($payment['payment_status'] === 'partial'): ?>
                                         <span class="badge bg-warning">Partial</span>
                                     <?php endif; ?>
                                 </td>
                                 <td>
                                    <button class="btn btn-sm btn-outline-primary" onclick="viewPaymentReceipt(<?= $payment['id'] ?>)">
                                        <i class="fas fa-eye me-1"></i>View Receipt
                                    </button>
                                    <button class="btn btn-sm btn-outline-secondary" onclick="editPayment(<?= $payment['id'] ?>)">
                                        <i class="fas fa-edit me-1"></i>Edit
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted">No payment records found.</td>
                        </tr>
                    <?php endif; ?>
                 </tbody>
               </table>
             </div>
          </div>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-lg-4 col-md-6 mb-4">
        <div class="card border-0 shadow-sm">
          <div class="card-body">
            <div class="d-flex align-items-center">
              <div class="flex-shrink-0">
                <div class="bg-primary text-white rounded p-3">
                  <i class="fas fa-money-bill-wave fa-lg"></i>
                </div>
              </div>
              <div class="flex-grow-1 ms-3">
                <h6 class="card-title mb-1">Today's Payments</h6>
                <h4 class="text-primary mb-0">₱15,750</h4>
                <small class="text-muted">+12% from yesterday</small>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <div class="col-lg-4 col-md-6 mb-4">
        <div class="card border-0 shadow-sm">
          <div class="card-body">
            <div class="d-flex align-items-center">
              <div class="flex-shrink-0">
                <div class="bg-success text-white rounded p-3">
                  <i class="fas fa-check-circle fa-lg"></i>
                </div>
              </div>
              <div class="flex-grow-1 ms-3">
                <h6 class="card-title mb-1">Completed</h6>
                <h4 class="text-success mb-0">25</h4>
                <small class="text-muted">transactions today</small>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <div class="col-lg-4 col-md-6 mb-4">
        <div class="card border-0 shadow-sm">
          <div class="card-body">
            <div class="d-flex align-items-center">
              <div class="flex-shrink-0">
                <div class="bg-warning text-white rounded p-3">
                  <i class="fas fa-clock fa-lg"></i>
                </div>
              </div>
              <div class="flex-grow-1 ms-3">
                <h6 class="card-title mb-1">Pending</h6>
                <h4 class="text-warning mb-0">8</h4>
                <small class="text-muted">awaiting confirmation</small>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Add Payment Modal -->
   <?php $contributions = $contributions ?? []; ?>
   <?= view('partials/modal-add-payment', [
    'action' => base_url('payments/save'),  // controller route to handle form submission
    'title' => 'Add Payment',
    'contributions' => $contributions // array of contributions for the dropdown
]) ?>

<!-- QR Receipt Modal -->
<?= view('partials/modal-qr-receipt', [
    'title' => 'Payment Receipt',
]) ?>

<script>
// Define base URL for payment.js
window.APP_BASE_URL = '<?= base_url() ?>';

// Function to view payment receipt
function viewPaymentReceipt(paymentId) {
    // Fetch payment data
    fetch(`${window.APP_BASE_URL}/payments/recent`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.payments) {
                // Find the specific payment
                const payment = data.payments.find(p => p.id == paymentId);
                if (payment) {
                    // Show QR receipt modal
                    if (typeof showQRReceipt === 'function') {
                        showQRReceipt(payment);
                    } else {
                        console.error('showQRReceipt function not found');
                        showNotification('QR Receipt modal not available', 'danger');
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

// Function to edit payment
function editPayment(paymentId) {
    showNotification('Edit payment feature coming soon! Payment ID: ' + paymentId, 'info');
}

// Search and Filter Functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchStudentName');
    const statusFilter = document.getElementById('statusFilter');
    
         function filterTable() {
         const searchValue = searchInput.value.toLowerCase().trim();
         const statusValue = statusFilter.value;
         const tableRows = document.querySelectorAll('#paymentsTableBody tr');
         
         tableRows.forEach(row => {
             const studentId = row.querySelector('td:nth-child(1)').textContent.toLowerCase();
             const studentName = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
             
             // Get status directly from data attribute
             const rowStatus = row.getAttribute('data-payment-status') || '';
             
             // Check if row matches search and status filter
             const matchesSearch = searchValue === '' || studentId.includes(searchValue) || studentName.includes(searchValue);
             const matchesStatus = statusValue === '' || rowStatus === statusValue;
             
             if (matchesSearch && matchesStatus) {
                 row.style.display = '';
             } else {
                 row.style.display = 'none';
             }
         });
     }
    
    if (searchInput) {
        searchInput.addEventListener('input', filterTable);
    }
    
    if (statusFilter) {
        statusFilter.addEventListener('change', filterTable);
    }
});
</script>

<script src="<?= base_url('js/payment.js') ?>"></script>

<?= $this->endSection() ?>