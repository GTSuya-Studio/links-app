<?php
// ============================================================
// admin/export.php — JSON export of the data (categories,
// subcategories, links). Contains no secrets (passwords,
// PIN, etc. are excluded).
// ============================================================
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();

$cats = db()->query('SELECT id, name, color, is_locked, slug, sort_order FROM categories ORDER BY sort_order, name')->fetchAll();

foreach ($cats as &$cat) {
    $stmt = db()->prepare('SELECT id, name, color, sort_order FROM subcategories WHERE category_id=? ORDER BY sort_order, name');
    $stmt->execute([$cat['id']]);
    $subs = $stmt->fetchAll();

    foreach ($subs as &$sub) {
        $lnk = db()->prepare('SELECT title, url, description, sort_order FROM links WHERE subcategory_id=? ORDER BY sort_order, title');
        $lnk->execute([$sub['id']]);
        $sub['links'] = $lnk->fetchAll();
    }
    unset($sub);

    $cat['subcategories'] = $subs;
}
unset($cat);

$export = [
    'exported_at'  => date('c'),
    'site_title'   => setting('site_title', 'Links'),
    'categories'   => $cats,
];

$filename = 'links-export-' . date('Y-m-d') . '.json';

header('Content-Type: application/json; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
echo json_encode($export, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
