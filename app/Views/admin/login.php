<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login | ClearPay</title>
  <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
    integrity="sha512-KNUXkD2pCK9lEp+vZP1HcFhRmtYCrgS1uqk+OBrWiDEwVKHm2VZCs4wzAHzkCmqkPFrd59KPZoEYL2cbW8M1dA=="
    crossorigin="anonymous"
    referrerpolicy="no-referrer"
  />
  <link href="<?= base_url('css/login.css') ?>" rel="stylesheet">
</head>
<body class="main-container">
  <div class="left">
    <h2 class="content">This area will be for an image</h2>
  </div>
  <div class="right">
    <div class="content">
        <h3 class="text-center">ClearPay Login</h3>

        <!-- Display flashdata error message if exists -->
        <?php if (session()->getFlashdata('error')): ?>
          <div class="alert alert-danger">
            <?= session()->getFlashdata('error') ?>
          </div>
        <?php endif; ?>

        <form class="method" method="post" action="<?= base_url('loginPost') ?>">
          <div class="username-field">
            <label for="username" class="form-label">Username</label>
            <input type="text" name="username" class="form-control" required>
          </div>

          <div class="password-field">
            <label for="password" class="form-label">Password</label>
            <input type="password" name="password" class="form-control" required>
          </div>

          <button type="submit" class="btn btn-primary">Login</button>
        </form>
      </div>
  </div>
</body>
</html>
