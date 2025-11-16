<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Super Admin Login | ClearPay</title>
  <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
    crossorigin="anonymous"
    referrerpolicy="no-referrer"
  />
  <link href="<?= base_url('css/auth-shared.css') ?>" rel="stylesheet">
  <link href="<?= base_url('css/auth-login.css') ?>" rel="stylesheet">
  <style>
    .super-admin-badge {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      padding: 8px 16px;
      border-radius: 20px;
      font-size: 0.75rem;
      font-weight: 600;
      display: inline-block;
      margin-bottom: 20px;
    }
    .headline {
      color: #667eea;
    }
  </style>
</head>
<body>
  <div class="login-container">
    <!-- Left Column: Login Form -->
    <div class="login-left">
      <nav class="login-nav">
        <a href="<?= base_url('/') ?>" class="nav-link">Admin Portal</a>
        <a href="<?= base_url('super-admin/login') ?>" class="nav-link active">Super Admin</a>
      </nav>

      <div class="login-content">
        <!-- Branding -->
        <?php 
        $logoOptions = ['size' => 'medium'];
        include(APPPATH . 'Views/partials/logo.php');
        ?>

        <!-- Super Admin Badge -->
        <div class="super-admin-badge">
          <i class="fas fa-shield-alt"></i> Super Admin Portal
        </div>

        <!-- Headline -->
        <h2 class="headline">Super Admin Access</h2>
        
        <!-- Sub-headline -->
        <p class="sub-headline">Login to manage officer approvals and system administration</p>

        <!-- Display flashdata error message if exists -->
        <?php if (session()->getFlashdata('error')): ?>
          <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i>
            <?= session()->getFlashdata('error') ?>
          </div>
        <?php endif; ?>

        <!-- Login Form -->
        <form method="post" action="<?= base_url('super-admin/loginPost') ?>" class="login-form">
          <div class="form-group">
            <label for="username">Username</label>
            <input 
              type="text" 
              name="username" 
              id="username" 
              class="form-control" 
              placeholder="Enter your username"
              required
              autofocus
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

          <button type="submit" class="btn-login">
            <i class="fas fa-shield-alt"></i>
            Login as Super Admin
          </button>

          <div class="signup-link">
            <span>Need regular admin access?</span>
            <a href="<?= base_url('/') ?>" class="signup-btn">Go to Admin Portal</a>
          </div>
        </form>
      </div>
    </div>

    <!-- Right Column: Illustration -->
    <div class="login-right">
      <div class="illustration-container">
        <div class="payment-illustration">
          <div class="payment-card">
            <div class="card-chip"></div>
            <div class="card-number">**** **** **** 0001</div>
            <div class="card-holder">SUPER ADMIN</div>
            <div class="card-expiry">12/25</div>
          </div>
          
          <div class="floating-icon icon-1">
            <i class="fas fa-shield-alt"></i>
          </div>
          <div class="floating-icon icon-2">
            <i class="fas fa-user-check"></i>
          </div>
          <div class="floating-icon icon-3">
            <i class="fas fa-crown"></i>
          </div>
          <div class="floating-icon icon-4">
            <i class="fas fa-key"></i>
          </div>
          <div class="floating-icon icon-5">
            <i class="fas fa-lock"></i>
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

