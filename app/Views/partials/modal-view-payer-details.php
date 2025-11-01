<!-- View Payer Details Modal -->
<div class="modal fade" id="viewPayerDetailsModal" tabindex="-1" aria-labelledby="viewPayerDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="viewPayerDetailsModalLabel">
                    <i class="fas fa-user me-2"></i>Payer Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Payer Information -->
                <div class="card mb-3 border-0 shadow-sm">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Student Information</h6>
                    </div>
                    <div class="card-body">
                        <!-- Profile Picture Section -->
                        <div class="row mb-4">
                            <div class="col-12 text-center">
                                <div class="profile-picture-container d-inline-block">
                                    <div class="profile-picture-wrapper" style="width: 120px; height: 120px; border-radius: 50%; overflow: hidden; border: 4px solid #e9ecef; display: flex; align-items: center; justify-content: center; background: #f8f9fa;">
                                        <img id="viewPayerProfilePicture" 
                                             src="" 
                                             alt="Profile Picture" 
                                             style="width: 100%; height: 100%; object-fit: cover; display: none;">
                                        <i class="fas fa-user fa-3x text-muted" id="viewPayerProfileIcon"></i>
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <small class="text-muted">Profile Picture</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="text-muted small mb-1">Student ID</label>
                                <p class="mb-0 fw-bold" id="viewPayerId">-</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="text-muted small mb-1">Full Name</label>
                                <p class="mb-0 fw-bold" id="viewPayerName">-</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="text-muted small mb-1">Email Address</label>
                                <p class="mb-0" id="viewPayerEmail">-</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="text-muted small mb-1">Contact Number</label>
                                <p class="mb-0" id="viewPayerContact">-</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="text-muted small mb-1">Course/Department</label>
                                <p class="mb-0" id="viewPayerCourse">-</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Summary -->
                <div class="card mb-3 border-0 shadow-sm">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="fas fa-chart-line me-2"></i>Payment Summary</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 text-center mb-3">
                                <div class="p-3 bg-primary bg-opacity-10 rounded">
                                    <i class="fas fa-money-bill-wave text-primary mb-2" style="font-size: 2rem;"></i>
                                    <p class="text-muted mb-1 small">Total Amount Paid</p>
                                    <h4 class="mb-0 text-primary" id="viewTotalPaid">₱0.00</h4>
                                </div>
                            </div>
                            <div class="col-md-4 text-center mb-3">
                                <div class="p-3 bg-success bg-opacity-10 rounded">
                                    <i class="fas fa-list text-success mb-2" style="font-size: 2rem;"></i>
                                    <p class="text-muted mb-1 small">Total Payments</p>
                                    <h4 class="mb-0 text-success" id="viewTotalPayments">0</h4>
                                </div>
                            </div>
                            <div class="col-md-4 text-center mb-3">
                                <div class="p-3 bg-info bg-opacity-10 rounded">
                                    <i class="fas fa-calendar-alt text-info mb-2" style="font-size: 2rem;"></i>
                                    <p class="text-muted mb-1 small">Last Payment</p>
                                    <h6 class="mb-0 text-info" id="viewLastPayment">-</h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment History -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="fas fa-history me-2"></i>Payment History</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                            <table class="table table-hover mb-0">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th>Date</th>
                                        <th>Contribution</th>
                                        <th>Amount</th>
                                        <th>Method</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody id="viewPaymentHistory">
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">
                                            <i class="fas fa-spinner fa-spin me-2"></i>Loading payment history...
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Close
                </button>
                <button type="button" class="btn btn-primary" id="editPayerFromViewBtn">
                    <i class="fas fa-edit me-2"></i>Edit Payer
                </button>
            </div>
        </div>
    </div>
</div>
