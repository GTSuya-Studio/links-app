<?php
// ============================================================
// admin/login.php — Login page
// ============================================================
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

if (is_logged_in()) {
    header('Location: ' . BASE_URL . '/admin/');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    if (login($password)) {
        header('Location: ' . BASE_URL . '/admin/');
        exit;
    }
    $error = t('error_wrong_password');
    // Anti-brute-force delay
    sleep(1);
}

$siteTitle = setting('site_title', 'Links');
?>
<!DOCTYPE html>
<html lang="<?= h(current_lang_code()) ?>">
<head>
  <meta charset="UTF-8">
  <?= early_theme_script() ?>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex, nofollow">
  <title>Admin — <?= h($siteTitle) ?></title>
  <link rel="icon" href="<?= h(site_icon_url()) ?>">
  <link rel="stylesheet" href="<?= asset_url('assets/css/style.css') ?>">
  <link rel="stylesheet" href="<?= asset_url('assets/css/admin.css') ?>">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
<div class="login-wrapper">
  <div class="login-card">
    <img src="<?= h(site_icon_url()) ?>" alt="" style="width:48px;height:48px;object-fit:contain;border-radius:10px;margin:0 auto .5rem;display:block">
    <h1 class="login-title"><?= h($siteTitle) ?></h1>
    <p class="login-sub"><?= h(t('login_subtitle')) ?></p>

    <?php if ($error): ?>
      <div class="alert alert-danger"><?= h($error) ?></div>
    <?php endif; ?>

    <form method="POST">
      <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">

      <div class="form-group">
        <label class="form-label" for="password"><?= h(t('password_label')) ?></label>
        <input
          class="form-control"
          type="password"
          id="password"
          name="password"
          autofocus
          autocomplete="current-password"
          placeholder="••••••••"
        >
      </div>

      <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;margin-top:.5rem">
        <?= h(t('login_button')) ?>
      </button>
    </form>

    <div style="text-align:center;margin-top:1.25rem">
      <a href="<?= BASE_URL ?>/" style="font-size:.8rem;color:var(--text-3)"><?= h(t('back_to_site')) ?></a>
    </div>
  </div>
</div>
<script src="<?= asset_url('assets/js/app.js') ?>"></script>
<script src="<?= asset_url('assets/js/admin.js') ?>"></script>
</body>
</html>
