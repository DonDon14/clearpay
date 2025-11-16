<?= $this->extend('layouts/main') ?>

<style>
    .header-table {
        background-color: #f8f9fa;
        position: sticky;
        top: 0;
        z-index: 1000;
    }
</style>

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
                     <div class="input-group">
                         <input type="text" id="searchStudentName" class="form-control" placeholder="Search by name or scan ID...">
                         <button type="button" class="btn btn-outline-primary" onclick="scanIDInPaymentsPage()" title="Scan School ID">
                             <i class="fas fa-qrcode"></i>
                         </button>
                     </div>
                 </div>
                 <div class="col-md-4">
                     <select id="statusFilter" class="form-select">
                         <option value="">All Status</option>
                         <option value="fully paid">Completed</option>
                         <option value="partial">Partial</option>
                         <option value="unpaid">Unpaid</option>
                     </select>
                 </div>
             </div>
             
                    <!-- Grouped Payments Table -->
             <div class="table-responsive" style="max-height: 600px; overflow-y: auto; overflow-x: hidden;">
              <table class="table table-hover table-fit">
                 <thead class="header-table">
                  <tr>
                                <th>Payer</th>
                                <th>Contribution</th>
                                <th>Total Paid</th>
                                <th>Payment Status</th>
                                <th>Refund Status</th>
                                <th>Payment Count</th>
                                <th>Last Payment</th>
                                <th>Actions</th>
                  </tr>
                </thead>
                 <tbody id="paymentsTableBody">
                                <?php if (!empty($groupedPayments)): ?>
                                    <?php foreach ($groupedPayments as $group): ?>
                                        <tr class="payment-group-row" 
                                            data-payer-id="<?= esc($group['payer_id']) ?>" 
                                            data-payer-student-id="<?= esc($group['payer_student_id'] ?? $group['payer_id']) ?>" 
                                            data-contribution-id="<?= esc($group['contribution_id']) ?>"
                                            data-payment-sequence="<?= esc($group['payment_sequence'] ?? 1) ?>"
                                            data-payment-status="<?= esc($group['computed_status']) ?>"
                                            data-payer-name="<?= esc($group['payer_name']) ?>"
                                            data-contribution-title="<?= esc($group['contribution_title']) ?>"
                                            style="cursor: pointer;">
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <?php if (!empty($group['profile_picture'])): ?>
                                                        <?php 
                                                        // Check if it's a Cloudinary URL (full URL) or local path
                                                        $groupPicUrl = (strpos($group['profile_picture'], 'res.cloudinary.com') !== false) 
                                                            ? $group['profile_picture'] 
                                                            : base_url($group['profile_picture']);
                                                        ?>
                                                        <img src="<?= $groupPicUrl ?>" 
                                                             alt="Profile" class="rounded-circle me-2" 
                                                             style="width: 32px; height: 32px; object-fit: cover;">
                                                    <?php else: ?>
                                                        <div class="bg-primary text-white rounded-circle me-2 d-flex align-items-center justify-content-center" 
                                                             style="width: 32px; height: 32px;">
                                                            <i class="fas fa-user"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                    <div>
                                                        <div class="fw-bold"><?= esc($group['payer_name']) ?></div>
                                                        <small class="text-muted"><?= esc($group['payer_student_id']) ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <div class="fw-bold">
                                                        <?= esc($group['contribution_title']) ?>
                                                        <?php if (isset($group['payment_sequence']) && $group['payment_sequence'] > 1): ?>
                                                            <span class="badge bg-info ms-1">Group <?= $group['payment_sequence'] ?></span>
                                                        <?php endif; ?>
                                                    </div>
                                                    <small class="text-muted">₱<?= number_format($group['contribution_amount'], 2) ?></small>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="fw-bold text-success">₱<?= number_format($group['total_paid'], 2) ?></div>
                                                <?php if ($group['remaining_balance'] > 0): ?>
                                                    <small class="text-muted">Remaining: ₱<?= number_format($group['remaining_balance'], 2) ?></small>
                                                <?php endif; ?>
                                            </td>
                                <td>
                                    <?php 
                                        // Payment Status
                                        $status = $group['computed_status'];
                                        $paymentBadgeClass = match($status) {
                                            'fully paid' => 'bg-primary text-white',
                                            'partial' => 'bg-warning text-dark',
                                            'unpaid' => 'bg-secondary text-white',
                                            default => 'bg-light text-dark'
                                        };
                                        $paymentStatusText = match($status) {
                                            'fully paid' => 'Completed',
                                            'partial' => 'Partial',
                                            'unpaid' => 'Unpaid',
                                            default => ucfirst($status)
                                        };
                                    ?>
                                    <span class="badge <?= $paymentBadgeClass ?>"><?= $paymentStatusText ?></span>
                                </td>
                                <td>
                                    <?php 
                                        // Refund Status
                                        $refundStatus = $group['refund_status'] ?? 'no_refund';
                                        $totalRefunded = (float)($group['total_refunded'] ?? 0);
                                        
                                        if ($refundStatus === 'fully_refunded') {
                                            $refundBadgeClass = 'bg-danger text-white';
                                            $refundStatusText = 'Fully Refunded';
                                        } elseif ($refundStatus === 'partially_refunded') {
                                            $refundBadgeClass = 'bg-warning text-dark';
                                            $refundStatusText = 'Partially Refunded<br><small>₱' . number_format($totalRefunded, 2) . ' of ₱' . number_format($group['total_paid'], 2) . '</small>';
                                        } else {
                                            $refundBadgeClass = 'bg-success text-white';
                                            $refundStatusText = 'No Refund';
                                        }
                                    ?>
                                    <span class="badge <?= $refundBadgeClass ?>"><?= $refundStatusText ?></span>
                                </td>
                                <td>
                                                <span class="badge bg-info"><?= $group['payment_count'] ?> payment<?= $group['payment_count'] > 1 ? 's' : '' ?></span>
                                            </td>
                                            <td><?= date('M d, Y', strtotime($group['last_payment_date'])) ?></td>
                                <td>
                                        <div class="btn-group" role="group">
                                                    <button class="btn btn-sm btn-outline-warning refund-payment-group-btn" 
                                                            data-payer-id="<?= esc($group['payer_id']) ?>" 
                                                            data-contribution-id="<?= esc($group['contribution_id']) ?>"
                                                            data-payment-sequence="<?= esc($group['payment_sequence'] ?? 1) ?>"
                                                            title="Refund this payment group">
                                                        <i class="fas fa-undo me-1"></i>Refund
                                            </button>
                                                    <?php if ($group['computed_status'] === 'partial'): ?>
                                                        <button class="btn btn-sm btn-success add-payment-btn" 
                                                                data-payer-id="<?= esc($group['payer_id']) ?>" 
                                                                data-payer-name="<?= esc($group['payer_name']) ?>"
                                                                data-payer-student-id="<?= esc($group['payer_student_id']) ?>"
                                                                data-contribution-id="<?= esc($group['contribution_id']) ?>"
                                                                data-contribution-title="<?= esc($group['contribution_title']) ?>"
                                                                data-contribution-amount="<?= esc($group['contribution_amount']) ?>"
                                                                data-total-paid="<?= esc($group['total_paid']) ?>"
                                                                data-remaining-balance="<?= esc($group['remaining_balance']) ?>"
                                                                title="Add Additional Payment">
                                                <i class="fas fa-plus me-1"></i>Add Payment
                                            </button>
                                        <?php endif; ?>
                                                    <button class="btn btn-sm btn-outline-danger delete-payment-group-btn" 
                                                            data-payer-id="<?= esc($group['payer_id']) ?>" 
                                                            data-contribution-id="<?= esc($group['contribution_id']) ?>"
                                                            data-payment-sequence="<?= esc($group['payment_sequence'] ?? 1) ?>"
                                                            data-payer-name="<?= esc($group['payer_name']) ?>"
                                                            data-contribution-title="<?= esc($group['contribution_title']) ?>"
                                                            data-payment-count="<?= esc($group['payment_count']) ?>"
                                                            data-total-paid="<?= esc($group['total_paid']) ?>"
                                                            title="Delete Payment Group">
                                                        <i class="fas fa-trash me-1"></i>Delete
                                                    </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                                        <td colspan="7" class="text-center py-4">
                                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                            <h6 class="text-muted">No Payments Found</h6>
                                            <p class="text-muted">Payments will appear here once they are recorded</p>
                                        </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
              </table>
                    </div>
          </div>
        </div>
      </div>
    </div>
  </div>

<!-- Payment History Modal -->
<div class="modal fade" id="paymentHistoryModal" tabindex="-1" aria-labelledby="paymentHistoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="paymentHistoryModalLabel">
                    <i class="fas fa-history me-2"></i>Payment History
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="paymentHistoryContent">
                    <!-- Content will be loaded here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

 <!-- Delete Payment Confirmation Modal -->
 <div class="modal fade" id="deletePaymentModal" tabindex="-1" aria-labelledby="deletePaymentModalLabel" aria-hidden="true">
     <div class="modal-dialog">
         <div class="modal-content">
             <div class="modal-header bg-danger text-white">
                 <h5 class="modal-title" id="deletePaymentModalLabel">
                    <i class="fas fa-exclamation-triangle me-2"></i>Delete Payment
                 </h5>
                 <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
             </div>
             <div class="modal-body">
                <div class="alert alert-danger">
                    <h6 class="alert-heading">Warning!</h6>
                    <p class="mb-0">This action cannot be undone. The payment record will be permanently deleted.</p>
                 </div>
                
                <div class="mt-3">
                    <h6>Payment Details:</h6>
                    <ul class="list-unstyled">
                        <li><strong>Payer:</strong> <span id="deletePayerName"></span></li>
                        <li><strong>Contribution:</strong> <span id="deleteContribution"></span></li>
                        <li><strong>Amount:</strong> <span id="deleteAmount"></span></li>
                    </ul>
                </div>
             </div>
             <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Cancel
                </button>
                <button type="button" class="btn btn-danger" id="confirmDeletePayment">
                     <i class="fas fa-trash me-2"></i>Delete Payment
                 </button>
             </div>
         </div>
     </div>
 </div>

<!-- Delete Payment Group Confirmation Modal -->
<div class="modal fade" id="deletePaymentGroupModal" tabindex="-1" aria-labelledby="deletePaymentGroupModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deletePaymentGroupModalLabel">
                    <i class="fas fa-exclamation-triangle me-2"></i>Delete Payment Group
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger">
                    <h6 class="alert-heading">Warning!</h6>
                    <p class="mb-0">This action will permanently delete ALL payments in this group. This cannot be undone.</p>
                </div>
                
                <div class="mt-3">
                    <h6>Payment Group Details:</h6>
                    <ul class="list-unstyled">
                        <li><strong>Payer:</strong> <span id="deleteGroupPayerName"></span></li>
                        <li><strong>Contribution:</strong> <span id="deleteGroupContribution"></span></li>
                        <li><strong>Payment Count:</strong> <span id="deleteGroupPaymentCount"></span></li>
                        <li><strong>Total Amount:</strong> <span id="deleteGroupTotalAmount"></span></li>
                        <li><strong>Group:</strong> <span id="deleteGroupSequence"></span></li>
                    </ul>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Cancel
                </button>
                <button type="button" class="btn btn-danger" id="confirmDeletePaymentGroup">
                    <i class="fas fa-trash me-2"></i>Delete Group
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Include Edit Payment Modal -->
<?= view('partials/modal-edit-payment') ?>

<!-- Include QR Receipt Modal -->
<?= $this->include('partials/modal-qr-receipt') ?>

<!-- Include Add Payment Modal -->
<?= $this->include('partials/modal-add-payment', ['contributions' => $contributions, 'title' => 'Add New Payment', 'action' => base_url('payments/save')]) ?>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    let currentPaymentData = null;

    // Handle payment group row click
    $(document).on('click', '.payment-group-row', function(e) {
        // Don't trigger if clicking on buttons
        if ($(e.target).closest('.view-payment-history-btn, .add-payment-btn').length > 0) {
            return;
        }
        
        const payerId = $(this).data('payer-id');
        const contributionId = $(this).data('contribution-id');
        const paymentSequence = $(this).data('payment-sequence') || 1;
        viewPaymentHistory(payerId, contributionId, paymentSequence);
    });

    // Handle view payment history button click
    $(document).on('click', '.view-payment-history-btn', function(e) {
        e.stopPropagation();
        const payerId = $(this).data('payer-id');
        const contributionId = $(this).data('contribution-id');
        const paymentSequence = $(this).data('payment-sequence') || 1;
        viewPaymentHistory(payerId, contributionId, paymentSequence);
    });

    // Handle add payment button click for partial payments
    $(document).on('click', '.add-payment-btn', function(e) {
        e.stopPropagation();
        
        // Get all the data from the button
        const paymentData = {
            id: $(this).data('payer-id'),
            payer_name: $(this).data('payer-name'),
            payer_student_id: $(this).data('payer-student-id'),
            contribution_id: $(this).data('contribution-id'),
            contribution_title: $(this).data('contribution-title'),
            contribution_amount: $(this).data('contribution-amount'),
            total_paid: $(this).data('total-paid'),
            remaining_balance: $(this).data('remaining-balance'),
            payment_status: 'partial',
            payment_sequence: $(this).data('payment-sequence') || 1
        };
        
        // Use the dedicated additional payment modal
        if (typeof openAddPaymentToPartialModal === 'function') {
            openAddPaymentToPartialModal(paymentData);
        } else {
            alert('Additional payment modal is not available');
        }
    });

    // Handle individual payment click in history modal
    $(document).on('click', '.payment-item-row', function(e) {
        // Don't trigger if clicking on action buttons
        if ($(e.target).closest('.edit-payment-btn, .delete-payment-btn, button').length > 0) {
            return;
        }
        
        const paymentId = $(this).data('payment-id');
        
        // Fetch payment data for QR receipt
        fetch(`<?= base_url('payments/get-payment-details') ?>?payment_id=${paymentId}`, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.text().then(text => {
                try {
                    return JSON.parse(text);
                } catch (e) {
                    throw new Error('Invalid JSON response: ' + text.substring(0, 100));
                }
            });
        })
        .then(data => {
            if (data.success) {
                if (typeof showQRReceipt === 'function') {
                    showQRReceipt(data.payment);
                } else {
                    alert('showQRReceipt function not available. Please refresh the page.');
                }
            } else {
                alert('Error: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            alert('An error occurred while fetching payment details: ' + error.message);
        });
    });

    // Search functionality (by name, contribution, or student ID)
    $('#searchStudentName').on('input', function() {
        const query = $(this).val().toLowerCase().trim();
        $('.payment-group-row').each(function() {
            const payerName = String($(this).data('payer-name') || '').toLowerCase();
            const contributionTitle = String($(this).data('contribution-title') || '').toLowerCase();
            const studentId = String($(this).data('payer-student-id') || '').toLowerCase();

            const matches =
                query === '' ||
                payerName.includes(query) ||
                contributionTitle.includes(query) ||
                studentId.includes(query);

            $(this).toggle(matches);
        });
    });

    // Status filter
    $('#statusFilter').on('change', function() {
        const selectedStatus = $(this).val();
        $('.payment-group-row').each(function() {
            const status = $(this).data('payment-status');
            
            if (selectedStatus === '' || status === selectedStatus) {
                $(this).show();
                    } else {
                $(this).hide();
            }
        });
    });

    // Payer search functionality
    $('#payerSearch').on('input', function() {
        const query = $(this).val();
        if (query.length >= 2) {
            searchPayers(query);
                } else {
            $('#payerSearchResults').empty();
        }
    });

    function viewPaymentHistory(payerId, contributionId, paymentSequence = 1) {
        fetch(`<?= base_url('payments/get-payment-history') ?>?payer_id=${payerId}&contribution_id=${contributionId}&payment_sequence=${paymentSequence}`, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayPaymentHistory(data.payments);
                $('#paymentHistoryModal').modal('show');
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            alert('An error occurred while fetching payment history.');
        });
    }

    function displayPaymentHistory(paymentGroups) {
        if (paymentGroups.length === 0) {
            $('#paymentHistoryContent').html(`
                <div class="text-center py-4">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <h6 class="text-muted">No Payment History</h6>
                    <p class="text-muted">No individual payments found for this contribution</p>
                </div>
            `);
            return;
        }

        const payerName = paymentGroups[0].payments[0].payer_name;
        const contributionTitle = paymentGroups[0].payments[0].contribution_title;
        
        let html = `
            <div class="mb-3">
                <h6 class="text-primary">${payerName}</h6>
                <p class="text-muted mb-0">${contributionTitle}</p>
            </div>
        `;

        // Display each payment group
        paymentGroups.forEach((group, groupIndex) => {
            // Calculate total refunded for the group
            let groupTotalRefunded = 0;
            let hasRefunds = false;
            let allFullyRefunded = true;
            
            if (group.payments && group.payments.length > 0) {
                group.payments.forEach(payment => {
                    const totalRefunded = parseFloat(payment.total_refunded || 0);
                    if (totalRefunded > 0) {
                        hasRefunds = true;
                        groupTotalRefunded += totalRefunded;
                    }
                    if (payment.refund_status !== 'fully_refunded') {
                        allFullyRefunded = false;
                    }
                });
            }
            
            // Add refund status badge to group header
            let groupRefundBadge = '';
            if (allFullyRefunded && hasRefunds) {
                groupRefundBadge = '<span class="badge bg-danger ms-2">All Fully Refunded</span>';
            } else if (hasRefunds) {
                groupRefundBadge = `<span class="badge bg-warning text-dark ms-2">₱${groupTotalRefunded.toFixed(2)} Refunded</span>`;
            }
            
            html += `
                <div class="card mb-3">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">
                            <i class="fas fa-layer-group me-2"></i>
                            Payment Group ${group.sequence}
                            <span class="badge bg-primary ms-2">${group.payment_count} payment${group.payment_count > 1 ? 's' : ''}</span>
                            <span class="badge bg-success ms-1">₱${parseFloat(group.total_amount).toFixed(2)} total</span>
                            ${groupRefundBadge}
                        </h6>
                        <button type="button" class="btn btn-sm btn-outline-danger refund-group-btn-history"
                                data-payer-id="${payerId}"
                                data-contribution-id="${contributionId}"
                                data-sequence="${group.sequence}"
                                title="Refund entire group">
                            <i class="fas fa-undo me-1"></i>Refund Group
                        </button>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Payment Date</th>
                                        <th>Amount</th>
                                        <th>Refund Status</th>
                                        <th>Method</th>
                                        <th>Reference</th>
                                        <th>Receipt</th>
                                        <th>Recorded By</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
            `;

            group.payments.forEach(payment => {
                const statusBadge = getStatusBadge(payment.payment_status);
                const refundStatus = payment.refund_status || 'no_refund';
                const totalRefunded = parseFloat(payment.total_refunded || 0);
                
                html += `
                    <tr class="payment-item-row" data-payment-id="${payment.id}" style="cursor: pointer;">
                        <td>${formatDate(payment.payment_date)}</td>
                        <td class="fw-bold text-success">₱${parseFloat(payment.amount_paid).toFixed(2)}</td>
                        <td>
                            ${refundStatus === 'fully_refunded' ? 
                                '<span class="badge bg-danger">Fully Refunded</span>' : 
                                refundStatus === 'partially_refunded' ? 
                                `<span class="badge bg-warning text-dark">Partially Refunded<br><small>₱${totalRefunded.toFixed(2)} of ₱${parseFloat(payment.amount_paid).toFixed(2)}</small></span>` :
                                '<span class="badge bg-success">No Refund</span>'
                            }
                        </td>
                        <td>${formatPaymentMethod(payment.payment_method)}</td>
                        <td>${payment.reference_number || 'N/A'}</td>
                        <td>${payment.receipt_number || 'N/A'}</td>
                        <td>${payment.recorded_by_name || 'N/A'}</td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge ${statusBadge.class}">${statusBadge.text}</span>
                                <button type="button" class="btn btn-sm btn-outline-danger refund-payment-btn-history"
                                        data-payment-id="${payment.id}"
                                        data-payer-id="${payerId}"
                                        data-contribution-id="${contributionId}"
                                        data-sequence="${group.sequence}"
                                        title="Refund this payment">
                                    <i class="fas fa-undo"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-warning edit-payment-btn" 
                                        data-payment-id="${payment.id}" 
                                        data-payment-data='${JSON.stringify(payment)}'
                                        title="Edit Payment">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-danger delete-payment-btn" 
                                        data-payment-id="${payment.id}" 
                                        data-payer-name="${payment.payer_name}"
                                        data-amount="${payment.amount_paid}"
                                        data-contribution="${payment.contribution_title}"
                                        title="Delete Payment">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
            });

            html += `
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            `;
        });

        html += `
            <div class="mt-3">
                <small class="text-muted">
                    <i class="fas fa-info-circle me-1"></i>
                    Click any payment to view QR receipt
                </small>
            </div>
        `;

        $('#paymentHistoryContent').html(html);
    }

    // Removed local showQRReceipt function - using global one from modal-qr-receipt.php

    function getStatusBadge(status) {
        switch(status) {
            case 'fully paid':
                return { class: 'bg-primary text-white', text: 'COMPLETED' };
            case 'partial':
                return { class: 'bg-warning text-dark', text: 'PARTIAL' };
            case 'pending':
                return { class: 'bg-warning text-dark', text: 'PENDING' };
            case 'failed':
                return { class: 'bg-danger text-white', text: 'FAILED' };
            default:
                return { class: 'bg-secondary text-white', text: status.toUpperCase() };
        }
    }

    function formatDate(dateString) {
        if (!dateString) return 'N/A';
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    function formatPaymentMethod(method) {
        if (!method) return 'N/A';
        const methods = {
            'cash': 'Cash',
            'online': 'Online',
            'check': 'Check',
            'bank': 'Bank Transfer'
        };
        return methods[method] || method;
    }

    // Function to open Add Payment modal with pre-populated data AND validation
    function openAddPaymentModalWithValidation(payerData, contributionData, paymentData) {
        // Use the modal's built-in functionality
        if (window.selectedPayerId !== undefined) {
            window.selectedPayerId = payerData.id;
        }
        
        // Show the modal - let the modal handle the rest
        $('#addPaymentModal').modal('show');
        
        // Pre-populate data after modal is shown
        setTimeout(() => {
            const payerSelectInput = document.getElementById('payerSelect');
            if (payerSelectInput) payerSelectInput.value = payerData.payer_name;
            
            const contributionSelect = document.getElementById('contributionId');
            if (contributionSelect) contributionSelect.value = contributionData.id;
            
            const amountPaidEl = document.getElementById('amountPaid');
            if (amountPaidEl) amountPaidEl.value = paymentData.remaining_balance;
        }, 300);
    }

    // Function to open Add Payment modal with pre-populated data
    function openAddPaymentModal(payerData, contributionData, paymentData) {
        // Use the modal's built-in functionality
        if (window.selectedPayerId !== undefined) {
            window.selectedPayerId = payerData.id;
        }
        
        // Show the modal - let the modal handle the rest
        $('#addPaymentModal').modal('show');
        
        // Pre-populate data after modal is shown
        setTimeout(() => {
            const payerSelectInput = document.getElementById('payerSelect');
            if (payerSelectInput) payerSelectInput.value = payerData.payer_name;
            
            const contributionSelect = document.getElementById('contributionId');
            if (contributionSelect) contributionSelect.value = contributionData.id;
            
            const amountPaidEl = document.getElementById('amountPaid');
            if (amountPaidEl) amountPaidEl.value = paymentData.remaining_balance;
        }, 300);
    }

    window.searchPayers = function(query) {
        fetch(`<?= base_url('payments/search-payers') ?>?q=${encodeURIComponent(query)}`, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayPayerResults(data.results);
            }
        })
        .catch(error => {
        });
    }

    window.displayPayerResults = function(results) {
        const resultsContainer = $('#payerSearchResults');
        resultsContainer.empty();

        if (results.length === 0) {
            resultsContainer.html('<div class="text-muted">No payers found</div>');
            return;
        }

        results.forEach(payer => {
            const payerItem = $(`
                <div class="payer-result-item p-2 border rounded mb-2" style="cursor: pointer;">
                    <div class="fw-bold">${payer.payer_name}</div>
                    <small class="text-muted">ID: ${payer.payer_id}</small>
                </div>
            `);

            payerItem.on('click', function() {
                window.selectedPayerId = payer.id;
                $('#payerSearch').val(payer.payer_name);
                resultsContainer.empty();
                
                // Note: Contribution checking is now handled automatically in the modal
                // when a contribution is selected from the dropdown
            });

            resultsContainer.append(payerItem);
        });
    }

    window.showDuplicatePaymentConfirmation = function(data, formData) {
        // Create confirmation modal
        const modalHtml = `
            <div class="modal fade" id="duplicatePaymentModal" tabindex="-1" aria-labelledby="duplicatePaymentModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header ${data.message.includes('Already Fully Paid') ? 'bg-warning text-dark' : 'bg-info text-white'}">
                            <h5 class="modal-title" id="duplicatePaymentModalLabel">
                                <i class="fas ${data.message.includes('Already Fully Paid') ? 'fa-exclamation-triangle' : 'fa-info-circle'} me-2"></i>
                                ${data.message.includes('Already Fully Paid') ? 'Contribution Already Fully Paid' : 'Duplicate Payment Confirmation'}
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="alert ${data.message.includes('Already Fully Paid') ? 'alert-warning' : 'alert-info'}">
                                <h6><i class="fas ${data.message.includes('Already Fully Paid') ? 'fa-exclamation-triangle' : 'fa-info-circle'} me-2"></i>
                                ${data.message.includes('Already Fully Paid') ? 'Fully Paid Contribution' : 'Duplicate Payment Detected'}</h6>
                                <p class="mb-0">${data.message.replace(/\n/g, '<br>')}</p>
                            </div>
                            
                            ${data.existing_payments && data.existing_payments.length > 0 ? `
                                <div class="mt-3">
                                    <h6>Existing Payment Groups:</h6>
                                    ${(() => {
                                        // Group payments by payment_sequence
                                        const paymentGroups = {};
                                        data.existing_payments.forEach(payment => {
                                            const sequence = payment.payment_sequence || 1;
                                            if (!paymentGroups[sequence]) {
                                                paymentGroups[sequence] = [];
                                            }
                                            paymentGroups[sequence].push(payment);
                                        });
                                        
                                        // Calculate totals for each group
                                        const groupSummaries = Object.keys(paymentGroups).map(sequence => {
                                            const groupPayments = paymentGroups[sequence];
                                            const totalPaid = groupPayments.reduce((sum, p) => sum + parseFloat(p.amount_paid), 0);
                                            const contributionAmount = parseFloat(data.existing_payments[0].contribution_amount || 0);
                                            const isFullyPaid = totalPaid >= contributionAmount;
                                            const remainingBalance = Math.max(0, contributionAmount - totalPaid);
                                            
                                            return {
                                                sequence: sequence,
                                                payments: groupPayments,
                                                totalPaid: totalPaid,
                                                paymentCount: groupPayments.length,
                                                isFullyPaid: isFullyPaid,
                                                remainingBalance: remainingBalance,
                                                lastPaymentDate: groupPayments.reduce((latest, p) => 
                                                    new Date(p.payment_date) > new Date(latest) ? p.payment_date : latest, 
                                                    groupPayments[0].payment_date
                                                )
                                            };
                                        });
                                        
                                        // Sort groups by sequence
                                        groupSummaries.sort((a, b) => parseInt(a.sequence) - parseInt(b.sequence));
                                        
                                        return groupSummaries.map(group => `
                                            <div class="card mb-3">
                                                <div class="card-header d-flex justify-content-between align-items-center">
                                                    <h6 class="mb-0">
                                                        <i class="fas fa-layer-group me-2"></i>
                                                        Payment Group ${group.sequence}
                                                        ${group.isFullyPaid ? '<span class="badge bg-success ms-2">Completed</span>' : '<span class="badge bg-warning ms-2">Partial</span>'}
                                                    </h6>
                                                    <small class="text-muted">${group.paymentCount} payment${group.paymentCount > 1 ? 's' : ''}</small>
                                                </div>
                                                <div class="card-body">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <strong>Total Paid:</strong> ₱${group.totalPaid.toFixed(2)}
                                                        </div>
                                                        <div class="col-md-6">
                                                            <strong>Last Payment:</strong> ${new Date(group.lastPaymentDate).toLocaleDateString()}
                                                        </div>
                                                    </div>
                                                    ${!group.isFullyPaid ? `
                                                        <div class="mt-2">
                                                            <small class="text-muted">Remaining: ₱${group.remainingBalance.toFixed(2)}</small>
                                                        </div>
                                                    ` : ''}
                                                    <div class="mt-2">
                                                        <small class="text-muted">Individual Payments:</small>
                                                        <div class="table-responsive mt-1">
                                                            <table class="table table-sm table-borderless">
                                                                <tbody>
                                                                    ${group.payments.map(payment => `
                                                                        <tr>
                                                                            <td>${new Date(payment.payment_date).toLocaleDateString()}</td>
                                                                            <td>₱${parseFloat(payment.amount_paid).toFixed(2)}</td>
                                                                            <td>${payment.payment_method || 'N/A'}</td>
                                                                            <td><span class="badge bg-${payment.payment_status === 'fully paid' ? 'success' : 'warning'}">${payment.payment_status}</span></td>
                                                                        </tr>
                                                                    `).join('')}
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        `).join('');
                                    })()}
                                </div>
                            ` : ''}
                            
                            <div class="mt-3">
                                <h6>New Payment Details:</h6>
                                <ul class="list-unstyled">
                                    <li><strong>Amount:</strong> ₱${parseFloat(formData.amount_paid).toFixed(2)}</li>
                                    <li><strong>Method:</strong> ${formData.payment_method}</li>
                                    <li><strong>Date:</strong> ${new Date(formData.payment_date).toLocaleDateString()}</li>
                                </ul>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="fas fa-times me-2"></i>No
                            </button>
                            <button type="button" class="btn ${data.message.includes('Already Fully Paid') ? 'btn-warning' : 'btn-info'}" onclick="confirmDuplicatePayment()">
                                <i class="fas fa-check me-2"></i>Yes
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // Remove existing modal if any
        $('#duplicatePaymentModal').remove();
        
        // Add modal to body
        $('body').append(modalHtml);
        
        // Store form data for confirmation
        window.pendingPaymentData = formData;
    
    // Show modal
        $('#duplicatePaymentModal').modal('show');
    }

    window.confirmDuplicatePayment = function() {
        
        if (!window.pendingPaymentData) {
            alert('No payment data found');
                            return;
        }
        
        
        const formData = window.pendingPaymentData;
        
        // Add confirmation flag
        formData.confirmed = '1';
        
        // Ensure required fields are present
        if (!formData.is_partial_payment) {
            const contributionAmount = parseFloat($('#contributionSelect option:selected').data('amount')) || 0;
            const amountPaid = parseFloat(formData.amount_paid) || 0;
            const remainingBalance = contributionAmount - amountPaid;
            formData.is_partial_payment = remainingBalance > 0 ? '1' : '0';
            formData.remaining_balance = remainingBalance;
        }
        
        fetch('<?= base_url('payments/save-with-confirmation') ?>', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/x-www-form-urlencoded',
                'Accept': 'application/json'
            },
            body: new URLSearchParams(formData)
        })
        .then(response => {
            return response.json();
        })
        .then(data => {
            if (data.success) {
                alert('Payment added successfully!');
                $('#duplicatePaymentModal').modal('hide');
                $('#addPaymentModal').modal('hide');
                location.reload(); // Reload to show updated data
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            alert('An error occurred while adding the payment.');
        });
        
        // Clean up
        window.pendingPaymentData = null;
    }


    // Note: checkPayerUnpaidContributions function has been moved to the modal
    // and is now handled automatically when contributions are selected

    // Handle edit payment button click
    $(document).on('click', '.edit-payment-btn', function(e) {
        e.stopPropagation(); // Prevent row click
        e.preventDefault();
        
        const paymentId = $(this).data('payment-id');
        const paymentDataStr = $(this).data('payment-data');
        
        if (!paymentId) {
            alert('Payment ID not found');
            return;
        }
        
        // Fetch full payment details including contribution amount
        fetch(`<?= base_url('payments/get-details/') ?>${paymentId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.payment) {
                    // Use the fetched payment data
                    if (typeof openEditPaymentModal === 'function') {
                        openEditPaymentModal(data.payment);
                    } else {
                        // Fallback to direct modal population
                        populateEditModal(data.payment);
                    }
                } else {
                    alert('Error loading payment details: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                alert('Error loading payment details');
            });
    });
    
    // Fallback function to populate edit modal directly
    function populateEditModal(payment) {
        $('#editPaymentId').val(payment.id);
        $('#editPayerName').val(payment.payer_name || '');
        $('#editContribution').val(payment.contribution_title || '');
        $('#editAmountPaid').val(parseFloat(payment.amount_paid || 0).toFixed(2));
        $('#editPaymentMethod').val(payment.payment_method || '');
        $('#editReceiptNumber').val(payment.receipt_number || '');
        $('#editContributionId').val(payment.contribution_id || '');
        $('#editContributionAmount').val(payment.contribution_amount || 0);
        $('#editPaymentStatus').val(payment.payment_status || 'fully paid');
        
        // Format payment date for datetime-local input
        if (payment.payment_date) {
            const paymentDate = new Date(payment.payment_date);
            const formattedDate = paymentDate.toISOString().slice(0, 16);
            $('#editPaymentDate').val(formattedDate);
        }
        
        // Calculate remaining balance
        if (typeof updateEditRemainingBalance === 'function') {
            updateEditRemainingBalance();
        }
        
        // Show modal
        $('#editPaymentModal').modal('show');
    }

    // Make functions globally available
    // Delete payment functionality
    let currentDeletePaymentId = null;

    // Handle delete button click
    $(document).on('click', '.delete-payment-btn', function(e) {
        e.stopPropagation(); // Prevent row click
        
        const paymentId = $(this).data('payment-id');
        const payerName = $(this).data('payer-name');
        const amount = $(this).data('amount');
        const contribution = $(this).data('contribution');
        
        // Store payment ID for deletion
        currentDeletePaymentId = paymentId;
    
    // Populate modal with payment details
        $('#deletePayerName').text(payerName);
        $('#deleteContribution').text(contribution);
        $('#deleteAmount').text('₱' + parseFloat(amount).toFixed(2));
        
        // Show confirmation modal
        $('#deletePaymentModal').modal('show');
    });

    // Handle confirm delete
    $('#confirmDeletePayment').on('click', function() {
        if (!currentDeletePaymentId) {
            alert('No payment selected for deletion');
        return;
    }
    
        // Disable button to prevent double-click
        $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Deleting...');

        // Send delete request
        fetch('<?= base_url('payments/delete') ?>', {
            method: 'POST',
        headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/x-www-form-urlencoded',
                'Accept': 'application/json'
            },
            body: new URLSearchParams({
                payment_id: currentDeletePaymentId
            })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Close modal
                $('#deletePaymentModal').modal('hide');
                
                // Show success message
                alert('Payment deleted successfully!');
                
                // Reload the page to refresh the data
                location.reload();
        } else {
                alert('Error: ' + data.message);
        }
    })
    .catch(error => {
            alert('An error occurred while deleting the payment.');
        })
        .finally(() => {
            // Re-enable button
            $('#confirmDeletePayment').prop('disabled', false).html('<i class="fas fa-trash me-2"></i>Delete Payment');
            currentDeletePaymentId = null;
        });
    });

    // Reset delete payment ID when modal is closed
    $('#deletePaymentModal').on('hidden.bs.modal', function() {
        currentDeletePaymentId = null;
    });

    // Delete payment group functionality
    let currentDeleteGroupData = null;

    // Handle delete group button click
    $(document).on('click', '.delete-payment-group-btn', function(e) {
        e.stopPropagation(); // Prevent row click
        
        const payerId = $(this).data('payer-id');
        const contributionId = $(this).data('contribution-id');
        const paymentSequence = $(this).data('payment-sequence');
        const payerName = $(this).data('payer-name');
        const contributionTitle = $(this).data('contribution-title');
        const paymentCount = $(this).data('payment-count');
        const totalPaid = $(this).data('total-paid');
        
        // Store group data for deletion
        currentDeleteGroupData = {
            payer_id: payerId,
            contribution_id: contributionId,
            payment_sequence: paymentSequence,
            payer_name: payerName,
            contribution_title: contributionTitle,
            payment_count: paymentCount,
            total_paid: totalPaid
        };
        
        // Populate modal with group details
        $('#deleteGroupPayerName').text(payerName);
        $('#deleteGroupContribution').text(contributionTitle);
        $('#deleteGroupPaymentCount').text(paymentCount + ' payment' + (paymentCount > 1 ? 's' : ''));
        $('#deleteGroupTotalAmount').text('₱' + parseFloat(totalPaid).toFixed(2));
        $('#deleteGroupSequence').text(paymentSequence > 1 ? `Group ${paymentSequence}` : 'Main Group');
        
        // Show confirmation modal
        $('#deletePaymentGroupModal').modal('show');
    });

    // Handle confirm delete group
    $('#confirmDeletePaymentGroup').on('click', function() {
        if (!currentDeleteGroupData) {
            alert('No payment group selected for deletion');
            return;
        }

        // Disable button to prevent double-click
        $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Deleting...');

        
        // Send delete request
        fetch('<?= base_url('payments/delete-group') ?>', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/x-www-form-urlencoded',
                'Accept': 'application/json'
            },
            body: new URLSearchParams({
                payer_id: currentDeleteGroupData.payer_id,
                contribution_id: currentDeleteGroupData.contribution_id,
                payment_sequence: currentDeleteGroupData.payment_sequence
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Close modal
                $('#deletePaymentGroupModal').modal('hide');
                
                // Show success message
                alert(`Payment group deleted successfully! ${data.deleted_count} payment(s) removed.`);
                
                // Reload the page to refresh the data
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            alert('An error occurred while deleting the payment group.');
        })
        .finally(() => {
            // Re-enable button
            $('#confirmDeletePaymentGroup').prop('disabled', false).html('<i class="fas fa-trash me-2"></i>Delete Group');
            currentDeleteGroupData = null;
        });
    });

    // Reset delete group data when modal is closed
    $('#deletePaymentGroupModal').on('hidden.bs.modal', function() {
        currentDeleteGroupData = null;
    });

    window.prefillPartialPaymentForm = function(data) {
        // Pre-populate the form with remaining amount for partial payment
        $('#amountPaid').val(data.remaining_amount.toFixed(2));
        
        // Show helpful message
        const message = `Partial payment detected! Remaining amount: ₱${data.remaining_amount.toFixed(2)}`;
        
        // Create a temporary alert
        const alertHtml = `
            <div class="alert alert-info alert-dismissible fade show" role="alert" id="partialPaymentAlert">
                <i class="fas fa-info-circle me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        
        // Insert alert before the form
        $('#addPaymentModal .modal-body').prepend(alertHtml);
        
        // Auto-dismiss after 5 seconds
        setTimeout(() => {
            $('#partialPaymentAlert').alert('close');
        }, 5000);
    }

    // --- School ID Scanner for Payments page ---
    let paymentsIDScannerStream = null;

    function stopPaymentsIDScanner() {
        if (paymentsIDScannerStream) {
            paymentsIDScannerStream.getTracks().forEach(t => t.stop());
            paymentsIDScannerStream = null;
        }
    }

    window.scanIDInPaymentsPage = async function() {
        try {
            // Create/show modal
            const modalEl = document.getElementById('idScannerModalPayments');
            const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            modal.show();

            // Start camera
            paymentsIDScannerStream = await navigator.mediaDevices.getUserMedia({
                video: {
                    facingMode: 'environment',
                    width: { ideal: 640 },
                    height: { ideal: 480 }
                }
            });

            const video = document.getElementById('paymentsIDVideo');
            video.srcObject = paymentsIDScannerStream;

            video.onloadedmetadata = () => {
                video.play();

                const canvas = document.createElement('canvas');
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                const ctx = canvas.getContext('2d');

                function loop() {
                    if (video.readyState === video.HAVE_ENOUGH_DATA) {
                        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
                        const img = ctx.getImageData(0, 0, canvas.width, canvas.height);
                        if (typeof jsQR !== 'undefined') {
                            const code = jsQR(img.data, img.width, img.height);
                            if (code) {
                                // Stop and close
                                stopPaymentsIDScanner();
                                const inst = bootstrap.Modal.getInstance(modalEl);
                                if (inst) inst.hide();

                                // Extract ID (JSON or numeric prefix)
                                let scanned = code.data || '';
                                let studentId = '';
                                try {
                                    const parsed = JSON.parse(scanned);
                                    studentId = String(parsed.payer_id || parsed.student_id || parsed.id || '').trim();
                                } catch (_) {
                                    const m = scanned.match(/^(\d{3,})/);
                                    if (m) studentId = m[1];
                                }
                                if (!studentId) studentId = scanned.trim();

                                // Apply to search box and trigger filter
                                const search = document.getElementById('searchStudentName');
                                if (search) {
                                    search.value = studentId;
                                    const evt = new Event('input', { bubbles: true });
                                    search.dispatchEvent(evt);
                                }
                                if (typeof showNotification === 'function') {
                                    showNotification('Filtering by ID: ' + studentId, 'success');
                                }
                                return;
                            }
                        }
                    }
                    requestAnimationFrame(loop);
                }
                loop();
            };

            // Cleanup backdrops when modal hides
            modalEl.addEventListener('hidden.bs.modal', function onHide() {
                stopPaymentsIDScanner();
                const backdrops = document.querySelectorAll('.modal-backdrop');
                // Keep at most one backdrop if parent modals are open
                if (backdrops.length > 1) {
                    for (let i = 1; i < backdrops.length; i++) backdrops[i].remove();
                }
                modalEl.removeEventListener('hidden.bs.modal', onHide);
            }, { once: true });
        } catch (error) {
            const msg = (error && (error.name === 'NotAllowedError' || error.name === 'SecurityError'))
                ? 'Camera permission denied. Allow camera in site permissions.'
                : (error && error.name === 'NotReadableError')
                    ? 'Camera is in use by another app/tab. Close it and try again.'
                    : 'Unable to access camera. Please check permissions.';
            if (typeof showNotification === 'function') showNotification(msg, 'error');
        }
    };

    // For future contexts that need a direct scan callback
    window.scanIDForPayment = window.scanIDInPaymentsPage;

    // Refund Payment Group button (in main payments table)
    $(document).on('click', '.refund-payment-group-btn', function(e) {
        e.stopPropagation();
        const payerId = $(this).data('payer-id');
        const contributionId = $(this).data('contribution-id');
        const sequence = $(this).data('payment-sequence');

        // Call the global function from refund-transaction.js
        if (typeof window.openRefundModalForGroup === 'function') {
            window.openRefundModalForGroup(payerId, contributionId, sequence);
        } else {
            alert('Refund functionality not initialized. Please refresh the page.');
        }
    });

    // Refund Group button (in payment history modal)
    $(document).on('click', '.refund-group-btn-history', function(e) {
        e.stopPropagation();
        const payerId = $(this).data('payer-id');
        const contributionId = $(this).data('contribution-id');
        const sequence = $(this).data('sequence');

        // Call the global function from refund-transaction.js
        if (typeof window.openRefundModalForGroup === 'function') {
            window.openRefundModalForGroup(payerId, contributionId, sequence);
        } else {
            alert('Refund functionality not initialized. Please refresh the page.');
        }
        
        // Close the payment history modal
        const historyModal = bootstrap.Modal.getInstance(document.getElementById('paymentHistoryModal'));
        if (historyModal) {
            historyModal.hide();
        }
    });

    // Refund Payment button (in payment history modal - individual payment)
    $(document).on('click', '.refund-payment-btn-history', function(e) {
        e.stopPropagation();
        const paymentId = $(this).data('payment-id');
        const payerId = $(this).data('payer-id');
        const contributionId = $(this).data('contribution-id');
        const sequence = $(this).data('sequence');

        // Call the global function from refund-transaction.js
        if (typeof window.openRefundModalForPayment === 'function') {
            window.openRefundModalForPayment(paymentId, payerId, contributionId, sequence);
        } else {
            alert('Refund functionality not initialized. Please refresh the page.');
        }
        
        // Close the payment history modal
        const historyModal = bootstrap.Modal.getInstance(document.getElementById('paymentHistoryModal'));
        if (historyModal) {
            historyModal.hide();
        }
    });
});
</script>

<!-- Payments Page - ID Scanner Modal -->
<div class="modal fade" id="idScannerModalPayments" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-qrcode me-2"></i>Scan School ID</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <video id="paymentsIDVideo" autoplay playsinline style="width:100%;border:2px solid #0d6efd;border-radius:8px;"></video>
                <div class="text-muted small mt-2">Point camera at the school ID QR code</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
    <style>
    #idScannerModalPayments { z-index: 1065 !important; }
    </style>
</div>

<!-- Include Additional Payment Modal -->
<?= view('partials/modal-add-payment-to-partial') ?>

<!-- Include Refund Transaction Modal -->
<?php
// Get refund methods for the modal
$refundMethodModel = new \App\Models\RefundMethodModel();
$refundMethods = $refundMethodModel->getActiveMethods();
?>
<?= view('partials/modal-refund-transaction', ['refundMethods' => $refundMethods]) ?>

<?= $this->endSection() ?>