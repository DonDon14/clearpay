<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sign Up | ClearPay</title>
  <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
    integrity="sha512-KNUXkD2pCK9lEp+vZP1HcFhRmtYCrgS1uqk+OBrWiDEwVKHm2VZCs4wzAHzkCmqkPFrd59KPZoEYL2cbW8M1dA=="
    crossorigin="anonymous"
    referrerpolicy="no-referrer"
  />
  <link href="<?= base_url('css/login.css') ?>" rel="stylesheet">
</head>
<body>
  <div class="login-container">
    <!-- Left Column: Registration Form -->
    <div class="login-left">
      <!-- Top Navigation -->
      <nav class="login-nav">
        <a href="<?= base_url('/') ?>" class="nav-link">Home</a>
        <a href="<?= base_url('/') ?>" class="nav-link">Login</a>
        <a href="#" class="nav-link active">Sign Up</a>
        <a href="#" class="nav-link">Help</a>
      </nav>

      <div class="login-content">
        <!-- Branding -->
        <div class="branding">
          <h1>ClearPay</h1>
        </div>

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
  </script>
</body>
</html>
