<div class="modal fade" id="requestRefundModal" tabindex="-1" aria-labelledby="requestRefundModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="requestRefundModalLabel">
                    <i class="fas fa-undo me-2"></i>Request Refund
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="refundRequestForm">
                    <div class="mb-3">
                        <label for="payment_id" class="form-label">Select Payment <span class="text-danger">*</span></label>
                        <select class="form-select" id="payment_id" name="payment_id" required>
                            <option value="">-- Select Payment --</option>
                            <?php if (isset($refundablePayments)) foreach ($refundablePayments as $payment): ?>
                                <option value="<?= $payment['id'] ?>" 
                                        data-amount="<?= $payment['amount_paid'] ?>"
                                        data-available="<?= $payment['available_refund'] ?>"
                                        data-refund-status="<?= $payment['refund_status'] ?>">
                                    <?= esc($payment['contribution_title']) ?> - 
                                    Receipt: <?= esc($payment['receipt_number'] ?? 'N/A') ?> - 
                                    Amount: ₱<?= number_format($payment['amount_paid'], 2) ?> 
                                    (Available: ₱<?= number_format($payment['available_refund'], 2) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">Only payments with available refund amounts are shown</small>
                    </div>

                    <div class="mb-3" id="paymentInfoSection" style="display: none;">
                        <div class="card bg-light p-3">
                            <div class="row">
                                <div class="col-md-6">
                                    <small class="text-muted">Original Amount:</small>
                                    <div class="fw-bold" id="originalAmount">₱0.00</div>
                                </div>
                                <div class="col-md-6">
                                    <small class="text-muted">Available for Refund:</small>
                                    <div class="fw-bold text-success" id="availableAmount">₱0.00</div>
                                </div>
                            </div>
                            <div class="mt-2">
                                <small class="text-muted">Refund Status:</small>
                                <div id="refundStatusBadge"></div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="refund_amount" class="form-label">Refund Amount <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">₱</span>
                            <input type="number" class="form-control" id="refund_amount" name="refund_amount" 
                                   step="0.01" min="0.01" required>
                        </div>
                        <small class="text-muted">Maximum available: <span id="maxRefundAmount">₱0.00</span></small>
                    </div>

                    <div class="mb-3">
                        <label for="refund_method" class="form-label">Refund Method <span class="text-danger">*</span></label>
                        <select class="form-select" id="refund_method" name="refund_method" required>
                            <option value="">-- Select Refund Method --</option>
                            <?php if (isset($refundMethods) && !empty($refundMethods)): ?>
                                <?php foreach ($refundMethods as $method): ?>
                                    <option value="<?= esc($method['code']) ?>"><?= esc($method['name']) ?></option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option value="original_method">Original Payment Method</option>
                                <option value="cash">Cash</option>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="gcash">GCash</option>
                                <option value="paymaya">PayMaya</option>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="refund_reason" class="form-label">Reason for Refund</label>
                        <textarea class="form-control" id="refund_reason" name="refund_reason" 
                                  rows="3" placeholder="Please provide a reason for requesting this refund..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="submitRefundRequest()">
                    <i class="fas fa-paper-plane me-1"></i>Submit Request
                </button>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    const modalElem = document.getElementById('requestRefundModal');
    if (!modalElem) return;
    let handlerAttached = false;
    if (!handlerAttached) {
        modalElem.addEventListener('shown.bs.modal', async function() {
            // Populate refund methods from backend
            try {
                const res = await fetch('/payer/refund-methods', { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                const json = await res.json();
                if (json && json.success) {
                    const methodSelect = document.getElementById('refund_method');
                    if (methodSelect) {
                        // Clear existing (keep placeholder at index 0)
                        methodSelect.options.length = 1;
                        json.methods.forEach(m => {
                            const opt = document.createElement('option');
                            opt.value = m.code;
                            opt.textContent = m.name;
                            methodSelect.appendChild(opt);
                        });
                        // Select first available method by default
                        if (methodSelect.options.length > 1) {
                            methodSelect.selectedIndex = 1;
                        }
                    }
                }
            } catch(e) {
                // If fetch fails, keep current options
            }

            // Existing autofill for payment and amounts
            let payment = window._refundModalPayment;
            if (!payment && modalElem.dataset && modalElem.dataset.payment) {
                try { payment = JSON.parse(modalElem.dataset.payment); } catch(e) {}
            }
            var select = document.getElementById('payment_id');
            if (payment && select && payment.id) {
                const paymentId = String(payment.id);
                let found = false;
                for (let i=0; i<select.options.length; ++i) {
                    if (String(select.options[i].value) === paymentId) { found = true; break; }
                }
                if (!found) {
                    let option = document.createElement('option');
                    option.value = paymentId;
                    option.text = `${payment.contribution_title || payment.title || 'Payment'} - Receipt: ${(payment.receipt_number||payment.reference_number||'N/A')} - Amount: ₱${parseFloat(payment.amount_paid||0).toFixed(2)}`;
                    option.setAttribute('data-amount', payment.amount_paid||0);
                    option.setAttribute('data-available', payment.available_refund||0);
                    option.setAttribute('data-refund-status', payment.refund_status||'');
                    select.insertBefore(option, select.firstChild.nextSibling);
                }
                select.value = paymentId;
                if (document.getElementById('refund_amount')) {
                    document.getElementById('refund_amount').value = payment.available_refund || payment.amount_paid || '';
                }
                if (document.getElementById('maxRefundAmount')) {
                    document.getElementById('maxRefundAmount').innerText = `₱${parseFloat(payment.available_refund||payment.amount_paid||0).toFixed(2)}`;
                }
                if (document.getElementById('originalAmount')) {
                    document.getElementById('originalAmount').innerText = `₱${parseFloat(payment.amount_paid||0).toFixed(2)}`;
                }
                if (document.getElementById('availableAmount')) {
                    document.getElementById('availableAmount').innerText = `₱${parseFloat(payment.available_refund||payment.amount_paid||0).toFixed(2)}`;
                }
                var info = document.getElementById('paymentInfoSection');
                if (info) info.style.display = '';
                window._refundModalPayment = null;
                delete modalElem.dataset.payment;
            }
        });
        handlerAttached = true;
    }
})();
</script>

<script>
function submitRefundRequest() {
    var form = document.getElementById('refundRequestForm');
    if (!form) return;
    if (!form.reportValidity()) return;
    var data = new FormData(form);
    var btn = form.closest('.modal-content').querySelector('.btn-primary');
    var originalBtn = btn ? btn.innerHTML : '';
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';
    }
    fetch('/payer/submit-refund-request', {
        method: 'POST',
        body: data,
        headers: {'X-Requested-With':'XMLHttpRequest'},
    })
    .then(resp => resp.json ? resp.json() : resp)
    .then(result => {
        if (result && result.success) {
            alert('Refund request submitted successfully!');
            const modal = bootstrap.Modal.getInstance(document.getElementById('requestRefundModal'));
            if (modal) modal.hide();
        } else {
            alert((result && result.message) || 'An error occurred while submitting.');
        }
    })
    .catch(e => {
        alert('An unexpected error occurred when submitting the refund request.');
    })
    .finally(() => {
        if (btn) {
            btn.innerHTML = originalBtn;
            btn.disabled = false;
        }
    });
}
</script>