/**
 * Payment Methods Management JavaScript
 * 
 * This file contains all the JavaScript functionality for managing payment methods
 * with custom instructions support.
 * 
 * Dependencies:
 * - Bootstrap 5
 * - FontAwesome icons
 * - showNotification function (global)
 * 
 * Usage:
 * <script src="<?= base_url('js/modals/payment_methods_management.js') ?>"></script>
 */

// Payment Methods Management Class
class PaymentMethodsManager {
    constructor(options = {}) {
        this.baseUrl = options.baseUrl || '';
        this.paymentMethods = options.paymentMethods || [];
        this.init();
    }

    init() {
        this.bindEvents();
    }

    bindEvents() {
        // Add Payment Method Form
        const addForm = document.getElementById('addPaymentMethodForm');
        if (addForm) {
            addForm.addEventListener('submit', (e) => this.handleAddPaymentMethod(e));
        }

        // Edit Payment Method Form
        const editForm = document.getElementById('editPaymentMethodForm');
        if (editForm) {
            editForm.addEventListener('submit', (e) => this.handleEditPaymentMethod(e));
        }

        // Reset forms when modals are closed
        const addModal = document.getElementById('addPaymentMethodModal');
        if (addModal) {
            addModal.addEventListener('hidden.bs.modal', () => this.resetAddForm());
        }

        const editModal = document.getElementById('editPaymentMethodModal');
        if (editModal) {
            editModal.addEventListener('hidden.bs.modal', () => this.resetEditForm());
        }
    }

    // Add Payment Method
    handleAddPaymentMethod(e) {
        e.preventDefault();
        
        const formData = new FormData(e.target);
        
        fetch(`${this.baseUrl}/admin/settings/payment-methods/store`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message, 'success');
                this.refreshPaymentMethodsTable();
                this.closeModal('addPaymentMethodModal');
            } else {
                showNotification(data.message || 'Failed to add payment method', 'error');
                if (data.errors) {
                    this.displayValidationErrors(data.errors, 'add');
                }
            }
        })
        .catch(error => {
            console.error('Error adding payment method:', error);
            showNotification('An error occurred while adding the payment method', 'error');
        });
    }

    // Edit Payment Method
    handleEditPaymentMethod(e) {
        e.preventDefault();
        
        const formData = new FormData(e.target);
        const id = formData.get('id');
        
        fetch(`${this.baseUrl}/admin/settings/payment-methods/update/${id}`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message, 'success');
                this.refreshPaymentMethodsTable();
                this.closeModal('editPaymentMethodModal');
            } else {
                showNotification(data.message || 'Failed to update payment method', 'error');
                if (data.errors) {
                    this.displayValidationErrors(data.errors, 'edit');
                }
            }
        })
        .catch(error => {
            console.error('Error updating payment method:', error);
            showNotification('An error occurred while updating the payment method', 'error');
        });
    }

    // Toggle Payment Method Status
    togglePaymentMethodStatus(id) {
        if (!confirm('Are you sure you want to change the status of this payment method?')) {
            return;
        }

        fetch(`${this.baseUrl}/admin/settings/payment-methods/toggle-status/${id}`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message, 'success');
                this.refreshPaymentMethodsTable();
            } else {
                showNotification(data.message || 'Failed to update payment method status', 'error');
            }
        })
        .catch(error => {
            console.error('Error toggling payment method status:', error);
            showNotification('An error occurred while updating the payment method status', 'error');
        });
    }

    // Delete Payment Method
    deletePaymentMethod(id) {
        if (!confirm('Are you sure you want to delete this payment method? This action cannot be undone.')) {
            return;
        }

        fetch(`${this.baseUrl}/admin/settings/payment-methods/delete/${id}`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message, 'success');
                this.refreshPaymentMethodsTable();
            } else {
                showNotification(data.message || 'Failed to delete payment method', 'error');
            }
        })
        .catch(error => {
            console.error('Error deleting payment method:', error);
            showNotification('An error occurred while deleting the payment method', 'error');
        });
    }

    // Edit Payment Method (populate form)
    editPaymentMethod(id) {
        const paymentMethod = this.paymentMethods.find(method => method.id == id);
        if (!paymentMethod) {
            showNotification('Payment method not found', 'error');
            return;
        }

        // Populate edit form
        document.getElementById('editId').value = paymentMethod.id;
        document.getElementById('editName').value = paymentMethod.name || '';
        document.getElementById('editDescription').value = paymentMethod.description || '';
        document.getElementById('editAccountDetails').value = paymentMethod.account_details || '';
        document.getElementById('editAccountNumber').value = paymentMethod.account_number || '';
        document.getElementById('editAccountName').value = paymentMethod.account_name || '';
        document.getElementById('editReferencePrefix').value = paymentMethod.reference_prefix || 'CP';
        document.getElementById('editStatus').value = paymentMethod.status || 'active';

        // Parse existing custom instructions and populate step-by-step interface
        this.populateInstructionSteps('edit', paymentMethod.custom_instructions || '');

        // Show current QR code if exists
        const currentQrCodeDiv = document.getElementById('currentQrCode');
        if (paymentMethod.qr_code_path) {
            currentQrCodeDiv.innerHTML = `
                <div class="alert alert-info">
                    <strong>Current QR Code:</strong><br>
                    <img src="${this.baseUrl}/${paymentMethod.qr_code_path}" alt="Current QR Code" style="max-width: 100px; max-height: 100px;" class="mt-2">
                </div>
            `;
        } else {
            currentQrCodeDiv.innerHTML = '<div class="alert alert-secondary">No QR code uploaded</div>';
        }

        // Show edit modal
        const editModal = new bootstrap.Modal(document.getElementById('editPaymentMethodModal'));
        editModal.show();
    }

    // Parse existing HTML instructions and populate step-by-step interface
    populateInstructionSteps(mode, htmlInstructions) {
        const prefix = mode === 'add' ? 'add' : 'edit';
        const stepsContainer = document.getElementById(prefix + 'InstructionSteps');
        const additionalInfoField = document.getElementById(prefix + 'AdditionalInfo');
        
        // Clear existing steps
        stepsContainer.innerHTML = '';
        
        if (!htmlInstructions) {
            // Add default step
            this.addInstructionStep(prefix + 'InstructionSteps');
            return;
        }
        
        // Parse HTML to extract steps and additional info
        const parser = new DOMParser();
        const doc = parser.parseFromString(htmlInstructions, 'text/html');
        
        // Extract instruction steps from <li> elements
        const listItems = doc.querySelectorAll('li');
        const steps = Array.from(listItems).map(li => li.textContent.trim()).filter(step => step.length > 0);
        
        // Extract additional info (look for "Additional Notes" section)
        let additionalInfo = '';
        const allDivs = doc.querySelectorAll('div');
        for (let div of allDivs) {
            if (div.textContent.includes('Additional Notes')) {
                const smallElement = div.querySelector('small');
                if (smallElement) {
                    additionalInfo = smallElement.textContent.trim();
                }
                break;
            }
        }
        
        // Populate steps
        if (steps.length > 0) {
            steps.forEach((step, index) => {
                this.addInstructionStep(prefix + 'InstructionSteps');
                const stepInput = stepsContainer.querySelector(`[data-step="${index + 1}"]`);
                if (stepInput) {
                    stepInput.value = step;
                }
            });
        } else {
            // Add default step if no steps found
            this.addInstructionStep(prefix + 'InstructionSteps');
        }
        
        // Populate additional info
        if (additionalInfoField) {
            additionalInfoField.value = additionalInfo;
        }
        
        // Update preview
        this.updatePreview(mode);
    }

    // Add instruction step (called from the modal's JavaScript)
    addInstructionStep(containerId) {
        const container = document.getElementById(containerId);
        const stepCount = container.querySelectorAll('.instruction-step').length + 1;
        
        const stepDiv = document.createElement('div');
        stepDiv.className = 'instruction-step mb-2';
        stepDiv.innerHTML = `
            <div class="input-group">
                <span class="input-group-text">${stepCount}</span>
                <input type="text" class="form-control instruction-step-input" placeholder="Enter instruction step..." data-step="${stepCount}">
                <button type="button" class="btn btn-outline-danger btn-sm remove-step" style="display: none;">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        `;
        container.appendChild(stepDiv);
        
        // Update remove buttons visibility
        this.updateRemoveButtons(containerId);
        
        // Add event listeners
        const removeBtn = stepDiv.querySelector('.remove-step');
        const input = stepDiv.querySelector('.instruction-step-input');
        
        removeBtn.addEventListener('click', () => {
            stepDiv.remove();
            this.updateRemoveButtons(containerId);
            this.updatePreview(containerId.includes('add') ? 'add' : 'edit');
        });
        
        input.addEventListener('input', () => {
            this.updatePreview(containerId.includes('add') ? 'add' : 'edit');
        });
        
        // Update preview
        this.updatePreview(containerId.includes('add') ? 'add' : 'edit');
    }

    // Update remove buttons visibility
    updateRemoveButtons(containerId) {
        const container = document.getElementById(containerId);
        const steps = container.querySelectorAll('.instruction-step');
        const removeButtons = container.querySelectorAll('.remove-step');
        
        // Show remove buttons only if there are multiple steps
        removeButtons.forEach(button => {
            button.style.display = steps.length > 1 ? 'block' : 'none';
        });
        
        // Renumber steps
        steps.forEach((step, index) => {
            const numberSpan = step.querySelector('.input-group-text');
            const input = step.querySelector('.instruction-step-input');
            numberSpan.textContent = index + 1;
            input.setAttribute('data-step', index + 1);
        });
    }

    // Update preview
    updatePreview(mode) {
        const prefix = mode === 'add' ? 'add' : 'edit';
        const previewArea = document.getElementById(prefix + 'PreviewArea');
        const hiddenField = document.getElementById(prefix + 'CustomInstructions');
        
        if (!previewArea || !hiddenField) return;
        
        // Get form data
        const methodName = document.getElementById(prefix + 'Name').value || 'Payment Method';
        const accountNumber = document.getElementById(prefix + 'AccountNumber').value || '';
        const accountName = document.getElementById(prefix + 'AccountName').value || '';
        const additionalInfo = document.getElementById(prefix + 'AdditionalInfo').value || '';
        
        // Get instruction steps
        const stepsContainer = document.getElementById(prefix + 'InstructionSteps');
        const stepInputs = stepsContainer.querySelectorAll('.instruction-step-input');
        const steps = Array.from(stepInputs).map(input => input.value.trim()).filter(step => step.length > 0);
        
        // Generate HTML
        const html = this.generateStandardizedHTML(methodName, accountNumber, accountName, steps, additionalInfo);
        
        // Update preview
        previewArea.innerHTML = html;
        
        // Update hidden field
        hiddenField.value = html;
    }

    // Generate standardized HTML
    generateStandardizedHTML(methodName, accountNumber, accountName, steps, additionalInfo) {
        let html = `
            <div class="alert alert-info">
                <h6><i class="fas fa-qrcode me-2"></i>${methodName} Payment Instructions</h6>
                <p class="mb-2">Please follow these steps to complete your payment:</p>
        `;
        
        // Add QR code section if account details are available
        if (accountNumber || accountName) {
            html += `
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Amount:</strong> ₱{amount}<br>
                        <strong>Payment Type:</strong> ${methodName}<br>
                        <strong>Reference:</strong> {reference_prefix}-{timestamp}
                    </div>
                    <div class="col-md-6">
            `;
            
            if (accountNumber) {
                html += `<strong>Account Number:</strong> ${accountNumber}<br>`;
            }
            if (accountName) {
                html += `<strong>Account Name:</strong> ${accountName}<br>`;
            }
            
            html += `</div></div>`;
        }
        
        // Add instruction steps
        if (steps.length > 0) {
            html += `
                <div class="mb-2">
                    <strong>Instructions:</strong><br>
                    <ul class="mb-0 small">
            `;
            
            steps.forEach(step => {
                html += `<li>${step}</li>`;
            });
            
            html += `</ul></div>`;
        }
        
        // Add additional info
        if (additionalInfo) {
            html += `
                <div class="mt-2">
                    <strong>Additional Notes:</strong><br>
                    <small>${additionalInfo}</small>
                </div>
            `;
        }
        
        html += `</div>`;
        
        return html;
    }

    // Refresh Payment Methods Table
    refreshPaymentMethodsTable() {
        fetch(`${this.baseUrl}/admin/settings/payment-methods/data`, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.paymentMethods = data.data;
                this.renderPaymentMethodsTable(data.data);
            }
        })
        .catch(error => {
            console.error('Error refreshing payment methods:', error);
        });
    }

    // Render Payment Methods Table
    renderPaymentMethodsTable(paymentMethods) {
        const tbody = document.getElementById('paymentMethodsTableBody');
        if (!tbody) return;

        if (paymentMethods.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">
                        <i class="fas fa-credit-card fa-2x mb-2"></i><br>
                        No payment methods found. Add your first payment method to get started.
                    </td>
                </tr>
            `;
            return;
        }

        tbody.innerHTML = paymentMethods.map(method => `
            <tr data-id="${method.id}">
                <td>
                    <div class="d-flex align-items-center">
                        ${method.icon ? `<img src="${this.baseUrl}/${method.icon}" alt="${method.name}" class="me-2" style="width: 24px; height: 24px; object-fit: contain;">` : ''}
                        <strong>${method.name}</strong>
                    </div>
                </td>
                <td>${method.description || ''}</td>
                <td>${method.account_number || 'N/A'}</td>
                <td>${method.account_name || 'N/A'}</td>
                <td>
                    ${method.qr_code_path ? '<span class="badge bg-success">Available</span>' : '<span class="badge bg-secondary">None</span>'}
                </td>
                <td>
                    <span class="badge bg-${method.status === 'active' ? 'success' : 'secondary'}">
                        ${method.status.charAt(0).toUpperCase() + method.status.slice(1)}
                    </span>
                </td>
                <td>
                    <div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-outline-primary" onclick="paymentMethodsManager.editPaymentMethod(${method.id})" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button type="button" class="btn btn-outline-${method.status === 'active' ? 'warning' : 'success'}" onclick="paymentMethodsManager.togglePaymentMethodStatus(${method.id})" title="${method.status === 'active' ? 'Deactivate' : 'Activate'}">
                            <i class="fas fa-${method.status === 'active' ? 'pause' : 'play'}"></i>
                        </button>
                        <button type="button" class="btn btn-outline-danger" onclick="paymentMethodsManager.deletePaymentMethod(${method.id})" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');
    }

    // Display Validation Errors
    displayValidationErrors(errors, formType) {
        const prefix = formType === 'add' ? 'add' : 'edit';
        
        // Clear previous errors
        document.querySelectorAll(`#${prefix}PaymentMethodModal .invalid-feedback`).forEach(el => {
            el.textContent = '';
            el.style.display = 'none';
        });
        document.querySelectorAll(`#${prefix}PaymentMethodModal .form-control, #${prefix}PaymentMethodModal .form-select`).forEach(el => {
            el.classList.remove('is-invalid');
        });

        // Display new errors
        Object.keys(errors).forEach(field => {
            const input = document.getElementById(`${prefix}${field.charAt(0).toUpperCase() + field.slice(1)}`);
            if (input) {
                input.classList.add('is-invalid');
                const feedback = input.nextElementSibling;
                if (feedback && feedback.classList.contains('invalid-feedback')) {
                    feedback.textContent = errors[field];
                    feedback.style.display = 'block';
                }
            }
        });
    }

    // Close Modal
    closeModal(modalId) {
        const modal = bootstrap.Modal.getInstance(document.getElementById(modalId));
        if (modal) {
            modal.hide();
        }
    }

    // Reset Add Form
    resetAddForm() {
        const form = document.getElementById('addPaymentMethodForm');
        if (form) {
            form.reset();
            document.querySelectorAll('#addPaymentMethodModal .invalid-feedback').forEach(el => {
                el.textContent = '';
                el.style.display = 'none';
            });
            document.querySelectorAll('#addPaymentMethodModal .form-control, #addPaymentMethodModal .form-select').forEach(el => {
                el.classList.remove('is-invalid');
            });
            
            // Reset instruction steps
            this.populateInstructionSteps('add', '');
        }
    }

    // Reset Edit Form
    resetEditForm() {
        const form = document.getElementById('editPaymentMethodForm');
        if (form) {
            form.reset();
            document.querySelectorAll('#editPaymentMethodModal .invalid-feedback').forEach(el => {
                el.textContent = '';
                el.style.display = 'none';
            });
            document.querySelectorAll('#editPaymentMethodModal .form-control, #editPaymentMethodModal .form-select').forEach(el => {
                el.classList.remove('is-invalid');
            });
            document.getElementById('currentQrCode').innerHTML = '';
            
            // Reset instruction steps
            this.populateInstructionSteps('edit', '');
        }
    }
}

// Global functions for backward compatibility
function editPaymentMethod(id) {
    if (window.paymentMethodsManager) {
        window.paymentMethodsManager.editPaymentMethod(id);
    }
}

function togglePaymentMethodStatus(id) {
    if (window.paymentMethodsManager) {
        window.paymentMethodsManager.togglePaymentMethodStatus(id);
    }
}

function deletePaymentMethod(id) {
    if (window.paymentMethodsManager) {
        window.paymentMethodsManager.deletePaymentMethod(id);
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Payment Methods Manager
    window.paymentMethodsManager = new PaymentMethodsManager({
        baseUrl: window.baseUrl || '',
        paymentMethods: window.paymentMethodsData || []
    });
    
    // Initialize user-friendly interface
    initializeUserFriendlyInterface();
    
    console.log('Payment Methods Management initialized');
});

// Initialize user-friendly interface
function initializeUserFriendlyInterface() {
    // Add instruction step functionality for Add modal
    const addInstructionStepBtn = document.getElementById('addInstructionStep');
    if (addInstructionStepBtn) {
        addInstructionStepBtn.addEventListener('click', function() {
            if (window.paymentMethodsManager) {
                window.paymentMethodsManager.addInstructionStep('addInstructionSteps');
            }
        });
    }
    
    // Add instruction step functionality for Edit modal
    const editAddInstructionStepBtn = document.getElementById('editAddInstructionStep');
    if (editAddInstructionStepBtn) {
        editAddInstructionStepBtn.addEventListener('click', function() {
            if (window.paymentMethodsManager) {
                window.paymentMethodsManager.addInstructionStep('editInstructionSteps');
            }
        });
    }
    
    // Add event listeners for form field changes
    const formFields = ['Name', 'AccountNumber', 'AccountName', 'AdditionalInfo'];
    formFields.forEach(field => {
        // Add modal
        const addField = document.getElementById('add' + field);
        if (addField) {
            addField.addEventListener('input', () => {
                if (window.paymentMethodsManager) {
                    window.paymentMethodsManager.updatePreview('add');
                }
            });
        }
        
        // Edit modal
        const editField = document.getElementById('edit' + field);
        if (editField) {
            editField.addEventListener('input', () => {
                if (window.paymentMethodsManager) {
                    window.paymentMethodsManager.updatePreview('edit');
                }
            });
        }
    });
    
    // Initialize preview for add modal
    if (window.paymentMethodsManager) {
        window.paymentMethodsManager.updatePreview('add');
    }
}