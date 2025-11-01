<!-- Contribution Payments Modal -->
<div class="modal fade" id="contributionPaymentsModal" tabindex="-1" aria-labelledby="contributionPaymentsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <div>
                    <h5 class="modal-title" id="contributionPaymentsModalLabel">
                        <i class="fas fa-hand-holding-usd me-2"></i>
                        <span id="contributionModalTitle">Contribution Payments</span>
                    </h5>
                    <small class="text-white-50">
                        <i class="fas fa-mouse-pointer me-1"></i>
                        Click any payer to view payment history
                    </small>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

                         <div class="modal-body">
                 <!-- Search Bar -->
                 <div id="contributionPaymentsSearchBar" style="display: none;" class="mb-3">
                     <div class="input-group">
                         <span class="input-group-text"><i class="fas fa-search"></i></span>
                         <input type="text" class="form-control" id="searchPayersInput" placeholder="Search by Payer ID or Name...">
                         <button type="button" class="btn btn-outline-secondary" id="clearSearchBtn" style="display: none;">
                             <i class="fas fa-times"></i>
                         </button>
                     </div>
                 </div>

                 <!-- Loading State -->
                 <div id="contributionPaymentsLoading" class="text-center py-5" style="display: none;">
                     <div class="spinner-border text-primary" role="status">
                         <span class="visually-hidden">Loading...</span>
                     </div>
                     <p class="text-muted mt-2">Loading payments...</p>
                 </div>

                 <!-- Payments List -->
                 <div id="contributionPaymentsContent">
                     <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle" id="contributionPaymentsTable">
                            <thead class="table-light">
                                <tr>
                                    <th>Payer ID</th>
                                    <th>Payer Name</th>
                                    <th>Payment Group</th>
                                    <th>Total Paid</th>
                                    <th>Remaining Balance</th>
                                    <th>Status</th>
                                    <th>Last Payment</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="contributionPaymentsTableBody">
                                <!-- Payments will be loaded here via JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Empty State -->
                <div id="contributionPaymentsEmpty" class="text-center py-5" style="display: none;">
                    <div class="mb-3">
                        <i class="fas fa-inbox text-muted" style="font-size: 3rem;"></i>
                    </div>
                    <h5 class="text-muted">No payments found</h5>
                    <p class="text-muted">No payments have been recorded for this contribution yet.</p>
                </div>
            </div>

            <div class="modal-footer">
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-success dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-file-export me-1"></i>Export
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#" onclick="exportContributionPayments(); return false;">
                            <i class="fas fa-file-csv me-2"></i>Export to CSV
                        </a></li>
                        <li><a class="dropdown-item" href="#" onclick="exportContributionPaymentsPDF(); return false;">
                            <i class="fas fa-file-pdf me-2"></i>Export to PDF
                        </a></li>
                    </ul>
                </div>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Payment History Modal for Individual Payer -->
<div class="modal fade" id="payerPaymentHistoryModal" tabindex="-1" aria-labelledby="payerPaymentHistoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="payerPaymentHistoryModalLabel">
                    <i class="fas fa-history me-2"></i>
                    <span id="payerHistoryTitle">Payment History</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <!-- Loading State -->
                <div id="payerHistoryLoading" class="text-center py-5" style="display: none;">
                    <div class="spinner-border text-info" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="text-muted mt-2">Loading payment history...</p>
                </div>

                <!-- History Content -->
                <div id="payerHistoryContent" style="display: none;">
                    <div class="list-group" id="payerHistoryList">
                        <!-- Payment history items will be loaded here -->
                    </div>
                </div>

                <!-- Empty State -->
                <div id="payerHistoryEmpty" class="text-center py-5" style="display: none;">
                    <div class="mb-3">
                        <i class="fas fa-inbox text-muted" style="font-size: 3rem;"></i>
                    </div>
                    <h5 class="text-muted">No payment history</h5>
                    <p class="text-muted">No payment transactions recorded.</p>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Close
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.contribution-payment-row {
    cursor: pointer;
    transition: background-color 0.2s ease;
}

.contribution-payment-row:hover {
    background-color: #f0f9ff !important;
}

.contribution-payment-row:active {
    background-color: #dbeafe !important;
}

.payment-history-item {
    cursor: pointer;
    transition: all 0.2s ease;
}

.payment-history-item:hover {
    background-color: #f8f9fa !important;
    transform: translateX(5px);
}
</style>

<script>
// Store contribution and payer data (make them global)
window.currentContributionId = null;
window.currentContributionData = {};

// Function to show contribution payments modal with aggregated data
function showContributionPayments(contributionId, contributionTitle, contributionAmount) {
    window.currentContributionId = contributionId;
    window.currentContributionData = {
        id: contributionId,
        title: contributionTitle,
        amount: contributionAmount
    };
    
    // Update modal title
    document.getElementById('contributionModalTitle').textContent = contributionTitle;
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('contributionPaymentsModal'));
    modal.show();
    
    // Reset states
    document.getElementById('contributionPaymentsLoading').style.display = 'block';
    document.getElementById('contributionPaymentsContent').style.display = 'none';
    document.getElementById('contributionPaymentsEmpty').style.display = 'none';
    document.getElementById('contributionPaymentsSearchBar').style.display = 'none';
    
    // Clear search
    document.getElementById('searchPayersInput').value = '';
    
    // Clear previous data
    document.getElementById('contributionPaymentsTableBody').innerHTML = '';
    
    // Fetch payments for this contribution
    fetch(`${window.APP_BASE_URL || ''}/payments/by-contribution/${contributionId}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('contributionPaymentsLoading').style.display = 'none';
            
            if (data.success && data.payments && data.payments.length > 0) {
                // Aggregate payments by payer and payment_sequence
                const payerMap = {};
                
                data.payments.forEach(payment => {
                    const payerId = payment.payer_id;
                    const sequence = payment.payment_sequence || 1;
                    const key = payerId + '_' + sequence; // Unique key for payer+sequence combination
                    
                    if (!payerMap[key]) {
                        payerMap[key] = {
                            payer_id: payerId,
                            payer_student_id: payment.payer_student_id || payment.payer_id || 'N/A',
                            payer_name: payment.payer_name,
                            payment_sequence: sequence,
                            contact_number: payment.contact_number || null,
                            email_address: payment.email_address || null,
                            total_paid: 0,
                            status: 'fully paid',
                            last_payment_date: null,
                            payments: [],
                            contribution_amount: null,
                            contribution_title: null
                        };
                    }
                    
                    payerMap[key].total_paid += parseFloat(payment.amount_paid);
                    payerMap[key].payments.push(payment);
                    
                    // Store contribution amount from the first payment
                    if (!payerMap[key].contribution_amount && payment.contribution_amount) {
                        payerMap[key].contribution_amount = parseFloat(payment.contribution_amount);
                    }
                    
                    // Store contribution title from the first payment
                    if (!payerMap[key].contribution_title && payment.contribution_title) {
                        payerMap[key].contribution_title = payment.contribution_title;
                    }
                    
                    // Store contact info if not already stored
                    if (!payerMap[key].contact_number && payment.contact_number) {
                        payerMap[key].contact_number = payment.contact_number;
                    }
                    if (!payerMap[key].email_address && payment.email_address) {
                        payerMap[key].email_address = payment.email_address;
                    }
                   
                    // Track latest payment date
                    const paymentDate = new Date(payment.payment_date);
                    if (!payerMap[key].last_payment_date || paymentDate > new Date(payerMap[key].last_payment_date)) {
                        payerMap[key].last_payment_date = payment.payment_date;
                    }
                });
                
                // Determine final status for each payer based on totals
                Object.values(payerMap).forEach(payerData => {
                    // If we have contribution amount, check if fully paid
                    if (payerData.contribution_amount) {
                        if (payerData.total_paid >= payerData.contribution_amount) {
                            payerData.status = 'fully paid';
                        } else {
                            payerData.status = 'partial';
                        }
                    } else {
                        // Fallback: if no contribution amount, check if any payment is partial
                        const hasPartialPayment = payerData.payments.some(p => p.payment_status === 'partial');
                        if (hasPartialPayment) {
                            payerData.status = 'partial';
                        } else {
                            payerData.status = 'fully paid';
                        }
                    }
                });
                
                // Display aggregated data
                const tbody = document.getElementById('contributionPaymentsTableBody');
                tbody.innerHTML = '';
                
                console.log('Aggregated payerMap:', payerMap);
                
                Object.values(payerMap).forEach(payerData => {
                    const row = document.createElement('tr');
                    row.className = 'contribution-payment-row';
                    row.setAttribute('data-payer-id', payerData.payer_id);
                    row.setAttribute('data-payer-data', JSON.stringify(payerData));
                    
                    console.log('Payer data for row:', payerData);
                    
                    // Format last payment date
                    let lastPaymentDate = 'N/A';
                    if (payerData.last_payment_date) {
                        const date = new Date(payerData.last_payment_date);
                        lastPaymentDate = date.toLocaleDateString('en-US', { 
                            year: 'numeric', 
                            month: 'short', 
                            day: 'numeric'
                        });
                    }
                    
                    // Calculate remaining balance
                    let remainingBalance = 0;
                    if (payerData.contribution_amount) {
                        remainingBalance = Math.max(0, payerData.contribution_amount - payerData.total_paid);
                    }
                    
                    // Status badge
                    let statusBadge = '';
                    if (payerData.status === 'fully paid') {
                        statusBadge = '<span class="badge bg-primary text-white">COMPLETED</span>';
                    } else {
                        statusBadge = '<span class="badge bg-warning text-dark">PARTIAL</span>';
                    }
                    
                    // Actions column
                    let actionsHTML = '';
                    if (payerData.status === 'partial' && typeof openAddPaymentToPartialModal === 'function') {
                        // Find a partial payment to pass to the modal
                        const partialPayment = payerData.payments.find(p => p.payment_status === 'partial');
                        if (partialPayment) {
                            // Store payment data in a data attribute instead of inline
                            const paymentDataId = `payment-${partialPayment.id}`;
                            window[paymentDataId] = partialPayment;
                            
                            actionsHTML = `<button class="btn btn-sm btn-info" onclick="event.stopPropagation(); openAddPaymentToPartialModal(window['${paymentDataId}'])">
                                <i class="fas fa-plus me-1"></i>Add Payment
                            </button>`;
                        }
                    }
                    
                    row.innerHTML = `
                        <td>${payerData.payer_student_id}</td>
                        <td>${payerData.payer_name}</td>
                        <td><span class="badge bg-secondary">Group ${payerData.payment_sequence || 1}</span></td>
                        <td class="fw-semibold">₱${payerData.total_paid.toFixed(2)}</td>
                        <td class="fw-semibold ${remainingBalance > 0 ? 'text-danger' : 'text-success'}">
                            ₱${remainingBalance.toFixed(2)}
                        </td>
                        <td>${statusBadge}</td>
                        <td>${lastPaymentDate}</td>
                        <td onclick="event.stopPropagation();">${actionsHTML}</td>
                    `;
                    
                    // Add click event to show payment history
                    row.addEventListener('click', function(e) {
                        // Don't trigger if clicking on action buttons
                        if (e.target.closest('button')) {
                            return;
                        }
                        showPayerPaymentHistory(payerData);
                    });
                    
                    tbody.appendChild(row);
                });
                
                document.getElementById('contributionPaymentsContent').style.display = 'block';
                document.getElementById('contributionPaymentsSearchBar').style.display = 'block';
                
                // Setup search functionality
                setupContributionPaymentsSearch();
            } else {
                document.getElementById('contributionPaymentsEmpty').style.display = 'block';
            }
        })
        .catch(error => {
            console.error('Error fetching contribution payments:', error);
            document.getElementById('contributionPaymentsLoading').style.display = 'none';
            document.getElementById('contributionPaymentsEmpty').style.display = 'block';
            showNotification('Error loading payments', 'danger');
        });
}

// Function to show payment history for a specific payer (make it global)
window.showPayerPaymentHistory = function(payerData) {
    console.log('showPayerPaymentHistory called with:', payerData);
    
    // Update modal title with payer name and contribution name
    const contributionName = payerData.contribution_title || window.currentContributionData?.title || 'N/A';
    console.log('Contribution name for title:', contributionName);
    document.getElementById('payerHistoryTitle').textContent = `Payment History - ${payerData.payer_name} - ${contributionName}`;
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('payerPaymentHistoryModal'));
    modal.show();
    
    // Reset states
    document.getElementById('payerHistoryLoading').style.display = 'block';
    document.getElementById('payerHistoryContent').style.display = 'none';
    document.getElementById('payerHistoryEmpty').style.display = 'none';
    
    // Clear previous data
    document.getElementById('payerHistoryList').innerHTML = '';
    
    // Hide loading and show content
    setTimeout(() => {
        document.getElementById('payerHistoryLoading').style.display = 'none';
        
        if (payerData.payments && payerData.payments.length > 0) {
            // Sort payments by date (newest first)
            const sortedPayments = [...payerData.payments].sort((a, b) => 
                new Date(b.payment_date) - new Date(a.payment_date)
            );
            
            const list = document.getElementById('payerHistoryList');
            
                         sortedPayments.forEach(payment => {
                const item = document.createElement('div');
                item.className = 'list-group-item payment-history-item';
                
                // Format payment date
                const paymentDate = new Date(payment.payment_date);
                const formattedDate = paymentDate.toLocaleDateString('en-US', { 
                    year: 'numeric', 
                    month: 'short', 
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });
                
                // Status badge (use computed_status if available)
                let statusBadge = '';
                const paymentStatus = payment.computed_status || payment.payment_status || 'partial';
                if (paymentStatus === 'fully paid') {
                    statusBadge = '<span class="badge bg-primary text-white">COMPLETED</span>';
                } else {
                    statusBadge = '<span class="badge bg-warning text-dark">PARTIAL</span>';
                }
                
                item.innerHTML = `
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="mb-1">
                                <i class="fas fa-money-bill-wave me-2 text-primary"></i>
                                ₱${parseFloat(payment.amount_paid).toFixed(2)}
                            </h6>
                            <p class="mb-1 text-muted small">
                                <i class="fas fa-calendar me-1"></i>${formattedDate}
                            </p>
                            <p class="mb-0 small">
                                <i class="fas fa-credit-card me-1"></i>${payment.payment_method.toUpperCase()}
                                ${payment.remaining_balance > 0 ? ` • Remaining: ₱${parseFloat(payment.remaining_balance).toFixed(2)}` : ''}
                            </p>
                        </div>
                        <div class="text-end">
                            ${statusBadge}
                        </div>
                    </div>
                `;
                
                // Add click event to show QR receipt with complete payer data
                item.addEventListener('click', function() {
                    if (typeof showQRReceipt === 'function') {
                        // Build complete payment object with payer data
                        const completePaymentData = {
                            ...payment,
                            payer_name: payerData.payer_name,
                            payer_id: payerData.payer_student_id, // Use payer_student_id as payer_id for display
                            payer_student_id: payerData.payer_student_id,
                            // Get payer contact info from the payment if available
                            contact_number: payment.contact_number || payerData.contact_number || 'N/A',
                            email_address: payment.email_address || payerData.email_address || 'N/A',
                            contribution_title: payment.contribution_title || payerData.contribution_title || 'N/A'
                        };
                        console.log('Complete payment data for QR receipt:', completePaymentData);
                        showQRReceipt(completePaymentData);
                    } else {
                        showNotification('QR Receipt modal is loading', 'warning');
                    }
                });
                
                list.appendChild(item);
            });
            
            document.getElementById('payerHistoryContent').style.display = 'block';
        } else {
            document.getElementById('payerHistoryEmpty').style.display = 'block';
                 }
     }, 300);
 };

 // Function to setup search functionality
 function setupContributionPaymentsSearch() {
     const searchInput = document.getElementById('searchPayersInput');
     const clearBtn = document.getElementById('clearSearchBtn');
     const tableBody = document.getElementById('contributionPaymentsTableBody');
     
     if (!searchInput || !clearBtn || !tableBody) return;
     
     // Search on input
     searchInput.addEventListener('input', function() {
         const searchTerm = this.value.toLowerCase().trim();
         
         // Show/hide clear button
         if (searchTerm.length > 0) {
             clearBtn.style.display = 'block';
         } else {
             clearBtn.style.display = 'none';
         }
         
         // Get all table rows
         const rows = tableBody.querySelectorAll('tr');
         
         rows.forEach(row => {
             // Get payer ID and name from the row
             const payerId = row.children[0].textContent.toLowerCase().trim();
             const payerName = row.children[1].textContent.toLowerCase().trim();
             
             // Check if search term matches either payer ID or name
             if (payerId.includes(searchTerm) || payerName.includes(searchTerm)) {
                 row.style.display = '';
             } else {
                 row.style.display = 'none';
             }
         });
     });
     
     // Clear search
     clearBtn.addEventListener('click', function() {
         searchInput.value = '';
         this.style.display = 'none';
         
         // Show all rows
         const rows = tableBody.querySelectorAll('tr');
         rows.forEach(row => {
             row.style.display = '';
         });
     });
 }
 
 // Function to export contribution payments to CSV
 function exportContributionPayments() {
     const tableBody = document.getElementById('contributionPaymentsTableBody');
     if (!tableBody || !tableBody.querySelectorAll('tr').length) {
         showNotification('No data to export', 'warning');
         return;
     }
     
     const contributionTitle = document.getElementById('contributionModalTitle').textContent;
     const rows = tableBody.querySelectorAll('tr');
     
     // CSV headers with UTF-8 BOM for Excel compatibility
     let csvContent = 'data:text/csv;charset=utf-8,\uFEFF';
     csvContent += 'Payer ID,Payer Name,Payment Group,Total Paid,Remaining Balance,Status,Last Payment\n';
     
     // Add data rows
     rows.forEach(row => {
         const cells = row.querySelectorAll('td');
         if (cells.length >= 8) {
             const payerId = cells[0].textContent.trim();
             const payerName = cells[1].textContent.trim();
             const paymentGroup = cells[2].textContent.trim().replace(/Group\s+/gi, ''); // Remove "Group " text
             const totalPaid = cells[3].textContent.trim();
             const remainingBalance = cells[4].textContent.trim();
             const status = cells[5].textContent.trim();
             const lastPayment = cells[6].textContent.trim();
             
             // Build CSV row (remove badges and formatting)
             const cleanStatus = status.replace(/COMPLETED|PARTIAL/g, '').trim() || status;
             const csvRow = `"${payerId}","${payerName}","${paymentGroup}","${totalPaid}","${remainingBalance}","${cleanStatus}","${lastPayment}"\n`;
             csvContent += csvRow;
         }
     });
     
     // Create download link
     const encodedUri = encodeURI(csvContent);
     const link = document.createElement('a');
     link.setAttribute('href', encodedUri);
     
     // Generate filename with contribution title and current date
     const date = new Date().toISOString().split('T')[0];
     const filename = `Contribution_${contributionTitle.replace(/[^a-z0-9]/gi, '_')}_${date}.csv`;
     link.setAttribute('download', filename);
     
     // Trigger download
     document.body.appendChild(link);
     link.click();
     document.body.removeChild(link);
     
     showNotification('Data exported successfully', 'success');
 }
 
 // Function to export contribution payments to PDF
 function exportContributionPaymentsPDF() {
     if (!window.currentContributionId) {
         showNotification('No contribution selected', 'warning');
         return;
     }
     
     // Redirect to the PDF export endpoint
     window.location.href = `${window.APP_BASE_URL || ''}/payments/export-contribution-pdf/${window.currentContributionId}`;
 }
 </script>
