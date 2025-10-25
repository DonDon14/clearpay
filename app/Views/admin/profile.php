<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
// Dummy data for UI development - replace with actual controller data later
$profile_picture = session()->get('profile_picture') ?? '';
$phone = '+63 915 123 4567';

// User activity dummy data
$recentActivities = [
    [
        'id' => 1,
        'action' => 'Successful Login',
        'description' => 'Logged in from Windows PC',
        'time' => '2 hours ago',
        'icon' => 'sign-in-alt',
        'type' => 'success'
    ],
    [
        'id' => 2,
        'action' => 'Profile Updated',
        'description' => 'Changed phone number',
        'time' => '1 day ago',
        'icon' => 'user-edit',
        'type' => 'primary'
    ],
    [
        'id' => 3,
        'action' => 'Password Changed',
        'description' => 'Security password updated',
        'time' => '2 weeks ago',
        'icon' => 'key',
        'type' => 'warning'
    ],
    [
        'id' => 4,
        'action' => 'Payment Processed',
        'description' => 'Processed payment for Maria Santos',
        'time' => '3 weeks ago',
        'icon' => 'credit-card',
        'type' => 'info'
    ]
];

// Profile stats dummy data
$profileStats = [
    'total_payments' => 45,
    'total_amount' => 125430,
    'active_contributors' => 12,
    'payments_growth' => 12,
    'amount_growth' => 8,
    'contributors_growth' => 3
];
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">Profile Settings</h1>
                    <p class="mb-0 text-muted">Manage your profile information and account settings</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-lg-4 col-md-6 mb-4">
            <?= view('partials/card', [
                'title' => 'Total Payments',
                'text' => number_format($profileStats['total_payments']),
                'icon' => 'credit-card',
                'iconColor' => 'text-primary'
            ]) ?>
        </div>
        <div class="col-lg-4 col-md-6 mb-4">
            <?= view('partials/card', [
                'title' => 'Total Amount',
                'text' => '₱' . number_format($profileStats['total_amount']),
                'icon' => 'peso-sign',
                'iconColor' => 'text-success'
            ]) ?>
        </div>
        <div class="col-lg-4 col-md-6 mb-4">
            <?= view('partials/card', [
                'title' => 'Active Contributors',
                'text' => number_format($profileStats['active_contributors']),
                'icon' => 'users',
                'iconColor' => 'text-info'
            ]) ?>
        </div>
    </div>

    <!-- Profile Overview Section -->
    <?= view('partials/container-card', [
        'title' => 'Profile Overview',
        'subtitle' => 'Your account information',
        'content' => '
            <div class="d-flex align-items-center gap-4 p-3 bg-light rounded">
                <div class="profile-avatar-container" style="position: relative;">
                    <div class="profile-avatar" style="width: 80px; height: 80px; background: linear-gradient(135deg, #007bff, #28a745); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2rem; cursor: pointer;" onclick="openProfilePictureModal()" title="Click to change profile picture">
                        ' . (!empty($profile_picture) ? 
                            '<img src="' . esc($profile_picture) . '" alt="Profile Picture" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">' : 
                            '<i class="fas fa-user"></i>'
                        ) . '
                    </div>
                </div>
                <div class="profile-details">
                    <h2 class="h4 mb-1">' . esc(session()->get('name') ?? 'Admin User') . '</h2>
                    <p class="text-primary fw-medium mb-1">System Administrator</p>
                    <p class="text-muted mb-0">' . esc(session()->get('email') ?? 'admin@example.com') . '</p>
                </div>
            </div>
        '
    ]) ?>

    <!-- Settings Cards Grid -->
    <div class="row">
        <!-- Personal Information Card -->
        <div class="col-lg-6 mb-4">
            <?= view('partials/container-card', [
                'title' => 'Personal Information',
                'subtitle' => 'Update your personal details',
                'content' => '
                    <!-- Profile Picture Section -->
                    <div class="row align-items-center mb-4 p-3 bg-light rounded">
                        <div class="col-auto">
                            <div class="profile-picture-container" style="position: relative;">
                                <div class="profile-picture-preview" style="width: 120px; height: 120px; border-radius: 50%; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.1);" id="profilePicturePreview">
                                    ' . (!empty($profile_picture) ? 
                                        '<img src="' . esc($profile_picture) . '" alt="Profile Picture" id="profileImage" style="width: 100%; height: 100%; object-fit: cover;">' : 
                                        '<div class="profile-placeholder d-flex align-items-center justify-content-center h-100" style="background: linear-gradient(135deg, #007bff, #6f42c1); color: white;">
                                            <i class="fas fa-user fa-3x"></i>
                                        </div>'
                                    ) . '
                                </div>
                                <input type="file" id="profilePictureInput" accept="image/*" style="display: none;" onchange="handleProfilePictureChange(event)">
                                <div id="profilePictureUploadBtn" style="display: none; margin-top: 0.5rem;">
                                    <button type="button" class="btn btn-primary btn-sm" onclick="triggerFileInput()">
                                        <i class="fas fa-camera"></i> Change Photo
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col">
                            <h5>' . esc(session()->get('name') ?? 'Admin User') . '</h5>
                            <p class="text-muted mb-1">' . esc(session()->get('email') ?? 'admin@clearpay.com') . '</p>
                            <small class="text-secondary">JPG, PNG or GIF. Max file size 2MB.</small>
                        </div>
                    </div>
                    
                    <form id="personalInfoForm">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Full Name</label>
                                <input type="text" class="form-control" id="fullName" name="full_name" value="' . esc(session()->get('name') ?? 'John Doe') . '" readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" name="username" value="' . esc(session()->get('username') ?? 'johndoe') . '" readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email" value="' . esc(session()->get('email') ?? 'john@example.com') . '" readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" id="phone" name="phone" value="' . esc($phone) . '" readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Department</label>
                                <input type="text" class="form-control" id="department" name="department" value="Finance & Administration" readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Position</label>
                                <input type="text" class="form-control" id="position" name="position" value="System Administrator" readonly>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-end gap-2" id="personalActions" style="display: none !important;">
                            <button type="button" class="btn btn-secondary btn-sm" onclick="cancelEdit(\'personal\')">Cancel</button>
                            <button type="submit" class="btn btn-primary btn-sm">Save Changes</button>
                        </div>
                    </form>
                    
                    <div class="d-flex justify-content-end mt-3">
                        <button class="btn btn-outline-primary btn-sm" onclick="toggleEditMode(\'personal\')">
                            <i class="fas fa-edit"></i> Edit Information
                        </button>
                    </div>
                '
            ]) ?>
        </div>

        <!-- Security Settings Card -->
        <div class="col-lg-6 mb-4">
            <?= view('partials/container-card', [
                'title' => 'Security Settings',
                'subtitle' => 'Update your password and security preferences',
                'content' => '
                    <form id="securityForm">
                        <div class="mb-3">
                            <label class="form-label">Current Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="currentPassword" name="current_password" placeholder="Enter current password" readonly>
                                <button type="button" class="btn btn-outline-secondary" onclick="togglePassword(\'currentPassword\')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">New Password</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="newPassword" name="new_password" placeholder="Enter new password" readonly>
                                    <button type="button" class="btn btn-outline-secondary" onclick="togglePassword(\'newPassword\')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Confirm Password</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="confirmPassword" name="confirm_password" placeholder="Confirm new password" readonly>
                                    <button type="button" class="btn btn-outline-secondary" onclick="togglePassword(\'confirmPassword\')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="border-top pt-3 mt-3">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <i class="fas fa-check-circle text-success"></i>
                                <span class="small text-muted">Last password change: 2 weeks ago</span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <i class="fas fa-clock text-info"></i>
                                <span class="small text-muted">Last login: Today at 9:30 AM</span>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-end gap-2 mt-3" id="securityActions" style="display: none !important;">
                            <button type="button" class="btn btn-secondary btn-sm" onclick="cancelEdit(\'security\')">Cancel</button>
                            <button type="submit" class="btn btn-primary btn-sm">Update Password</button>
                        </div>
                    </form>
                    
                    <div class="d-flex justify-content-end mt-3">
                        <button class="btn btn-outline-primary btn-sm" onclick="toggleEditMode(\'security\')">
                            <i class="fas fa-key"></i> Change Password
                        </button>
                    </div>
                '
            ]) ?>
        </div>

        <!-- Preferences Card -->
        <div class="col-lg-6 mb-4">
            <?= view('partials/container-card', [
                'title' => 'Account Preferences',
                'subtitle' => 'Customize your account settings',
                'content' => '
                    <div class="preference-list">
                        <div class="d-flex justify-content-between align-items-center py-3 border-bottom">
                            <div>
                                <h6 class="mb-1"><i class="fas fa-moon me-2"></i>Dark Mode</h6>
                                <small class="text-muted">Enable dark theme for better night viewing</small>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="darkMode">
                                <label class="form-check-label" for="darkMode"></label>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center py-3 border-bottom">
                            <div>
                                <h6 class="mb-1"><i class="fas fa-bell me-2"></i>Email Notifications</h6>
                                <small class="text-muted">Receive email alerts for important events</small>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="emailNotifications" checked>
                                <label class="form-check-label" for="emailNotifications"></label>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center py-3 border-bottom">
                            <div>
                                <h6 class="mb-1"><i class="fas fa-mobile-alt me-2"></i>SMS Notifications</h6>
                                <small class="text-muted">Get SMS alerts for critical updates</small>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="smsNotifications">
                                <label class="form-check-label" for="smsNotifications"></label>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center py-3 border-bottom">
                            <div>
                                <h6 class="mb-1"><i class="fas fa-language me-2"></i>Language</h6>
                                <small class="text-muted">Select your preferred language</small>
                            </div>
                            <div style="min-width: 120px;">
                                <select class="form-select form-select-sm">
                                    <option value="en" selected>English</option>
                                    <option value="fil">Filipino</option>
                                    <option value="es">Spanish</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center py-3">
                            <div>
                                <h6 class="mb-1"><i class="fas fa-peso-sign me-2"></i>Currency Format</h6>
                                <small class="text-muted">Default currency display format</small>
                            </div>
                            <div style="min-width: 120px;">
                                <select class="form-select form-select-sm">
                                    <option value="php" selected>PHP (₱)</option>
                                    <option value="usd">USD ($)</option>
                                    <option value="eur">EUR (€)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                '
            ]) ?>
        </div>

        <!-- Recent Activity Card -->
        <div class="col-lg-6 mb-4">
            <?= view('partials/container-card', [
                'title' => 'Recent Activity',
                'subtitle' => 'Your latest account activities',
                'content' => '
                    <div class="activity-list">
                        ' . implode('', array_map(function($activity) {
                            $typeColors = [
                                'success' => 'success',
                                'primary' => 'primary',
                                'warning' => 'warning',
                                'info' => 'info'
                            ];
                            $color = $typeColors[$activity['type']] ?? 'secondary';
                            
                            return '
                                <div class="d-flex align-items-start gap-3 p-3 rounded border mb-2">
                                    <div class="flex-shrink-0">
                                        <div class="d-flex align-items-center justify-content-center rounded-circle bg-' . $color . ' bg-opacity-10 text-' . $color . '" style="width: 40px; height: 40px;">
                                            <i class="fas fa-' . $activity['icon'] . '"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 min-w-0">
                                        <h6 class="mb-1 fw-semibold">' . htmlspecialchars($activity['action']) . '</h6>
                                        <p class="mb-1 text-muted small">' . htmlspecialchars($activity['description']) . '</p>
                                        <small class="text-secondary">' . htmlspecialchars($activity['time']) . '</small>
                                    </div>
                                </div>
                            ';
                        }, $recentActivities)) . '
                    </div>
                    
                    <div class="text-center mt-3">
                        <button class="btn btn-outline-primary btn-sm">View All Activity</button>
                    </div>
                '
            ]) ?>
        </div>
    </div>

<!-- Profile Picture Upload Modal -->
<div class="modal fade" id="profilePictureModal" tabindex="-1" aria-labelledby="profilePictureModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="profilePictureModalLabel">Change Profile Picture</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <div class="mb-3">
                    <div class="preview-avatar mx-auto mb-3" style="width: 120px; height: 120px; background: #f8f9fa; border: 2px dashed #dee2e6; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-user fa-3x text-muted"></i>
                    </div>
                </div>
                <div class="mb-3">
                    <button class="btn btn-outline-primary me-2" onclick="triggerFileUpload()">
                        <i class="fas fa-camera"></i> Choose Photo
                    </button>
                    <button class="btn btn-outline-danger" onclick="removeAvatar()">
                        <i class="fas fa-trash"></i> Remove
                    </button>
                </div>
                <input type="file" id="avatarFile" accept="image/*" style="display: none;" onchange="previewAvatar(this)">
                <p class="text-muted small">Supported formats: JPG, PNG, GIF. Max size: 2MB</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveAvatar()">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<script>
// Global variables
let currentEditMode = null;

// Profile picture functions
function triggerFileInput() {
    document.getElementById('profilePictureInput').click();
}

function openProfilePictureModal() {
    new bootstrap.Modal(document.getElementById('profilePictureModal')).show();
}

function handleProfilePictureChange(event) {
    const file = event.target.files[0];
    if (!file) return;

    const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
    if (!allowedTypes.includes(file.type)) {
        showNotification('Please select a valid image file (JPG, PNG, or GIF)', 'error');
        return;
    }

    const maxSize = 2 * 1024 * 1024; // 2MB
    if (file.size > maxSize) {
        showNotification('File size must be less than 2MB', 'error');
        return;
    }

    const reader = new FileReader();
    reader.onload = function(e) {
        const preview = document.getElementById('profilePicturePreview');
        preview.innerHTML = `<img src="${e.target.result}" alt="Profile Picture" id="profileImage" style="width: 100%; height: 100%; object-fit: cover;">`;
        
        // Update all profile avatars on the page
        const avatars = document.querySelectorAll('.profile-avatar');
        avatars.forEach(avatar => {
            avatar.innerHTML = `<img src="${e.target.result}" alt="Profile Picture" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">`;
        });
    };
    reader.readAsDataURL(file);

    showNotification('Profile picture updated successfully!', 'success');
}

// Edit mode functions
function toggleEditMode(section) {
    const form = document.getElementById(section === 'personal' ? 'personalInfoForm' : 'securityForm');
    const inputs = form.querySelectorAll('input:not([type="file"])');
    const actions = document.getElementById(section + 'Actions');
    const editBtn = form.closest('.card-body').querySelector('.btn-outline-primary');
    
    const isCurrentlyReadOnly = inputs[0].readOnly;
    
    inputs.forEach(input => {
        input.readOnly = !isCurrentlyReadOnly;
        if (!isCurrentlyReadOnly) {
            input.classList.add('border-primary');
        } else {
            input.classList.remove('border-primary');
        }
    });
    
    if (section === 'personal') {
        const uploadBtn = document.getElementById('profilePictureUploadBtn');
        if (uploadBtn) {
            uploadBtn.style.display = isCurrentlyReadOnly ? 'block' : 'none';
        }
    }
    
    if (isCurrentlyReadOnly) {
        actions.style.display = 'flex !important';
        actions.classList.remove('d-none');
        editBtn.innerHTML = '<i class="fas fa-times"></i> Cancel';
        editBtn.onclick = () => cancelEdit(section);
        currentEditMode = section;
    } else {
        actions.style.display = 'none !important';
        actions.classList.add('d-none');
        editBtn.innerHTML = `<i class="fas fa-${section === 'personal' ? 'edit' : 'key'}"></i> ${section === 'personal' ? 'Edit Information' : 'Change Password'}`;
        editBtn.onclick = () => toggleEditMode(section);
        currentEditMode = null;
    }
}

function cancelEdit(section) {
    const form = document.getElementById(section === 'personal' ? 'personalInfoForm' : 'securityForm');
    const inputs = form.querySelectorAll('input:not([type="file"])');
    const actions = document.getElementById(section + 'Actions');
    const editBtn = form.closest('.card-body').querySelector('.btn-outline-primary');
    
    inputs.forEach(input => {
        input.readOnly = true;
        input.classList.remove('border-primary');
    });
    
    if (section === 'personal') {
        const uploadBtn = document.getElementById('profilePictureUploadBtn');
        if (uploadBtn) {
            uploadBtn.style.display = 'none';
        }
    }
    
    actions.style.display = 'none !important';
    actions.classList.add('d-none');
    editBtn.innerHTML = `<i class="fas fa-${section === 'personal' ? 'edit' : 'key'}"></i> ${section === 'personal' ? 'Edit Information' : 'Change Password'}`;
    editBtn.onclick = () => toggleEditMode(section);
    currentEditMode = null;
    
    // Reset form values
    form.reset();
}

// Password toggle
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const button = field.nextElementSibling;
    const icon = button.querySelector('i');
    
    if (field.type === 'password') {
        field.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        field.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

// Modal functions for avatar upload
function triggerFileUpload() {
    document.getElementById('avatarFile').click();
}

function previewAvatar(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.querySelector('.preview-avatar');
            preview.innerHTML = `<img src="${e.target.result}" alt="Preview" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">`;
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function removeAvatar() {
    const preview = document.querySelector('.preview-avatar');
    preview.innerHTML = '<i class="fas fa-user fa-3x text-muted"></i>';
    document.getElementById('avatarFile').value = '';
}

function saveAvatar() {
    showNotification('Profile picture updated successfully!', 'success');
    bootstrap.Modal.getInstance(document.getElementById('profilePictureModal')).hide();
}

// Quick action functions
function exportProfileData() {
    showNotification('Profile data export started', 'info');
}

function viewSecurityLog() {
    showNotification('Opening security log', 'info');
}

// Notification function
function showNotification(message, type = 'info') {
    // Create a simple toast notification
    const toast = document.createElement('div');
    toast.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show`;
    toast.style.position = 'fixed';
    toast.style.top = '20px';
    toast.style.right = '20px';
    toast.style.zIndex = '9999';
    toast.style.minWidth = '300px';
    toast.innerHTML = `
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

// Form submissions
document.addEventListener('DOMContentLoaded', function() {
    // Personal info form
    document.getElementById('personalInfoForm').addEventListener('submit', function(e) {
        e.preventDefault();
        showNotification('Personal information updated successfully!', 'success');
        cancelEdit('personal');
    });
    
    // Security form
    document.getElementById('securityForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const newPassword = document.getElementById('newPassword').value;
        const confirmPassword = document.getElementById('confirmPassword').value;
        
        if (newPassword !== confirmPassword) {
            showNotification('Passwords do not match!', 'error');
            return;
        }
        
        showNotification('Password updated successfully!', 'success');
        cancelEdit('security');
    });
});
</script>
<?= $this->endSection() ?>