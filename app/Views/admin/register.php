<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sign Up | ClearPay</title>
  <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"
    integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA=="
    crossorigin="anonymous"
    referrerpolicy="no-referrer"
  />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="<?= base_url('css/auth-shared.css') ?>" rel="stylesheet">
  <link href="<?= base_url('css/auth-register.css') ?>" rel="stylesheet">
</head>
<body>
  <div class="login-container">
    <!-- Left Column: Registration Form -->
    <div class="login-left">
      <!-- Top Navigation -->
      <nav class="login-nav">
        <a href="<?= base_url('/') ?>" class="nav-link">Home</a>
        <a href="<?= base_url('register') ?>" class="nav-link active">Sign Up</a>
        <a href="#" class="nav-link">Help</a>
      </nav>

      <div class="login-content">
        <!-- Branding -->
        <?php 
        $logoOptions = ['size' => 'medium'];
        include(APPPATH . 'Views/partials/logo.php');
        ?>

        <!-- Headline -->
        <h2 class="headline">Create Your Account</h2>
        
        <!-- Sub-headline -->
        <p class="sub-headline">Join us and start managing payments efficiently!</p>

        <!-- Display flashdata messages -->
        <?php if (session()->getFlashdata('error')): ?>
          <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i>
            <?= session()->getFlashdata('error') ?>
          </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('errors')): ?>
          <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i>
            <?php foreach (session()->getFlashdata('errors') as $error): ?>
              <div><?= $error ?></div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <!-- Registration Form -->
        <form method="post" action="<?= base_url('registerPost') ?>" class="login-form">
          <div class="form-group">
            <label for="name">Full Name</label>
            <input 
              type="text" 
              name="name" 
              id="name" 
              class="form-control" 
              placeholder="Enter your full name"
              value="<?= old('name') ?>"
              required
            >
            <i class="fas fa-user input-icon"></i>
          </div>

          <div class="form-group">
            <label for="username">Username</label>
            <input 
              type="text" 
              name="username" 
              id="username" 
              class="form-control" 
              placeholder="Choose a username"
              value="<?= old('username') ?>"
              required
            >
            <i class="fas fa-at input-icon"></i>
          </div>

          <div class="form-group">
            <label for="email">Email Address</label>
            <input 
              type="email" 
              name="email" 
              id="email" 
              class="form-control" 
              placeholder="Enter your email"
              value="<?= old('email') ?>"
              required
            >
            <i class="fas fa-envelope input-icon"></i>
          </div>

          <div class="form-group">
            <label for="phone">Phone Number (Optional)</label>
            <input 
              type="tel" 
              name="phone" 
              id="phone" 
              class="form-control" 
              placeholder="Enter your phone number"
              value="<?= old('phone') ?>"
            >
            <i class="fas fa-phone input-icon"></i>
          </div>

          <div class="form-group">
            <label for="role">Role</label>
            <select 
              name="role" 
              id="role" 
              class="form-control" 
              required
            >
              <option value="officer" <?= old('role') === 'officer' ? 'selected' : '' ?>>Officer</option>
            </select>
            <i class="fas fa-user-tag input-icon"></i>
            <small class="form-text text-muted">Officers require approval from Super Admin before they can access the system.</small>
          </div>

          <div class="form-group">
            <label for="password">Password</label>
            <input 
              type="password" 
              name="password" 
              id="password" 
              class="form-control" 
              placeholder="Create a password"
              required
            >
            <i class="fas fa-lock input-icon"></i>
            <button type="button" class="password-toggle" onclick="togglePassword('password', 'toggleIcon')">
              <i class="fas fa-eye" id="toggleIcon"></i>
            </button>
          </div>

          <div class="form-group">
            <label for="confirm_password">Confirm Password</label>
            <input 
              type="password" 
              name="confirm_password" 
              id="confirm_password" 
              class="form-control" 
              placeholder="Confirm your password"
              required
            >
            <i class="fas fa-lock input-icon"></i>
            <button type="button" class="password-toggle" onclick="togglePassword('confirm_password', 'toggleIcon2')">
              <i class="fas fa-eye" id="toggleIcon2"></i>
            </button>
            <small id="passwordMatchMsg" style="display: none; font-size: 0.875rem; margin-top: 0.25rem;"></small>
          </div>

          <button type="submit" class="btn-login">
            <i class="fas fa-user-plus"></i>
            Create Account
          </button>

          <div class="signup-link">
            <span>Already have an account?</span>
            <a href="<?= base_url('/') ?>" class="signup-btn">Login</a>
          </div>
        </form>
      </div>
    </div>

    <!-- Right Column: Illustration (same as login) -->
    <div class="login-right">
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
  <div class="modal fade" id="verificationModal" tabindex="-1" aria-labelledby="verificationModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="verificationModalLabel">
            <i class="fas fa-envelope-circle-check text-primary"></i> Verify Your Email
          </h5>
        </div>
        <div class="modal-body">
          <div class="text-center mb-4">
            <div class="mb-3">
              <i class="fas fa-envelope-open-text fa-3x text-primary"></i>
            </div>
            <p class="mb-2">We've sent a verification code to:</p>
            <p class="fw-bold text-primary" id="pendingEmail"></p>
          </div>
          
          <div class="alert alert-info d-none" id="emailSentAlert">
            <i class="fas fa-check-circle"></i>
            <span id="emailSentMessage">Email sent successfully!</span>
          </div>
          
          <div class="alert alert-danger d-none" id="verificationError"></div>
          
          <form id="verificationForm">
            <div class="mb-3">
              <label for="verification_code" class="form-label">Enter Verification Code</label>
              <input 
                type="text" 
                class="form-control text-center fs-4 fw-bold" 
                id="verification_code" 
                name="verification_code" 
                placeholder="000000"
                maxlength="6"
                pattern="[0-9]{6}"
                autocomplete="off"
                required
              >
              <div class="form-text">Enter the 6-digit code sent to your email</div>
            </div>
            
            <div class="d-grid gap-2">
              <button type="submit" class="btn btn-primary">
                <i class="fas fa-check-circle"></i> Verify Email
              </button>
              <button type="button" class="btn btn-link text-secondary" id="resendBtn">
                <i class="fas fa-redo"></i> Resend Code
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <script>
    function togglePassword(inputId, iconId) {
      const passwordInput = document.getElementById(inputId);
      const toggleIcon = document.getElementById(iconId);
      
      if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleIcon.classList.remove('fa-eye');
        toggleIcon.classList.add('fa-eye-slash');
      } else {
        passwordInput.type = 'password';
        toggleIcon.classList.remove('fa-eye-slash');
        toggleIcon.classList.add('fa-eye');
      }
    }

    // Handle form submission
    document.querySelector('.login-form').addEventListener('submit', async function(e) {
      e.preventDefault();
      
      const formData = new FormData(this);
      const submitBtn = this.querySelector('button[type="submit"]');
      const originalText = submitBtn.innerHTML;
      
      submitBtn.disabled = true;
      submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating Account...';
      
      try {
        const response = await fetch('<?= base_url('registerPost') ?>', {
          method: 'POST',
          body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
          // Show verification modal
          document.getElementById('pendingEmail').textContent = result.email || 'your email';
          const verificationModal = new bootstrap.Modal(document.getElementById('verificationModal'));
          verificationModal.show();
          
          // Show verification code in console for debugging (remove in production)
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
        const response = await fetch('<?= base_url('verifyEmail') ?>', {
          method: 'POST',
          body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
          alert(result.message);
          window.location.href = result.redirect;
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
      this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
      
      try {
        const response = await fetch('<?= base_url('resendVerificationCode') ?>', {
          method: 'POST'
        });
        
        const result = await response.json();
        
        if (result.success) {
          document.getElementById('emailSentAlert').classList.remove('d-none');
          document.getElementById('emailSentMessage').textContent = result.message;
          setTimeout(() => {
            document.getElementById('emailSentAlert').classList.add('d-none');
          }, 5000);
        } else {
          alert(result.error);
        }
      } catch (error) {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
      } finally {
        this.disabled = false;
        this.innerHTML = '<i class="fas fa-redo"></i> Resend Code';
      }
    });

    // Auto-format verification code input
    document.getElementById('verification_code').addEventListener('input', function(e) {
      this.value = this.value.replace(/[^0-9]/g, '');
    });

    // Password match validation
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirm_password');
    const passwordMatchMsg = document.getElementById('passwordMatchMsg');

    function checkPasswordMatch() {
      const password = passwordInput.value;
      const confirmPassword = confirmPasswordInput.value;

      if (confirmPassword.length === 0) {
        passwordMatchMsg.style.display = 'none';
        return;
      }

      // Note: Passwords are case-sensitive for security
      // This is standard practice for password fields
      if (password === confirmPassword) {
        passwordMatchMsg.style.display = 'block';
        passwordMatchMsg.textContent = '✓ Passwords match';
        passwordMatchMsg.style.color = '#10b981';
      } else {
        passwordMatchMsg.style.display = 'block';
        passwordMatchMsg.textContent = '✗ Passwords do not match';
        passwordMatchMsg.style.color = '#ef4444';
      }
    }

    confirmPasswordInput.addEventListener('input', checkPasswordMatch);
    passwordInput.addEventListener('input', checkPasswordMatch);
  </script>
  <!-- Bootstrap JS for Modal -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
