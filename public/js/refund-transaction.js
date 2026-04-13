if (typeof window.APP_BASE_URL === 'undefined') {
    window.APP_BASE_URL = window.location.origin + '/';
}

let groupedPaymentsCache = [];

function escapeHtml(text) {
    if (!text) {
        return '';
    }

    return String(text).replace(/[&<>"']/g, function(match) {
        return {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;',
        }[match];
    });
}

function notifyRefund(message, type) {
    if (typeof window.showNotification === 'function') {
        window.showNotification(message, type);
        return;
    }

    alert(message);
}

function formatRefundErrors(errors) {
    if (!errors || typeof errors !== 'object') {
        return '';
    }

    const values = Object.values(errors).filter(Boolean);
    if (!values.length) {
        return '';
    }

    return values.join(' ');
}

function getReferenceLabel(group) {
    if ((group.item_type || 'contribution') === 'product') {
        return 'Purchase #' + (group.payment_sequence || group.payment_count || 1);
    }

    return 'Contribution';
}

function resetRefundSelection() {
    const form = document.getElementById('processRefundForm');
    if (form) {
        form.reset();
    }

    $('#refund_contribution_id').val('');
    $('#refund_product_id').val('');
    $('#refund_payment_ids').val('');
    $('#refund_payment_sequence').val('');
    $('#paymentInfoDisplay').html('<p class="text-muted mb-0">Select a refund type and payment(s) above</p>');
    $('#available_amount').text('PHP 0.00');
    $('#refund_amount').val('').prop('readonly', true);
}

function loadGroupedPayments() {
    $.ajax({
        url: window.APP_BASE_URL + 'admin/refunds/get-payment-groups',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            groupedPaymentsCache = response.success && response.data ? response.data : [];
            renderGroupedPayments();
        },
        error: function() {
            $('#groupsList, #sequencesList').html('<tr><td colspan="6" class="text-center text-danger py-4">Failed to load refundable items</td></tr>');
        },
    });
}

function renderGroupedPayments() {
    if (!groupedPaymentsCache.length) {
        $('#groupsList').html('<tr><td colspan="6" class="text-center text-muted py-4">No refundable items available</td></tr>');
        $('#sequencesList').html('<tr><td colspan="5" class="text-center text-muted py-4">No refundable items available</td></tr>');
        return;
    }

    let groupsHtml = '';
    let sequencesHtml = '';

    groupedPaymentsCache.forEach(function(group) {
        const badge = (group.item_type || 'contribution') === 'product'
            ? '<span class="badge bg-primary ms-1">Product</span>'
            : '<span class="badge bg-info ms-1">Contribution</span>';
        const reference = escapeHtml(getReferenceLabel(group));
        const commonData = `
            data-payer-id="${group.payer_id || ''}"
            data-contribution-id="${group.contribution_id || ''}"
            data-product-id="${group.product_id || ''}"
            data-sequence="${group.payment_sequence || ''}"
        `;

        groupsHtml += `
            <tr>
                <td>${escapeHtml(group.payer_name || 'Unknown')}</td>
                <td>${escapeHtml(group.contribution_title || 'Unknown')} ${badge}</td>
                <td>${reference}</td>
                <td>PHP ${parseFloat(group.total_paid || 0).toFixed(2)}</td>
                <td>${group.payment_count || 0} payment(s)</td>
                <td><button class="btn btn-sm btn-primary select-group-btn" ${commonData}><i class="fas fa-undo"></i> Select</button></td>
            </tr>
        `;

        sequencesHtml += `
            <tr>
                <td>${escapeHtml(group.payer_name || 'Unknown')}</td>
                <td>${escapeHtml(group.contribution_title || 'Unknown')} ${badge}</td>
                <td>${reference}</td>
                <td>PHP ${parseFloat(group.total_paid || 0).toFixed(2)}</td>
                <td><button class="btn btn-sm btn-primary select-sequence-btn" ${commonData}><i class="fas fa-list"></i> Select</button></td>
            </tr>
        `;
    });

    $('#groupsList').html(groupsHtml);
    $('#sequencesList').html(sequencesHtml);
}

function requestGroupDetails(payerId, contributionId, productId, sequence, onSuccess) {
    $.ajax({
        url: window.APP_BASE_URL + 'admin/refunds/get-payment-group-details',
        method: 'GET',
        data: {
            payer_id: payerId,
            contribution_id: contributionId || '',
            product_id: productId || '',
            payment_sequence: sequence,
        },
        success: function(response) {
            if (!response.success || !response.group) {
                notifyRefund(response.message || 'Failed to load refund details', 'error');
                return;
            }

            onSuccess(response.group);
        },
        error: function(xhr) {
            notifyRefund(xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Failed to load refund details', 'error');
        },
    });
}

function fillGroupForm(group) {
    const firstPayment = (group.payments || [])[0] || {};
    const payerId = group.payer_id || firstPayment.payer_id || '';
    const contributionId = group.contribution_id || firstPayment.contribution_id || '';
    const productId = group.product_id || firstPayment.product_id || '';
    const title = group.contribution_title || firstPayment.contribution_title || 'Unknown';
    const itemBadge = productId ? 'Product' : 'Contribution';

    $('#refund_payer_id').val(payerId);
    $('#refund_contribution_id').val(contributionId);
    $('#refund_product_id').val(productId);
    $('#refund_payment_sequence').val(group.payment_sequence || '');
    $('#refund_payment_ids').val('');
    $('#refund_amount').val(parseFloat(group.available_for_refund || 0).toFixed(2)).prop('readonly', true);
    $('#available_amount').text('PHP ' + parseFloat(group.available_for_refund || 0).toFixed(2));

    const paymentsList = (group.payments || []).map(function(payment) {
        return `<li>Payment #${payment.id}: PHP ${parseFloat(payment.amount_paid || 0).toFixed(2)}${payment.receipt_number ? ' (' + escapeHtml(payment.receipt_number) + ')' : ''}</li>`;
    }).join('');

    $('#paymentInfoDisplay').html(`
        <strong>${escapeHtml(group.payer_name || firstPayment.payer_name || 'Unknown')}</strong><br>
        <small>${itemBadge}: ${escapeHtml(title)}</small><br>
        <span class="text-muted">Total Paid: PHP ${parseFloat(group.total_amount || 0).toFixed(2)}</span><br>
        <span class="text-danger">Refundable: PHP ${parseFloat(group.available_for_refund || 0).toFixed(2)}</span>
        <ul class="mb-0 mt-2 ps-3">${paymentsList}</ul>
    `);
}

function updateSequenceSelection() {
    const selectedIds = [];
    let selectedTotal = 0;
    let totalAvailable = 0;

    $('.sequence-payment-check').each(function() {
        const amount = parseFloat($(this).data('amount') || 0);
        totalAvailable += amount;

        if ($(this).is(':checked')) {
            selectedIds.push($(this).val());
            selectedTotal += amount;
        }
    });

    $('#refund_payment_ids').val(JSON.stringify(selectedIds));
    $('#refund_amount').val(selectedTotal.toFixed(2)).prop('readonly', true);
    $('#available_amount').text('PHP ' + totalAvailable.toFixed(2));
}

function fillSequenceForm(group) {
    const firstPayment = (group.payments || [])[0] || {};
    const payerId = group.payer_id || firstPayment.payer_id || '';
    const contributionId = group.contribution_id || firstPayment.contribution_id || '';
    const productId = group.product_id || firstPayment.product_id || '';
    const title = group.contribution_title || firstPayment.contribution_title || 'Unknown';

    $('#refund_payer_id').val(payerId);
    $('#refund_contribution_id').val(contributionId);
    $('#refund_product_id').val(productId);
    $('#refund_payment_sequence').val(group.payment_sequence || '');

    const requests = (group.payments || []).map(function(payment) {
        return $.ajax({
            url: window.APP_BASE_URL + 'admin/refunds/get-payment-details',
            method: 'GET',
            data: { payment_id: payment.id },
        });
    });

    Promise.all(requests).then(function(responses) {
        const rows = (group.payments || []).map(function(payment, index) {
            const response = responses[index] || {};
            const available = response.success ? parseFloat(response.payment.available_for_refund || 0) : parseFloat(payment.amount_paid || 0);
            const disabled = available <= 0 ? 'disabled' : '';
            const checked = available > 0 ? 'checked' : '';
            return `
                <div class="form-check mb-2">
                    <input class="form-check-input sequence-payment-check" type="checkbox" id="refund_payment_${payment.id}" value="${payment.id}" data-amount="${available}" ${checked} ${disabled}>
                    <label class="form-check-label ${disabled ? 'text-muted' : ''}" for="refund_payment_${payment.id}">
                        Payment #${payment.id}: PHP ${parseFloat(payment.amount_paid || 0).toFixed(2)}
                        ${payment.receipt_number ? ' - ' + escapeHtml(payment.receipt_number) : ''}
                        ${available <= 0 ? '<span class="badge bg-secondary ms-2">Fully Refunded</span>' : ''}
                    </label>
                </div>
            `;
        }).join('');

        $('#paymentInfoDisplay').html(`
            <strong>${escapeHtml(group.payer_name || firstPayment.payer_name || 'Unknown')}</strong><br>
            <small>${productId ? 'Product' : 'Contribution'}: ${escapeHtml(title)}</small>
            <div class="mt-2">${rows}</div>
        `);

        $('.sequence-payment-check').off('change').on('change', updateSequenceSelection);
        updateSequenceSelection();
    }).catch(function() {
        notifyRefund('Failed to load individual payment refund details', 'error');
    });
}

function openRefundModal() {
    const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('refundTransactionModal'));
    modal.show();
}

function initRefundTransactionModal() {
    $('#refund_type').off('change').on('change', function() {
        const refundType = $(this).val();
        $('.refund-view').hide();
        resetRefundSelection();
        $('#refund_type_field').val(refundType);

        if (refundType === 'group') {
            $('#groupRefundView').show();
        } else if (refundType === 'sequence') {
            $('#sequenceRefundView').show();
        }
    });

    $('#groupSearch').off('keyup').on('keyup', function() {
        const search = $(this).val().toLowerCase();
        $('#groupsList tr').each(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(search) > -1);
        });
    });

    $('#sequenceSearch').off('keyup').on('keyup', function() {
        const search = $(this).val().toLowerCase();
        $('#sequencesList tr').each(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(search) > -1);
        });
    });

    $(document).off('click', '.select-group-btn').on('click', '.select-group-btn', function() {
        requestGroupDetails($(this).data('payer-id'), $(this).data('contribution-id'), $(this).data('product-id'), $(this).data('sequence'), fillGroupForm);
    });

    $(document).off('click', '.select-sequence-btn').on('click', '.select-sequence-btn', function() {
        requestGroupDetails($(this).data('payer-id'), $(this).data('contribution-id'), $(this).data('product-id'), $(this).data('sequence'), fillSequenceForm);
    });

    $('#processRefundForm').off('submit').on('submit', function(event) {
        event.preventDefault();

        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.html();
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Processing...');

        $.ajax({
            url: window.APP_BASE_URL + 'admin/refunds/process',
            method: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                submitBtn.prop('disabled', false).html(originalText);

                if (!response.success) {
                    const validationMessage = formatRefundErrors(response.errors);
                    notifyRefund(validationMessage || response.message || 'Failed to process refund', 'error');
                    return;
                }

                notifyRefund('Refund processed successfully', 'success');
                resetRefundSelection();
                bootstrap.Modal.getOrCreateInstance(document.getElementById('refundTransactionModal')).hide();
                $(document).trigger('refund:success', [response]);
                loadGroupedPayments();
            },
            error: function(xhr) {
                submitBtn.prop('disabled', false).html(originalText);
                const response = xhr.responseJSON || {};
                const validationMessage = formatRefundErrors(response.errors);
                notifyRefund(validationMessage || response.message || 'Failed to process refund', 'error');
            },
        });
    });

    $('#refundTransactionModal').off('shown.bs.modal').on('shown.bs.modal', function() {
        loadGroupedPayments();
    });

    $('#refundTransactionModal').off('hidden.bs.modal').on('hidden.bs.modal', function() {
        resetRefundSelection();
        $('#refund_type').val('');
        $('.refund-view').hide();
    });
}

window.openRefundModalForGroup = function(payerId, contributionId, sequence, productId) {
    openRefundModal();
    $('#refundTransactionModal').one('shown.bs.modal', function() {
        $('#refund_type').val('group').trigger('change');
        requestGroupDetails(payerId, contributionId || '', productId || '', sequence, fillGroupForm);
    });
};

window.openRefundModalForPayment = function(paymentId, payerId, contributionId, sequence, productId) {
    openRefundModal();
    $('#refundTransactionModal').one('shown.bs.modal', function() {
        $('#refund_type').val('sequence').trigger('change');
        requestGroupDetails(payerId, contributionId || '', productId || '', sequence, function(group) {
            fillSequenceForm(group);
            setTimeout(function() {
                $('.sequence-payment-check').prop('checked', false);
                $('#refund_payment_' + paymentId).prop('checked', true).trigger('change');
            }, 250);
        });
    });
};

if (typeof jQuery !== 'undefined') {
    $(document).ready(initRefundTransactionModal);
}
