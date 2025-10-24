<header class="header">
  <h1>Dashboard</h1>
  <div class="user-menu">
    <span>Welcome, <?= session('username') ?></span>
    <a href="<?= base_url('logout') ?>" class="btn btn-sm btn-outline-danger ms-3">Logout</a>
  </div>
</header>
