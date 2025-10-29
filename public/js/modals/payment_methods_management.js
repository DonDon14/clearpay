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
        document.getElementById('editCustomInstructions').value = paymentMethod.custom_instructions || '';
        document.getElementById('editStatus').value = paymentMethod.status || 'active';

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
    
    console.log('Payment Methods Management initialized');
});