<?php if (!isset($GLOBALS['profile_modal_included'])) { $GLOBALS['profile_modal_included'] = true; ?>
<!-- Profile Modal -->
<div class="modal fade" id="profileModal" tabindex="-1" aria-labelledby="profileModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title" id="profileModalLabel">
          <i class="fas fa-user-circle me-2"></i>
          Edit Profile
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <!-- Profile Picture Section -->
        <div class="text-center mb-4">
          <div class="profile-picture-container position-relative d-inline-block">
            <div id="profilePicturePreview" class="rounded-circle overflow-hidden" style="width: 120px; height: 120px; background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); display: flex; align-items: center; justify-content: center; cursor: pointer; border: 4px solid #e5e7eb;">
              <?php if (session('profile_picture')): ?>
                <?php 
                // Check if it's a Cloudinary URL (full URL) or local path
                $modalProfilePic = session('profile_picture');
                $modalPicUrl = (strpos($modalProfilePic, 'res.cloudinary.com') !== false) 
                    ? $modalProfilePic 
                    : base_url($modalProfilePic);
                ?>
                <img id="profilePictureImg" src="<?= $modalPicUrl ?>" alt="Profile Picture" style="width: 100%; height: 100%; object-fit: cover;">
                <i class="fas fa-user fa-3x text-white" id="profileIcon" style="display: none;"></i>
              <?php else: ?>
                <i class="fas fa-user fa-3x text-white" id="profileIcon"></i>
                <img id="profilePictureImg" src="" alt="Profile Picture" style="width: 100%; height: 100%; object-fit: cover; display: none;">
              <?php endif; ?>
            </div>
            <label for="profilePictureInput" class="btn btn-primary btn-sm position-absolute" style="bottom: 0; right: 0; border-radius: 50%; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; cursor: pointer; border: 3px solid white;">
              <i class="fas fa-camera"></i>
            </label>
            <input type="file" id="profilePictureInput" accept="image/*" style="display: none;">
          </div>
          <p class="text-muted small mt-2 mb-0">Click to upload profile picture</p>
        </div>

        <!-- Profile Form -->
        <form id="profileForm" autocomplete="off">
          <input type="hidden" id="profileUserId" name="user_id" value="<?= session('user-id') ?>">
          
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="profileName" class="form-label">Full Name <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="profileName" name="name" value="<?= esc(session('name') ?? '') ?>" required>
              <div class="invalid-feedback"></div>
            </div>
            
            <div class="col-md-6 mb-3">
              <label for="profileUsername" class="form-label">Username</label>
              <input type="text" class="form-control" id="profileUsername" name="username" value="<?= esc(session('username') ?? '') ?>" readonly>
              <small class="text-muted">Username cannot be changed</small>
            </div>
            
            <div class="col-md-6 mb-3">
              <label for="profileEmail" class="form-label">Email Address <span class="text-danger">*</span></label>
              <input type="email" class="form-control" id="profileEmail" name="email" value="<?= esc(session('email') ?? '') ?>" required>
              <div class="invalid-feedback"></div>
            </div>
            
            <div class="col-md-6 mb-3">
              <label for="profilePhone" class="form-label">Phone Number</label>
              <input type="tel" class="form-control" id="profilePhone" name="phone" value="<?= esc(session('phone') ?? '') ?>">
              <div class="invalid-feedback"></div>
            </div>
          </div>

          <!-- Change Password Section -->
          <div class="mb-3">
            <button type="button" class="btn btn-link p-0 text-decoration-none" data-bs-toggle="collapse" data-bs-target="#changePasswordSection" aria-expanded="false">
              <i class="fas fa-key me-2"></i>
              Change Password
            </button>
          </div>

          <div class="collapse mb-3" id="changePasswordSection">
            <div class="card card-body bg-light">
              <div class="row">
                <div class="col-12 mb-3">
                  <label for="currentPassword" class="form-label">Current Password</label>
                  <div class="input-group">
                    <input type="password" class="form-control" id="currentPassword" name="current_password" placeholder="Enter current password" autocomplete="new-password" value="">
                    <button class="btn btn-outline-secondary toggle-password-btn" type="button" tabindex="-1"><i class="fas fa-eye"></i></button>
                  </div>
                  <div class="invalid-feedback"></div>
                </div>
                
                <div class="col-md-6 mb-3">
                  <label for="newPassword" class="form-label">New Password</label>
                  <div class="input-group">
                    <input type="password" class="form-control" id="newPassword" name="new_password" placeholder="Enter new password" minlength="6" autocomplete="new-password" value="">
                    <button class="btn btn-outline-secondary toggle-password-btn" type="button" tabindex="-1"><i class="fas fa-eye"></i></button>
                  </div>
                  <div class="invalid-feedback"></div>
                </div>
                
                <div class="col-md-6 mb-3">
                  <label for="confirmPassword" class="form-label">Confirm New Password</label>
                  <div class="input-group">
                    <input type="password" class="form-control" id="confirmPassword" name="confirm_password" placeholder="Confirm new password" autocomplete="new-password" value="">
                    <button class="btn btn-outline-secondary toggle-password-btn" type="button" tabindex="-1"><i class="fas fa-eye"></i></button>
                  </div>
                  <div class="invalid-feedback"></div>
                </div>
              </div>
            </div>
          </div>

          <!-- Form Actions -->
          <div class="d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">
              <i class="fas fa-save me-2"></i>
              Save Changes
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Profile picture preview
    const profilePictureInput = document.getElementById('profilePictureInput');
    const profilePictureImg = document.getElementById('profilePictureImg');
    const profileIcon = document.getElementById('profileIcon');
    
    if (profilePictureInput) {
        profilePictureInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    profilePictureImg.src = e.target.result;
                    profilePictureImg.style.display = 'block';
                    profileIcon.style.display = 'none';
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // Profile form submission
    const profileForm = document.getElementById('profileForm');
    const passwordSection = document.getElementById('changePasswordSection');
    const currentPasswordEl = document.getElementById('currentPassword');
    const newPasswordEl = document.getElementById('newPassword');
    const confirmPasswordEl = document.getElementById('confirmPassword');
    
    if (profileForm) {
        // Ensure password fields are cleared and section is collapsed every time modal opens
        const profileModal = document.getElementById('profileModal');
        if (profileModal) {
            profileModal.addEventListener('show.bs.modal', function() {
                if (currentPasswordEl) currentPasswordEl.value = '';
                if (newPasswordEl) newPasswordEl.value = '';
                if (confirmPasswordEl) confirmPasswordEl.value = '';
                if (passwordSection && passwordSection.classList.contains('show')) {
                    const bsCollapse = bootstrap.Collapse.getOrCreateInstance(passwordSection, { toggle: false });
                    bsCollapse.hide();
                }
            });
        }

        profileForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(profileForm);
            const profilePicFile = document.getElementById('profilePictureInput').files[0];
            
            if (profilePicFile) {
                formData.append('profile_picture', profilePicFile);
            }
            
            // Get password fields
            const currentPassword = currentPasswordEl ? currentPasswordEl.value : '';
            const newPassword = newPasswordEl ? newPasswordEl.value : '';
            const confirmPassword = confirmPasswordEl ? confirmPasswordEl.value : '';
            
            // Validate password change if any field is filled
            if (currentPassword || newPassword || confirmPassword) {
                if (!currentPassword) {
                    showNotification('Please enter current password', 'danger');
                    return;
                }
                if (!newPassword || newPassword.length < 6) {
                    showNotification('New password must be at least 6 characters', 'danger');
                    return;
                }
                if (newPassword !== confirmPassword) {
                    showNotification('Passwords do not match', 'danger');
                    return;
                }
                formData.append('change_password', '1');
            }
            
            // Show loading state
            const submitBtn = profileForm.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Saving...';
            
            // Submit form
            fetch('<?= base_url('profile/update') ?>', {
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
                    
                    // Update profile picture if uploaded
                    if (data.profile_picture_url) {
                        const profileIcon = document.getElementById('profileIcon');
                        const profilePictureImg = document.getElementById('profilePictureImg');
                        const headerAvatar = document.querySelector('.header .avatar-circle i');
                        const dropdownAvatar = document.querySelector('.user-dropdown .user-avatar-large i');
                        
                        if (profilePictureImg) {
                            profilePictureImg.src = data.profile_picture_url;
                            profilePictureImg.style.display = 'block';
                            if (profileIcon) profileIcon.style.display = 'none';
                        }
                        
                        // Update header avatar
                        if (headerAvatar) {
                            headerAvatar.style.display = 'none';
                            const headerAvatarImg = headerAvatar.parentElement.querySelector('img');
                            if (!headerAvatarImg) {
                                const img = document.createElement('img');
                                img.src = data.profile_picture_url;
                                img.style.cssText = 'width: 100%; height: 100%; object-fit: cover; border-radius: 50%;';
                                headerAvatar.parentElement.appendChild(img);
                            } else {
                                headerAvatarImg.src = data.profile_picture_url;
                            }
                        }
                        
                        // Update dropdown avatar
                        if (dropdownAvatar) {
                            dropdownAvatar.style.display = 'none';
                            const dropdownAvatarImg = dropdownAvatar.parentElement.querySelector('img');
                            if (!dropdownAvatarImg) {
                                const img = document.createElement('img');
                                img.src = data.profile_picture_url;
                                img.style.cssText = 'width: 100%; height: 100%; object-fit: cover; border-radius: 50%;';
                                dropdownAvatar.parentElement.appendChild(img);
                            } else {
                                dropdownAvatarImg.src = data.profile_picture_url;
                            }
                        }
                    }
                    
                    // Reload page to reflect changes
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    showNotification(data.message || 'Error updating profile', 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Error updating profile', 'danger');
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            });
        });
    }

    document.querySelectorAll('.toggle-password-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const input = btn.parentElement.querySelector('input');
            if (input.type === 'password') {
                input.type = 'text';
                btn.querySelector('i').classList.remove('fa-eye');
                btn.querySelector('i').classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                btn.querySelector('i').classList.remove('fa-eye-slash');
                btn.querySelector('i').classList.add('fa-eye');
            }
        });
    });
});

// Function to open profile modal with current user data
function openProfileModal() {
    const modalElement = document.getElementById('profileModal');
    if (modalElement) {
        // AJAX fetch user (admin) profile info before opening
        fetch('profile/get', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(res => res.json())
            .then(data => {
                if (data && data.success && data.user) {
                    const user = data.user;
                    document.getElementById('profileName').value = user.name || '';
                    document.getElementById('profileUsername').value = user.username || '';
                    document.getElementById('profileEmail').value = user.email || '';
                    document.getElementById('profilePhone').value = user.phone || '';
                    if (user.profile_picture) {
                        document.getElementById('profilePictureImg').src = user.profile_picture;
                        document.getElementById('profilePictureImg').style.display = 'block';
                        document.getElementById('profileIcon').style.display = 'none';
                    }
                }
            });
        const modal = new bootstrap.Modal(modalElement);
        modal.show();
    }
}
</script>

<style>
/* Profile Modal Styling - Must be at highest z-index */
.modal {
    z-index: 1060 !important;
}

.modal-backdrop {
    z-index: 1055 !important;
}

#profileModal .modal-dialog {
    max-width: 600px;
    z-index: 1060 !important;
}

#profileModal .modal-content {
    border-radius: 16px;
    border: 1px solid rgba(226, 232, 240, 0.8);
    box-shadow: 0 20px 60px -10px rgba(0, 0, 0, 0.1);
    max-height: 90vh;
    display: flex;
    flex-direction: column;
    z-index: 1060 !important;
    position: relative;
}

#profileModal .modal-header {
    background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%);
    border-radius: 16px 16px 0 0;
    flex-shrink: 0;
}

#profileModal .modal-body {
    overflow-y: auto;
    max-height: calc(90vh - 120px);
    padding: 1.5rem;
}

#profileModal .profile-picture-container:hover {
    transform: scale(1.05);
    transition: transform 0.3s ease;
}

#profileModal #profilePicturePreview:hover {
    border-color: #3b82f6;
    transition: border-color 0.3s ease;
}

/* Ensure form elements don't overlap */
#profileModal .row {
    margin-left: 0;
    margin-right: 0;
}

#profileModal .col-md-6 {
    padding-left: 0.5rem;
    padding-right: 0.5rem;
}

#profileModal .mb-3 {
    margin-bottom: 1rem !important;
}

/* Password section styling */
#profileModal .collapse {
    margin-top: 1rem;
}

#profileModal .card-body {
    padding: 1rem;
}

/* Button area spacing */
#profileModal .d-flex {
    margin-top: 1.5rem;
    padding-top: 1rem;
    border-top: 1px solid rgba(226, 232, 240, 0.5);
}

/* Custom scrollbar for modal body */
#profileModal .modal-body::-webkit-scrollbar {
    width: 6px;
}

#profileModal .modal-body::-webkit-scrollbar-track {
    background: transparent;
}

#profileModal .modal-body::-webkit-scrollbar-thumb {
    background: rgba(148, 163, 184, 0.3);
    border-radius: 3px;
}

#profileModal .modal-body::-webkit-scrollbar-thumb:hover {
    background: rgba(148, 163, 184, 0.5);
}

</style>
<?php } ?>
