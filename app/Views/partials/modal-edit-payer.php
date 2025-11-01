<!-- Edit Payer Modal -->
<div class="modal fade" id="editPayerModal" tabindex="-1" aria-labelledby="editPayerModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="editPayerModalLabel">
                    <i class="fas fa-user-edit me-2"></i>Edit Payer Information
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editPayerForm" onsubmit="saveEditedPayer(event)">
                <div class="modal-body">
                    <input type="hidden" id="editPayerId" name="payer_id">
                    
                    <div class="mb-3">
                        <label for="editPayerIdField" class="form-label">Student ID <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="editPayerIdField" name="payer_id_field" required readonly>
                        <small class="text-muted">Student ID cannot be changed</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="editPayerName" class="form-label">Full Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="editPayerName" name="payer_name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="editContactNumber" class="form-label">Contact Number</label>
                        <input type="tel" class="form-control" id="editContactNumber" name="contact_number" placeholder="09XX XXX XXXX">
                    </div>
                    
                    <div class="mb-3">
                        <label for="editEmailAddress" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="editEmailAddress" name="email_address" placeholder="example@email.com">
                    </div>
                    <div class="mb-3">
                        <label for="editCourseDepartment" class="form-label">Course/Department</label>
                        <input type="text" class="form-control" id="editCourseDepartment" name="course_department" placeholder="e.g., BS Computer Science, IT Department">
                        <small class="form-text text-muted">Course or department name</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-save me-2"></i>Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
