// Payment Management JavaScript

document.addEventListener('DOMContentLoaded', function() {
    const paymentForm = document.getElementById('paymentForm');
    const paymentModal = document.getElementById('addPaymentModal');
    
    if (paymentForm) {
        paymentForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            // Show loading state
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
            submitBtn.disabled = true;
            
            // Clear previous error messages
            clearErrorMessages();
            
            fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message
                    showNotification(data.message, 'success');
                    
                    // Show reference number if provided
                    if (data.reference_number) {
                        showNotification(`Reference Number: ${data.reference_number}`, 'info');
                    }
                    
                    // Close modal
                    const modal = bootstrap.Modal.getInstance(paymentModal);
                    if (modal) {
                        modal.hide();
                    }
                    
                    // Reset form
                    paymentForm.reset();
                    
                    // Show QR receipt if payment data is provided
                    if (data.payment) {
                        // Wait for payment modal to close, then show QR receipt
                        setTimeout(() => {
                            if (typeof showQRReceipt === 'function') {
                                showQRReceipt(data.payment);
                            } else {
                                // Reload page if QR receipt function not available
                                window.location.reload();
                            }
                        }, 800);
                    } else if (data.payment_id) {
                        // Fallback: Fetch payment data if not in response
                        const baseUrl = window.APP_BASE_URL || '';
                        fetch(`${baseUrl}/payments/recent`)
                            .then(response => response.json())
                            .then(recentData => {
                                if (recentData.success) {
                                    const payment = recentData.payments.find(p => p.id == data.payment_id);
                                    if (payment && typeof showQRReceipt === 'function') {
                                        setTimeout(() => {
                                            showQRReceipt(payment);
                                        }, 800);
                                    } else {
                                        window.location.reload();
                                    }
                                } else {
                                    window.location.reload();
                                }
                            })
                            .catch(error => {
                                console.error('Error fetching payment data:', error);
                                window.location.reload();
                            });
                    } else {
                        // Reload page to show updated data
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    }
                    
                } else {
                    // Show error message
                    showNotification(data.message || 'An error occurred', 'error');
                    
                    // Display field errors if any
                    if (data.errors) {
                        displayFieldErrors(data.errors);
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('An unexpected error occurred', 'error');
            })
            .finally(() => {
                // Restore button state
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
        });
    }
    
    // Reset form when modal is closed
    if (paymentModal) {
        paymentModal.addEventListener('hidden.bs.modal', function() {
            if (paymentForm) {
                paymentForm.reset();
                clearErrorMessages();
                // Reset remaining balance
                document.getElementById('remainingBalance').value = '0.00';
            }
        });
    }
});

// Function to display field validation errors
function displayFieldErrors(errors) {
    Object.keys(errors).forEach(field => {
        // Map field names to their actual input IDs
        const fieldMap = {
            'payer_name': 'payerName',
            'payer_id': 'payerId',
            'contribution_id': 'contributionId',
            'amount_paid': 'amountPaid',
            'payment_method': 'paymentMethod',
            'is_partial_payment': 'isPartialPayment',
            'payment_date': 'paymentDate',
            'contact_number': 'contactNumber',
            'email_address': 'emailAddress'
        };
        
        const inputId = fieldMap[field] || field;
        const input = document.getElementById(inputId);
        
        if (input) {
            input.classList.add('is-invalid');
            
            // Find or create error message element
            let errorElement = input.parentNode.querySelector('.invalid-feedback');
            if (!errorElement) {
                errorElement = document.createElement('div');
                errorElement.className = 'invalid-feedback';
                input.parentNode.appendChild(errorElement);
            }
            errorElement.textContent = errors[field];
        }
    });
}

// Function to clear error messages
function clearErrorMessages() {
    const form = document.getElementById('paymentForm');
    if (form) {
        // Remove is-invalid class from all inputs
        form.querySelectorAll('.is-invalid').forEach(input => {
            input.classList.remove('is-invalid');
        });
        
        // Remove all error message elements
        form.querySelectorAll('.invalid-feedback').forEach(element => {
            element.remove();
        });
    }
}

// Function to show notifications
function showNotification(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show`;
    toast.style.position = 'fixed';
    toast.style.top = '20px';
    toast.style.right = '20px';
    toast.style.zIndex = '9999';
    toast.style.minWidth = '300px';
    toast.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'} me-2"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        if (toast.parentElement) {
            toast.remove();
        }
    }, 5000);
}