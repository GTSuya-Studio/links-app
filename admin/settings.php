<?php
// ============================================================
// admin/settings.php — Site settings
// ============================================================
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();

$msg   = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';

    if ($action === 'site') {
        setting_set('site_title',         trim($_POST['site_title'] ?? '') ?: 'Links');
        setting_set('site_subtitle',      trim($_POST['site_subtitle'] ?? ''));

        // Site icon upload
        if (!empty($_FILES['site_icon']) && $_FILES['site_icon']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['site_icon'];
            $allowedExt = ['png' => 'image/png', 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'svg' => 'image/svg+xml'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

            if ($file['size'] > 2 * 1024 * 1024) {
                $error = t('err_file_too_large');
            } elseif (!isset($allowedExt[$ext])) {
                $error = t('err_unsupported_format');
            } else {
                $finfo    = finfo_open(FILEINFO_MIME_TYPE);
                $mimeType = finfo_file($finfo, $file['tmp_name']);
                finfo_close($finfo);

                $validMime = ($ext === 'svg')
                    ? in_array($mimeType, ['image/svg+xml', 'text/plain', 'text/xml'])
                    : ($mimeType === $allowedExt[$ext]);

                if (!$validMime) {
                    $error = t('err_file_mismatch');
                } else {
                    $uploadDir = __DIR__ . '/../assets/uploads/';
                    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

                    // Remove the old icon if it exists
                    $oldIcon = setting('site_icon', '');
                    if ($oldIcon) {
                        $oldPath = __DIR__ . '/../' . $oldIcon;
                        if (is_file($oldPath)) @unlink($oldPath);
                    }

                    $newName = 'site-icon-' . time() . '.' . $ext;
                    if (move_uploaded_file($file['tmp_name'], $uploadDir . $newName)) {
                        setting_set('site_icon', 'assets/uploads/' . $newName);
                    } else {
                        $error = t('err_file_save');
                    }
                }
            }
        }

        if (!$error) $msg = t('msg_settings_saved');
    }

    if ($action === 'display') {
        setting_set('show_descriptions',  isset($_POST['show_descriptions']) ? '1' : '0');
        setting_set('public_search_enabled', isset($_POST['public_search_enabled']) ? '1' : '0');
        $langCode = $_POST['site_language'] ?? 'fr';
        if (isset(available_languages()[$langCode])) {
            setting_set('site_language', $langCode);
        }
        $msg = t('msg_settings_saved');
    }

    if ($action === 'remove_icon') {
        $oldIcon = setting('site_icon', '');
        if ($oldIcon) {
            $oldPath = __DIR__ . '/../' . $oldIcon;
            if (is_file($oldPath)) @unlink($oldPath);
            setting_set('site_icon', '');
        }
        $msg = t('msg_icon_removed');
    }

    if ($action === 'password') {
        $current = $_POST['current_password'] ?? '';
        $new     = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if (hash('sha256', $current) !== setting('admin_password_hash')) {
            $error = t('err_wrong_current_password');
        } elseif (strlen($new) < 8) {
            $error = t('err_password_too_short');
        } elseif ($new !== $confirm) {
            $error = t('err_passwords_mismatch');
        } else {
            setting_set('admin_password_hash', hash('sha256', $new));
            $msg = t('msg_password_changed');
        }
    }

    if ($action === 'pin') {
        $pin     = trim($_POST['pin_code'] ?? '');
        $confirm = trim($_POST['pin_code_confirm'] ?? '');

        if (!preg_match('/^\d{4}$/', $pin)) {
            $error = t('err_pin_format');
        } elseif ($pin !== $confirm) {
            $error = t('err_pin_mismatch');
        } else {
            setting_set('pin_code_hash', hash('sha256', $pin));
            $msg = t('msg_pin_saved');
        }
    }

    if ($action === 'remove_pin') {
        setting_set('pin_code_hash', '');
        // Unlock all categories to avoid a lockout when no PIN is set
        db()->exec('UPDATE categories SET is_locked = 0');
        $msg = t('msg_pin_removed');
    }

    if ($action === 'pin_duration') {
        $minutes = (int)($_POST['pin_unlock_duration'] ?? 45);
        if ($minutes < 1 || $minutes > 1440) {
            $error = t('err_pin_duration_range');
        } else {
            setting_set('pin_unlock_duration', (string)$minutes);
            $msg = t('msg_pin_duration_saved');
        }
    }
}

$siteTitle         = setting('site_title', 'Links');
$siteSubtitle      = setting('site_subtitle', 'Mes marque-pages');
$showDescriptions  = setting('show_descriptions', '1');
$siteIcon          = setting('site_icon', '');
$publicSearchEnabled = setting('public_search_enabled', '0');
$pinUnlockDuration = (int)setting('pin_unlock_duration', '45');
$pinSet            = setting('pin_code_hash', '') !== '';
$tab               = $_GET['tab'] ?? 'personnaliser';
if (!in_array($tab, ['personnaliser', 'securite', 'systeme'], true)) $tab = 'personnaliser';

require __DIR__ . '/partials/header.php';
?>

<?php
$tabLabels = [
  'personnaliser' => [t('tab_personalize'), t('tab_personalize_sub')],
  'securite'      => [t('tab_security'), t('tab_security_sub')],
  'systeme'       => [t('tab_system'), t('tab_system_sub')],
];
[$tabTitle, $tabSubtitle] = $tabLabels[$tab];
?>
<div class="topbar">
  <div>
    <h1 class="page-title"><?= h($tabTitle) ?></h1>
    <p class="page-subtitle"><?= h($tabSubtitle) ?></p>
  </div>
</div>

<?php if ($msg): ?><div class="alert alert-success"><?= h($msg) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-danger"><?= h($error) ?></div><?php endif; ?>

<?php if ($tab === 'personnaliser'): ?>
<!-- Site settings -->
<div class="card">
  <div class="card-header"><span class="card-title" style="display:flex;align-items:center;gap:.5rem">
    <?= icon('globe', 16, 'style="flex-shrink:0"') ?>
    <?= h(t('card_site_info')) ?>
  </span></div>
  <form method="POST" action="?tab=<?= h($tab) ?>" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
    <input type="hidden" name="action" value="site">
    <div class="form-group">
      <label class="form-label"><?= h(t('field_site_icon')) ?></label>
      <div style="display:flex;align-items:center;gap:1rem">
        <div style="width:48px;height:48px;border-radius:var(--radius-sm);border:1px solid var(--border);display:flex;align-items:center;justify-content:center;overflow:hidden;background:var(--bg);flex-shrink:0">
          <img src="<?= h(site_icon_url()) ?>" alt="" style="width:100%;height:100%;object-fit:contain">
        </div>
        <div style="flex:1">
          <input class="form-control" type="file" name="site_icon" accept=".png,.jpg,.jpeg,.svg">
          <div class="form-hint"><?= h(t('site_icon_hint')) ?></div>
        </div>
      </div>
      <?php if ($siteIcon): ?>
        <button type="submit" form="form-remove-icon" class="btn btn-danger btn-sm" style="margin-top:.6rem"><?= h(t('remove_icon')) ?></button>
      <?php endif; ?>
    </div>
    <div class="form-group">
      <label class="form-label"><?= h(t('field_site_title')) ?></label>
      <input class="form-control" type="text" name="site_title"
             value="<?= h($siteTitle) ?>" placeholder="Links" required>
      <div class="form-hint"><?= h(t('site_title_hint')) ?></div>
    </div>
    <div class="form-group">
      <label class="form-label"><?= h(t('field_site_subtitle')) ?></label>
      <input class="form-control" type="text" name="site_subtitle"
             value="<?= h($siteSubtitle) ?>" placeholder="<?= h(t('placeholder_site_subtitle')) ?>">
    </div>
    <button type="submit" class="btn btn-primary"><?= h(t('save')) ?></button>
  </form>
  <form method="POST" id="form-remove-icon" action="?tab=<?= h($tab) ?>" style="display:none">
    <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
    <input type="hidden" name="action" value="remove_icon">
  </form>
</div>

<!-- Display -->
<div class="card">
  <div class="card-header"><span class="card-title" style="display:flex;align-items:center;gap:.5rem">
    <?= icon('eye', 16, 'style="flex-shrink:0"') ?>
    <?= h(t('card_display')) ?>
  </span></div>
  <form method="POST" action="?tab=<?= h($tab) ?>">
    <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
    <input type="hidden" name="action" value="display">
    <div class="form-group">
      <label style="display:flex;align-items:center;gap:.6rem;cursor:pointer">
        <input type="checkbox" name="show_descriptions"
               <?= $showDescriptions === '1' ? 'checked' : '' ?>
               style="width:16px;height:16px;accent-color:var(--accent)">
        <span class="form-label" style="margin:0"><?= h(t('show_descriptions_label')) ?></span>
      </label>
      <div class="form-hint"><?= h(t('show_descriptions_hint')) ?></div>
    </div>
    <div class="form-group">
      <label style="display:flex;align-items:center;gap:.6rem;cursor:pointer">
        <input type="checkbox" name="public_search_enabled"
               <?= $publicSearchEnabled === '1' ? 'checked' : '' ?>
               style="width:16px;height:16px;accent-color:var(--accent)">
        <span class="form-label" style="margin:0"><?= h(t('public_search_label')) ?></span>
      </label>
      <div class="form-hint"><?= h(t('public_search_hint')) ?></div>
    </div>
    <div class="form-group">
      <label class="form-label"><?= h(t('language_label')) ?></label>
      <select class="form-control" name="site_language" style="max-width:260px">
        <?php foreach (available_languages() as $langCode => $langName): ?>
          <option value="<?= h($langCode) ?>" <?= $langCode === current_lang_code() ? 'selected' : '' ?>><?= h($langName) ?></option>
        <?php endforeach; ?>
      </select>
      <div class="form-hint"><?= h(t('language_hint')) ?></div>
    </div>
    <button type="submit" class="btn btn-primary"><?= h(t('save')) ?></button>
  </form>
</div>
<?php endif; ?>

<?php if ($tab === 'securite'): ?>
<!-- Password change -->
<div class="card">
  <div class="card-header"><span class="card-title" style="display:flex;align-items:center;gap:.5rem">
    <?= icon('lock', 16, 'style="flex-shrink:0"') ?>
    <?= h(t('card_change_password')) ?>
  </span></div>
  <form method="POST" action="?tab=<?= h($tab) ?>">
    <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
    <input type="hidden" name="action" value="password">
    <div class="form-group">
      <label class="form-label"><?= h(t('current_password')) ?></label>
      <input class="form-control" type="password" name="current_password" required autocomplete="current-password">
    </div>
    <div class="form-row">
      <div class="form-group">
        <label class="form-label"><?= h(t('new_password')) ?></label>
        <input class="form-control" type="password" name="new_password" required
               autocomplete="new-password" minlength="8">
        <div class="form-hint"><?= h(t('new_password_hint')) ?></div>
      </div>
      <div class="form-group">
        <label class="form-label"><?= h(t('confirm_password')) ?></label>
        <input class="form-control" type="password" name="confirm_password" required autocomplete="new-password">
      </div>
    </div>
    <button type="submit" class="btn btn-primary"><?= h(t('change_password_button')) ?></button>
  </form>
</div>

<!-- PIN code (protected categories) -->
<div class="card">
  <div class="card-header"><span class="card-title" style="display:flex;align-items:center;gap:.5rem">
    <?= icon('keypad', 16, 'style="flex-shrink:0"') ?>
    <?= h(t('card_pin')) ?>
  </span></div>
  <p style="font-size:.85rem;color:var(--text-3);margin-bottom:1rem">
    <?= h(t('pin_description')) ?>
  </p>

  <form method="POST" action="?tab=<?= h($tab) ?>" style="margin-bottom:1.5rem;padding-bottom:1.25rem;border-bottom:1px solid var(--border)">
    <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
    <input type="hidden" name="action" value="pin_duration">
    <div class="form-group">
      <label class="form-label"><?= h(t('pin_duration_label')) ?></label>
      <div style="display:flex;align-items:center;gap:.6rem">
        <input class="form-control" type="number" name="pin_unlock_duration"
               value="<?= h((string)$pinUnlockDuration) ?>" min="1" max="1440" style="max-width:120px">
        <span style="font-size:.85rem;color:var(--text-3)"><?= h(t('minutes')) ?></span>
        <button type="submit" class="btn btn-ghost btn-sm"><?= h(t('save')) ?></button>
      </div>
      <div class="form-hint"><?= h(t('pin_duration_hint')) ?></div>
    </div>
  </form>

  <form method="POST" action="?tab=<?= h($tab) ?>">
    <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
    <input type="hidden" name="action" value="pin">
    <div class="form-row">
      <div class="form-group">
        <label class="form-label"><?= h($pinSet ? t('pin_new_label') : t('pin_label')) ?></label>
        <input class="form-control" type="text" name="pin_code" inputmode="numeric" pattern="\d{4}"
               maxlength="4" placeholder="0000" autocomplete="off">
        <div class="form-hint"><?= h(t('pin_hint_digits')) ?></div>
      </div>
      <div class="form-group">
        <label class="form-label"><?= h(t('confirm_password')) ?></label>
        <input class="form-control" type="text" name="pin_code_confirm" inputmode="numeric" pattern="\d{4}"
               maxlength="4" placeholder="0000" autocomplete="off">
      </div>
    </div>
    <div style="display:flex;gap:.6rem;align-items:center">
      <button type="submit" class="btn btn-primary"><?= h($pinSet ? t('change_pin_button') : t('set_pin_button')) ?></button>
      <?php if ($pinSet): ?>
        <span style="font-size:.8rem;color:var(--success);display:inline-flex;align-items:center;gap:.3rem"><?= icon('check', 14) ?> <?= h(t('pin_active')) ?></span>
      <?php else: ?>
        <span style="font-size:.8rem;color:var(--text-3)"><?= h(t('pin_none')) ?></span>
      <?php endif; ?>
    </div>
  </form>
  <?php if ($pinSet): ?>
    <form method="POST" action="?tab=<?= h($tab) ?>" style="margin-top:.75rem">
      <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
      <input type="hidden" name="action" value="remove_pin">
      <button type="submit" class="btn btn-danger btn-sm"
        onclick="return confirm(<?= json_encode(t('confirm_remove_pin')) ?>)">
        <?= h(t('remove_pin_button')) ?>
      </button>
    </form>
  <?php endif; ?>
</div>
<?php endif; ?>

<?php if ($tab === 'systeme'): ?>
<!-- Technical info -->
<div class="card" style="border-color:var(--border)">
  <div class="card-header"><span class="card-title" style="display:flex;align-items:center;gap:.5rem">
    <?= icon('info-circle', 16, 'style="flex-shrink:0"') ?>
    <?= h(t('card_system_info')) ?>
  </span></div>
  <table class="data-table">
    <tr><th style="width:200px"><?= h(t('sys_php')) ?></th><td><?= PHP_VERSION ?></td></tr>
    <tr><th><?= h(t('sys_base_url')) ?></th><td><code><?= h(BASE_URL) ?></code></td></tr>
    <tr><th><?= h(t('sys_mysql')) ?></th><td><?= db()->query('SELECT VERSION()')->fetchColumn() ?></td></tr>
    <tr><th><?= h(t('sys_default_password')) ?></th>
        <td>
          <?php if (setting('admin_password_hash') === hash('sha256', 'admin')): ?>
            <span style="color:var(--danger);font-weight:600;display:inline-flex;align-items:center;gap:.4rem"><?= icon('warning', 16) ?> <?= h(t('default_password_warning')) ?></span>
          <?php else: ?>
            <span style="color:var(--success);display:inline-flex;align-items:center;gap:.3rem"><?= icon('check', 14) ?> <?= h(t('custom_password_ok')) ?></span>
          <?php endif; ?>
        </td>
    </tr>
  </table>
</div>

<!-- Data export -->
<div class="card">
  <div class="card-header"><span class="card-title" style="display:flex;align-items:center;gap:.5rem">
    <?= icon('download', 16, 'style="flex-shrink:0"') ?>
    <?= h(t('card_export')) ?>
  </span></div>
  <p style="font-size:.85rem;color:var(--text-3);margin-bottom:1rem">
    <?= h(t('export_description')) ?>
  </p>
  <a href="<?= BASE_URL ?>/admin/export.php" class="btn btn-ghost"><?= h(t('download_export')) ?></a>
</div>
<?php endif; ?>

<?php require __DIR__ . '/partials/footer.php'; ?>
