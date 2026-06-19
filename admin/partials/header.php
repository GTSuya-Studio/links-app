<!DOCTYPE html>
<html lang="<?= h(current_lang_code()) ?>">
<head>
  <meta charset="UTF-8">
  <?= early_theme_script() ?>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex, nofollow">
  <meta name="csrf" content="<?= h(csrf_token()) ?>">
  <title>Admin — <?= h($siteTitle ?? 'Links') ?></title>
  <link rel="icon" href="<?= h(site_icon_url()) ?>">
  <link rel="stylesheet" href="<?= asset_url('assets/css/style.css') ?>">
  <link rel="stylesheet" href="<?= asset_url('assets/css/admin.css') ?>">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
<div class="admin-layout">
  <aside class="sidebar admin-sidebar">
    <div class="sidebar-header">
      <div style="display:flex;align-items:center;gap:.6rem">
        <img src="<?= h(site_icon_url()) ?>" alt="" style="width:28px;height:28px;object-fit:contain;border-radius:6px;flex-shrink:0">
        <div class="site-title"><?= h(setting('site_title', 'Links')) ?></div>
      </div>
      <div class="site-subtitle"><?= h(t('settings')) ?></div>
    </div>
    <nav class="sidebar-nav">
      <a class="nav-item <?= $tab==='personnaliser'?'active':'' ?>" href="<?= BASE_URL ?>/admin/settings.php?tab=personnaliser"><?= h(t('tab_personalize')) ?></a>
      <a class="nav-item <?= $tab==='securite'?'active':'' ?>" href="<?= BASE_URL ?>/admin/settings.php?tab=securite"><?= h(t('tab_security')) ?></a>
      <a class="nav-item <?= $tab==='systeme'?'active':'' ?>" href="<?= BASE_URL ?>/admin/settings.php?tab=systeme"><?= h(t('tab_system')) ?></a>
    </nav>
    <div class="sidebar-footer">
      <?= theme_toggle_button() ?>
      <div style="display:flex;gap:.6rem;align-items:center">
        <a href="<?= BASE_URL ?>/" title="<?= h(t('view_site')) ?>" style="color:var(--text-2);display:flex;line-height:0">
          <?= icon('eye', 19) ?>
        </a>
        <a href="<?= BASE_URL ?>/admin/" title="<?= h(t('administration')) ?>" style="color:var(--text-2);display:flex;line-height:0">
          <?= icon('lock', 19) ?>
        </a>
        <a href="<?= BASE_URL ?>/admin/?logout=1" onclick="return confirm(<?= json_encode(t('confirm_logout')) ?>)" title="<?= h(t('logout')) ?>" style="color:var(--text-2);display:flex;line-height:0">
          <?= icon('logout', 19) ?>
        </a>
      </div>
    </div>
  </aside>
  <main class="main">
