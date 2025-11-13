<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Payer Signup | ClearPay</title>
  <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
    crossorigin="anonymous"
    referrerpolicy="no-referrer"
  />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="<?= base_url('css/auth-shared.css') ?>" rel="stylesheet">
  <link href="<?= base_url('css/auth-login.css') ?>" rel="stylesheet">
  <style>
    /* Use same classes as login page for consistency */
    .signup-container {
      display: flex;
      height: 100vh;
      width: 100%;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      position: relative;
    }
    
    /* Use same structure as login page - signup-left matches login-left */
    .signup-left {
      flex: 0 0 50%;
      background: #ffffff;
      display: flex;
      flex-direction: column;
      position: relative;
      overflow-y: auto; /* This is the key for scrolling! */
    }
    
    /* Mobile: full width, hide right panel */
    @media (max-width: 768px) {
      .signup-container {
        flex-direction: column;
      }
      
      .signup-left,
      .signup-right {
        flex: 0 0 100%;
      }
      
      .signup-right {
        display: none;
      }
    }
    
    .signup-right {
      flex: 0 0 50%;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      display: flex;
      align-items: center;
      justify-content: center;
      position: relative;
      overflow: hidden;
    }
    
    .profile-picture-preview {
      width: 120px;
      height: 120px;
      border-radius: 50%;
      object-fit: cover;
      border: 3px solid #667eea;
      cursor: pointer;
      transition: all 0.3s;
    }
    .profile-picture-preview:hover {
      opacity: 0.8;
      transform: scale(1.05);
    }
    .profile-picture-placeholder {
      width: 120px;
      height: 120px;
      border-radius: 50%;
      border: 3px dashed #ccc;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      background: #f8f9fa;
      transition: all 0.3s;
    }
    .profile-picture-placeholder:hover {
      border-color: #667eea;
      background: #f0f0f0;
    }
    
    /* Ensure login-content can scroll properly - matches login page */
    .login-content {
      flex: 1;
      display: flex;
      flex-direction: column;
      justify-content: flex-start; /* Changed from center to allow scrolling */
      padding: 2rem 6rem;
      max-width: 600px;
      margin: 0 auto;
      width: 100%;
      overflow-y: auto;
      overflow-x: hidden;
      -webkit-overflow-scrolling: touch; /* Smooth scrolling on iOS */
    }
    
    /* Mobile adjustments for login-content */
    @media (max-width: 1024px) {
      .login-content {
        padding: 2rem 3rem;
      }
    }
    
    @media (max-width: 768px) {
      .login-content {
        padding: 2rem 2rem;
        padding-bottom: 3rem; /* Extra padding for mobile */
      }
    }
  </style>
</head>
<body>
  <div class="login-container signup-container">
    <!-- Left Column: Signup Form -->
    <div class="login-left signup-left">
      <!-- Top Navigation -->
      <nav class="login-nav">
        <a href="<?= base_url('/') ?>" class="nav-link">Home</a>
        <a href="<?= base_url('payer/login') ?>" class="nav-link">Login</a>
        <a href="#" class="nav-link">Help</a>
      </nav>

      <div class="login-content">
        <!-- Branding -->
        <?php 
        $logoOptions = ['size' => 'medium'];
        include(APPPATH . 'Views/partials/logo.php');
        ?>

        <!-- Headline -->
        <h2 class="headline">Create Account</h2>
        
        <!-- Sub-headline -->
        <p class="sub-headline">Sign up to access your payment information</p>

        <!-- Display flashdata messages -->
        <?php if (session()->getFlashdata('error')): ?>
          <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i>
            <?= session()->getFlashdata('error') ?>
          </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('success')): ?>
          <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <?= session()->getFlashdata('success') ?>
          </div>
        <?php endif; ?>

        <!-- Signup Form -->
        <form method="post" action="<?= base_url('payer/signupPost') ?>" enctype="multipart/form-data" id="signupForm" class="login-form">
          <!-- Profile Picture (Optional) -->
          <div class="mb-4 text-center">
            <label class="form-label d-block">Profile Picture (Optional)</label>
            <div class="d-inline-block position-relative">
              <img id="profilePreview" src="" alt="Profile Preview" class="profile-picture-preview" style="display: none;">
              <div id="profilePlaceholder" class="profile-picture-placeholder">
                <i class="fas fa-user fa-3x text-muted"></i>
              </div>
              <input type="file" name="profile_picture" id="profile_picture" accept="image/*" style="display: none;">
              <button type="button" class="btn btn-sm btn-outline-primary mt-2" onclick="document.getElementById('profile_picture').click();">
                <i class="fas fa-camera me-1"></i>Upload Photo
              </button>
              <button type="button" id="removeProfileBtn" class="btn btn-sm btn-outline-danger mt-2" style="display: none;" onclick="removeProfilePicture();">
                <i class="fas fa-times me-1"></i>Remove
              </button>
            </div>
            <small class="form-text text-muted d-block mt-2">JPG, PNG or GIF. Max size: 2MB</small>
            <div class="invalid-feedback" id="profile_picture_error"></div>
          </div>

          <div class="form-group">
            <label for="payer_id">Student ID / Username <span class="text-danger">*</span></label>
            <input 
              type="text" 
              name="payer_id" 
              id="payer_id" 
              class="form-control" 
              placeholder="Enter your Student ID"
              value="<?= old('payer_id') ?>" 
              required
            >
            <i class="fas fa-id-card input-icon"></i>
            <small class="form-text text-muted">This will be your username for login</small>
            <div class="invalid-feedback"></div>
          </div>

          <div class="form-group">
            <label for="password">Password <span class="text-danger">*</span></label>
            <input 
              type="password" 
              name="password" 
              id="password" 
              class="form-control" 
              placeholder="Enter your password"
              required
              minlength="6"
            >
            <i class="fas fa-lock input-icon"></i>
            <small class="form-text text-muted">Minimum 6 characters</small>
            <div class="invalid-feedback"></div>
          </div>

          <div class="form-group">
            <label for="confirm_password">Confirm Password <span class="text-danger">*</span></label>
            <input 
              type="password" 
              name="confirm_password" 
              id="confirm_password" 
              class="form-control" 
              placeholder="Confirm your password"
              required
              minlength="6"
            >
            <i class="fas fa-lock input-icon"></i>
            <div class="invalid-feedback"></div>
          </div>

          <div class="form-group">
            <label for="payer_name">Full Name <span class="text-danger">*</span></label>
            <input 
              type="text" 
              name="payer_name" 
              id="payer_name" 
              class="form-control" 
              placeholder="Enter your full name"
              value="<?= old('payer_name') ?>"
              required
            >
            <i class="fas fa-user input-icon"></i>
            <div class="invalid-feedback"></div>
          </div>

          <div class="form-group">
            <label for="email_address">Email Address</label>
            <input 
              type="email" 
              name="email_address" 
              id="email_address" 
              class="form-control" 
              placeholder="Enter your email address (optional)"
              value="<?= old('email_address') ?>"
            >
            <i class="fas fa-envelope input-icon"></i>
            <small class="form-text text-warning">
              <i class="fas fa-info-circle me-1"></i>
              <strong>Recommended:</strong> Having an email helps you receive important updates and notifications
            </small>
            <div class="invalid-feedback"></div>
          </div>

          <div class="form-group">
            <label for="contact_number">Contact Number</label>
            <input 
              type="tel" 
              name="contact_number" 
              id="contact_number" 
              class="form-control" 
              placeholder="09123456789"
              value="<?= old('contact_number') ?>"
              maxlength="11"
            >
            <i class="fas fa-phone input-icon"></i>
            <small class="form-text text-muted">Must be exactly 11 digits (numbers only)</small>
            <div class="invalid-feedback"></div>
          </div>

          <div class="form-group">
            <label for="course_department">Course/Department</label>
            <input 
              type="text" 
              name="course_department" 
              id="course_department" 
              class="form-control" 
              placeholder="e.g., BS Computer Science, IT Department"
              value="<?= old('course_department') ?>"
            >
            <i class="fas fa-graduation-cap input-icon"></i>
            <small class="form-text text-muted">Your course or department name</small>
            <div class="invalid-feedback"></div>
          </div>

          <button type="submit" class="btn-login" id="signupBtn">
            <i class="fas fa-user-plus"></i>
            Create Account
          </button>

          <div class="signup-link">
            <span>Already have an account?</span>
            <a href="<?= base_url('payer/login') ?>" class="signup-btn">Login Here</a>
          </div>
        </form>
      </div>
    </div>

    <!-- Right Column: Illustration -->
    <div class="login-right signup-right">
      <div class="illustration-container">
        <!-- Payment Illustration -->
        <div class="payment-illustration">
          <div class="payment-card">
            <div class="card-chip"></div>
            <div class="card-number">**** **** **** 1234</div>
            <div class="card-holder">CLEARPAY</div>
            <div class="card-expiry">12/25</div>
          </div>
          
          <!-- Floating elements -->
          <div class="floating-icon icon-1">
            <i class="fas fa-money-check-alt"></i>
          </div>
          <div class="floating-icon icon-2">
            <i class="fas fa-chart-line"></i>
          </div>
          <div class="floating-icon icon-3">
            <i class="fas fa-shield-alt"></i>
          </div>
          <div class="floating-icon icon-4">
            <i class="fas fa-qrcode"></i>
          </div>
          <div class="floating-icon icon-5">
            <i class="fas fa-credit-card"></i>
          </div>
          
          <!-- Abstract shapes -->
          <div class="abstract-shape shape-1"></div>
          <div class="abstract-shape shape-2"></div>
          <div class="abstract-shape shape-3"></div>
        </div>
        
        <!-- Background pattern -->
        <div class="bg-pattern"></div>
      </div>
    </div>
  </div>

  <!-- Email Verification Modal -->
  <div class="modal fade" id="verificationModal" tabindex="-1" aria-labelledby="verificationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title" id="verificationModalLabel">
            <i class="fas fa-envelope-circle-check me-2"></i>Verify Your Email
          </h5>
        </div>
        <div class="modal-body">
          <div class="alert alert-info d-none" id="emailSentAlert">
            <i class="fas fa-info-circle me-2"></i>
            <span>Verification code sent to your email!</span>
          </div>
          
          <p>We've sent a verification code to <strong id="pendingEmail">your email</strong>. Please enter the code below to complete your registration.</p>
          
          <form id="verificationForm">
            <div class="mb-3">
              <label for="verification_code" class="form-label">Verification Code</label>
              <input 
                type="text" 
                class="form-control form-control-lg text-center" 
                id="verification_code" 
                name="verification_code" 
                placeholder="000000"
                maxlength="6"
                pattern="[0-9]{6}"
                required
                style="font-size: 24px; letter-spacing: 10px;"
              >
              <small class="form-text text-muted">Enter the 6-digit code sent to your email</small>
            </div>
            
            <div class="alert alert-danger d-none" id="verificationError" role="alert"></div>
            
            <div class="d-grid gap-2">
              <button type="submit" class="btn btn-primary">
                <i class="fas fa-check me-2"></i>Verify Email
              </button>
              <button type="button" class="btn btn-outline-secondary" id="resendBtn">
                <i class="fas fa-redo me-2"></i>Resend Code
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="<?= base_url('js/phone-helper.js') ?>"></script>
  <script>
    // Initialize phone number field
    if (typeof window.initPhoneNumberField === 'function') {
      window.initPhoneNumberField('contact_number', {
        required: false,
        errorMessage: 'Contact number must be exactly 11 digits'
      });
    }

    // Profile picture preview
    const profileInput = document.getElementById('profile_picture');
    const profilePreview = document.getElementById('profilePreview');
    const profilePlaceholder = document.getElementById('profilePlaceholder');
    const removeProfileBtn = document.getElementById('removeProfileBtn');

    profileInput.addEventListener('change', function(e) {
      const file = e.target.files[0];
      if (file) {
        // Validate file type
        if (!file.type.match('image.*')) {
          showFieldError('profile_picture', 'Please select a valid image file');
          profileInput.value = '';
          return;
        }

        // Validate file size (2MB max)
        if (file.size > 2 * 1024 * 1024) {
          showFieldError('profile_picture', 'Image size must be less than 2MB');
          profileInput.value = '';
          return;
        }

        // Show preview
        const reader = new FileReader();
        reader.onload = function(e) {
          profilePreview.src = e.target.result;
          profilePreview.style.display = 'block';
          profilePlaceholder.style.display = 'none';
          removeProfileBtn.style.display = 'inline-block';
          clearFieldError('profile_picture');
        };
        reader.readAsDataURL(file);
      }
    });

    function removeProfilePicture() {
      profileInput.value = '';
      profilePreview.src = '';
      profilePreview.style.display = 'none';
      profilePlaceholder.style.display = 'flex';
      removeProfileBtn.style.display = 'none';
    }

    function showFieldError(fieldId, message) {
      const errorDiv = document.getElementById(fieldId + '_error');
      if (errorDiv) {
        errorDiv.textContent = message;
        errorDiv.style.display = 'block';
      }
    }

    function clearFieldError(fieldId) {
      const errorDiv = document.getElementById(fieldId + '_error');
      if (errorDiv) {
        errorDiv.textContent = '';
        errorDiv.style.display = 'none';
      }
    }

    // Form submission with AJAX
    document.getElementById('signupForm').addEventListener('submit', async function(e) {
      e.preventDefault();
      
      let isValid = true;
      
      // Check required fields
      const requiredFields = ['payer_id', 'payer_name', 'password', 'confirm_password'];
      requiredFields.forEach(function(fieldId) {
        const field = document.getElementById(fieldId);
        if (!field.value.trim()) {
          field.classList.add('is-invalid');
          isValid = false;
        } else {
          field.classList.remove('is-invalid');
        }
      });
      
      // Validate password match
      const passwordField = document.getElementById('password');
      const confirmPasswordField = document.getElementById('confirm_password');
      if (passwordField.value !== confirmPasswordField.value) {
        confirmPasswordField.classList.add('is-invalid');
        confirmPasswordField.parentElement.querySelector('.invalid-feedback').textContent = 'Passwords do not match';
        isValid = false;
      } else {
        confirmPasswordField.classList.remove('is-invalid');
      }
      
      // Validate password length
      if (passwordField.value && passwordField.value.length < 6) {
        passwordField.classList.add('is-invalid');
        passwordField.parentElement.querySelector('.invalid-feedback').textContent = 'Password must be at least 6 characters';
        isValid = false;
      }

      // Validate email format if provided
      const emailField = document.getElementById('email_address');
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (emailField.value && !emailRegex.test(emailField.value)) {
        emailField.classList.add('is-invalid');
        emailField.parentElement.querySelector('.invalid-feedback').textContent = 'Please enter a valid email address';
        isValid = false;
      } else if (emailField.value) {
        emailField.classList.remove('is-invalid');
      }

      // Validate contact number if provided
      const contactField = document.getElementById('contact_number');
      if (contactField.value) {
        const sanitized = contactField.value.replace(/\D/g, '');
        if (sanitized.length !== 11) {
          contactField.classList.add('is-invalid');
          contactField.parentElement.querySelector('.invalid-feedback').textContent = 'Contact number must be exactly 11 digits';
          isValid = false;
        } else {
          contactField.classList.remove('is-invalid');
        }
      }

      if (!isValid) {
        return false;
      }

      // Disable submit button to prevent double submission
      const submitBtn = document.getElementById('signupBtn');
      const originalText = submitBtn.innerHTML;
      submitBtn.disabled = true;
      submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating Account...';

      try {
        const formData = new FormData(this);
        const response = await fetch('<?= base_url('payer/signupPost') ?>', {
          method: 'POST',
          body: formData
        });

        const result = await response.json();

        if (result.success) {
          // Check if email verification is required
          if (result.requires_verification && result.email) {
            // Show verification modal only if email is provided
            document.getElementById('pendingEmail').textContent = result.email || 'your email';
            const verificationModal = new bootstrap.Modal(document.getElementById('verificationModal'));
            verificationModal.show();

            // Show verification code in console for debugging
            if (result.verification_code) {
              console.log('🔑 Verification Code (for testing only):', result.verification_code);
              console.log('📧 Email sent status:', result.email_sent);

              // Show code in alert if email was not sent (for development)
              if (!result.email_sent) {
                alert('Email not configured. Your verification code is: ' + result.verification_code + '\n\nPlease configure SMTP settings in app/Config/Email.php');
              }
            }

            // Show email sent message
            const emailSentAlert = document.getElementById('emailSentAlert');
            emailSentAlert.classList.remove('d-none');

            if (result.email_sent) {
              emailSentAlert.querySelector('span').textContent = 'Verification code sent to your email!';
              emailSentAlert.className = 'alert alert-success';
            } else {
              emailSentAlert.querySelector('span').textContent = 'Email not configured. Please check console for your verification code.';
              emailSentAlert.className = 'alert alert-warning';
            }
          } else {
            // No email provided or no verification needed - redirect to login
            alert(result.message || 'Account created successfully! You can now login.');
            if (result.redirect) {
              window.location.href = result.redirect;
            } else {
              window.location.href = '<?= base_url('payer/login') ?>';
            }
          }
        } else {
          alert(result.error || 'Registration failed. Please try again.');
        }
      } catch (error) {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
      } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
      }
    });

    // Handle verification form submission
    document.getElementById('verificationForm').addEventListener('submit', async function(e) {
      e.preventDefault();

      const formData = new FormData(this);
      const submitBtn = this.querySelector('button[type="submit"]');
      const originalText = submitBtn.innerHTML;

      submitBtn.disabled = true;
      submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Verifying...';

      // Clear previous errors
      document.getElementById('verificationError').classList.add('d-none');

      try {
        const response = await fetch('<?= base_url('payer/verifyEmail') ?>', {
          method: 'POST',
          body: formData
        });

        const result = await response.json();

        if (result.success) {
          alert(result.message);
          if (result.redirect) {
            window.location.href = result.redirect;
          } else {
            window.location.href = '<?= base_url('payer/login') ?>';
          }
        } else {
          document.getElementById('verificationError').classList.remove('d-none');
          document.getElementById('verificationError').textContent = result.error;
        }
      } catch (error) {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
      } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
      }
    });

    // Handle resend code button
    document.getElementById('resendBtn').addEventListener('click', async function() {
      this.disabled = true;
      const originalText = this.innerHTML;
      this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';

      try {
        const response = await fetch('<?= base_url('payer/resendVerificationCode') ?>', {
          method: 'POST'
        });

        const result = await response.json();

        if (result.success) {
          document.getElementById('emailSentAlert').classList.remove('d-none');
          document.getElementById('emailSentAlert').querySelector('span').textContent = result.message;
          document.getElementById('emailSentAlert').className = 'alert alert-success';
          setTimeout(() => {
            document.getElementById('emailSentAlert').classList.add('d-none');
          }, 5000);
          
          // Show code in console if email not sent
          if (result.verification_code && !result.email_sent) {
            console.log('🔑 New Verification Code (for testing only):', result.verification_code);
          }
        } else {
          alert(result.error || 'Failed to resend verification code.');
        }
      } catch (error) {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
      } finally {
        this.disabled = false;
        this.innerHTML = originalText;
      }
    });
  </script>
</body>
</html>

