<?php
// ============================================================
// admin/index.php — Main view (categories + inline editing)
// ============================================================
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();

if (isset($_GET['logout'])) {
    logout();
    header('Location: ' . BASE_URL . '/admin/login.php');
    exit;
}

// ---- AJAX reorder API (fetch POST with X-Requested-With) ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
    header('Content-Type: application/json');
    $body = json_decode(file_get_contents('php://input'), true);
    if (!$body || !hash_equals(csrf_token(), $body['csrf'] ?? '')) {
        http_response_code(403); echo json_encode(['error'=>'csrf']); exit;
    }
    $type   = $body['type'] ?? '';
    $ids    = array_map('intval', $body['ids'] ?? []);
    $tables = ['categories'=>'categories','subcategories'=>'subcategories','links'=>'links'];
    if (isset($tables[$type]) && $ids) {
        $stmt = db()->prepare("UPDATE {$tables[$type]} SET sort_order=? WHERE id=?");
        foreach ($ids as $i => $id) $stmt->execute([$i + 1, $id]);
        echo json_encode(['ok'=>true]);
    } else {
        echo json_encode(['error'=>'invalid']);
    }
    exit;
}

// ---- Category CRUD ----------------------------------------
$msg = $error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';

    if ($action === 'add_cat') {
        $name     = trim($_POST['name'] ?? '');
        $color    = valid_color($_POST['color'] ?? '');
        $isLocked = isset($_POST['is_locked']) ? 1 : 0;
        if ($name === '') { $error = t('err_name_required'); } else {
            $sl = slug($name);
            $ex = db()->prepare('SELECT id FROM categories WHERE slug=?'); $ex->execute([$sl]);
            if ($ex->fetch()) $sl .= '-'.time();
            $max = db()->query('SELECT COALESCE(MAX(sort_order),0)+1 FROM categories')->fetchColumn();
            db()->prepare('INSERT INTO categories (name,slug,color,is_locked,sort_order) VALUES (?,?,?,?,?)')
                ->execute([$name, $sl, $color, $isLocked, $max]);
            $msg = t('msg_cat_added');
        }
    }
    if ($action === 'edit_cat') {
        $id       = (int)($_POST['id'] ?? 0);
        $name     = trim($_POST['name'] ?? '');
        $color    = valid_color($_POST['color'] ?? '');
        $isLocked = isset($_POST['is_locked']) ? 1 : 0;
        if ($id && $name) { db()->prepare('UPDATE categories SET name=?,color=?,is_locked=? WHERE id=?')->execute([$name,$color,$isLocked,$id]); $msg=t('msg_cat_edited'); }
    }
    if ($action === 'del_cat') {
        $id = (int)($_POST['id']??0);
        if ($id) { db()->prepare('DELETE FROM categories WHERE id=?')->execute([$id]); $msg=t('msg_cat_deleted'); }
    }

    // Subcategory CRUD
    if ($action === 'add_sub') {
        $catId = (int)($_POST['category_id']??0); $name = trim($_POST['name']??'');
        $color = valid_color($_POST['color'] ?? '');
        if (!$catId||!$name) { $error=t('err_fields_required'); } else {
            $max = db()->prepare('SELECT COALESCE(MAX(sort_order),0)+1 FROM subcategories WHERE category_id=?');
            $max->execute([$catId]);
            db()->prepare('INSERT INTO subcategories (category_id,name,color,sort_order) VALUES (?,?,?,?)')->execute([$catId,$name,$color,$max->fetchColumn()]);
            $msg=t('msg_sub_added');
        }
    }
    if ($action === 'edit_sub') {
        $id=(int)($_POST['id']??0); $name=trim($_POST['name']??''); $catId=(int)($_POST['category_id']??0);
        $color = valid_color($_POST['color'] ?? '');
        if ($id&&$name&&$catId) { db()->prepare('UPDATE subcategories SET name=?,category_id=?,color=? WHERE id=?')->execute([$name,$catId,$color,$id]); $msg=t('msg_sub_edited'); }
    }
    if ($action === 'del_sub') {
        $id=(int)($_POST['id']??0);
        if ($id) { db()->prepare('DELETE FROM subcategories WHERE id=?')->execute([$id]); $msg=t('msg_sub_deleted'); }
    }

    // Link CRUD
    if ($action === 'add_link') {
        $subId=(int)($_POST['subcategory_id']??0); $title=trim($_POST['title']??''); $url=trim($_POST['url']??''); $desc=trim($_POST['description']??'');
        if (!$subId||!$title||!$url) { $error=t('err_fields_required'); } else {
            if (!preg_match('#^https?://#',$url)) $url='https://'.$url;
            $max=db()->prepare('SELECT COALESCE(MAX(sort_order),0)+1 FROM links WHERE subcategory_id=?'); $max->execute([$subId]);
            db()->prepare('INSERT INTO links (subcategory_id,title,url,description,sort_order) VALUES (?,?,?,?,?)')->execute([$subId,$title,$url,$desc?:null,$max->fetchColumn()]);
            $msg=t('msg_link_added');
        }
    }
    if ($action === 'edit_link') {
        $id=(int)($_POST['id']??0); $subId=(int)($_POST['subcategory_id']??0); $title=trim($_POST['title']??''); $url=trim($_POST['url']??''); $desc=trim($_POST['description']??'');
        if ($id&&$subId&&$title&&$url) {
            if (!preg_match('#^https?://#',$url)) $url='https://'.$url;
            db()->prepare('UPDATE links SET subcategory_id=?,title=?,url=?,description=? WHERE id=?')->execute([$subId,$title,$url,$desc?:null,$id]);
            $msg=t('msg_link_edited');
        }
    }
    if ($action === 'del_link') {
        $id=(int)($_POST['id']??0);
        if ($id) { db()->prepare('DELETE FROM links WHERE id=?')->execute([$id]); $msg=t('msg_link_deleted'); }
    }
}

// ---- Data ------------------------------------------------
$cats = db()->query('SELECT * FROM categories ORDER BY sort_order, name')->fetchAll();
$showDescriptions = setting('show_descriptions', '1') === '1';

// Global search (all links, all categories)
$searchQuery   = trim($_GET['search'] ?? '');
$searchResults = [];
if ($searchQuery !== '') {
    $stmt = db()->prepare(
        "SELECT l.*, s.name AS sub_name, s.color AS sub_color, c.id AS cat_id, c.name AS cat_name, c.color AS cat_color
         FROM links l
         JOIN subcategories s ON s.id = l.subcategory_id
         JOIN categories c ON c.id = s.category_id
         WHERE l.title LIKE ? OR l.url LIKE ? OR l.description LIKE ?
         ORDER BY c.sort_order, s.sort_order, l.sort_order"
    );
    $like = '%' . $searchQuery . '%';
    $stmt->execute([$like, $like, $like]);
    $searchResults = $stmt->fetchAll();
}

// Active category
$activeCatId = (int)($_GET['cat'] ?? 0);
if ($activeCatId) {
    $stmt = db()->prepare('SELECT * FROM categories WHERE id=?'); $stmt->execute([$activeCatId]);
    $activeCat = $stmt->fetch() ?: null;
    if (!$activeCat) $activeCatId = 0;
}
if (!$activeCatId && !empty($cats)) {
    $activeCat = $cats[0]; $activeCatId = $activeCat['id'];
}

$subcats = [];
if ($activeCatId) {
    $subcats = get_subcats_with_links($activeCatId);
}


// Full list of subcategories (all categories) for the "Edit link" modal's
// select, which must work even from search results where the link may
// belong to a category different from the one currently displayed.
$allSubcatsStmt = db()->query(
    'SELECT s.id, s.name, c.name AS cat_name
     FROM subcategories s JOIN categories c ON c.id = s.category_id
     ORDER BY c.sort_order, s.sort_order, s.name'
);
$allSubcats = $allSubcatsStmt->fetchAll();

$siteTitle = setting('site_title', 'Links');
?>
<!DOCTYPE html>
<html lang="<?= h(current_lang_code()) ?>">
<head>
  <meta charset="UTF-8">
  <?= early_theme_script() ?>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex, nofollow">
  <meta name="csrf" content="<?= h(csrf_token()) ?>">
  <title>Admin — <?= h($siteTitle) ?></title>
  <link rel="icon" href="<?= h(site_icon_url()) ?>">
  <link rel="stylesheet" href="<?= asset_url('assets/css/style.css') ?>">
  <link rel="stylesheet" href="<?= asset_url('assets/css/admin.css') ?>">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    /* Overrides specific to this embedded admin page */
    .admin-topbar {
      display: flex; align-items: center; justify-content: flex-end;
      padding: .75rem 1rem;
    }
    .admin-topbar .actions { display: flex; gap: .5rem; align-items: center; }
    .sub-block { margin-bottom: 1.75rem; }
    .sub-header {
      display: flex; align-items: center; justify-content: space-between;
      margin-bottom: .5rem;
      padding: .4rem .5rem;
      margin-left: -.5rem;
      border-radius: var(--radius-sm);
      transition: background .15s;
    }
    .sub-header-hover:hover {
      background: color-mix(in srgb, var(--sub-color, var(--accent)) 12%, transparent);
    }
    .sub-actions { display: flex; gap: .35rem; opacity: 0; transition: opacity .15s; }
    .sub-header-hover:hover .sub-actions { opacity: 1; }
    .link-row { display: flex; align-items: flex-start; gap: .6rem; padding: .35rem .5rem; border-radius: var(--radius-sm); }
    .link-row:hover { background: var(--accent-soft); }
    .link-row:hover .link-actions { opacity: 1; }
    .link-actions { display: flex; gap: .25rem; margin-left: auto; margin-top: .1rem; opacity: 0; transition: opacity .15s; flex-shrink: 0; }
    .drag-h { cursor: grab; color: var(--text-3); flex-shrink: 0; margin-top: .25rem; display: inline-flex; align-items: center; }
    .drag-h:active { cursor: grabbing; }
    .sortable-ghost { opacity: .35; background: var(--accent-soft); }
    .cat-row-actions { display: flex; gap: .15rem; padding-right: .5rem; opacity: 0; transition: opacity .15s; flex-shrink: 0; }
    .cat-nav-row { transition: background .15s; }
    .cat-nav-row:hover .cat-row-actions { opacity: 1; }
    .save-toast {
      position: fixed; bottom: 1.5rem; right: 1.5rem;
      background: var(--accent); color: #fff;
      padding: .5rem 1rem; border-radius: var(--radius-sm);
      font-size: .82rem; font-weight: 600;
      opacity: 0; pointer-events: none;
      transition: opacity .3s;
      z-index: 999;
    }
    .save-toast.show { opacity: 1; }
    .search-result-row { display: flex; align-items: flex-start; gap: .6rem; padding: .5rem .5rem; border-radius: var(--radius-sm); width: 380px; }
    .search-result-row:hover { background: var(--accent-soft); }
    .search-result-row:hover .link-actions { opacity: 1; }
  </style>
</head>
<body>
<div class="admin-layout">

  <!-- Sidebar: category list with drag & drop -->
  <aside class="sidebar admin-sidebar">
    <div class="sidebar-header">
      <div style="display:flex;align-items:center;gap:.6rem">
        <img src="<?= h(site_icon_url()) ?>" alt="" style="width:28px;height:28px;object-fit:contain;border-radius:6px;flex-shrink:0">
        <div class="site-title"><?= h($siteTitle) ?></div>
      </div>
      <div class="site-subtitle"><?= h(t('administration')) ?></div>
    </div>

    <nav class="sidebar-nav">
      <div style="padding:.6rem 1rem .8rem">
        <form method="GET" action="<?= BASE_URL ?>/admin/" style="position:relative">
          <input type="search" name="search" value="<?= h($searchQuery) ?>"
                 placeholder="<?= h(t('search_placeholder')) ?>"
                 class="search-input">
          <?php if ($searchQuery !== ''): ?>
            <a href="<?= BASE_URL ?>/admin/" class="search-clear" title="<?= h(t('clear')) ?>"><?= icon('close', 13) ?></a>
          <?php endif; ?>
        </form>
      </div>
      <ul id="cat-sortable" data-sortable="<?= BASE_URL ?>/admin/" data-type="categories" style="list-style:none">
        <?php foreach ($cats as $cat): ?>
          <li class="sortable-item cat-nav-row" data-id="<?= $cat['id'] ?>"
              style="display:flex;align-items:center;gap:0;--cat-color:<?= h($cat['color'] ?? '#6366f1') ?>">
            <span class="drag-h" style="padding:.55rem .35rem .55rem .35rem" title="<?= h(t('drag_reorder_category')) ?>"><?= icon('grip', 14) ?></span>
            <a class="nav-item nav-cat <?= $cat['id']===$activeCatId?'active':'' ?>"
               href="?cat=<?= $cat['id'] ?>"
               style="flex:1;padding:.55rem .5rem">
              <?= h($cat['name']) ?>
            </a>
            <span class="cat-row-actions">
              <button class="btn btn-ghost btn-sm" style="padding:.2rem .35rem"
                data-modal-open="modal-edit-cat"
                data-edit="<?= h(json_encode(['id'=>$cat['id'],'name'=>$cat['name'],'color'=>$cat['color']??'#6366f1','is_locked'=>(int)($cat['is_locked']??0)])) ?>"
                title="<?= h(t('edit')) ?>"><?= icon('edit', 14) ?></button>
              <form method="POST" style="display:inline">
                <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
                <input type="hidden" name="action" value="del_cat">
                <input type="hidden" name="id" value="<?= $cat['id'] ?>">
                <button type="submit" class="btn btn-danger btn-sm" style="padding:.2rem .35rem"
                  data-confirm="<?= h(t('confirm_delete_category', [':name' => $cat['name']])) ?>"><?= icon('trash', 14) ?></button>
              </form>
            </span>
          </li>
        <?php endforeach; ?>
      </ul>

      <div style="padding:.5rem 1.25rem">
        <button class="btn btn-ghost btn-sm" style="width:100%;justify-content:center" data-modal-open="modal-add-cat"><?= h(t('add_category')) ?></button>
      </div>
    </nav>

    <div class="sidebar-footer">
      <?= theme_toggle_button() ?>
      <div style="display:flex;gap:.6rem;align-items:center">
        <a href="<?= BASE_URL ?>/<?= $activeCatId ? '?cat='.$activeCatId : '' ?>" title="<?= h(t('view_site')) ?>" style="color:var(--text-2);display:flex;line-height:0">
          <?= icon('eye', 19) ?>
        </a>
        <a href="<?= BASE_URL ?>/admin/settings.php" title="<?= h(t('settings')) ?>" style="color:var(--text-2);display:flex;line-height:0">
          <?= icon('gear', 19) ?>
        </a>
        <a href="?logout=1" onclick="return confirm(<?= json_encode(t('confirm_logout')) ?>)" title="<?= h(t('logout')) ?>" style="color:var(--text-2);display:flex;line-height:0">
          <?= icon('logout', 19) ?>
        </a>
      </div>
    </div>
  </aside>

  <!-- Main content -->
  <main class="main" style="padding-top:1.75rem">

    <div style="padding:0 2rem 4rem">
    <?php if ($searchQuery !== ''): ?>
      <div style="margin-bottom:1.25rem">
        <h2 style="font-size:1.1rem;font-weight:700"><?= h(t('search_results_for', [':query' => $searchQuery])) ?></h2>
        <p style="font-size:.82rem;color:var(--text-3);margin-top:.2rem"><?= h(t('search_results_count', [':count' => (string)count($searchResults)])) ?></p>
      </div>
      <?php if (empty($searchResults)): ?>
        <p style="color:var(--text-3);font-size:.875rem"><?= h(t('no_results')) ?></p>
      <?php else: ?>
        <ul style="list-style:none">
          <?php foreach ($searchResults as $link): ?>
            <li class="search-result-row">
              <?= render_admin_link($link, $showDescriptions) ?>
              <div class="link-actions" style="margin-left:auto">
                <a href="<?= BASE_URL ?>/admin/?cat=<?= $link['cat_id'] ?>" class="btn btn-ghost btn-sm" title="<?= h(t('go_to_category')) ?>">
                  <?= icon('chevron-right', 14) ?>
                </a>
                <button class="btn btn-ghost btn-sm"
                  data-modal-open="modal-edit-link"
                  data-edit="<?= h(json_encode(['id'=>$link['id'],'subcategory_id'=>$link['subcategory_id'],'title'=>$link['title'],'url'=>$link['url'],'description'=>$link['description']??''])) ?>">
                  <?= icon('edit', 14) ?>
                </button>
                <form method="POST" style="display:inline">
                  <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
                  <input type="hidden" name="action" value="del_link">
                  <input type="hidden" name="id" value="<?= $link['id'] ?>">
                  <button type="submit" class="btn btn-danger btn-sm"
                    data-confirm="<?= h(t('confirm_delete_link', [':name' => $link['title']])) ?>"><?= icon('trash', 14) ?></button>
                </form>
              </div>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>


    <?php elseif (empty($cats)): ?>
      <div class="empty">
        <div class="empty-icon">
          <?= icon('bookmark', 40) ?>
        </div>
        <p><?= h(t('start_add_category')) ?></p>
      </div>

    <?php elseif (empty($subcats)): ?>
      <div class="empty" style="padding:3rem 2rem">
        <div class="empty-icon">
          <?= icon('folder', 40) ?>
        </div>
        <p><?= h(t('no_subcategories_admin')) ?><br>
          <button class="btn btn-ghost" style="margin-top:.75rem" data-modal-open="modal-add-sub"><?= h(t('add_subcategory')) ?></button>
        </p>
      </div>

    <?php else: ?>
      <div style="margin-bottom:1.25rem">
        <button class="btn btn-ghost btn-sm" data-modal-open="modal-add-sub"><?= h(t('add_subcategory')) ?></button>
      </div>
      <?php if ($msg): ?><p style="font-size:.85rem;color:var(--success);margin-bottom:1rem;display:flex;align-items:center;gap:.4rem"><?= icon('check', 14) ?> <?= h($msg) ?></p><?php endif; ?>
      <?php if ($error): ?><p style="font-size:.85rem;color:var(--danger);margin-bottom:1rem"><?= h($error) ?></p><?php endif; ?>
      <!-- Subcategory list (sortable) -->
      <ul id="sub-sortable" data-sortable="<?= BASE_URL ?>/admin/" data-type="subcategories" class="masonry" style="list-style:none">
        <?php foreach ($subcats as $sub): ?>
          <li class="sortable-item sub-block" data-id="<?= $sub['id'] ?>">
            <div class="subcategory" style="--sub-color:<?= h($sub['color'] ?? '#6366f1') ?>">
              <div class="sub-header sub-header-hover">
                <div style="display:flex;align-items:center;gap:.5rem">
                  <span class="drag-h" title="<?= h(t('drag_reorder')) ?>"><?= icon('grip', 14) ?></span>
                  <span class="subcategory-title" style="margin:0"><?= h($sub['name']) ?></span>
                </div>
                <div class="sub-actions">
                  <button class="btn btn-ghost btn-sm"
                    data-modal-open="modal-edit-sub"
                    data-edit="<?= h(json_encode(['id'=>$sub['id'],'name'=>$sub['name'],'category_id'=>$sub['category_id'],'color'=>$sub['color']??'#6366f1'])) ?>">
                    <?= icon('edit', 14) ?>
                  </button>
                  <form method="POST" style="display:inline">
                    <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
                    <input type="hidden" name="action" value="del_sub">
                    <input type="hidden" name="id" value="<?= $sub['id'] ?>">
                    <button type="submit" class="btn btn-danger btn-sm"
                      data-confirm="<?= h(t('confirm_delete_subcategory', [':name' => $sub['name']])) ?>"><?= icon('trash', 14) ?></button>
                  </form>
                </div>
              </div>

              <!-- Links (sortable) -->
              <ul class="links-list link-sortable" data-sortable="<?= BASE_URL ?>/admin/" data-type="links" style="list-style:none">
                <?php foreach ($sub['links'] as $link): ?>
                  <li class="sortable-item link-row" data-id="<?= $link['id'] ?>">
                    <span class="drag-h"><?= icon('grip', 14) ?></span>
                    <?= render_admin_link($link, $showDescriptions) ?>
                    <div class="link-actions">
                      <button class="btn btn-ghost btn-sm"
                        data-modal-open="modal-edit-link"
                        data-edit="<?= h(json_encode(['id'=>$link['id'],'subcategory_id'=>$link['subcategory_id'],'title'=>$link['title'],'url'=>$link['url'],'description'=>$link['description']??''])) ?>">
                        <?= icon('edit', 14) ?>
                      </button>
                      <form method="POST" style="display:inline">
                        <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
                        <input type="hidden" name="action" value="del_link">
                        <input type="hidden" name="id" value="<?= $link['id'] ?>">
                        <button type="submit" class="btn btn-danger btn-sm"
                          data-confirm="<?= h(t('confirm_delete_link', [':name' => $link['title']])) ?>"><?= icon('trash', 14) ?></button>
                      </form>
                    </div>
                  </li>
                <?php endforeach; ?>
              </ul>

              <div style="margin-top:.6rem">
                <button class="btn btn-ghost btn-sm"
                  data-modal-open="modal-add-link"
                  data-prefill-sub="<?= $sub['id'] ?>">
                  <?= h(t('add_link')) ?>
                </button>
              </div>

            </div>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
    </div>
  </main>
</div>

<!-- Save toast -->
<div class="save-toast" id="save-toast" style="display:flex;align-items:center;gap:.4rem"><?= icon('check', 14) ?> <?= h(t('order_saved')) ?></div>

<!-- ===== MODALS ===== -->

<!-- Add category -->
<div class="modal-overlay" id="modal-add-cat">
  <div class="modal">
    <h2 class="modal-title"><?= h(t('modal_new_category')) ?></h2>
    <form method="POST">
      <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
      <input type="hidden" name="action" value="add_cat">
      <div class="form-group">
        <label class="form-label"><?= h(t('field_name')) ?></label>
        <input class="form-control" type="text" name="name" required autofocus placeholder="<?= h(t('placeholder_category_name')) ?>">
      </div>
      <div class="form-group">
        <label class="form-label"><?= h(t('color_label')) ?></label>
        <div style="display:flex;align-items:center;gap:.75rem">
          <input type="color" name="color" value="#6366f1"
                 style="width:40px;height:36px;padding:2px;border:1px solid var(--border);border-radius:var(--radius-sm);background:var(--bg);cursor:pointer">
          <span style="font-size:.8rem;color:var(--text-3)"><?= h(t('hint_color_menu')) ?></span>
        </div>
      </div>
      <div class="form-group">
        <label style="display:flex;align-items:center;gap:.6rem;cursor:pointer">
          <input type="checkbox" name="is_locked" value="1"
                 style="width:16px;height:16px;accent-color:var(--accent)">
          <span class="form-label" style="margin:0"><?= h(t('lock_pin_label')) ?></span>
        </label>
        <div class="form-hint"><?= h(t('lock_pin_hint')) ?></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-ghost" data-modal-close><?= h(t('cancel')) ?></button>
        <button type="submit" class="btn btn-primary"><?= h(t('add')) ?></button>
      </div>
    </form>
  </div>
</div>

<!-- Edit category -->
<div class="modal-overlay" id="modal-edit-cat">
  <div class="modal">
    <h2 class="modal-title"><?= h(t('modal_edit_category')) ?></h2>
    <form method="POST">
      <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
      <input type="hidden" name="action" value="edit_cat">
      <input type="hidden" name="id" value="">
      <div class="form-group">
        <label class="form-label"><?= h(t('field_name')) ?></label>
        <input class="form-control" type="text" name="name" required>
      </div>
      <div class="form-group">
        <label class="form-label"><?= h(t('color_label')) ?></label>
        <div style="display:flex;align-items:center;gap:.75rem">
          <input type="color" name="color" value="#6366f1"
                 style="width:40px;height:36px;padding:2px;border:1px solid var(--border);border-radius:var(--radius-sm);background:var(--bg);cursor:pointer">
          <span style="font-size:.8rem;color:var(--text-3)"><?= h(t('hint_color_menu')) ?></span>
        </div>
      </div>
      <div class="form-group">
        <label style="display:flex;align-items:center;gap:.6rem;cursor:pointer">
          <input type="checkbox" name="is_locked" value="1" id="edit-cat-locked"
                 style="width:16px;height:16px;accent-color:var(--accent)">
          <span class="form-label" style="margin:0"><?= h(t('lock_pin_label')) ?></span>
        </label>
        <div class="form-hint"><?= h(t('lock_pin_hint')) ?></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-ghost" data-modal-close><?= h(t('cancel')) ?></button>
        <button type="submit" class="btn btn-primary"><?= h(t('save')) ?></button>
      </div>
    </form>
  </div>
</div>

<!-- Add subcategory -->
<div class="modal-overlay" id="modal-add-sub">
  <div class="modal">
    <h2 class="modal-title"><?= h(t('modal_new_subcategory')) ?></h2>
    <form method="POST">
      <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
      <input type="hidden" name="action" value="add_sub">
      <input type="hidden" name="category_id" value="<?= $activeCatId ?>">
      <div class="form-group">
        <label class="form-label"><?= h(t('field_name')) ?></label>
        <input class="form-control" type="text" name="name" required autofocus placeholder="<?= h(t('placeholder_subcategory_name')) ?>">
      </div>
      <div class="form-group">
        <label class="form-label"><?= h(t('color_label')) ?></label>
        <div style="display:flex;align-items:center;gap:.75rem">
          <input type="color" name="color" value="#6366f1"
                 style="width:40px;height:36px;padding:2px;border:1px solid var(--border);border-radius:var(--radius-sm);background:var(--bg);cursor:pointer">
          <span style="font-size:.8rem;color:var(--text-3)"><?= h(t('hint_color_subcat')) ?></span>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-ghost" data-modal-close><?= h(t('cancel')) ?></button>
        <button type="submit" class="btn btn-primary"><?= h(t('add')) ?></button>
      </div>
    </form>
  </div>
</div>

<!-- Edit subcategory -->
<div class="modal-overlay" id="modal-edit-sub">
  <div class="modal">
    <h2 class="modal-title"><?= h(t('modal_edit_subcategory')) ?></h2>
    <form method="POST">
      <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
      <input type="hidden" name="action" value="edit_sub">
      <input type="hidden" name="id" value="">
      <div class="form-group">
        <label class="form-label"><?= h(t('field_category')) ?></label>
        <select class="form-control" name="category_id" required>
          <?php foreach ($cats as $c): ?>
            <option value="<?= $c['id'] ?>"><?= h($c['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label"><?= h(t('field_name')) ?></label>
        <input class="form-control" type="text" name="name" required>
      </div>
      <div class="form-group">
        <label class="form-label"><?= h(t('color_label')) ?></label>
        <div style="display:flex;align-items:center;gap:.75rem">
          <input type="color" name="color" value="#6366f1"
                 style="width:40px;height:36px;padding:2px;border:1px solid var(--border);border-radius:var(--radius-sm);background:var(--bg);cursor:pointer">
          <span style="font-size:.8rem;color:var(--text-3)"><?= h(t('hint_color_subcat')) ?></span>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-ghost" data-modal-close><?= h(t('cancel')) ?></button>
        <button type="submit" class="btn btn-primary"><?= h(t('save')) ?></button>
      </div>
    </form>
  </div>
</div>

<!-- Add link -->
<div class="modal-overlay" id="modal-add-link">
  <div class="modal">
    <h2 class="modal-title"><?= h(t('modal_new_link')) ?></h2>
    <form method="POST">
      <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
      <input type="hidden" name="action" value="add_link">
      <div class="form-group">
        <label class="form-label"><?= h(t('field_subcategory')) ?></label>
        <select class="form-control" name="subcategory_id" id="add-link-sub" required>
          <option value=""><?= h(t('choose')) ?></option>
          <?php foreach ($subcats as $s): ?>
            <option value="<?= $s['id'] ?>"><?= h($s['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label"><?= h(t('field_url')) ?></label>
        <div style="display:flex;gap:.5rem;align-items:center">
          <input class="form-control" type="url" name="url" id="add-link-url" required placeholder="https://github.com">
          <span id="meta-spinner" style="display:none;color:var(--text-3);font-size:.75rem;white-space:nowrap;flex-shrink:0">…</span>
        </div>
        <div class="form-hint"><?= h(t('auto_meta_hint')) ?></div>
      </div>
      <div class="form-group">
        <label class="form-label"><?= h(t('field_title')) ?></label>
        <input class="form-control" type="text" name="title" id="add-link-title" required placeholder="<?= h(t('placeholder_link_title')) ?>">
      </div>
      <div class="form-group">
        <label class="form-label"><?= h(t('field_description')) ?> <span style="color:var(--text-3)"><?= h(t('field_description_optional')) ?></span></label>
        <input class="form-control" type="text" name="description" id="add-link-desc" maxlength="500" placeholder="<?= h(t('placeholder_link_desc')) ?>">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-ghost" data-modal-close><?= h(t('cancel')) ?></button>
        <button type="submit" class="btn btn-primary"><?= h(t('add')) ?></button>
      </div>
    </form>
  </div>
</div>

<!-- Edit link -->
<div class="modal-overlay" id="modal-edit-link">
  <div class="modal">
    <h2 class="modal-title"><?= h(t('modal_edit_link')) ?></h2>
    <form method="POST">
      <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
      <input type="hidden" name="action" value="edit_link">
      <input type="hidden" name="id" value="">
      <div class="form-group">
        <label class="form-label"><?= h(t('field_subcategory')) ?></label>
        <select class="form-control" name="subcategory_id" required>
          <option value=""><?= h(t('choose')) ?></option>
          <?php foreach ($allSubcats as $s): ?>
            <option value="<?= $s['id'] ?>"><?= h($s['cat_name']) ?> › <?= h($s['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label"><?= h(t('field_title')) ?></label>
        <input class="form-control" type="text" name="title" required>
      </div>
      <div class="form-group">
        <label class="form-label"><?= h(t('field_url')) ?></label>
        <input class="form-control" type="url" name="url" required>
      </div>
      <div class="form-group">
        <label class="form-label"><?= h(t('field_description')) ?></label>
        <input class="form-control" type="text" name="description" maxlength="500">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-ghost" data-modal-close><?= h(t('cancel')) ?></button>
        <button type="submit" class="btn btn-primary"><?= h(t('save')) ?></button>
      </div>
    </form>
  </div>
</div>

<script src="<?= asset_url('assets/js/app.js') ?>"></script>
<script src="<?= asset_url('assets/js/admin.js') ?>"></script>
<script>
// ---- Pre-select subcategory from a subcategory's "+ Link" button
document.querySelectorAll('[data-prefill-sub]').forEach(function(btn){
  btn.addEventListener('click', function(){
    const subId = btn.getAttribute('data-prefill-sub');
    const sel = document.querySelector('#add-link-sub');
    if (sel) sel.value = subId;
  });
});

// ---- Auto-fetch title + description from the URL
(function() {
  const urlInput   = document.getElementById('add-link-url');
  const titleInput = document.getElementById('add-link-title');
  const descInput  = document.getElementById('add-link-desc');
  const spinner    = document.getElementById('meta-spinner');
  if (!urlInput) return;

  let fetchTimer = null;

  urlInput.addEventListener('blur', triggerFetch);
  urlInput.addEventListener('input', function() {
    clearTimeout(fetchTimer);
    fetchTimer = setTimeout(triggerFetch, 1200);
  });

  function triggerFetch() {
    const url = urlInput.value.trim();
    if (!url || !url.startsWith('http')) return;
    // Don't overwrite what the user has already typed
    if (titleInput.value.trim()) return;

    spinner.style.display = 'inline';
    spinner.textContent = <?= json_encode(t('loading')) ?>;

    fetch('<?= BASE_URL ?>/admin/fetch-meta.php?url=' + encodeURIComponent(url))
      .then(function(r) { return r.json(); })
      .then(function(data) {
        spinner.style.display = 'none';
        if (data.error) return;
        if (data.title && !titleInput.value.trim()) {
          titleInput.value = data.title;
        }
        if (data.description && !descInput.value.trim()) {
          descInput.value = data.description;
        }
      })
      .catch(function() { spinner.style.display = 'none'; });
  }
})();

// ---- Remember/restore the active category (key shared with the public site)
const CAT_KEY = 'links_last_cat_shared';
const currentCat = <?= $activeCatId ?>;
if (currentCat) localStorage.setItem(CAT_KEY, currentCat);
// Only redirect if the URL has no parameters at all (not even ?search=...)
if (!window.location.search) {
  const last = localStorage.getItem(CAT_KEY);
  if (last) window.location.replace('?cat=' + last);
}

// ---- Save toast
const toast = document.getElementById('save-toast');
function showToast() {
  toast.classList.add('show');
  setTimeout(()=>toast.classList.remove('show'), 2000);
}
const origFetch = window.fetch;
window.fetch = function(){ return origFetch.apply(this, arguments).then(r=>{ showToast(); return r; }); };
</script>
</body>
</html>
