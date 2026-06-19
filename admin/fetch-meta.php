<?php
// ============================================================
// admin/fetch-meta.php — Meta tag fetching proxy
// ============================================================
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json');

if (!is_logged_in()) {
    http_response_code(403);
    echo json_encode(['error' => t('err_unauthorized')]);
    exit;
}

$url = trim($_GET['url'] ?? '');
if (!$url || !filter_var($url, FILTER_VALIDATE_URL)) {
    echo json_encode(['error' => t('err_invalid_url')]);
    exit;
}

// Security: http/https only
if (!preg_match('#^https?://#i', $url)) {
    echo json_encode(['error' => t('err_protocol_not_allowed')]);
    exit;
}

// Anti-SSRF security: resolve the hostname and block private/reserved IPs
// (loopback, internal networks, link-local, cloud metadata, etc.)
$host = parse_url($url, PHP_URL_HOST);
if (!$host) {
    echo json_encode(['error' => t('err_invalid_url')]);
    exit;
}

$ips = [];
$ipv4 = gethostbyname($host);
if ($ipv4 && $ipv4 !== $host) $ips[] = $ipv4;
// AAAA resolution if available
if (function_exists('dns_get_record')) {
    $records = @dns_get_record($host, DNS_AAAA);
    if ($records) {
        foreach ($records as $r) {
            if (!empty($r['ipv6'])) $ips[] = $r['ipv6'];
        }
    }
}
if (filter_var($host, FILTER_VALIDATE_IP)) $ips[] = $host;

if (empty($ips)) {
    echo json_encode(['error' => t('err_cannot_resolve_domain')]);
    exit;
}

foreach ($ips as $ip) {
    $isPrivate = !filter_var(
        $ip,
        FILTER_VALIDATE_IP,
        FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
    );
    if ($isPrivate) {
        echo json_encode(['error' => t('err_address_not_allowed')]);
        exit;
    }
}

$ctx = stream_context_create([
    'http' => [
        'timeout'          => 6,
        'follow_location'  => true,
        'max_redirects'    => 5,
        'user_agent'       => 'Mozilla/5.0 (compatible; LinksFetcher/1.0)',
        'ignore_errors'    => true,
    ],
    'ssl' => [
        'verify_peer'      => false,
        'verify_peer_name' => false,
    ],
]);

$html = @file_get_contents($url, false, $ctx);

if (!$html) {
    echo json_encode(['error' => t('err_cannot_fetch_page')]);
    exit;
}

// Limit to 50KB to avoid parsing huge pages
$html = substr($html, 0, 51200);

$title = '';
$description = '';

// Page charset
$charset = 'UTF-8';
if (preg_match('/<meta[^>]+charset=["\']?([a-zA-Z0-9\-]+)/i', $html, $m)) {
    $charset = $m[1];
}
if (strtolower($charset) !== 'utf-8') {
    $html = mb_convert_encoding($html, 'UTF-8', $charset);
}

// og:title
if (preg_match('/<meta[^>]+property=["\']og:title["\'][^>]+content=["\']([^"\']+)/i', $html, $m)) {
    $title = $m[1];
} elseif (preg_match('/<meta[^>]+content=["\']([^"\']+)["\'][^>]+property=["\']og:title["\']/i', $html, $m)) {
    $title = $m[1];
}

// <title> fallback
if (!$title && preg_match('/<title[^>]*>([^<]+)<\/title>/is', $html, $m)) {
    $title = trim($m[1]);
}

// og:description
if (preg_match('/<meta[^>]+property=["\']og:description["\'][^>]+content=["\']([^"\']+)/i', $html, $m)) {
    $description = $m[1];
} elseif (preg_match('/<meta[^>]+content=["\']([^"\']+)["\'][^>]+property=["\']og:description["\']/i', $html, $m)) {
    $description = $m[1];
}

// meta description fallback
if (!$description && preg_match('/<meta[^>]+name=["\']description["\'][^>]+content=["\']([^"\']+)/i', $html, $m)) {
    $description = $m[1];
} elseif (!$description && preg_match('/<meta[^>]+content=["\']([^"\']+)["\'][^>]+name=["\']description["\']/i', $html, $m)) {
    $description = $m[1];
}

// Clean up
$title       = html_entity_decode(trim($title),       ENT_QUOTES | ENT_HTML5, 'UTF-8');
$description = html_entity_decode(trim($description), ENT_QUOTES | ENT_HTML5, 'UTF-8');

// Truncate the description to 500 chars
if (mb_strlen($description) > 500) {
    $description = mb_substr($description, 0, 497) . '…';
}

echo json_encode([
    'title'       => $title,
    'description' => $description,
]);
