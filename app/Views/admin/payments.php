<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
  <div class="container-fluid">
    <div class="row mb-4">
      <div class="col-12">
        <div class="card shadow-sm">
          <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Recent Payments</h5>
            <button class="btn btn-primary btn-sm">
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
                  <tr>
                    <td>STU001</td>
                    <td>John Doe</td>
                    <td>₱2,500.00</td>
                    <td>Tuition Fee</td>
                    <td>Oct 24, 2025</td>
                    <td><span class="badge bg-success">Completed</span></td>
                    <td>
                      <button class="btn btn-sm btn-outline-primary">View</button>
                      <button class="btn btn-sm btn-outline-secondary">Edit</button>
                    </td>
                  </tr>
                  <tr>
                    <td>STU002</td>
                    <td>Jane Smith</td>
                    <td>₱1,800.00</td>
                    <td>Laboratory Fee</td>
                    <td>Oct 24, 2025</td>
                    <td><span class="badge bg-warning">Pending</span></td>
                    <td>
                      <button class="btn btn-sm btn-outline-primary">View</button>
                      <button class="btn btn-sm btn-outline-secondary">Edit</button>
                    </td>
                  </tr>
                  <tr>
                    <td>STU003</td>
                    <td>Mike Johnson</td>
                    <td>₱3,200.00</td>
                    <td>Miscellaneous Fee</td>
                    <td>Oct 23, 2025</td>
                    <td><span class="badge bg-success">Completed</span></td>
                    <td>
                      <button class="btn btn-sm btn-outline-primary">View</button>
                      <button class="btn btn-sm btn-outline-secondary">Edit</button>
                    </td>
                  </tr>
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
<?= $this->endSection() ?>