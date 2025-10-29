<!-- Add Payer Modal -->
<div class="modal fade" id="addPayerModal" tabindex="-1" aria-labelledby="addPayerModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="addPayerModalLabel">
                    <i class="fas fa-user-plus me-2"></i>Add New Payer
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addPayerForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="payer_id" class="form-label">Student ID <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="payer_id" name="payer_id" required 
                                   placeholder="Enter student ID">
                            <button type="button" class="btn btn-outline-primary" onclick="openSchoolIDScanner()" title="Scan School ID">
                                <i class="fas fa-qrcode"></i>
                            </button>
                        </div>
                        <small class="form-text text-muted">Enter student ID or scan QR code</small>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label for="payer_name" class="form-label">Full Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="payer_name" name="payer_name" required 
                               placeholder="Enter full name">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label for="contact_number" class="form-label">Contact Number</label>
                        <input type="tel" class="form-control" id="contact_number" name="contact_number" 
                               placeholder="09123456789" maxlength="11">
                        <small class="form-text text-muted">Must be exactly 11 digits (numbers only)</small>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label for="email_address" class="form-label">Email Address <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="email_address" name="email_address" required
                               placeholder="student@example.com">
                        <small class="form-text text-muted">Required for payer login</small>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label for="course_department" class="form-label">Course/Department</label>
                        <input type="text" class="form-control" id="course_department" name="course_department" 
                               placeholder="e.g., BS Computer Science, IT Department">
                        <small class="form-text text-muted">Course or department name</small>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="savePayerBtn">
                        <i class="fas fa-save me-2"></i>Save Payer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
(function() {
    'use strict';
    
    // Helper function to show notifications (use existing or create simple one)
    function showNotification(message, type) {
        // Check if global showNotification exists
        if (typeof window.showNotification === 'function') {
            window.showNotification(message, type);
            return;
        }
        
        // Fallback: Use Bootstrap alert
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type === 'success' ? 'success' : type === 'error' ? 'danger' : 'info'} alert-dismissible fade show position-fixed top-0 end-0 m-3`;
        alertDiv.style.zIndex = '9999';
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        document.body.appendChild(alertDiv);
        setTimeout(() => {
            alertDiv.remove();
        }, 4000);
    }
    
    // Validate email format
    function validateEmail(email) {
        if (!email || email.trim() === '') {
            return false; // Email is now required
        }
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
    
    // Show field error
    function showFieldError(fieldId, message) {
        const field = document.getElementById(fieldId);
        if (field) {
            field.classList.add('is-invalid');
            const feedback = field.parentNode.querySelector('.invalid-feedback');
            if (feedback) {
                feedback.textContent = message;
            }
        }
    }
    
    // Clear field error
    function clearFieldError(fieldId) {
        const field = document.getElementById(fieldId);
        if (field) {
            field.classList.remove('is-invalid');
            const feedback = field.parentNode.querySelector('.invalid-feedback');
            if (feedback) {
                feedback.textContent = '';
            }
        }
    }
    
    // Clear all errors
    function clearAllErrors() {
        ['payer_id', 'payer_name', 'contact_number', 'email_address'].forEach(id => {
            clearFieldError(id);
        });
    }
    
    // Initialize when DOM is ready
    function initAddPayerModal() {
        const addPayerForm = document.getElementById('addPayerForm');
        const contactNumberField = document.getElementById('contact_number');
        const payerIdField = document.getElementById('payer_id');
        
        if (!addPayerForm) return;
        
        // Initialize phone number field using the helper
        if (contactNumberField && typeof window.initPhoneNumberField === 'function') {
            window.initPhoneNumberField('contact_number', {
                required: false,
                errorMessage: 'Contact number must be exactly 11 digits'
            });
        }
        
        // Email validation on blur and input
        const emailField = document.getElementById('email_address');
        if (emailField) {
            emailField.addEventListener('blur', function(e) {
                const value = e.target.value.trim();
                if (!value) {
                    showFieldError('email_address', 'Email address is required');
                } else if (!validateEmail(value)) {
                    showFieldError('email_address', 'Invalid email address format');
                } else {
                    clearFieldError('email_address');
                }
            });
            
            emailField.addEventListener('input', function(e) {
                // Clear error while typing
                if (e.target.value.trim()) {
                    clearFieldError('email_address');
                }
            });
        }
        
        // Form submission handler
        addPayerForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            // Clear previous errors
            clearAllErrors();
            
            // Get form data
            const formData = new FormData(addPayerForm);
            const payerId = formData.get('payer_id')?.trim() || '';
            const payerName = formData.get('payer_name')?.trim() || '';
            const contactNumber = formData.get('contact_number')?.trim() || '';
            const emailAddress = formData.get('email_address')?.trim() || '';
            
            // Client-side validation
            let isValid = true;
            
            // Validate required fields
            if (!payerId) {
                showFieldError('payer_id', 'Student ID is required');
                isValid = false;
            }
            
            if (!payerName) {
                showFieldError('payer_name', 'Full name is required');
                isValid = false;
            }
            
            // Validate email (required)
            if (!emailAddress) {
                showFieldError('email_address', 'Email address is required');
                isValid = false;
            } else if (!validateEmail(emailAddress)) {
                showFieldError('email_address', 'Invalid email address format');
                isValid = false;
            }
            
            // Validate phone number if provided
            if (contactNumber && typeof window.validatePhoneNumber === 'function' && !window.validatePhoneNumber(contactNumber)) {
                showFieldError('contact_number', 'Contact number must be exactly 11 digits (numbers only)');
                isValid = false;
            }
            
            if (!isValid) {
                showNotification('Please correct the errors in the form', 'error');
                return;
            }
            
            // Sanitize phone number before sending
            const sanitizedFormData = new FormData();
            sanitizedFormData.append('payer_id', payerId);
            sanitizedFormData.append('payer_name', payerName);
            sanitizedFormData.append('email_address', emailAddress); // Email is now required
            if (contactNumber && typeof window.sanitizePhoneNumber === 'function') {
                sanitizedFormData.append('contact_number', window.sanitizePhoneNumber(contactNumber));
            }
            const courseDepartment = formData.get('course_department')?.trim() || '';
            if (courseDepartment) {
                sanitizedFormData.append('course_department', courseDepartment);
            }
            
            // Disable submit button
            const submitBtn = document.getElementById('savePayerBtn');
            const originalBtnText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Saving...';
            
            try {
                const baseUrl = window.APP_BASE_URL || '<?= base_url() ?>';
                const response = await fetch(`${baseUrl}/payers/save`, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: sanitizedFormData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showNotification('Payer added successfully!', 'success');
                    
                    // Reset form
                    addPayerForm.reset();
                    clearAllErrors();
                    
                    // Close modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('addPayerModal'));
                    if (modal) {
                        modal.hide();
                    }
                    
                    // Reload page after short delay to show new payer
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    // Show error message
                    const errorMessage = data.message || 'Error adding payer';
                    showNotification(errorMessage, 'error');
                    
                    // Show field-specific errors if any
                    if (data.errors) {
                        Object.keys(data.errors).forEach(field => {
                            showFieldError(field, data.errors[field]);
                        });
                    } else {
                        // Try to determine which field has the error
                        if (errorMessage.includes('Student ID')) {
                            showFieldError('payer_id', errorMessage);
                        } else if (errorMessage.includes('email')) {
                            showFieldError('email_address', errorMessage);
                        } else if (errorMessage.includes('Contact') || errorMessage.includes('phone')) {
                            showFieldError('contact_number', errorMessage);
                        }
                    }
                }
            } catch (error) {
                console.error('Error:', error);
                showNotification('Error adding payer. Please try again.', 'error');
            } finally {
                // Re-enable submit button
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;
            }
        });
        
        // Clear errors when modal is hidden
        const modal = document.getElementById('addPayerModal');
        if (modal) {
            modal.addEventListener('hidden.bs.modal', function() {
                addPayerForm.reset();
                clearAllErrors();
                
                // Reset button state
                const submitBtn = document.getElementById('savePayerBtn');
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="fas fa-save me-2"></i>Save Payer';
                }
            });
        }
    }
    
    // Function to open school ID scanner (if available)
    window.openSchoolIDScanner = function() {
        if (typeof window.scanSchoolID !== 'undefined') {
            window.scanSchoolID();
        } else {
            // Try to use the ID scanner modal if available
            const idScannerModal = document.getElementById('idScannerModal');
            if (idScannerModal) {
                const modal = new bootstrap.Modal(idScannerModal);
                modal.show();
            } else {
                showNotification('ID Scanner not available. Please enter ID manually.', 'info');
            }
        }
    };
    
    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAddPayerModal);
    } else {
        initAddPayerModal();
    }
})();
</script>
