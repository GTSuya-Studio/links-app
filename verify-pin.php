<?php
// ============================================================
// verify-pin.php — PIN code verification (locked categories)
// ============================================================
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';

header('Content-Type: application/json');

if (!defined('SESSION_SECRET')) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => t('err_server_config')]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => t('err_method_not_allowed')]);
    exit;
}

$body  = json_decode(file_get_contents('php://input'), true);
$pin   = trim($body['pin'] ?? '');
$catId = (int)($body['cat_id'] ?? 0);

if (!preg_match('/^\d{4}$/', $pin) || !$catId) {
    echo json_encode(['ok' => false, 'error' => t('err_invalid_request')]);
    exit;
}

$pinHash = setting('pin_code_hash', '');
if (!$pinHash || hash('sha256', $pin) !== $pinHash) {
    usleep(400000); // slight anti-brute-force delay
    echo json_encode(['ok' => false, 'error' => t('pin_incorrect')]);
    exit;
}

// Check that the category exists and is actually locked
$stmt = db()->prepare('SELECT id FROM categories WHERE id = ? AND is_locked = 1');
$stmt->execute([$catId]);
if (!$stmt->fetch()) {
    echo json_encode(['ok' => false, 'error' => t('err_invalid_category')]);
    exit;
}

// Unlocking: add/refresh the entry for this category,
// with a configurable expiration (Settings > Security), 45 min by default.
$durationMinutes = (int)setting('pin_unlock_duration', '45');
if ($durationMinutes < 1) $durationMinutes = 45;

$unlocked = [];
if (!empty($_COOKIE['unlocked_cats'])) {
    $decoded = unlock_cookie_decode($_COOKIE['unlocked_cats']);
    if ($decoded !== null) $unlocked = $decoded;
}
$unlocked[$catId] = time() + ($durationMinutes * 60);

setcookie('unlocked_cats', unlock_cookie_encode($unlocked), [
    'expires'  => 0, // session cookie (also expires when the browser closes)
    'path'     => '/',
    'secure'   => true,
    'httponly' => true,
    'samesite' => 'Strict',
]);

echo json_encode(['ok' => true]);
