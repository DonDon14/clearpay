<?php
$title = $title ?? 'Additional Payment';
$action = $action ?? base_url('payments/save');
$peso = '₱';
?>

<div class="modal fade" id="addPaymentToPartialModal" tabindex="-1" aria-labelledby="addPaymentToPartialModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-info text-white">
        <h5 class="modal-title" id="addPaymentToPartialModalLabel">
          <i class="fas fa-money-bill-wave me-2"></i><?= $title ?>
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <div class="alert alert-info mb-4">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <h6 class="alert-heading mb-1">Payment Summary</h6>
              <p class="mb-0"><strong>Payer:</strong> <span id="summaryPayerName">-</span></p>
              <p class="mb-0"><strong>Contribution:</strong> <span id="summaryContribution">-</span></p>
              <p class="mb-0"><strong>Total Amount:</strong> <span id="summaryTotalAmount" class="text-primary">-</span></p>
              <p class="mb-0"><strong>Amount Paid:</strong> <span id="summaryAmountPaid" class="text-success">-</span></p>
            </div>
            <div class="text-end">
              <h6 class="text-muted mb-1">Remaining Balance</h6>
              <h3 class="text-danger mb-0" id="summaryRemainingBalance"><?= $peso ?>0.00</h3>
            </div>
          </div>
        </div>

        <form id="addPaymentToPartialForm" action="<?= $action ?>" method="POST">
          <input type="hidden" id="partialPayerId" name="payer_id">
          <input type="hidden" id="partialPayerName" name="payer_name">
          <input type="hidden" id="partialContributionId" name="contribution_id">
          <input type="hidden" id="partialPaymentDate" name="payment_date" value="<?= get_current_datetime('Y-m-d H:i:s') ?>">

          <div class="mb-3">
            <label for="partialAmountPaid" class="form-label">Amount to Pay <span class="text-danger">*</span></label>
            <div class="input-group">
              <span class="input-group-text"><?= $peso ?></span>
              <input type="number" class="form-control" id="partialAmountPaid" name="amount_paid" step="0.01" min="0.01" required>
              <button type="button" class="btn btn-outline-success" id="partialFullyPaidBtn" title="Fill with remaining balance">
                <i class="fas fa-check-circle"></i> Fully Paid
              </button>
            </div>
            <small class="text-muted">Maximum: <span id="maxAmount" class="fw-bold text-danger"></span></small>
          </div>

          <div class="mb-3">
            <label for="partialPaymentMethod" class="form-label">Payment Method <span class="text-danger">*</span></label>
            <?= payment_method_dropdown_with_icons('payment_method', null, [
              'id' => 'partialPaymentMethod',
              'required' => 'required'
            ]) ?>
          </div>
        </form>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
          <i class="fas fa-times me-1"></i>Cancel
        </button>
        <button type="button" class="btn btn-primary" onclick="submitPartialPayment()">
          <i class="fas fa-save me-1"></i>Save Payment
        </button>
      </div>
    </div>
  </div>
</div>

<script>
let currentPartialPaymentData = null;

function openAddPaymentToPartialModal(payment) {
    if (!payment) {
        alert('No payment data provided');
        return;
    }

    if (!payment.id || !payment.contribution_id || !payment.payer_name) {
        alert('Missing required payment data');
        return;
    }

    currentPartialPaymentData = payment;

    const modal = new bootstrap.Modal(document.getElementById('addPaymentToPartialModal'));
    modal.show();

    modal._element.addEventListener('shown.bs.modal', function() {
        const groupText = payment.payment_sequence ? ` - Group ${payment.payment_sequence}` : '';
        const titleElement = document.getElementById('addPaymentToPartialModalLabel');
        if (titleElement) {
            titleElement.innerHTML = `<i class="fas fa-money-bill-wave me-2"></i>Additional Payment for ${payment.payer_name}${groupText}`;
        }

        const summaryPayerName = document.getElementById('summaryPayerName');
        const summaryContribution = document.getElementById('summaryContribution');
        const summaryTotalAmount = document.getElementById('summaryTotalAmount');
        const summaryAmountPaid = document.getElementById('summaryAmountPaid');
        const summaryRemainingBalance = document.getElementById('summaryRemainingBalance');

        if (summaryPayerName) summaryPayerName.textContent = payment.payer_name || 'N/A';
        if (summaryContribution) summaryContribution.textContent = payment.contribution_title || 'N/A';
        if (summaryTotalAmount) summaryTotalAmount.textContent = '₱' + parseFloat(payment.contribution_amount || 0).toFixed(2);
        if (summaryAmountPaid) summaryAmountPaid.textContent = '₱' + parseFloat(payment.total_paid || 0).toFixed(2);
        if (summaryRemainingBalance) {
            const remainingBalance = parseFloat(payment.remaining_balance || 0);
            summaryRemainingBalance.textContent = '₱' + remainingBalance.toFixed(2);
            summaryRemainingBalance.dataset.originalBalance = remainingBalance;
        }

        const remainingBalance = parseFloat(payment.remaining_balance || 0);
        const maxAmountElement = document.getElementById('maxAmount');
        const amountPaidElement = document.getElementById('partialAmountPaid');

        if (maxAmountElement) maxAmountElement.textContent = '₱' + remainingBalance.toFixed(2);
        if (amountPaidElement) {
            amountPaidElement.max = remainingBalance;
        }

        setTimeout(() => {
            const payerIdElement = document.getElementById('partialPayerId');
            const payerNameElement = document.getElementById('partialPayerName');
            const contributionIdElement = document.getElementById('partialContributionId');

            if (payerIdElement) payerIdElement.value = payment.id || '';
            if (payerNameElement) payerNameElement.value = payment.payer_name || '';
            if (contributionIdElement) contributionIdElement.value = payment.contribution_id || '';
        }, 100);

        setTimeout(() => {
            const payerIdElement = document.getElementById('partialPayerId');
            const payerNameElement = document.getElementById('partialPayerName');
            const contributionIdElement = document.getElementById('partialContributionId');

            if (payerIdElement && !payerIdElement.value) payerIdElement.value = payment.id || '';
            if (payerNameElement && !payerNameElement.value) payerNameElement.value = payment.payer_name || '';
            if (contributionIdElement && !contributionIdElement.value) contributionIdElement.value = payment.contribution_id || '';
        }, 500);

        if (amountPaidElement) amountPaidElement.value = '';

        const paymentMethodElement = document.getElementById('partialPaymentMethod');
        if (paymentMethodElement) paymentMethodElement.value = '';
    }, { once: true });
}

function submitPartialPayment() {
    if (currentPartialPaymentData) {
        const payerIdElement = document.getElementById('partialPayerId');
        const payerNameElement = document.getElementById('partialPayerName');
        const contributionIdElement = document.getElementById('partialContributionId');

        if (payerIdElement) payerIdElement.value = currentPartialPaymentData.id || '';
        if (payerNameElement) payerNameElement.value = currentPartialPaymentData.payer_name || '';
        if (contributionIdElement) contributionIdElement.value = currentPartialPaymentData.contribution_id || '';

        payerIdElement?.dispatchEvent(new Event('input', { bubbles: true }));
        payerNameElement?.dispatchEvent(new Event('input', { bubbles: true }));
        contributionIdElement?.dispatchEvent(new Event('input', { bubbles: true }));
    }

    const form = document.getElementById('addPaymentToPartialForm');
    const formData = new FormData(form);

    if (currentPartialPaymentData) {
        formData.set('payer_id', currentPartialPaymentData.id || '');
        formData.set('payer_name', currentPartialPaymentData.payer_name || '');
        formData.set('contribution_id', currentPartialPaymentData.contribution_id || '');
    }

    const amountPaid = parseFloat(formData.get('amount_paid')) || 0;
    let paymentMethod = formData.get('payment_method');

    const paymentMethodInput = document.getElementById('partialPaymentMethod_input');
    if (paymentMethodInput && paymentMethodInput.value) {
        paymentMethod = paymentMethodInput.value;
        formData.set('payment_method', paymentMethod);
    } else {
        const altInput = document.querySelector('input[name="payment_method"]');
        const altButton = document.querySelector('button[id*="paymentMethod"]');

        if (altInput && altInput.value) {
            paymentMethod = altInput.value;
            formData.set('payment_method', paymentMethod);
        } else if (altButton && altButton.textContent && altButton.textContent !== 'Select Payment Method') {
            const buttonText = altButton.textContent.trim();
            const paymentMethodName = buttonText.split(' ')[0];
            paymentMethod = paymentMethodName;
            formData.set('payment_method', paymentMethod);
        }
    }

    if (!amountPaid || !paymentMethod) {
        alert('Please fill in all required fields');
        return;
    }

    const remainingBalance = parseFloat(document.getElementById('summaryRemainingBalance').textContent.replace('₱', ''));
    const newRemaining = remainingBalance - amountPaid;

    formData.set('is_partial_payment', newRemaining > 0 ? '1' : '0');
    formData.set('remaining_balance', Math.max(0, newRemaining).toString());

    const saveBtn = document.querySelector('#addPaymentToPartialModal .btn-primary');
    if (saveBtn) {
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving...';
    }

    fetch(form.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Payment added successfully!');
                const modal = bootstrap.Modal.getInstance(document.getElementById('addPaymentToPartialModal'));
                modal.hide();
                location.reload();
            } else {
                alert('Error: ' + (data.message || 'Failed to add payment'));
            }
        })
        .catch(() => {
            alert('An error occurred while adding the payment');
        })
        .finally(() => {
            if (saveBtn) {
                saveBtn.disabled = false;
                saveBtn.innerHTML = '<i class="fas fa-save me-1"></i>Save Payment';
            }
        });
}

document.addEventListener('DOMContentLoaded', function() {
    const amountInput = document.getElementById('partialAmountPaid');
    if (amountInput) {
        amountInput.addEventListener('input', function() {
            const remainingBalanceElement = document.getElementById('summaryRemainingBalance');
            if (!remainingBalanceElement) {
                return;
            }

            const originalRemainingBalance = parseFloat(
                remainingBalanceElement.dataset.originalBalance || remainingBalanceElement.textContent.replace('₱', '')
            );
            const currentValue = parseFloat(this.value) || 0;
            const newRemainingBalance = originalRemainingBalance - currentValue;

            if (newRemainingBalance >= 0) {
                remainingBalanceElement.textContent = '₱' + newRemainingBalance.toFixed(2);
                remainingBalanceElement.classList.remove('text-danger');
                remainingBalanceElement.classList.add('text-success');
            } else {
                remainingBalanceElement.textContent = '₱' + newRemainingBalance.toFixed(2);
                remainingBalanceElement.classList.remove('text-success');
                remainingBalanceElement.classList.add('text-danger');
            }

            if (currentValue > originalRemainingBalance) {
                this.value = originalRemainingBalance.toFixed(2);
                remainingBalanceElement.textContent = '₱0.00';
                remainingBalanceElement.classList.remove('text-danger');
                remainingBalanceElement.classList.add('text-success');
                alert(`Amount cannot exceed remaining balance of ₱${originalRemainingBalance.toFixed(2)}`);
            }
        });
    }

    const fullyPaidBtn = document.getElementById('partialFullyPaidBtn');
    if (fullyPaidBtn) {
        fullyPaidBtn.addEventListener('click', function() {
            const remainingBalanceElement = document.getElementById('summaryRemainingBalance');
            const amountPaidElement = document.getElementById('partialAmountPaid');

            if (!remainingBalanceElement || !amountPaidElement) {
                return;
            }

            const originalRemainingBalance = parseFloat(
                remainingBalanceElement.dataset.originalBalance || remainingBalanceElement.textContent.replace('₱', '')
            );
            if (originalRemainingBalance > 0) {
                amountPaidElement.value = originalRemainingBalance.toFixed(2);
                amountPaidElement.dispatchEvent(new Event('input', { bubbles: true }));
            }
        });
    }

    const modal = document.getElementById('addPaymentToPartialModal');
    if (modal) {
        modal.addEventListener('hidden.bs.modal', function() {
            const form = document.getElementById('addPaymentToPartialForm');
            if (form) {
                form.reset();
            }

            const remainingBalanceElement = document.getElementById('summaryRemainingBalance');
            if (remainingBalanceElement && remainingBalanceElement.dataset.originalBalance) {
                const originalBalance = parseFloat(remainingBalanceElement.dataset.originalBalance);
                remainingBalanceElement.textContent = '₱' + originalBalance.toFixed(2);
                remainingBalanceElement.classList.remove('text-success');
                remainingBalanceElement.classList.add('text-danger');
            }

            currentPartialPaymentData = null;
        });
    }
});

window.openAddPaymentToPartialModal = openAddPaymentToPartialModal;
</script>
