<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Payer Login | ClearPay</title>
  <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
    crossorigin="anonymous"
    referrerpolicy="no-referrer"
  />
  <link href="<?= base_url('css/auth-shared.css') ?>" rel="stylesheet">
  <link href="<?= base_url('css/auth-login.css') ?>" rel="stylesheet">
</head>
<body>
  <div class="login-container">
    <!-- Left Column: Login Form -->
    <div class="login-left">
      <!-- Top Navigation -->
      <nav class="login-nav">
        <a href="<?= base_url('/') ?>" class="nav-link active">Home</a>
        <a href="<?= base_url('register') ?>" class="nav-link">Sign Up</a>
        <a href="#" class="nav-link">Help</a>
      </nav>

      <div class="login-content">
        <!-- Branding -->
        <div class="branding">
          <h1>ClearPay</h1>
        </div>

        <!-- Headline -->
        <h2 class="headline">Payer Portal</h2>
        
        <!-- Sub-headline -->
        <p class="sub-headline">Access your payment information and history</p>

        <!-- Display flashdata error message if exists -->
        <?php if (session()->getFlashdata('error')): ?>
          <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i>
            <?= session()->getFlashdata('error') ?>
          </div>
        <?php endif; ?>

        <!-- Login Form -->
        <form method="post" action="<?= base_url('payer/loginPost') ?>" class="login-form">
          <div class="form-group">
            <label for="payer_id">Payer ID</label>
            <input 
              type="text" 
              name="payer_id" 
              id="payer_id" 
              class="form-control" 
              placeholder="Enter your Payer ID"
              value="<?= old('payer_id') ?>"
              required
            >
            <i class="fas fa-id-card input-icon"></i>
          </div>

          <div class="form-group">
            <label for="email_address">Email Address</label>
            <input 
              type="email" 
              name="email_address" 
              id="email_address" 
              class="form-control" 
              placeholder="Enter your email address"
              value="<?= old('email_address') ?>"
              required
            >
            <i class="fas fa-envelope input-icon"></i>
          </div>

          <button type="submit" class="btn-login">
            <i class="fas fa-sign-in-alt"></i>
            Login
          </button>

          <div class="signup-link">
            <span>Admin Access?</span>
            <a href="<?= base_url('/') ?>" class="signup-btn">Admin Login</a>
          </div>
        </form>

        <!-- Note -->
        <div class="alert alert-info mt-3" style="padding: 0.75rem;">
          <i class="fas fa-info-circle me-2"></i>
          <small>Use your Payer ID and Email to access your account</small>
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
</body>
</html>
