<!-- Contribution Modal -->
<div class="modal fade" id="contributionModal" tabindex="-1" aria-labelledby="contributionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="contributionForm" method="post" action="<?= isset($action) ? $action : '' ?>">
                <div class="modal-header">
                    <h5 class="modal-title" id="contributionModalLabel"><?= isset($title) ? $title : 'Add Contribution' ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <input type="hidden" name="id" id="contributionEditId" value="<?= isset($contribution['id']) ? $contribution['id'] : '' ?>">

                    <div class="mb-3">
                        <label for="contributionTitle" class="form-label">Title</label>
                        <input type="text" class="form-control" id="contributionTitle" name="title" value="<?= isset($contribution['title']) ? $contribution['title'] : '' ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="contributionCode" class="form-label">Contribution Code</label>
                        <input type="text" class="form-control" id="contributionCode" name="contribution_code" value="<?= isset($contribution['contribution_code']) ? $contribution['contribution_code'] : '' ?>" placeholder="Enter reference code for campus papers (e.g., CC-2025-001)">
                        <div class="form-text">Reference code for real papers that the campus needs</div>
                    </div>

                    <div class="mb-3">
                        <label for="contributionDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="contributionDescription" name="description" rows="3"><?= isset($contribution['description']) ? $contribution['description'] : '' ?></textarea>
                    </div>

                    <div class="row">
                        <div class="mb-3 col-md-6">
                            <label for="contributionGrandTotal" class="form-label">Grand Total <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" class="form-control" id="contributionGrandTotal" name="grand_total" value="<?= isset($contribution['grand_total']) ? $contribution['grand_total'] : '' ?>" required>
                            <div class="form-text">Total target amount to be collected from all payers</div>
                        </div>
                        <div class="mb-3 col-md-6">
                            <label for="contributionNumPayers" class="form-label">Number of Payers <span class="text-danger">*</span></label>
                            <input type="number" step="1" min="1" class="form-control" id="contributionNumPayers" name="number_of_payers" value="<?= isset($contribution['number_of_payers']) ? $contribution['number_of_payers'] : '' ?>" required>
                            <div class="form-text">Total number of payers for this contribution</div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="mb-3 col-md-6">
                            <label for="contributionAmount" class="form-label">Amount <span class="text-muted">(Per Payer)</span></label>
                            <input type="number" step="0.01" class="form-control" id="contributionAmount" name="amount" value="<?= isset($contribution['amount']) ? $contribution['amount'] : '0.00' ?>" readonly>
                            <div class="form-text">Auto-calculated: Grand Total ÷ Number of Payers</div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="mb-3 col-md-6">
                            <label for="contributionCostPrice" class="form-label">Cost Price</label>
                            <input type="number" step="0.01" class="form-control" id="contributionCostPrice" name="cost_price" value="<?= isset($contribution['cost_price']) ? $contribution['cost_price'] : '0.00' ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="contributionCategory" class="form-label">Category</label>
                        <select class="form-select" id="contributionCategory" name="category">
                            <option value="">-- Select Category --</option>
                            <?php 
                            $categories = $categories ?? [];
                            foreach ($categories as $category): 
                            ?>
                                <option value="<?= $category['code'] ?>" <?= (isset($contribution['category']) && $contribution['category'] === $category['code']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($category['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="contributionStatus" class="form-label">Status</label>
                        <select class="form-select" id="contributionStatus" name="status">
                            <option value="active" <?= (isset($contribution['status']) && $contribution['status'] === 'active') ? 'selected' : '' ?>>Active</option>
                            <option value="inactive" <?= (isset($contribution['status']) && $contribution['status'] === 'inactive') ? 'selected' : '' ?>>Inactive</option>
                        </select>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><?= isset($contribution['id']) ? 'Update' : 'Save' ?></button>
                </div>
            </form>
        </div>
    </div>
</div>
