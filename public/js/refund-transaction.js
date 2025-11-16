/**
 * Refund Transaction Modal JavaScript
 * Handles all functionality for the refund transaction modal
 */

// Ensure base URL is defined
if (typeof window.APP_BASE_URL === 'undefined') {
    window.APP_BASE_URL = window.location.origin + '/';
}

// Global state
let selectedSequencePayments = [];
let groupedPaymentsCache = [];

/**
 * Load grouped payments for the modal
 * This fetches all available payment groups
 */
function loadGroupedPayments() {
    if (typeof jQuery === 'undefined') {
        return;
    }

    $.ajax({
        url: window.APP_BASE_URL + 'admin/refunds/get-payment-groups',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success && response.data) {
                groupedPaymentsCache = response.data;
                renderGroupedPayments();
            } else {
                $('#groupsList, #sequencesList').html('<tr><td colspan="6" class="text-center text-danger py-4">Failed to load payment groups</td></tr>');
            }
        },
        error: function(xhr) {
            $('#groupsList, #sequencesList').html('<tr><td colspan="6" class="text-center text-danger py-4">Error loading payment groups</td></tr>');
        }
    });
}

/**
 * Render grouped payments in the tables
 */
function renderGroupedPayments() {
    // Render group refund table
    let groupsHtml = '';
    if (groupedPaymentsCache.length === 0) {
        groupsHtml = '<tr><td colspan="6" class="text-center text-muted py-4">No payment groups available for refund</td></tr>';
    } else {
        groupedPaymentsCache.forEach(function(group) {
            groupsHtml += `
                <tr>
                    <td>${escapeHtml(group.payer_name || 'Unknown')}</td>
                    <td>${escapeHtml(group.contribution_title || 'Unknown')}</td>
                    <td><span class="badge bg-info">#${group.payment_sequence || '1'}</span></td>
                    <td>₱${parseFloat(group.total_paid || 0).toFixed(2)}</td>
                    <td>${group.payment_count || 0} payment(s)</td>
                    <td>
                        <button class="btn btn-sm btn-primary select-group-btn" 
                                data-payer-id="${group.payer_id}" 
                                data-contribution-id="${group.contribution_id}" 
                                data-sequence="${group.payment_sequence || '1'}">
                            <i class="fas fa-undo"></i> Select Group
                        </button>
                    </td>
                </tr>
            `;
        });
    }
    $('#groupsList').html(groupsHtml);

    // Render sequence refund table (same data)
    let sequencesHtml = '';
    if (groupedPaymentsCache.length === 0) {
        sequencesHtml = '<tr><td colspan="5" class="text-center text-muted py-4">No payment sequences available for refund</td></tr>';
    } else {
        groupedPaymentsCache.forEach(function(group) {
            sequencesHtml += `
                <tr>
                    <td>${escapeHtml(group.payer_name || 'Unknown')}</td>
                    <td>${escapeHtml(group.contribution_title || 'Unknown')}</td>
                    <td><span class="badge bg-info">#${group.payment_sequence || '1'}</span></td>
                    <td>₱${parseFloat(group.total_paid || 0).toFixed(2)}</td>
                    <td>
                        <button class="btn btn-sm btn-primary select-sequence-btn" 
                                data-payer-id="${group.payer_id}" 
                                data-contribution-id="${group.contribution_id}" 
                                data-sequence="${group.payment_sequence || '1'}">
                            <i class="fas fa-list"></i> Select Payments
                        </button>
                    </td>
                </tr>
            `;
        });
    }
    $('#sequencesList').html(sequencesHtml);
}

/**
 * Initialize refund transaction modal
 */
function initRefundTransactionModal() {
    if (typeof jQuery === 'undefined') {
        return;
    }

    // Refund type change handler
    $('#refund_type').off('change').on('change', function() {
        const refundType = $(this).val();
        
        // Hide all views
        $('.refund-view').hide();
        
        // Clear form
        $('#processRefundForm')[0].reset();
        $('#paymentInfoDisplay').html('<p class="text-muted mb-0">Select a refund type and payment(s) above</p>');
        $('#available_amount').text('₱0.00');
        $('#refund_amount').val('').prop('readonly', true);
        
        // Show appropriate view
        if (refundType === 'group') {
            $('#groupRefundView').show();
        } else if (refundType === 'sequence') {
            $('#sequenceRefundView').show();
        }
        
        $('#refund_type_field').val(refundType);
    });

    // Search groups
    $('#groupSearch').off('keyup').on('keyup', function() {
        const search = $(this).val().toLowerCase();
        $('#groupsList tr').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(search) > -1);
        });
    });

    // Search sequences
    $('#sequenceSearch').off('keyup').on('keyup', function() {
        const search = $(this).val().toLowerCase();
        $('#sequencesList tr').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(search) > -1);
        });
    });

    // Select group for group refund
    $(document).off('click', '.select-group-btn').on('click', '.select-group-btn', function() {
        const payerId = $(this).data('payer-id');
        const contributionId = $(this).data('contribution-id');
        const sequence = $(this).data('sequence');
        
        $.ajax({
            url: window.APP_BASE_URL + 'admin/refunds/get-payment-group-details',
            method: 'GET',
            data: { 
                payer_id: payerId,
                contribution_id: contributionId,
                payment_sequence: sequence
            },
            success: function(response) {
                if (response.success) {
                    const group = response.group;
                    
                    if (!group || !group.payments || group.payments.length === 0) {
                        showNotification('No payments found in this group', 'error');
                        return;
                    }
                    
                    // Fill form fields
                    $('#refund_payer_id').val(payerId);
                    $('#refund_contribution_id').val(contributionId);
                    $('#refund_payment_sequence').val(sequence);
                    $('#refund_payment_id').val('');
                    $('#refund_payment_ids').val('');
                    $('#refund_amount').val(group.available_for_refund || 0).prop('readonly', true);
                    $('#available_amount').text('₱' + parseFloat(group.available_for_refund || 0).toFixed(2));
                    
                    // Get payer and contribution names
                    const payerName = group.payer_name || (group.payments[0] && group.payments[0].payer_name) || 'Unknown Payer';
                    const contributionTitle = group.contribution_title || (group.payments[0] && group.payments[0].contribution_title) || 'Unknown Contribution';
                    
                    // Display group info
                    let paymentsHtml = '<small class="text-muted">Payments in group:</small><ul class="list-unstyled mt-2 mb-0">';
                    group.payments.forEach(function(payment) {
                        paymentsHtml += `<li>• Payment #${payment.id}: ₱${parseFloat(payment.amount_paid || 0).toFixed(2)} (${payment.receipt_number || 'No receipt'})</li>`;
                    });
                    paymentsHtml += '</ul>';
                    
                    const infoHtml = `
                        <strong>${escapeHtml(payerName)}</strong><br>
                        <small>Sequence #${sequence} | ${escapeHtml(contributionTitle)}</small><br>
                        <span class="text-muted">Group Total: ₱${parseFloat(group.total_amount || 0).toFixed(2)}</span><br>
                        <span class="text-danger">Already Refunded: ₱${parseFloat(group.total_refunded || 0).toFixed(2)}</span><br>
                        ${paymentsHtml}
                    `;
                    $('#paymentInfoDisplay').html(infoHtml);
                } else {
                    showNotification(response.message || 'Failed to load payment group details', 'error');
                }
            },
            error: function(xhr) {
                let message = 'An error occurred while loading payment group details';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                showNotification(message, 'error');
            }
        });
    });

    // Select sequence for sequence refund
    $(document).off('click', '.select-sequence-btn').on('click', '.select-sequence-btn', function() {
        const payerId = $(this).data('payer-id');
        const contributionId = $(this).data('contribution-id');
        const sequence = $(this).data('sequence');
        
        $.ajax({
            url: window.APP_BASE_URL + 'admin/refunds/get-payment-group-details',
            method: 'GET',
            data: { 
                payer_id: payerId,
                contribution_id: contributionId,
                payment_sequence: sequence
            },
            success: function(response) {
                if (response.success) {
                    const group = response.group;
                    selectedSequencePayments = group.payments || [];
                    
                    // Fill form fields
                    $('#refund_payer_id').val(payerId);
                    $('#refund_contribution_id').val(contributionId);
                    $('#refund_payment_sequence').val(sequence);
                    $('#refund_payment_id').val('');
                    
                    // Calculate available refund amount per payment
                    // Need to check refunds for each payment individually
                    let paymentsHtml = '<small class="text-muted">Select payments to refund:</small><div class="mt-2">';
                    
                    // Get refund data for each payment to calculate available amounts
                    const paymentPromises = group.payments.map(function(payment) {
                        return $.ajax({
                            url: window.APP_BASE_URL + 'admin/refunds/get-payment-details',
                            method: 'GET',
                            data: { payment_id: payment.id }
                        });
                    });
                    
                    Promise.all(paymentPromises).then(function(paymentResponses) {
                        let totalAvailable = 0;
                        let selectedTotal = 0;
                        const selectedIds = [];
                        
                        group.payments.forEach(function(payment, index) {
                            const paymentResponse = paymentResponses[index];
                            const availableForRefund = paymentResponse.success ? 
                                (parseFloat(paymentResponse.payment.available_for_refund) || 0) : 
                                parseFloat(payment.amount_paid || 0);
                            
                            totalAvailable += availableForRefund;
                            
                            // Only check if there's available amount
                            const isAvailable = availableForRefund > 0;
                            
                            if (isAvailable) {
                                selectedTotal += availableForRefund;
                                selectedIds.push(payment.id);
                            }
                            
                            paymentsHtml += `
                                <div class="form-check">
                                    <input class="form-check-input sequence-payment-check" type="checkbox" 
                                           value="${payment.id}" id="pay_${payment.id}" 
                                           data-amount="${availableForRefund}"
                                           ${isAvailable ? 'checked' : 'disabled'}>
                                    <label class="form-check-label ${!isAvailable ? 'text-muted' : ''}" for="pay_${payment.id}">
                                        Payment #${payment.id}: ₱${parseFloat(payment.amount_paid || 0).toFixed(2)} 
                                        (${payment.receipt_number || 'No receipt'}) - ${payment.payment_date ? new Date(payment.payment_date).toLocaleDateString() : ''}
                                        ${availableForRefund < parseFloat(payment.amount_paid || 0) ? 
                                            `<br><small class="text-warning">Already refunded: ₱${(parseFloat(payment.amount_paid || 0) - availableForRefund).toFixed(2)}</small>` : 
                                            ''}
                                        ${!isAvailable ? '<span class="badge bg-secondary ms-2">Fully Refunded</span>' : ''}
                                    </label>
                                </div>
                            `;
                        });
                        
                        paymentsHtml += '</div>';
                        
                        // Get payer and contribution names
                        const payerName = group.payer_name || (group.payments[0] && group.payments[0].payer_name) || 'Unknown Payer';
                        const contributionTitle = group.contribution_title || (group.payments[0] && group.payments[0].contribution_title) || 'Unknown Contribution';
                        
                        const infoHtml = `
                            <strong>${escapeHtml(payerName)}</strong><br>
                            <small>Sequence #${sequence} | ${escapeHtml(contributionTitle)}</small><br>
                            <span class="text-muted">Group Total: ₱${parseFloat(group.total_amount || 0).toFixed(2)}</span><br>
                            <span class="text-danger">Already Refunded: ₱${parseFloat(group.total_refunded || 0).toFixed(2)}</span><br>
                            ${paymentsHtml}
                        `;
                        $('#paymentInfoDisplay').html(infoHtml);
                        
                        // Set initial refund amount and payment IDs
                        $('#refund_amount').val(selectedTotal.toFixed(2)).prop('readonly', true);
                        $('#refund_payment_ids').val(JSON.stringify(selectedIds));
                        $('#available_amount').text('₱' + totalAvailable.toFixed(2));
                        
                        // Update selected payments when checkboxes change
                        updateSequencePaymentSelection();
                    }).catch(function(error) {
                        // Fallback: show all payments as available
                        group.payments.forEach(function(payment) {
                            paymentsHtml += `
                                <div class="form-check">
                                    <input class="form-check-input sequence-payment-check" type="checkbox" 
                                           value="${payment.id}" id="pay_${payment.id}" 
                                           data-amount="${payment.amount_paid}" checked>
                                    <label class="form-check-label" for="pay_${payment.id}">
                                        Payment #${payment.id}: ₱${parseFloat(payment.amount_paid || 0).toFixed(2)} 
                                        (${payment.receipt_number || 'No receipt'}) - ${payment.payment_date ? new Date(payment.payment_date).toLocaleDateString() : ''}
                                    </label>
                                </div>
                            `;
                        });
                        paymentsHtml += '</div>';
                        const payerName = group.payer_name || (group.payments[0] && group.payments[0].payer_name) || 'Unknown Payer';
                        const contributionTitle = group.contribution_title || (group.payments[0] && group.payments[0].contribution_title) || 'Unknown Contribution';
                        const infoHtml = `
                            <strong>${escapeHtml(payerName)}</strong><br>
                            <small>Sequence #${sequence} | ${escapeHtml(contributionTitle)}</small><br>
                            <span class="text-muted">Group Total: ₱${parseFloat(group.total_amount || 0).toFixed(2)}</span><br>
                            <span class="text-danger">Already Refunded: ₱${parseFloat(group.total_refunded || 0).toFixed(2)}</span><br>
                            ${paymentsHtml}
                        `;
                        $('#paymentInfoDisplay').html(infoHtml);
                        updateSequencePaymentSelection();
                    });
                } else {
                    showNotification(response.message || 'Failed to load payment group details', 'error');
                }
            },
            error: function(xhr) {
                let message = 'An error occurred while loading payment group details';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                showNotification(message, 'error');
            }
        });
    });

    // Update sequence payment selection
    function updateSequencePaymentSelection() {
        const selectedIds = [];
        let selectedTotal = 0;
        
        $('.sequence-payment-check:checked').each(function() {
            const paymentId = $(this).val();
            const amount = parseFloat($(this).data('amount') || 0);
            selectedIds.push(paymentId);
            selectedTotal += amount;
        });
        
        $('#refund_payment_ids').val(JSON.stringify(selectedIds));
        $('#refund_amount').val(selectedTotal.toFixed(2)).prop('readonly', true);
    }

    $(document).off('change', '.sequence-payment-check').on('change', '.sequence-payment-check', function() {
        updateSequencePaymentSelection();
    });

    // Process refund form submission
    $('#processRefundForm').off('submit').on('submit', function(e) {
        e.preventDefault();
        
        const refundType = $('#refund_type_field').val();
        if (!refundType) {
            showNotification('Please select a refund type', 'error');
            return;
        }

        // Validate required fields
        const refundAmount = parseFloat($('#refund_amount').val() || 0);
        if (refundAmount <= 0) {
            showNotification('Please enter a refund amount greater than 0', 'error');
            return;
        }

        if (!confirm(`Are you sure you want to process this ${refundType} refund of ₱${refundAmount.toFixed(2)}?`)) {
            return;
        }

        const formData = $(this).serialize();
        
        // Show loading indicator
        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.html();
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Processing...');
        
        $.ajax({
            url: window.APP_BASE_URL + 'admin/refunds/process',
            method: 'POST',
            data: formData,
            success: function(response) {
                submitBtn.prop('disabled', false).html(originalText);
                
                if (response.success) {
                    let message = 'Refund processed successfully';
                    if (response.payment_count && response.payment_count > 1) {
                        message += ` (${response.payment_count} payments, Total: ₱${parseFloat(response.total_refunded || 0).toFixed(2)})`;
                    }
                    showNotification(message, 'success');
                    $('#processRefundForm')[0].reset();
                    $('#refund_type').val('').trigger('change');
                    $('#paymentInfoDisplay').html('<p class="text-muted mb-0">Select a refund type and payment(s) above</p>');
                    
                    // Close modal and trigger callback if defined
                    $('#refundTransactionModal').modal('hide');
                    
                    // Trigger custom event for other scripts to listen
                    $(document).trigger('refund:success', [response]);
                    
                    // Reload payment groups after a short delay
                    setTimeout(function() {
                        loadGroupedPayments();
                    }, 500);
                } else {
                    let errorMessage = response.message || 'Failed to process refund';
                    if (response.errors && typeof response.errors === 'object') {
                        const errorList = Object.values(response.errors).flat();
                        if (errorList.length > 0) {
                            errorMessage += ': ' + errorList.join(', ');
                        }
                    }
                    showNotification(errorMessage, 'error');
                }
            },
            error: function(xhr) {
                submitBtn.prop('disabled', false).html(originalText);
                
                let message = 'An error occurred while processing the refund';
                if (xhr.responseJSON) {
                    if (xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }
                    if (xhr.responseJSON.errors && typeof xhr.responseJSON.errors === 'object') {
                        const errorList = Object.values(xhr.responseJSON.errors).flat();
                        if (errorList.length > 0) {
                            message += ': ' + errorList.join(', ');
                        }
                    }
                } else if (xhr.status === 0) {
                    message = 'Network error. Please check your connection.';
                } else if (xhr.status >= 500) {
                    message = 'Server error. Please try again later.';
                }
                showNotification(message, 'error');
            }
        });
    });

    // Load grouped payments when modal is shown
    $('#refundTransactionModal').off('shown.bs.modal').on('shown.bs.modal', function() {
        loadGroupedPayments();
    });

    // Reset form when modal is hidden
    $('#refundTransactionModal').off('hidden.bs.modal').on('hidden.bs.modal', function() {
        $('#processRefundForm')[0].reset();
        $('#refund_type').val('').trigger('change');
        $('#paymentInfoDisplay').html('<p class="text-muted mb-0">Select a refund type and payment(s) above</p>');
        selectedSequencePayments = [];
    });
}

// Helper functions
function escapeHtml(text) {
    if (!text) return '';
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, m => map[m]);
}

function showNotification(message, type) {
    // Use existing notification system if available
    if (typeof window.showFlashMessage === 'function') {
        window.showFlashMessage(message, type);
    } else if (typeof showNotification !== window.showNotification) {
        // Fallback to alert
        alert(message);
    }
}

// Initialize when jQuery is ready
if (typeof jQuery !== 'undefined') {
    $(document).ready(function() {
        initRefundTransactionModal();
    });
} else {
    // Wait for jQuery
    document.addEventListener('DOMContentLoaded', function() {
        var checkJQuery = setInterval(function() {
            if (typeof jQuery !== 'undefined') {
                clearInterval(checkJQuery);
                $(document).ready(function() {
                    initRefundTransactionModal();
                });
            }
        }, 100);
        setTimeout(function() { clearInterval(checkJQuery); }, 5000);
    });
}


// --- Global Functions for Opening Refund Modal with Pre-filled Data ---

/**
 * Open refund modal for a payment group (Group Refund)
 * Pre-selects "Group Refund" and automatically selects the specified group
 */
window.openRefundModalForGroup = function(payerId, contributionId, sequence) {
    // Open the refund modal
    const refundModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('refundTransactionModal'));
    refundModal.show();

    // Wait for the modal to be fully shown and groups loaded
    $('#refundTransactionModal').one('shown.bs.modal', function() {
        // Set refund type to 'group'
        $('#refund_type').val('group').trigger('change');

        // Wait for groups to be loaded - use a retry mechanism
        let retryCount = 0;
        const maxRetries = 10;
        
        const trySelectGroup = () => {
            // Find the button - try multiple selectors
            let selectButton = $(`#groupsList button.select-group-btn[data-payer-id="${payerId}"][data-contribution-id="${contributionId}"][data-sequence="${sequence}"]`);
            
            if (!selectButton.length) {
                selectButton = $(`#groupsList button[data-payer-id="${payerId}"][data-contribution-id="${contributionId}"][data-sequence="${sequence}"]`);
            }
            
            if (selectButton.length) {
                selectButton.click();
            } else if (retryCount < maxRetries) {
                retryCount++;
                setTimeout(trySelectGroup, 300); // Retry after 300ms
            } else {
                // If we still can't find it, directly call the select group handler with the data
                // Trigger the AJAX call directly
                $.ajax({
                    url: window.APP_BASE_URL + 'admin/refunds/get-payment-group-details',
                    method: 'GET',
                    data: { 
                        payer_id: payerId,
                        contribution_id: contributionId,
                        payment_sequence: sequence
                    },
                    success: function(response) {
                        if (response.success) {
                            const group = response.group;
                            if (!group || !group.payments || group.payments.length === 0) {
                                showNotification('No payments found in this group', 'error');
                                return;
                            }
                            
                            // Fill form fields
                            $('#refund_payer_id').val(payerId);
                            $('#refund_contribution_id').val(contributionId);
                            $('#refund_payment_sequence').val(sequence);
                            $('#refund_payment_id').val('');
                            $('#refund_payment_ids').val('');
                            $('#refund_amount').val(parseFloat(group.available_for_refund || 0).toFixed(2));
                            $('#available_amount').text('₱' + parseFloat(group.available_for_refund || 0).toFixed(2));
                            
                            const payerName = group.payer_name || (group.payments[0] && group.payments[0].payer_name) || 'Unknown Payer';
                            const contributionTitle = group.contribution_title || (group.payments[0] && group.payments[0].contribution_title) || 'Unknown Contribution';
                            
                            let paymentsHtml = '<small class="text-muted">Payments in group:</small><ul class="list-unstyled mt-2 mb-0">';
                            group.payments.forEach(function(payment) {
                                paymentsHtml += `<li>• Payment #${payment.id}: ₱${parseFloat(payment.amount_paid || 0).toFixed(2)} (${payment.receipt_number || 'No receipt'})</li>`;
                            });
                            paymentsHtml += '</ul>';
                            
                            const infoHtml = `
                                <strong>${escapeHtml(payerName)}</strong><br>
                                <small>Sequence #${sequence} | ${escapeHtml(contributionTitle)}</small><br>
                                <span class="text-muted">Group Total: ₱${parseFloat(group.total_amount || 0).toFixed(2)}</span><br>
                                <span class="text-danger">Already Refunded: ₱${parseFloat(group.total_refunded || 0).toFixed(2)}</span><br>
                                ${paymentsHtml}
                            `;
                            $('#paymentInfoDisplay').html(infoHtml);
                        } else {
                            showNotification(response.message || 'Failed to load payment group details', 'error');
                        }
                    }
                });
            }
        };
        
        // Start trying after initial delay
        setTimeout(trySelectGroup, 500);
    });
};

/**
 * Open refund modal for a specific payment (Sequence Refund)
 * Pre-selects "Sequence Refund" and automatically selects only the specified payment
 */
window.openRefundModalForPayment = function(paymentId, payerId, contributionId, sequence) {
    // Open the refund modal
    const refundModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('refundTransactionModal'));
    refundModal.show();

    // Wait for the modal to be fully shown
    $('#refundTransactionModal').one('shown.bs.modal', function() {
        // Set refund type to 'sequence'
        $('#refund_type').val('sequence').trigger('change');

        // Wait for lists to be populated - use retry mechanism
        let retryCount = 0;
        const maxRetries = 10;
        
        const trySelectSequence = () => {
            // Find the button - try multiple selectors
            let selectButton = $(`#sequencesList button.select-sequence-btn[data-payer-id="${payerId}"][data-contribution-id="${contributionId}"][data-sequence="${sequence}"]`);
            
            if (!selectButton.length) {
                selectButton = $(`#sequencesList button[data-payer-id="${payerId}"][data-contribution-id="${contributionId}"][data-sequence="${sequence}"]`);
            }
            
            if (selectButton.length) {
                selectButton.click();
                
                // Wait for payment details to load, then select the specific payment
                setTimeout(() => {
                    selectSpecificPayment(paymentId);
                }, 1200); // Give time for payment details to load after clicking Select Payments
            } else if (retryCount < maxRetries) {
                retryCount++;
                setTimeout(trySelectSequence, 300); // Retry after 300ms
            } else {
                // If button still not found, directly call the AJAX to load group details
                loadSequenceAndSelectPayment(payerId, contributionId, sequence, paymentId);
            }
        };
        
        // Helper function to select specific payment after details are loaded
        const selectSpecificPayment = (paymentId) => {
            // Uncheck all payment checkboxes first
            $('.sequence-payment-check').prop('checked', false);
            
            // Check only the specific payment
            const specificPaymentCheckbox = $(`#pay_${paymentId}`);
            if (specificPaymentCheckbox.length && !specificPaymentCheckbox.is(':disabled')) {
                specificPaymentCheckbox.prop('checked', true);
                specificPaymentCheckbox.trigger('change');
                if (typeof updateSequencePaymentSelection === 'function') {
                    updateSequencePaymentSelection();
                }
            } else if (specificPaymentCheckbox.is(':disabled')) {
                showNotification('This payment is already fully refunded and cannot be selected.', 'warning');
            } else {
                // Retry finding the checkbox
                setTimeout(() => selectSpecificPayment(paymentId), 500);
            }
        };
        
        // Helper function to directly load sequence and select payment
        const loadSequenceAndSelectPayment = (payerId, contributionId, sequence, paymentId) => {
            $.ajax({
                url: window.APP_BASE_URL + 'admin/refunds/get-payment-group-details',
                method: 'GET',
                data: { 
                    payer_id: payerId,
                    contribution_id: contributionId,
                    payment_sequence: sequence
                },
                success: function(response) {
                    if (response.success) {
                        const group = response.group;
                        selectedSequencePayments = group.payments || [];
                        
                        // Fill form fields
                        $('#refund_payer_id').val(payerId);
                        $('#refund_contribution_id').val(contributionId);
                        $('#refund_payment_sequence').val(sequence);
                        $('#refund_payment_id').val('');
                        
                        // Load payment details and render checkboxes
                        const paymentPromises = group.payments.map(function(payment) {
                            return $.ajax({
                                url: window.APP_BASE_URL + 'admin/refunds/get-payment-details',
                                method: 'GET',
                                data: { payment_id: payment.id }
                            });
                        });
                        
                        Promise.all(paymentPromises).then(function(paymentResponses) {
                            let totalAvailable = 0;
                            let paymentsHtml = '<small class="text-muted">Select payments to refund:</small><div class="mt-2">';
                            
                            group.payments.forEach(function(payment, index) {
                                const paymentResponse = paymentResponses[index];
                                const availableForRefund = paymentResponse.success ? 
                                    (parseFloat(paymentResponse.payment.available_for_refund) || 0) : 
                                    parseFloat(payment.amount_paid || 0);
                                
                                totalAvailable += availableForRefund;
                                const isAvailable = availableForRefund > 0;
                                const isSelected = payment.id == paymentId && isAvailable;
                                
                                paymentsHtml += `
                                    <div class="form-check">
                                        <input class="form-check-input sequence-payment-check" type="checkbox" 
                                               value="${payment.id}" id="pay_${payment.id}" 
                                               data-amount="${availableForRefund}"
                                               ${isSelected ? 'checked' : ''} ${!isAvailable ? 'disabled' : ''}>
                                        <label class="form-check-label ${!isAvailable ? 'text-muted' : ''}" for="pay_${payment.id}">
                                            Payment #${payment.id}: ₱${parseFloat(payment.amount_paid || 0).toFixed(2)} 
                                            (${payment.receipt_number || 'No receipt'}) - ${payment.payment_date ? new Date(payment.payment_date).toLocaleDateString() : ''}
                                            ${!isAvailable ? '<span class="badge bg-secondary ms-2">Fully Refunded</span>' : ''}
                                        </label>
                                    </div>
                                `;
                            });
                            paymentsHtml += '</div>';
                            
                            const payerName = group.payer_name || (group.payments[0] && group.payments[0].payer_name) || 'Unknown Payer';
                            const contributionTitle = group.contribution_title || (group.payments[0] && group.payments[0].contribution_title) || 'Unknown Contribution';
                            
                            const infoHtml = `
                                <strong>${escapeHtml(payerName)}</strong><br>
                                <small>Sequence #${sequence} | ${escapeHtml(contributionTitle)}</small><br>
                                <span class="text-muted">Group Total: ₱${parseFloat(group.total_amount || 0).toFixed(2)}</span><br>
                                <span class="text-danger">Already Refunded: ₱${parseFloat(group.total_refunded || 0).toFixed(2)}</span><br>
                                ${paymentsHtml}
                            `;
                            $('#paymentInfoDisplay').html(infoHtml);
                            
                            // Update selected payments
                            if (typeof updateSequencePaymentSelection === 'function') {
                                updateSequencePaymentSelection();
                            }
                        });
                    }
                }
            });
        };
        
        // Start trying after initial delay
        setTimeout(trySelectSequence, 500);
    });
};

