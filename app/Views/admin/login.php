<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login | ClearPay</title>
  <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
    crossorigin="anonymous"
    referrerpolicy="no-referrer"
  />
  <link href="<?= base_url('css/login.css') ?>" rel="stylesheet">
</head>
<body>
  <div class="login-container">
    <!-- Left Column: Login Form -->
    <div class="login-left">
      <!-- Top Navigation (if needed) -->
      <nav class="login-nav">
        <a href="#" class="nav-link active">Home</a>
        <a href="#" class="nav-link">About Us</a>
        <a href="#" class="nav-link">Blog</a>
        <a href="#" class="nav-link">Help</a>
      </nav>

      <div class="login-content">
        <!-- Branding -->
        <div class="branding">
          <h1>ClearPay</h1>
        </div>

        <!-- Headline -->
        <h2 class="headline">Streamline Your Payment Management</h2>
        
        <!-- Sub-headline -->
        <p class="sub-headline">Welcome back! Please login to your account.</p>

        <!-- Display flashdata error message if exists -->
        <?php if (session()->getFlashdata('error')): ?>
          <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i>
            <?= session()->getFlashdata('error') ?>
          </div>
        <?php endif; ?>

        <!-- Login Form -->
        <form method="post" action="<?= base_url('loginPost') ?>" class="login-form">
          <div class="form-group">
            <label for="username">Username</label>
            <input 
              type="text" 
              name="username" 
              id="username" 
              class="form-control" 
              placeholder="Enter your username"
              required
            >
            <i class="fas fa-user input-icon"></i>
          </div>

          <div class="form-group">
            <label for="password">Password</label>
            <input 
              type="password" 
              name="password" 
              id="password" 
              class="form-control" 
              placeholder="Enter your password"
              required
            >
            <i class="fas fa-lock input-icon"></i>
            <button type="button" class="password-toggle" onclick="togglePassword()">
              <i class="fas fa-eye" id="toggleIcon"></i>
            </button>
          </div>

          <div class="form-options">
            <label class="remember-me">
              <input type="checkbox" name="remember" id="remember">
              <span>Remember Me</span>
            </label>
            <a href="#" class="forgot-password">Forgot Password?</a>
          </div>

          <button type="submit" class="btn-login">
            <i class="fas fa-sign-in-alt"></i>
            Login
          </button>

          <div class="signup-link">
            <span>Don't have an account?</span>
            <a href="<?= base_url('register') ?>" class="signup-btn">Sign Up</a>
          </div>
        </form>

        <!-- Social Login (Optional) -->
        <div class="social-login">
          <span class="social-text">Or login with</span>
          <div class="social-buttons">
            <button type="button" class="social-btn google" title="Google">
              <i class="fab fa-google"></i>
            </button>
            <button type="button" class="social-btn facebook" title="Facebook">
              <i class="fab fa-facebook"></i>
            </button>
            <button type="button" class="social-btn microsoft" title="Microsoft">
              <i class="fab fa-microsoft"></i>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Right Column: Illustration -->
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

  <script>
    function togglePassword() {
      const passwordInput = document.getElementById('password');
      const toggleIcon = document.getElementById('toggleIcon');
      
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
  </script>
</body>
</html>
