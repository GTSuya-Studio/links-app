<?php
// ============================================================
// index.php — Public page (category navigation)
// ============================================================
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';

$cats = db()->query('SELECT * FROM categories ORDER BY sort_order, name')->fetchAll();

// Active category: passed via GET or selected via JS (fallback = first one)
$activeCatId = (int)($_GET['cat'] ?? 0);
if ($activeCatId) {
    $activeCat = db()->prepare('SELECT * FROM categories WHERE id=?');
    $activeCat->execute([$activeCatId]);
    $activeCat = $activeCat->fetch();
    if (!$activeCat) { $activeCatId = 0; }
}
if (!$activeCatId && !empty($cats)) {
    $activeCat   = $cats[0];
    $activeCatId = $activeCat['id'];
}

// Security: if the category is locked and not yet unlocked, load neither
// subcategories nor links — nothing should leak into the HTML.
// If no PIN is configured globally, never block (anti-lockout safeguard).
$pinConfigured   = setting('pin_code_hash', '') !== '';
$isLocked        = $activeCat && !empty($activeCat['is_locked']) && $pinConfigured;
$isUnlocked      = $isLocked ? is_category_unlocked($activeCatId) : true;
$showPinScreen   = $isLocked && !$isUnlocked;

// Subcategories + links of the active category
$subcats = [];
if ($activeCatId && !$showPinScreen) {
    $subcats = get_subcats_with_links($activeCatId);
}

$siteTitle        = setting('site_title', 'Links');
$siteSubtitle     = setting('site_subtitle', 'Mes marque-pages');
$showDescriptions = setting('show_descriptions', '1') === '1';
$publicSearchEnabled = setting('public_search_enabled', '0') === '1';

// Public search: systematically excludes any locked category, regardless
// of whether it's unlocked for the current session — a search result must
// never act as a shortcut to bypass the PIN screen.
$searchQuery   = $publicSearchEnabled ? trim($_GET['search'] ?? '') : '';
$searchResults = [];
if ($searchQuery !== '') {
    $stmt = db()->prepare(
        "SELECT l.*, c.id AS cat_id
         FROM links l
         JOIN subcategories s ON s.id = l.subcategory_id
         JOIN categories c ON c.id = s.category_id
         WHERE c.is_locked = 0
           AND (l.title LIKE ? OR l.url LIKE ? OR l.description LIKE ?)
         ORDER BY c.sort_order, s.sort_order, l.sort_order"
    );
    $like = '%' . $searchQuery . '%';
    $stmt->execute([$like, $like, $like]);
    $searchResults = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="<?= h(current_lang_code()) ?>">
<head>
  <meta charset="UTF-8">
  <?= early_theme_script() ?>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex, nofollow">
  <meta name="description" content="<?= h($siteSubtitle ?: $siteTitle) ?>">
  <title><?= $activeCat ? h($activeCat['name']).' — ' : '' ?><?= h($siteTitle) ?></title>
  <link rel="icon" href="<?= h(site_icon_url()) ?>">
  <link rel="stylesheet" href="<?= asset_url('assets/css/style.css') ?>">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
<div class="app-wrapper">

  <aside class="sidebar">
    <div class="sidebar-header">
      <div style="display:flex;align-items:center;gap:.6rem">
        <img src="<?= h(site_icon_url()) ?>" alt="" style="width:28px;height:28px;object-fit:contain;border-radius:6px;flex-shrink:0">
        <div class="site-title"><?= h($siteTitle) ?></div>
      </div>
      <div class="site-subtitle"><?= h($siteSubtitle) ?></div>
    </div>

    <nav class="sidebar-nav" aria-label="Catégories">
      <?php if ($publicSearchEnabled): ?>
        <div style="padding:.6rem 1rem .8rem">
          <form method="GET" action="<?= BASE_URL ?>/" style="position:relative">
            <input type="search" name="search" value="<?= h($searchQuery) ?>"
                   placeholder="<?= h(t('search_placeholder')) ?>"
                   class="search-input">
            <?php if ($searchQuery !== ''): ?>
              <a href="<?= BASE_URL ?>/" class="search-clear" title="<?= h(t('clear')) ?>"><?= icon('close', 13) ?></a>
            <?php endif; ?>
          </form>
        </div>
      <?php endif; ?>
      <?php foreach ($cats as $cat): ?>
        <a class="nav-item nav-cat <?= $cat['id'] === $activeCatId ? 'active' : '' ?>"
           href="?cat=<?= $cat['id'] ?>"
           data-cat-id="<?= $cat['id'] ?>"
           style="--cat-color:<?= h($cat['color'] ?? '#6366f1') ?>">
          <span style="flex:1"><?= h($cat['name']) ?></span>
          <?php if (!empty($cat['is_locked'])): ?>
            <?= icon('lock', 13, 'style="flex-shrink:0;opacity:.6"') ?>
          <?php endif; ?>
        </a>
      <?php endforeach; ?>
    </nav>

    <div class="sidebar-footer">
      <?= theme_toggle_button() ?>
      <a href="<?= BASE_URL ?>/admin/<?= $activeCatId ? '?cat='.$activeCatId : '' ?>" title="<?= h(t('administration')) ?>" style="color:var(--text-2);display:flex;line-height:0">
        <?= icon('lock', 19) ?>
      </a>
    </div>
  </aside>

  <main class="main">
    <?php if ($searchQuery !== ''): ?>
      <div style="margin-bottom:1.25rem">
        <h2 style="font-size:1.1rem;font-weight:700"><?= h(t('search_results_for', [':query' => $searchQuery])) ?></h2>
        <p style="font-size:.82rem;color:var(--text-3);margin-top:.2rem"><?= h(t('search_results_count', [':count' => (string)count($searchResults)])) ?></p>
      </div>
      <?php if (empty($searchResults)): ?>
        <p style="color:var(--text-3);font-size:.875rem"><?= h(t('no_results')) ?></p>
      <?php else: ?>
        <ul class="links-list">
          <?php foreach ($searchResults as $link): ?>
            <li class="link-item">
              <?= render_public_link($link, $showDescriptions) ?>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>

    <?php elseif (empty($cats)): ?>
      <div class="empty">
        <div class="empty-icon">
          <?= icon('bookmark', 40) ?>
        </div>
        <p><?= h(t('no_links_yet')) ?><br>
           <a href="<?= BASE_URL ?>/admin/"><?= h(t('add_from_admin')) ?></a>
        </p>
      </div>

    <?php elseif ($showPinScreen): ?>
      <div class="pin-screen">
        <div class="pin-icon">
          <?= icon('lock', 32) ?>
        </div>
        <h2 class="pin-title"><?= h($activeCat['name']) ?></h2>
        <p class="pin-sub"><?= h(t('pin_protected_message')) ?></p>
        <div class="pin-inputs" id="pin-inputs">
          <input type="text" inputmode="numeric" maxlength="1" class="pin-digit" data-i="0">
          <input type="text" inputmode="numeric" maxlength="1" class="pin-digit" data-i="1">
          <input type="text" inputmode="numeric" maxlength="1" class="pin-digit" data-i="2">
          <input type="text" inputmode="numeric" maxlength="1" class="pin-digit" data-i="3">
        </div>
        <p class="pin-error" id="pin-error" style="display:none"><?= h(t('pin_incorrect')) ?></p>
      </div>
      <script>
        (function() {
          const catId  = <?= $activeCatId ?>;
          const inputs = Array.from(document.querySelectorAll('.pin-digit'));
          const errEl  = document.getElementById('pin-error');

          inputs.forEach(function(input, idx) {
            input.addEventListener('input', function() {
              input.value = input.value.replace(/\D/g, '').slice(0, 1);
              if (input.value && idx < inputs.length - 1) {
                inputs[idx + 1].focus();
              }
              if (inputs.every(function(i) { return i.value.length === 1; })) {
                submitPin();
              }
            });
            input.addEventListener('keydown', function(e) {
              if (e.key === 'Backspace' && !input.value && idx > 0) {
                inputs[idx - 1].focus();
              }
            });
          });

          if (inputs[0]) inputs[0].focus();

          function submitPin() {
            const pin = inputs.map(function(i) { return i.value; }).join('');
            errEl.style.display = 'none';
            fetch('<?= BASE_URL ?>/verify-pin.php', {
              method: 'POST',
              headers: { 'Content-Type': 'application/json' },
              body: JSON.stringify({ pin: pin, cat_id: catId })
            })
              .then(function(r) { return r.json(); })
              .then(function(data) {
                if (data.ok) {
                  window.location.reload();
                } else {
                  errEl.style.display = 'block';
                  inputs.forEach(function(i) { i.value = ''; });
                  inputs[0].focus();
                }
              })
              .catch(function() {
                errEl.textContent = <?= json_encode(t('connection_error_retry')) ?>;
                errEl.style.display = 'block';
              });
          }
        })();
      </script>

    <?php elseif ($activeCat): ?>

      <?php if (empty($subcats)): ?>
        <p style="color:var(--text-3);font-size:.875rem;margin-top:1rem">
          <?= h(t('no_subcategories')) ?>
        </p>
      <?php else: ?>
        <div class="masonry">
          <?php foreach ($subcats as $sub): ?>
            <div class="subcategory" style="--sub-color:<?= h($sub['color'] ?? '#6366f1') ?>">
              <div class="subcategory-title"><?= h($sub['name']) ?></div>
              <?php if (empty($sub['links'])): ?>
                <p style="font-size:.8rem;color:var(--text-3);padding:.25rem .5rem"><?= h(t('no_links')) ?></p>
              <?php else: ?>
                <ul class="links-list">
                  <?php foreach ($sub['links'] as $link): ?>
                    <li class="link-item">
                      <?= render_public_link($link, $showDescriptions) ?>
                    </li>
                  <?php endforeach; ?>
                </ul>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    <?php endif; ?>
  </main>

</div>
<script>
// Remember the last visited category (key shared with the admin)
const CAT_KEY = 'links_last_cat_shared';
const currentCat = <?= $activeCatId ?>;
if (currentCat) localStorage.setItem(CAT_KEY, currentCat);

// On load with no parameters, redirect to the last category
if (!window.location.search) {
  const last = localStorage.getItem(CAT_KEY);
  if (last) { window.location.replace('?cat=' + last); }
}
</script>
<script src="<?= asset_url('assets/js/app.js') ?>"></script>
</body>
</html>
