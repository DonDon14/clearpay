<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Forgot Password | ClearPay</title>
  <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
    crossorigin="anonymous"
    referrerpolicy="no-referrer"
  />
  <link href="<?= base_url('css/auth-shared.css') ?>" rel="stylesheet">
  <link href="<?= base_url('css/auth-forgot-password.css') ?>" rel="stylesheet">
</head>
<body>
  <div class="login-container">
    <!-- Left Column: Forgot Password Form -->
    <div class="login-left">
      <!-- Top Navigation -->
      <nav class="login-nav">
        <a href="<?= base_url('/') ?>" class="nav-link active">Home</a>
        <a href="<?= base_url('payer/signup') ?>" class="nav-link">Sign Up</a>
        <a href="#" class="nav-link">Help</a>
      </nav>

      <div class="login-content">
        <!-- Branding -->
        <?php 
        $logoOptions = ['size' => 'medium'];
        include(APPPATH . 'Views/partials/logo.php');
        ?>

        <!-- Headline -->
        <h2 class="headline">Reset Your Password</h2>
        <p class="sub-headline">Don't worry! Enter your email to receive a verification code.</p>

        <!-- Display flashdata messages -->
        <div id="alertContainer"></div>

        <!-- Step 1: Email Input -->
        <div id="step1-container">
          <form id="forgotPasswordForm" class="login-form">
            <div class="form-group">
              <label for="email">Email Address</label>
              <input 
                type="email" 
                name="email" 
                id="email" 
                class="form-control" 
                placeholder="Enter your email address"
                required
              >
              <i class="fas fa-envelope input-icon"></i>
            </div>

            <button type="submit" class="btn-login" id="sendCodeBtn">
              <i class="fas fa-paper-plane"></i>
              Continue
            </button>

            <div class="signup-link">
              <span>Remember your password?</span>
              <a href="<?= base_url('payer/login') ?>" class="signup-btn">Back to Login</a>
            </div>
          </form>
        </div>

        <!-- Step 1.5: Account Confirmation -->
        <div id="step1-5-container" style="display: none;">
          <div class="account-confirmation">
            <div class="confirmation-icon" id="confirmationIcon">
              <img id="accountProfilePicture" src="" alt="Profile Picture" style="display: none; width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
              <i class="fas fa-user" id="accountIconFallback"></i>
            </div>
            <h3 class="confirmation-title">Is this your account?</h3>
            <div class="account-info">
              <div class="account-name" id="accountName"></div>
              <div class="account-email" id="accountEmail"></div>
            </div>
            <div class="confirmation-actions">
              <button type="button" class="btn-login" id="confirmAccountBtn">
                <i class="fas fa-check"></i>
                Yes, Send Code
              </button>
              <button type="button" class="btn-link" id="notMyAccountBtn">
                <i class="fas fa-arrow-left"></i>
                No, This Isn't Me
              </button>
            </div>
          </div>
        </div>

        <!-- Step 2: Verify Code -->
        <div id="step2-container" style="display: none;">
          <form id="verifyCodeForm" class="login-form">
            <div class="form-group">
              <label for="reset_code">Verification Code</label>
              <input 
                type="text" 
                name="reset_code" 
                id="reset_code" 
                class="form-control" 
                placeholder="Enter 6-digit code"
                maxlength="6"
                required
              >
              <i class="fas fa-key input-icon"></i>
            </div>

            <button type="submit" class="btn-login" id="verifyCodeBtn">
              <i class="fas fa-check-circle"></i>
              Verify Code
            </button>

            <button type="button" class="btn-link" id="backToStep1Btn">
              <i class="fas fa-arrow-left"></i>
              Back to Email Entry
            </button>
          </form>
        </div>

        <!-- Step 3: New Password -->
        <div id="step3-container" style="display: none;">
          <form id="resetPasswordForm" class="login-form">
            <div class="form-group">
              <label for="password">New Password</label>
              <input 
                type="password" 
                name="password" 
                id="password" 
                class="form-control" 
                placeholder="Enter new password"
                required
              >
              <i class="fas fa-lock input-icon"></i>
              <button type="button" class="password-toggle" onclick="togglePassword('password')">
                <i class="fas fa-eye" id="toggleIcon1"></i>
              </button>
            </div>

            <div class="form-group">
              <label for="confirm_password">Confirm New Password</label>
              <input 
                type="password" 
                name="confirm_password" 
                id="confirm_password" 
                class="form-control" 
                placeholder="Confirm new password"
                required
              >
              <i class="fas fa-lock input-icon"></i>
              <button type="button" class="password-toggle" onclick="togglePassword('confirm_password')">
                <i class="fas fa-eye" id="toggleIcon2"></i>
              </button>
            </div>

            <button type="submit" class="btn-login" id="resetPasswordBtn">
              <i class="fas fa-key"></i>
              Reset Password
            </button>
          </form>
        </div>
      </div>
    </div>

    <!-- Right Column: Illustration -->
    <div class="login-right">
      <div class="illustration-container">
        <div class="payment-illustration">
          <div class="payment-card">
            <div class="card-chip"></div>
            <div class="card-number">**** **** **** 1234</div>
            <div class="card-holder">CLEARPAY</div>
            <div class="card-expiry">12/25</div>
          </div>
          
          <div class="floating-icon icon-1">
            <i class="fas fa-shield-alt"></i>
          </div>
          <div class="floating-icon icon-2">
            <i class="fas fa-lock"></i>
          </div>
          <div class="floating-icon icon-3">
            <i class="fas fa-key"></i>
          </div>
          <div class="floating-icon icon-4">
            <i class="fas fa-check-circle"></i>
          </div>
          <div class="floating-icon icon-5">
            <i class="fas fa-envelope"></i>
          </div>
          
          <div class="abstract-shape shape-1"></div>
          <div class="abstract-shape shape-2"></div>
          <div class="abstract-shape shape-3"></div>
        </div>
        
        <div class="bg-pattern"></div>
      </div>
    </div>
  </div>

  <script>
    let userEmail = '';

    function showAlert(message, type = 'success') {
      const alertContainer = document.getElementById('alertContainer');
      const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
      const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
      
      alertContainer.innerHTML = `
        <div class="alert ${alertClass}">
          <i class="fas ${icon}"></i>
          ${message}
        </div>
      `;
      
      setTimeout(() => {
        alertContainer.innerHTML = '';
      }, 5000);
    }

    function showStep(step) {
      document.getElementById('step1-container').style.display = step === 1 ? 'block' : 'none';
      document.getElementById('step1-5-container').style.display = step === 1.5 ? 'block' : 'none';
      document.getElementById('step2-container').style.display = step === 2 ? 'block' : 'none';
      document.getElementById('step3-container').style.display = step === 3 ? 'block' : 'none';
    }

    function togglePassword(fieldId) {
      const field = document.getElementById(fieldId);
      const iconId = fieldId === 'password' ? 'toggleIcon1' : 'toggleIcon2';
      const icon = document.getElementById(iconId);
      
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

    // Step 1: Check email and show account confirmation
    document.getElementById('forgotPasswordForm').addEventListener('submit', async function(e) {
      e.preventDefault();
      
      const email = document.getElementById('email').value;
      const sendBtn = document.getElementById('sendCodeBtn');
      const originalText = sendBtn.innerHTML;
      
      sendBtn.disabled = true;
      sendBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Checking...';
      
      try {
        const response = await fetch('<?= base_url('payer/forgotPasswordPost') ?>', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: `email=${encodeURIComponent(email)}`
        });
        
        const data = await response.json();
        
        if (data.success && data.account_found) {
          userEmail = email;
          // Show account confirmation
          document.getElementById('accountName').textContent = data.account_info.name;
          document.getElementById('accountEmail').textContent = data.account_info.masked_email;
          
          // Display profile picture if available
          const profilePicture = data.account_info.profile_picture;
          const profileImg = document.getElementById('accountProfilePicture');
          const iconFallback = document.getElementById('accountIconFallback');
          
          if (profilePicture) {
            profileImg.src = profilePicture;
            profileImg.style.display = 'block';
            iconFallback.style.display = 'none';
            profileImg.onerror = function() {
              // If image fails to load, show icon fallback
              profileImg.style.display = 'none';
              iconFallback.style.display = 'block';
            };
          } else {
            profileImg.style.display = 'none';
            iconFallback.style.display = 'block';
          }
          
          showStep(1.5);
        } else {
          // Account not found - show generic message for security
          showAlert(data.error || 'If an account with that email exists, you will receive a password reset verification code.', 'success');
        }
      } catch (error) {
        showAlert('Network error. Please try again.', 'danger');
      } finally {
        sendBtn.disabled = false;
        sendBtn.innerHTML = originalText;
      }
    });

    // Step 1.5: Confirm account and send verification code
    document.getElementById('confirmAccountBtn').addEventListener('click', async function() {
      const confirmBtn = document.getElementById('confirmAccountBtn');
      const originalText = confirmBtn.innerHTML;
      
      confirmBtn.disabled = true;
      confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending Code...';
      
      try {
        const response = await fetch('<?= base_url('payer/sendResetCode') ?>', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: `email=${encodeURIComponent(userEmail)}`
        });
        
        const data = await response.json();
        
        if (data.success) {
          showAlert(data.message, 'success');
          showStep(2);
          // Disable email field
          document.getElementById('email').disabled = true;
        } else {
          showAlert(data.error || 'Failed to send verification code. Please try again.', 'danger');
        }
      } catch (error) {
        showAlert('Network error. Please try again.', 'danger');
      } finally {
        confirmBtn.disabled = false;
        confirmBtn.innerHTML = originalText;
      }
    });

    // Not my account - go back to email entry
    document.getElementById('notMyAccountBtn').addEventListener('click', function() {
      document.getElementById('email').value = '';
      document.getElementById('email').disabled = false;
      showStep(1);
    });

    // Step 2: Verify code
    document.getElementById('verifyCodeForm').addEventListener('submit', async function(e) {
      e.preventDefault();
      
      const resetCode = document.getElementById('reset_code').value;
      const verifyBtn = document.getElementById('verifyCodeBtn');
      const originalText = verifyBtn.innerHTML;
      
      verifyBtn.disabled = true;
      verifyBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Verifying...';
      
      try {
        const response = await fetch('<?= base_url('payer/verifyResetCode') ?>', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: `email=${encodeURIComponent(userEmail)}&reset_code=${encodeURIComponent(resetCode)}`
        });
        
        const data = await response.json();
        
        if (data.success) {
          showAlert(data.message, 'success');
          showStep(3);
        } else {
          showAlert(data.error || 'Invalid verification code.', 'danger');
        }
      } catch (error) {
        showAlert('Network error. Please try again.', 'danger');
      } finally {
        verifyBtn.disabled = false;
        verifyBtn.innerHTML = originalText;
      }
    });

    // Step 3: Reset password
    document.getElementById('resetPasswordForm').addEventListener('submit', async function(e) {
      e.preventDefault();
      
      const password = document.getElementById('password').value;
      const confirmPassword = document.getElementById('confirm_password').value;
      const resetBtn = document.getElementById('resetPasswordBtn');
      const originalText = resetBtn.innerHTML;
      
      if (password !== confirmPassword) {
        showAlert('Passwords do not match.', 'danger');
        return;
      }
      
      if (password.length < 6) {
        showAlert('Password must be at least 6 characters long.', 'danger');
        return;
      }
      
      resetBtn.disabled = true;
      resetBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Resetting...';
      
      try {
        const response = await fetch('<?= base_url('payer/resetPassword') ?>', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: `email=${encodeURIComponent(userEmail)}&password=${encodeURIComponent(password)}&confirm_password=${encodeURIComponent(confirmPassword)}`
        });
        
        const data = await response.json();
        
        if (data.success) {
          showAlert(data.message, 'success');
          setTimeout(() => {
            if (data.redirect) {
              window.location.href = data.redirect;
            }
          }, 2000);
        } else {
          showAlert(data.error || 'Failed to reset password.', 'danger');
        }
      } catch (error) {
        showAlert('Network error. Please try again.', 'danger');
      } finally {
        resetBtn.disabled = false;
        resetBtn.innerHTML = originalText;
      }
    });

    // Back to step 1
    document.getElementById('backToStep1Btn').addEventListener('click', function() {
      document.getElementById('email').disabled = false;
      document.getElementById('reset_code').value = '';
      showStep(1);
    });

    // Only allow numbers in reset code field
    document.getElementById('reset_code').addEventListener('input', function(e) {
      e.target.value = e.target.value.replace(/[^0-9]/g, '');
    });
  </script>
</body>
</html>

