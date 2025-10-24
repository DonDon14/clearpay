<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= esc($title ?? 'Dashboard') ?></title>
  <link href="<?= base_url('css/global.css') ?>" rel="stylesheet">
  <link href="<?= base_url('css/header.css') ?>" rel="stylesheet">
  <link href="<?= base_url('css/sidebar.css') ?>" rel="stylesheet">
  <link href="<?= base_url('css/footer.css') ?>" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
  <div class="layout">
    <?= $this->include('partials/sidebar') ?>
    <div class="main">
      <?= $this->include('partials/header') ?>
      <main class="content">
        <?= $this->renderSection('content') ?>
      </main>
      <?= $this->include('partials/footer') ?>
    </div>
  </div>
</body>
</html>
