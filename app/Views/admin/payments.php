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
                 <thead class="table-light sticky-top">
                  <tr>
                                    <th>Payer</th>
                    <th>Contribution</th>
                                    <th>Total Paid</th>
                                    <th>Status</th>
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
                                            data-contribution-id="<?= esc($group['contribution_id']) ?>"
                                            data-payment-sequence="<?= esc($group['payment_sequence'] ?? 1) ?>"
                                            data-payment-status="<?= esc($group['computed_status']) ?>"
                                            data-payer-name="<?= esc($group['payer_name']) ?>"
                                            data-contribution-title="<?= esc($group['contribution_title']) ?>"
                                            style="cursor: pointer;">
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <?php if (!empty($group['profile_picture'])): ?>
                                                        <img src="<?= base_url($group['profile_picture']) ?>" 
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
                                                    $status = $group['computed_status'];
                                        $badgeClass = match($status) {
                                            'fully paid' => 'bg-primary text-white',
                                            'partial' => 'bg-warning text-dark',
                                            'unpaid' => 'bg-secondary text-white',
                                            default => 'bg-light text-dark'
                                        };
                                        $statusText = match($status) {
                                            'fully paid' => 'Completed',
                                            'partial' => 'Partial',
                                            'unpaid' => 'Unpaid',
                                            default => ucfirst($status)
                                        };
                                    ?>
                                    <span class="badge <?= $badgeClass ?>"><?= $statusText ?></span>
                                </td>
                                            <td>
                                                <span class="badge bg-info"><?= $group['payment_count'] ?> payment<?= $group['payment_count'] > 1 ? 's' : '' ?></span>
                                            </td>
                                            <td><?= date('M d, Y', strtotime($group['last_payment_date'])) ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary view-payment-history-btn" 
                                                        data-payer-id="<?= esc($group['payer_id']) ?>" 
                                                        data-contribution-id="<?= esc($group['contribution_id']) ?>"
                                                        data-payment-sequence="<?= esc($group['payment_sequence'] ?? 1) ?>"
                                                        title="View Payment History">
                                                    <i class="fas fa-history me-1"></i>History
                                            </button>
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

<!-- Include QR Receipt Modal -->
<?= $this->include('partials/modal-qr-receipt') ?>

<!-- Add Payment Modal (existing) -->
<div class="modal fade" id="addPaymentModal" tabindex="-1" aria-labelledby="addPaymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
         <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addPaymentModalLabel">
                    <i class="fas fa-plus me-2"></i>Add New Payment
                 </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
             </div>
             <div class="modal-body">
                <form id="addPaymentForm">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="payerSearch" class="form-label">Search Payer</label>
                            <div class="input-group">
                                <input type="text" id="payerSearch" class="form-control" placeholder="Search by name or ID...">
                                <button type="button" class="btn btn-outline-primary" onclick="scanIDForPayment()" title="Scan School ID">
                                    <i class="fas fa-qrcode"></i>
                                </button>
                            </div>
                            <div id="payerSearchResults" class="mt-2"></div>
                        </div>
                        <div class="col-md-6">
                            <label for="contributionSelect" class="form-label">Contribution</label>
                            <select id="contributionSelect" class="form-select" required>
                                <option value="">Select Contribution</option>
                                <?php foreach ($contributions as $contribution): ?>
                                    <option value="<?= $contribution['id'] ?>" data-amount="<?= $contribution['amount'] ?>">
                                        <?= esc($contribution['title']) ?> - ₱<?= number_format($contribution['amount'], 2) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="amountPaid" class="form-label">Amount Paid</label>
                            <input type="number" id="amountPaid" class="form-control" step="0.01" min="0" required>
                        </div>
                        <div class="col-md-6">
                            <label for="paymentMethod" class="form-label">Payment Method</label>
                            <select id="paymentMethod" class="form-select" required>
                                <option value="">Select Method</option>
                                <option value="cash">Cash</option>
                                <option value="online">Online</option>
                                <option value="check">Check</option>
                                <option value="bank">Bank Transfer</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="paymentDate" class="form-label">Payment Date</label>
                            <input type="datetime-local" id="paymentDate" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label for="referenceNumber" class="form-label">Reference Number</label>
                            <input type="text" id="referenceNumber" class="form-control" placeholder="Optional">
                        </div>
                 </div>
                </form>
             </div>
             <div class="modal-footer">
                 <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="submitPayment()">Add Payment</button>
             </div>
         </div>
     </div>
 </div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    let selectedPayer = null;
    let currentPaymentData = null;

    // Handle payment group row click
    $(document).on('click', '.payment-group-row', function(e) {
        // Don't trigger if clicking on the view history button
        if ($(e.target).closest('.view-payment-history-btn').length > 0) {
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

    // Handle individual payment click in history modal
    $(document).on('click', '.payment-item-row', function() {
        const paymentId = $(this).data('payment-id');
        showQRReceipt(paymentId);
    });

    // Search functionality
    $('#searchStudentName').on('input', function() {
        const query = $(this).val().toLowerCase();
        $('.payment-group-row').each(function() {
            const payerName = $(this).data('payer-name').toLowerCase();
            const contributionTitle = $(this).data('contribution-title').toLowerCase();
            
            if (payerName.includes(query) || contributionTitle.includes(query)) {
                $(this).show();
                } else {
                $(this).hide();
            }
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

    // Set default payment date to now
    const now = new Date();
    const localDateTime = new Date(now.getTime() - now.getTimezoneOffset() * 60000).toISOString().slice(0, 16);
    $('#paymentDate').val(localDateTime);

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
            console.error('Error:', error);
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
            html += `
                <div class="card mb-3">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">
                            <i class="fas fa-layer-group me-2"></i>
                            Payment Group ${group.sequence}
                            <span class="badge bg-primary ms-2">${group.payment_count} payment${group.payment_count > 1 ? 's' : ''}</span>
                            <span class="badge bg-success ms-1">₱${parseFloat(group.total_amount).toFixed(2)} total</span>
                        </h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Payment Date</th>
                                        <th>Amount</th>
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
                html += `
                    <tr class="payment-item-row" data-payment-id="${payment.id}" style="cursor: pointer;">
                        <td>${formatDate(payment.payment_date)}</td>
                        <td class="fw-bold text-success">₱${parseFloat(payment.amount_paid).toFixed(2)}</td>
                        <td>${formatPaymentMethod(payment.payment_method)}</td>
                        <td>${payment.reference_number || 'N/A'}</td>
                        <td>${payment.receipt_number || 'N/A'}</td>
                        <td>${payment.recorded_by_name || 'N/A'}</td>
                        <td>
                            <span class="badge ${statusBadge.class}">${statusBadge.text}</span>
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

    function showQRReceipt(paymentId) {
        // Find the payment data from the current payments array
        const paymentRow = $(`.payment-item-row[data-payment-id="${paymentId}"]`);
        if (paymentRow.length === 0) {
            alert('Payment data not found');
            return;
        }

        // Get payment data from the row
        const paymentData = {
            id: paymentId,
            payer_name: paymentRow.closest('tbody').prev().find('h6').text(),
            contribution_title: paymentRow.closest('tbody').prev().find('p').text(),
            amount_paid: paymentRow.find('td:nth-child(2)').text().replace('₱', ''),
            payment_method: paymentRow.find('td:nth-child(3)').text(),
            reference_number: paymentRow.find('td:nth-child(4)').text(),
            receipt_number: paymentRow.find('td:nth-child(5)').text(),
            payment_date: paymentRow.find('td:nth-child(1)').text(),
            payment_status: 'fully paid' // Assume completed for display
        };

        // Close the payment history modal first
        $('#paymentHistoryModal').modal('hide');
        
        // Show QR receipt modal
        if (typeof window.showQRReceipt === 'function') {
            window.showQRReceipt(paymentData);
        } else {
            alert('QR Receipt functionality not available');
        }
    }

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
            } else {
                console.error('Search failed:', data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
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
                selectedPayer = payer;
                $('#payerSearch').val(payer.payer_name);
                resultsContainer.empty();
                
                // Check for unpaid contributions and show warning
                checkPayerUnpaidContributions(payer.id);
            });

            resultsContainer.append(payerItem);
        });
    }

    window.submitPayment = function() {
        if (!selectedPayer) {
            alert('Please select a payer');
            return;
        }

        // Calculate remaining balance
        const contributionAmount = parseFloat($('#contributionSelect option:selected').data('amount')) || 0;
        const amountPaid = parseFloat($('#amountPaid').val()) || 0;
        const remainingBalance = contributionAmount - amountPaid;

        const formData = {
            payer_id: selectedPayer.id,
            contribution_id: $('#contributionSelect').val(),
            amount_paid: $('#amountPaid').val(),
            payment_method: $('#paymentMethod').val(),
            payment_date: $('#paymentDate').val(),
            reference_number: $('#referenceNumber').val(),
            is_partial_payment: remainingBalance > 0 ? '1' : '0',
            remaining_balance: remainingBalance
        };

        // Validate required fields
        if (!formData.contribution_id || !formData.amount_paid || !formData.payment_method || !formData.payment_date) {
            alert('Please fill in all required fields');
                            return;
                        }

        fetch('<?= base_url('payments/save') ?>', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/x-www-form-urlencoded',
                'Accept': 'application/json'
            },
            body: new URLSearchParams(formData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Payment added successfully!');
                $('#addPaymentModal').modal('hide');
                location.reload(); // Reload to show updated data
            } else {
                // Check if this is a duplicate payment that requires confirmation
                if (data.requires_confirmation) {
                    showDuplicatePaymentConfirmation(data, formData);
                } else {
                    alert('Error: ' + data.message);
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while adding the payment.');
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
                                    <h6>Existing Payments:</h6>
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Date</th>
                                                    <th>Amount</th>
                                                    <th>Method</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                ${data.existing_payments.map(payment => `
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
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn ${data.message.includes('Already Fully Paid') ? 'btn-warning' : 'btn-info'}" onclick="confirmDuplicatePayment()">
                                <i class="fas fa-check me-2"></i>${data.message.includes('Already Fully Paid') ? 'Yes, Add Another Payment' : 'Yes, Add Payment Anyway'}
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
        console.log('confirmDuplicatePayment called'); // Debug log
        
        if (!window.pendingPaymentData) {
            alert('No payment data found');
            return;
        }
        
        console.log('Pending payment data:', window.pendingPaymentData); // Debug log
        
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
        
        console.log('Form data being sent:', formData); // Debug log

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
            console.log('Response status:', response.status); // Debug log
            return response.json();
        })
        .then(data => {
            console.log('Response data:', data); // Debug log
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
            console.error('Error:', error);
            alert('An error occurred while adding the payment.');
        });
        
        // Clean up
        window.pendingPaymentData = null;
    }

    window.checkPayerUnpaidContributions = function(payerId) {
        // Remove existing warning if any
        $('#payerUnpaidWarning').remove();
        
        fetch(`<?= base_url('payments/check-unpaid-contributions') ?>?payer_id=${payerId}`, {
            method: 'GET',
        headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
            if (data.success && data.unpaid_contributions && data.unpaid_contributions.length > 0) {
                const warningHtml = `
                    <div id="payerUnpaidWarning" class="alert alert-info mt-2">
                        <h6><i class="fas fa-info-circle me-2"></i>Existing Contributions</h6>
                        <p class="mb-1">This payer has unpaid contributions:</p>
                        <ul class="mb-0">
                            ${data.unpaid_contributions.map(contrib => 
                                `<li>${contrib.title} (₱${parseFloat(contrib.remaining_amount).toFixed(2)} remaining)</li>`
                            ).join('')}
                        </ul>
                        <small class="text-muted">Note: You can add payments to any contribution, but duplicate payments within the same contribution will require confirmation.</small>
                    </div>
                `;
                $('#payerSearch').after(warningHtml);
        }
    })
    .catch(error => {
            console.error('Error checking unpaid contributions:', error);
        });
    }

    // Make functions globally available
    window.scanIDInPaymentsPage = function() {
        alert('QR Scanner functionality will be implemented');
    };

    window.scanIDForPayment = function() {
        alert('QR Scanner functionality will be implemented');
    };

    window.submitPayment = submitPayment;
});
</script>
<?= $this->endSection() ?>