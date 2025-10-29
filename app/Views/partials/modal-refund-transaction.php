<?php
/**
 * Refund Transaction Modal Partial
 * 
 * This is an independent modal for processing refunds that can be reused across pages.
 * Supports two refund types:
 * - Group Refund: Refund all payments in a sequence
 * - Sequence Refund: Select specific payments from a sequence to refund
 * 
 * Usage:
 * <?= view('partials/modal-refund-transaction', ['refundMethods' => $refundMethods]) ?>
 * 
 * The modal can be opened by triggering: $('#refundTransactionModal').modal('show')
 * 
 * JavaScript functionality is in: public/js/refund-transaction.js
 */

// Ensure refundMethods is defined (default to empty array)
$refundMethods = $refundMethods ?? [];
?>

<!-- Refund Transaction Modal -->
<div class="modal fade" id="refundTransactionModal" tabindex="-1" aria-labelledby="refundTransactionModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="refundTransactionModalLabel">
                    <i class="fas fa-undo me-2"></i>Process Refund Transaction
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-4">
                    <label for="refund_type" class="form-label fw-bold">Refund Type <span class="text-danger">*</span></label>
                    <select class="form-select form-select-lg" id="refund_type" name="refund_type" required>
                        <option value="">-- Select Refund Type --</option>
                        <option value="group">Group Refund (All Payments in Sequence)</option>
                        <option value="sequence">Sequence Refund (Select Specific Payments)</option>
                    </select>
                    <small class="text-muted d-block mt-2">
                        <strong>Group:</strong> Refund full amount of all payments in a payment sequence | 
                        <strong>Sequence:</strong> Select specific payments from a sequence to refund
                    </small>
                </div>

                <div class="row">
                    <!-- Selection Panel -->
                    <div class="col-lg-6">
                        <!-- Group Refund View -->
                        <div id="groupRefundView" class="refund-view" style="display: none;">
                            <h5 class="mb-3">Select Payment Group</h5>
                            <div class="mb-3">
                                <label for="groupSearch" class="form-label">Search Groups</label>
                                <input type="text" class="form-control" id="groupSearch" placeholder="Search by payer name or contribution...">
                            </div>
                            <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                                <table class="table table-hover table-sm">
                                    <thead class="sticky-top bg-light">
                                        <tr>
                                            <th>Payer</th>
                                            <th>Contribution</th>
                                            <th>Sequence</th>
                                            <th>Total</th>
                                            <th>Payments</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="groupsList">
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-4">
                                                <i class="fas fa-spinner fa-spin fa-2x mb-2"></i><br>
                                                Loading payment groups...
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Sequence Refund View -->
                        <div id="sequenceRefundView" class="refund-view" style="display: none;">
                            <h5 class="mb-3">Select Payment Sequence</h5>
                            <div class="mb-3">
                                <label for="sequenceSearch" class="form-label">Search Sequences</label>
                                <input type="text" class="form-control" id="sequenceSearch" placeholder="Search by payer name or contribution...">
                            </div>
                            <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                                <table class="table table-hover table-sm">
                                    <thead class="sticky-top bg-light">
                                        <tr>
                                            <th>Payer</th>
                                            <th>Contribution</th>
                                            <th>Sequence</th>
                                            <th>Total</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="sequencesList">
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-4">
                                                <i class="fas fa-spinner fa-spin fa-2x mb-2"></i><br>
                                                Loading payment sequences...
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Refund Details Panel -->
                    <div class="col-lg-6">
                        <h5 class="mb-3">Refund Details</h5>
                        <form id="processRefundForm">
                            <input type="hidden" id="refund_type_field" name="refund_type">
                            <input type="hidden" id="refund_payment_id" name="payment_id">
                            <input type="hidden" id="refund_payment_ids" name="payment_ids">
                            <input type="hidden" id="refund_payment_sequence" name="payment_sequence">
                            <input type="hidden" id="refund_payer_id" name="payer_id">
                            <input type="hidden" id="refund_contribution_id" name="contribution_id">
                            
                            <div class="mb-3">
                                <label class="form-label">Payment Information</label>
                                <div class="card bg-light p-3" id="paymentInfoDisplay">
                                    <p class="text-muted mb-0">Select a refund type and payment(s) above</p>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="refund_amount" class="form-label">Refund Amount <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">₱</span>
                                    <input type="number" class="form-control" id="refund_amount" name="refund_amount" step="0.01" min="0.01" required readonly>
                                </div>
                                <small class="text-muted">Available for refund: <span id="available_amount">₱0.00</span></small>
                            </div>

                            <div class="mb-3">
                                <label for="refund_method" class="form-label">Refund Method <span class="text-danger">*</span></label>
                                <select class="form-select" id="refund_method" name="refund_method" required>
                                    <?php if (!empty($refundMethods)): ?>
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
                                <label for="refund_reference" class="form-label">Refund Reference Number</label>
                                <input type="text" class="form-control" id="refund_reference" name="refund_reference" placeholder="Optional reference number">
                            </div>

                            <div class="mb-3">
                                <label for="refund_reason" class="form-label">Refund Reason</label>
                                <textarea class="form-control" id="refund_reason" name="refund_reason" rows="3" placeholder="Enter reason for refund"></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="admin_notes" class="form-label">Admin Notes</label>
                                <textarea class="form-control" id="admin_notes" name="admin_notes" rows="2" placeholder="Optional admin notes"></textarea>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-check me-2"></i>Process Refund
                                </button>
                                <button type="reset" class="btn btn-secondary" onclick="$('#refund_type').val('').trigger('change');">
                                    <i class="fas fa-times me-2"></i>Reset
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if (!isset($GLOBALS['refund_transaction_modal_included'])): ?>
    <?php $GLOBALS['refund_transaction_modal_included'] = true; ?>
    <script>
    // Set base URL for refund transaction JavaScript
    if (typeof window.APP_BASE_URL === 'undefined') {
        window.APP_BASE_URL = '<?= base_url() ?>';
    }
    </script>
    <script src="<?= base_url('js/refund-transaction.js') ?>"></script>
<?php endif; ?>

