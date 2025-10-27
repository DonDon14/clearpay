<?= $this->extend('layouts/payer-layout') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <!-- Profile Picture Section -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-camera me-2"></i>Profile Picture</h5>
                </div>
                <div class="card-body text-center">
                    <div class="profile-picture-container">
                        <div class="profile-picture-wrapper" id="profilePictureWrapper">
                            <?php if ($payer && !empty($payer['profile_picture'])): ?>
                                <img src="<?= base_url($payer['profile_picture']) ?>" 
                                     alt="Profile Picture" 
                                     class="profile-picture" 
                                     id="profilePicture">
                            <?php else: ?>
                                <div class="profile-picture-placeholder" id="profilePicturePlaceholder">
                                    <i class="fas fa-user fa-3x text-muted"></i>
                                </div>
                            <?php endif; ?>
                            <div class="profile-picture-overlay" id="profilePictureOverlay">
                                <i class="fas fa-camera"></i>
                                <span>Change Photo</span>
                            </div>
                        </div>
                    </div>
                    <input type="file" 
                           id="profilePictureInput" 
                           accept="image/*" 
                           style="display: none;">
                    <div class="mt-3">
                        <small class="text-muted">Click to upload a new profile picture</small>
                        <br>
                        <small class="text-muted">Max size: 2MB | Formats: JPEG, PNG, GIF</small>
                    </div>
                </div>
            </div>

            <!-- Personal Information Section -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-user-circle me-2"></i>Personal Information</h5>
                    <button class="btn btn-outline-primary btn-sm" id="editBtn">
                        <i class="fas fa-edit me-1"></i>Edit
                    </button>
                </div>
                <div class="card-body">
                    <?php if ($payer): ?>
                        <!-- View Mode -->
                        <div id="viewMode">
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <strong><i class="fas fa-id-card text-primary me-2"></i>Payer ID:</strong>
                                </div>
                                <div class="col-md-8">
                                    <?= esc($payer['payer_id']) ?>
                                </div>
                            </div>
                            <hr>
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <strong><i class="fas fa-user text-primary me-2"></i>Full Name:</strong>
                                </div>
                                <div class="col-md-8">
                                    <?= esc($payer['payer_name']) ?>
                                </div>
                            </div>
                            <hr>
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <strong><i class="fas fa-envelope text-primary me-2"></i>Email Address:</strong>
                                </div>
                                <div class="col-md-8">
                                    <?= esc($payer['email_address']) ?>
                                </div>
                            </div>
                            <hr>
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <strong><i class="fas fa-phone text-primary me-2"></i>Contact Number:</strong>
                                </div>
                                <div class="col-md-8">
                                    <?= esc($payer['contact_number']) ?>
                                </div>
                            </div>
                            <hr>
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <strong><i class="fas fa-calendar text-primary me-2"></i>Member Since:</strong>
                                </div>
                                <div class="col-md-8">
                                    <?= date('M d, Y', strtotime($payer['created_at'])) ?>
                                </div>
                            </div>
                        </div>

                        <!-- Edit Mode -->
                        <div id="editMode" style="display: none;">
                            <form id="profileForm">
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <strong><i class="fas fa-id-card text-primary me-2"></i>Payer ID:</strong>
                                    </div>
                                    <div class="col-md-8">
                                        <input type="text" class="form-control" value="<?= esc($payer['payer_id']) ?>" readonly style="background-color: #f8f9fa; cursor: not-allowed;">
                                        <small class="text-muted">Payer ID cannot be changed</small>
                                    </div>
                                </div>
                                <hr>
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <strong><i class="fas fa-user text-primary me-2"></i>Full Name:</strong>
                                    </div>
                                    <div class="col-md-8">
                                        <input type="text" class="form-control" value="<?= esc($payer['payer_name']) ?>" readonly style="background-color: #f8f9fa; cursor: not-allowed;">
                                        <small class="text-muted">Name cannot be changed</small>
                                    </div>
                                </div>
                                <hr>
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <strong><i class="fas fa-envelope text-primary me-2"></i>Email Address:</strong>
                                    </div>
                                    <div class="col-md-8">
                                        <input type="email" 
                                               class="form-control" 
                                               id="emailAddress" 
                                               name="email_address" 
                                               value="<?= esc($payer['email_address']) ?>" 
                                               required>
                                        <div class="invalid-feedback" id="emailError"></div>
                                    </div>
                                </div>
                                <hr>
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <strong><i class="fas fa-phone text-primary me-2"></i>Contact Number:</strong>
                                    </div>
                                    <div class="col-md-8">
                                        <input type="tel" 
                                               class="form-control" 
                                               id="contactNumber" 
                                               name="contact_number" 
                                               value="<?= esc($payer['contact_number']) ?>" 
                                               required>
                                        <div class="invalid-feedback" id="contactError"></div>
                                    </div>
                                </div>
                                <hr>
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <strong><i class="fas fa-calendar text-primary me-2"></i>Member Since:</strong>
                                    </div>
                                    <div class="col-md-8">
                                        <input type="text" class="form-control" value="<?= date('M d, Y', strtotime($payer['created_at'])) ?>" readonly style="background-color: #f8f9fa; cursor: not-allowed;">
                                    </div>
                                </div>
                                
                                <div class="d-flex justify-content-end gap-2">
                                    <button type="button" class="btn btn-secondary" id="cancelBtn">
                                        <i class="fas fa-times me-1"></i>Cancel
                                    </button>
                                    <button type="submit" class="btn btn-primary" id="saveBtn">
                                        <i class="fas fa-save me-1"></i>Save Changes
                                    </button>
                                </div>
                            </form>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>No information available.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.profile-picture-container {
    display: inline-block;
    position: relative;
}

.profile-picture-wrapper {
    position: relative;
    width: 150px;
    height: 150px;
    border-radius: 50%;
    overflow: hidden;
    cursor: pointer;
    border: 4px solid #e9ecef;
    transition: all 0.3s ease;
}

.profile-picture-wrapper:hover {
    border-color: #3b82f6;
    transform: scale(1.05);
}

.profile-picture {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.profile-picture-placeholder {
    width: 100%;
    height: 100%;
    background: #f8f9fa;
    display: flex;
    align-items: center;
    justify-content: center;
}

.profile-picture-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.7);
    color: white;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.profile-picture-wrapper:hover .profile-picture-overlay {
    opacity: 1;
}

.profile-picture-overlay i {
    font-size: 1.5rem;
    margin-bottom: 0.25rem;
}

.profile-picture-overlay span {
    font-size: 0.875rem;
    font-weight: 500;
}

.form-control:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 0 0.2rem rgba(59, 130, 246, 0.25);
}

.btn {
    transition: all 0.2s ease;
}

.btn:hover {
    transform: translateY(-1px);
}

/* Loading states */
.btn.loading {
    position: relative;
    color: transparent;
}

.btn.loading::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 16px;
    height: 16px;
    border: 2px solid transparent;
    border-top: 2px solid currentColor;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: translate(-50%, -50%) rotate(0deg); }
    100% { transform: translate(-50%, -50%) rotate(360deg); }
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .profile-picture-wrapper {
        width: 120px;
        height: 120px;
    }
    
    .d-flex.gap-2 {
        flex-direction: column;
    }
    
    .d-flex.gap-2 .btn {
        width: 100%;
        margin-bottom: 0.5rem;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const editBtn = document.getElementById('editBtn');
    const cancelBtn = document.getElementById('cancelBtn');
    const saveBtn = document.getElementById('saveBtn');
    const profileForm = document.getElementById('profileForm');
    const viewMode = document.getElementById('viewMode');
    const editMode = document.getElementById('editMode');
    const profilePictureWrapper = document.getElementById('profilePictureWrapper');
    const profilePictureInput = document.getElementById('profilePictureInput');
    const profilePicture = document.getElementById('profilePicture');
    const profilePicturePlaceholder = document.getElementById('profilePicturePlaceholder');

    // Edit mode toggle
    editBtn.addEventListener('click', function() {
        viewMode.style.display = 'none';
        editMode.style.display = 'block';
        editBtn.style.display = 'none';
    });

    cancelBtn.addEventListener('click', function() {
        editMode.style.display = 'none';
        viewMode.style.display = 'block';
        editBtn.style.display = 'block';
        
        // Reset form to original values
        document.getElementById('emailAddress').value = '<?= esc($payer['email_address'] ?? '') ?>';
        document.getElementById('contactNumber').value = '<?= esc($payer['contact_number'] ?? '') ?>';
        
        // Clear validation errors
        clearValidationErrors();
    });

    // Profile picture upload
    profilePictureWrapper.addEventListener('click', function() {
        profilePictureInput.click();
    });

    profilePictureInput.addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            // Validate file type
            const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
            if (!allowedTypes.includes(file.type)) {
                showNotification('Invalid file type. Only JPEG, PNG, and GIF are allowed.', 'error');
                return;
            }

            // Validate file size (max 2MB)
            if (file.size > 2 * 1024 * 1024) {
                showNotification('File size too large. Maximum 2MB allowed.', 'error');
                return;
            }

            // Show preview
            const reader = new FileReader();
            reader.onload = function(e) {
                if (profilePicture) {
                    profilePicture.src = e.target.result;
                } else {
                    profilePicturePlaceholder.innerHTML = `<img src="${e.target.result}" class="profile-picture" alt="Profile Picture">`;
                }
            };
            reader.readAsDataURL(file);

            // Upload file
            uploadProfilePicture(file);
        }
    });

    // Form submission
    profileForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData();
        formData.append('email_address', document.getElementById('emailAddress').value);
        formData.append('contact_number', document.getElementById('contactNumber').value);

        // Show loading state
        saveBtn.classList.add('loading');
        saveBtn.disabled = true;

        fetch('<?= base_url('payer/update-profile') ?>', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message, 'success');
                
                // Switch back to view mode
                editMode.style.display = 'none';
                viewMode.style.display = 'block';
                editBtn.style.display = 'block';
                
                // Update the displayed values
                updateDisplayedValues();
            } else {
                showNotification(data.message, 'error');
                
                // Show validation errors
                if (data.errors) {
                    showValidationErrors(data.errors);
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('An error occurred while updating profile.', 'error');
        })
        .finally(() => {
            saveBtn.classList.remove('loading');
            saveBtn.disabled = false;
        });
    });

    function uploadProfilePicture(file) {
        const formData = new FormData();
        formData.append('profile_picture', file);

        fetch('<?= base_url('payer/upload-profile-picture') ?>', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message, 'success');
                
                // Update header profile pictures
                updateHeaderProfilePicture(data.profile_picture);
            } else {
                showNotification(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('An error occurred while uploading profile picture.', 'error');
        });
    }

    function updateHeaderProfilePicture(profilePictureUrl) {
        // Update header avatar
        const headerAvatar = document.querySelector('.avatar-circle img');
        if (headerAvatar) {
            headerAvatar.src = profilePictureUrl;
        } else {
            // If no img exists, create one
            const avatarCircle = document.querySelector('.avatar-circle');
            if (avatarCircle) {
                avatarCircle.innerHTML = `<img src="${profilePictureUrl}" alt="Profile Picture" class="avatar-image">`;
            }
        }

        // Update dropdown avatar
        const dropdownAvatar = document.querySelector('.user-avatar-large img');
        if (dropdownAvatar) {
            dropdownAvatar.src = profilePictureUrl;
        } else {
            // If no img exists, create one
            const dropdownAvatarLarge = document.querySelector('.user-avatar-large');
            if (dropdownAvatarLarge) {
                dropdownAvatarLarge.innerHTML = `<img src="${profilePictureUrl}" alt="Profile Picture" class="avatar-image-large">`;
            }
        }
    }

    function updateDisplayedValues() {
        // This would typically involve refreshing the page or updating the DOM
        // For now, we'll just refresh the page to show updated values
        setTimeout(() => {
            location.reload();
        }, 1000);
    }

    function showValidationErrors(errors) {
        clearValidationErrors();
        
        if (errors.email_address) {
            document.getElementById('emailAddress').classList.add('is-invalid');
            document.getElementById('emailError').textContent = errors.email_address;
        }
        
        if (errors.contact_number) {
            document.getElementById('contactNumber').classList.add('is-invalid');
            document.getElementById('contactError').textContent = errors.contact_number;
        }
    }

    function clearValidationErrors() {
        document.getElementById('emailAddress').classList.remove('is-invalid');
        document.getElementById('contactNumber').classList.remove('is-invalid');
        document.getElementById('emailError').textContent = '';
        document.getElementById('contactError').textContent = '';
    }

    function showNotification(message, type) {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `alert alert-${type === 'error' ? 'danger' : 'success'} alert-dismissible fade show position-fixed`;
        notification.style.cssText = 'top: 20px; right: 20px; z-index: 10000; min-width: 300px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);';
        notification.innerHTML = `
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        
        // Append to body
        document.body.appendChild(notification);
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            notification.remove();
        }, 5000);
    }
});
</script>
<?= $this->endSection() ?>
