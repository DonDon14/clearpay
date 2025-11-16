<!-- Contribution History Modal -->
<div class="modal fade" id="contributionHistoryModal" tabindex="-1" aria-labelledby="contributionHistoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="contributionHistoryModalLabel">
                    <i class="fas fa-history me-2"></i>Contribution History
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="contributionHistoryContent">
                    <div class="text-center py-5">
                        <div class="spinner-border text-warning" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-3 text-muted">Loading contribution history...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const contributionHistoryModal = document.getElementById('contributionHistoryModal');
    
    if (contributionHistoryModal) {
        contributionHistoryModal.addEventListener('show.bs.modal', function() {
            loadContributionHistory();
        });
    }
    
    function loadContributionHistory() {
        const contentDiv = document.getElementById('contributionHistoryContent');
        contentDiv.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-warning" role="status"><span class="visually-hidden">Loading...</span></div><p class="mt-3 text-muted">Loading contribution history...</p></div>';
        
        fetch('<?= base_url('payments/contribution-history') ?>', {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.contributions) {
                displayContributionHistory(data.contributions);
            } else {
                contentDiv.innerHTML = '<div class="alert alert-warning"><i class="fas fa-exclamation-triangle me-2"></i>' + (data.message || 'No contribution history found') + '</div>';
            }
        })
        .catch(error => {
            console.error('Error loading contribution history:', error);
            contentDiv.innerHTML = '<div class="alert alert-danger"><i class="fas fa-exclamation-circle me-2"></i>Error loading contribution history. Please try again.</div>';
        });
    }
    
    function displayContributionHistory(contributions) {
        const contentDiv = document.getElementById('contributionHistoryContent');
        
        if (!contributions || contributions.length === 0) {
            contentDiv.innerHTML = '<div class="alert alert-info"><i class="fas fa-info-circle me-2"></i>No contributions found.</div>';
            return;
        }
        
        let html = '<div class="row">';
        
        contributions.forEach(contribution => {
            const totalPayments = contribution.payments ? contribution.payments.length : 0;
            const totalAmount = contribution.payments ? contribution.payments.reduce((sum, p) => sum + parseFloat(p.amount_paid || 0), 0) : 0;
            const statusBadge = contribution.status === 'active' ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>';
            
            html += `
                <div class="col-md-6 mb-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">
                                <i class="fas fa-hand-holding-usd me-2 text-primary"></i>
                                ${contribution.title || 'Untitled Contribution'}
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <strong>Contribution Details:</strong>
                                <ul class="list-unstyled mt-2">
                                    <li><i class="fas fa-tag me-2 text-muted"></i><strong>Code:</strong> ${contribution.contribution_code || 'N/A'}</li>
                                    <li><i class="fas fa-dollar-sign me-2 text-muted"></i><strong>Amount:</strong> ₱${parseFloat(contribution.amount || 0).toFixed(2)}</li>
                                    <li><i class="fas fa-info-circle me-2 text-muted"></i><strong>Status:</strong> ${statusBadge}</li>
                                    ${contribution.description ? '<li><i class="fas fa-align-left me-2 text-muted"></i><strong>Description:</strong> ' + contribution.description.substring(0, 100) + (contribution.description.length > 100 ? '...' : '') + '</li>' : ''}
                                </ul>
                            </div>
                            
                            <div class="mb-3">
                                <strong>Payment Summary:</strong>
                                <ul class="list-unstyled mt-2">
                                    <li><i class="fas fa-receipt me-2 text-success"></i><strong>Total Payments:</strong> ${totalPayments}</li>
                                    <li><i class="fas fa-money-bill-wave me-2 text-success"></i><strong>Total Amount Paid:</strong> ₱${totalAmount.toFixed(2)}</li>
                                </ul>
                            </div>
                            
                            ${contribution.payments && contribution.payments.length > 0 ? `
                                <div class="mt-3">
                                    <strong>Payment History:</strong>
                                    <div class="table-responsive mt-2" style="max-height: 300px; overflow-y: auto;">
                                        <table class="table table-sm table-hover">
                                            <thead class="table-light sticky-top">
                                                <tr>
                                                    <th>Payer</th>
                                                    <th>Amount</th>
                                                    <th>Date</th>
                                                    <th>Method</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                ${contribution.payments.map(payment => `
                                                    <tr>
                                                        <td>
                                                            <div>
                                                                <strong>${payment.payer_name || 'Unknown'}</strong><br>
                                                                <small class="text-muted">${payment.payer_student_id || payment.payer_id || 'N/A'}</small>
                                                            </div>
                                                        </td>
                                                        <td>₱${parseFloat(payment.amount_paid || 0).toFixed(2)}</td>
                                                        <td>${payment.payment_date ? new Date(payment.payment_date).toLocaleDateString() : 'N/A'}</td>
                                                        <td><span class="badge bg-info">${(payment.payment_method || 'N/A').replace('_', ' ')}</span></td>
                                                    </tr>
                                                `).join('')}
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            ` : '<p class="text-muted mb-0"><i class="fas fa-info-circle me-2"></i>No payments recorded for this contribution.</p>'}
                        </div>
                    </div>
                </div>
            `;
        });
        
        html += '</div>';
        contentDiv.innerHTML = html;
    }
});
</script>

