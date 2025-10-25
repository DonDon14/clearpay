<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

  <div class="container-fluid">
    <div class="row mb-4">
      <div class="col-12">
        <div class="card shadow-sm">
          <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Recent Payments</h5>
            <button 
              class="btn btn-primary btn-sm"
              data-bs-toggle="modal"
              data-bs-target="#addPaymentModal"
          >
              <i class="fas fa-plus me-2"></i>Add Payment
          </button>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-hover">
                <thead>
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
                <tbody>
                    <?php if (!empty($recentPayments)): ?>
                        <?php foreach ($recentPayments as $payment): ?>
                            <tr>
                                <td><?= esc($payment['payer_id']) ?></td>
                                <td><?= esc($payment['payer_name']) ?></td>
                                <td>₱<?= number_format($payment['amount_paid'], 2) ?></td>
                                <td><?= esc($payment['contribution_title'] ?? 'N/A') ?></td>
                                <td><?= date('M d, Y', strtotime($payment['payment_date'])) ?></td>
                                <td>
                                    <?php if ($payment['payment_status'] === 'fully paid'): ?>
                                        <span class="badge bg-success">Completed</span>
                                    <?php elseif ($payment['payment_status'] === 'partial'): ?>
                                        <span class="badge bg-warning">Partial</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Unpaid</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary">View</button>
                                    <button class="btn btn-sm btn-outline-secondary">Edit</button>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<?= $this->endSection() ?>