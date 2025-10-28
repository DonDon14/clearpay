/**
 * Payment Methods Management JavaScript
 * 
 * This file contains all the JavaScript functionality for managing payment methods.
 * It should be included after the payment methods modal partial.
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
        .then(result => {
            if (result.success) {
                this.showNotification('Payment method created successfully', 'success');
                // Close only the Add Payment Method modal
                bootstrap.Modal.getInstance(document.getElementById('addPaymentMethodModal')).hide();
                // Reset the form
                e.target.reset();
                document.getElementById('iconPreview').style.display = 'none';
                // Refresh the payment methods list without closing the main modal
                this.refreshPaymentMethodsList();
            } else {
                this.showNotification(result.message || 'Failed to create payment method', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            this.showNotification('An error occurred while creating payment method', 'error');
        });
    }

    // Edit Payment Method
    editPaymentMethod(id) {
        const method = this.paymentMethods.find(m => m.id == id);
        if (method) {
            document.getElementById('editPaymentMethodId').value = method.id;
            document.getElementById('editPaymentMethodName').value = method.name;
            document.getElementById('editPaymentMethodDescription').value = method.description || '';
            document.getElementById('editPaymentMethodAccountDetails').value = method.account_details || '';
            document.getElementById('editPaymentMethodStatus').value = method.status;
            
            // Update icon preview
            const iconPreviewImg = document.getElementById('editIconPreviewImg');
            if (method.icon) {
                iconPreviewImg.src = this.baseUrl + method.icon;
                iconPreviewImg.style.display = 'block';
            } else {
                iconPreviewImg.style.display = 'none';
            }
            
            new bootstrap.Modal(document.getElementById('editPaymentMethodModal')).show();
        }
    }

    // Update Payment Method
    handleEditPaymentMethod(e) {
        e.preventDefault();
        
        const formData = new FormData(e.target);
        const id = document.getElementById('editPaymentMethodId').value;
        
        fetch(`${this.baseUrl}/admin/settings/payment-methods/update/${id}`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                this.showNotification('Payment method updated successfully', 'success');
                // Close only the Edit Payment Method modal
                bootstrap.Modal.getInstance(document.getElementById('editPaymentMethodModal')).hide();
                // Refresh the payment methods list without closing the main modal
                this.refreshPaymentMethodsList();
            } else {
                this.showNotification(result.message || 'Failed to update payment method', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            this.showNotification('An error occurred while updating payment method', 'error');
        });
    }

    // Toggle Payment Method Status
    togglePaymentMethodStatus(id, currentStatus) {
        const newStatus = currentStatus === 'active' ? 'inactive' : 'active';
        const action = newStatus === 'active' ? 'activate' : 'deactivate';
        
        if (confirm(`Are you sure you want to ${action} this payment method?`)) {
            fetch(`${this.baseUrl}/admin/settings/payment-methods/toggle-status/${id}`, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    this.showNotification(`Payment method ${action}d successfully`, 'success');
                    // Refresh the payment methods list without closing the main modal
                    this.refreshPaymentMethodsList();
                } else {
                    this.showNotification(result.message || `Failed to ${action} payment method`, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                this.showNotification(`An error occurred while ${action}ing payment method`, 'error');
            });
        }
    }

    // Delete Payment Method
    deletePaymentMethod(id, name) {
        if (confirm(`Are you sure you want to delete "${name}"? This action cannot be undone.`)) {
            fetch(`${this.baseUrl}/admin/settings/payment-methods/delete/${id}`, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    this.showNotification('Payment method deleted successfully', 'success');
                    // Refresh the payment methods list without closing the main modal
                    this.refreshPaymentMethodsList();
                } else {
                    this.showNotification(result.message || 'Failed to delete payment method', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                this.showNotification('An error occurred while deleting payment method', 'error');
            });
        }
    }

    // Refresh Payment Methods List
    refreshPaymentMethodsList() {
        fetch(`${this.baseUrl}/admin/settings/payment-methods/data`, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                // Update the paymentMethods array
                this.paymentMethods = result.data;
                
                // Update the table body
                this.updatePaymentMethodsTable(result.data);
            } else {
                console.error('Failed to refresh payment methods:', result.message);
                // Fallback to page reload if AJAX fails
                location.reload();
            }
        })
        .catch(error => {
            console.error('Error refreshing payment methods list:', error);
            // Fallback to page reload if AJAX fails
            location.reload();
        });
    }

    // Update Payment Methods Table
    updatePaymentMethodsTable(methods) {
        const tbody = document.querySelector('#paymentMethodsModal tbody');
        if (!tbody) return;
        
        if (methods.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="5" class="text-center py-4">
                        <i class="fas fa-credit-card fa-2x text-muted mb-2"></i>
                        <p class="text-muted mb-0">No payment methods found</p>
                        <small class="text-muted">Add your first payment method to get started</small>
                    </td>
                </tr>
            `;
            return;
        }
        
        let html = '';
        methods.forEach(method => {
            const statusBadge = method.status === 'active' 
                ? '<span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>Active</span>'
                : '<span class="badge bg-secondary"><i class="fas fa-times-circle me-1"></i>Inactive</span>';
            
            const statusAction = method.status === 'active' ? 'pause' : 'play';
            const statusColor = method.status === 'active' ? 'warning' : 'success';
            
            const iconHtml = method.icon && method.icon.trim() !== '' 
                ? `<img src="${this.baseUrl + method.icon}" alt="${method.name} Icon" style="width: 32px; height: 32px; object-fit: cover; border-radius: 4px;" class="me-2">`
                : `<div class="payment-method-placeholder me-2" style="width: 32px; height: 32px; border-radius: 4px; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 10px;" data-method="${method.name}">${method.name.substring(0, 2).toUpperCase()}</div>`;
            
            html += `
                <tr>
                    <td>
                        <div class="d-flex align-items-center">
                            ${iconHtml}
                            <strong>${method.name}</strong>
                        </div>
                    </td>
                    <td>
                        ${method.description ? `<span class="text-muted">${method.description}</span>` : '<span class="text-muted fst-italic">No description</span>'}
                    </td>
                    <td>
                        ${method.account_details ? `<span class="text-muted">${method.account_details}</span>` : '<span class="text-muted fst-italic">No details</span>'}
                    </td>
                    <td>
                        ${statusBadge}
                    </td>
                    <td>
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="paymentMethodsManager.editPaymentMethod(${method.id})">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-${statusColor}" onclick="paymentMethodsManager.togglePaymentMethodStatus(${method.id}, '${method.status}')">
                                <i class="fas fa-${statusAction}"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="paymentMethodsManager.deletePaymentMethod(${method.id}, '${method.name}')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        });
        
        tbody.innerHTML = html;
    }

    // File Preview Functions
    previewIcon(input) {
        const preview = document.getElementById('iconPreview');
        const previewImg = document.getElementById('iconPreviewImg');
        
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                preview.style.display = 'block';
            };
            reader.readAsDataURL(input.files[0]);
        } else {
            preview.style.display = 'none';
        }
    }

    previewEditIcon(input) {
        const previewImg = document.getElementById('editIconPreviewImg');
        
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                previewImg.style.display = 'block';
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    // Notification helper
    showNotification(message, type) {
        if (typeof showNotification === 'function') {
            showNotification(message, type);
        } else {
            // Fallback notification
            console.log(`${type.toUpperCase()}: ${message}`);
        }
    }
}

// Global functions for backward compatibility
function editPaymentMethod(id) {
    if (window.paymentMethodsManager) {
        window.paymentMethodsManager.editPaymentMethod(id);
    }
}

function togglePaymentMethodStatus(id, currentStatus) {
    if (window.paymentMethodsManager) {
        window.paymentMethodsManager.togglePaymentMethodStatus(id, currentStatus);
    }
}

function deletePaymentMethod(id, name) {
    if (window.paymentMethodsManager) {
        window.paymentMethodsManager.deletePaymentMethod(id, name);
    }
}

function previewIcon(input) {
    if (window.paymentMethodsManager) {
        window.paymentMethodsManager.previewIcon(input);
    }
}

function previewEditIcon(input) {
    if (window.paymentMethodsManager) {
        window.paymentMethodsManager.previewEditIcon(input);
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Get payment methods data from the page
    const paymentMethodsData = window.paymentMethodsData || [];
    
    // Initialize the payment methods manager
    window.paymentMethodsManager = new PaymentMethodsManager({
        baseUrl: window.baseUrl || '',
        paymentMethods: paymentMethodsData
    });
});
