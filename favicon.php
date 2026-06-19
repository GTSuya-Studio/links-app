<?php
// ============================================================
// favicon.php — Local favicon cache
// Downloads a domain's favicon once, stores it
// in assets/uploads/favicons/, then serves it from cache.
// Falls back to Google S2 if the download fails.
// ============================================================
require_once __DIR__ . '/config.php';

$domain = trim($_GET['domain'] ?? '');

// Valid domain: letters, digits, dots, and hyphens only
if (!$domain || !preg_match('/^[a-zA-Z0-9.\-]+$/', $domain) || strlen($domain) > 253) {
    header('Location: https://www.google.com/s2/favicons?sz=32&domain=' . urlencode($domain));
    exit;
}

$cacheDir = __DIR__ . '/assets/uploads/favicons';
if (!is_dir($cacheDir)) {
    @mkdir($cacheDir, 0755, true);
}

$cacheFile = $cacheDir . '/' . md5($domain) . '.png';
$cacheMaxAge = 60 * 60 * 24 * 30; // 30 days

// Serve from cache if present and not too old
if (is_file($cacheFile) && (time() - filemtime($cacheFile)) < $cacheMaxAge) {
    header('Content-Type: image/png');
    header('Cache-Control: public, max-age=' . $cacheMaxAge);
    header('X-Favicon-Source: cache');
    readfile($cacheFile);
    exit;
}

// Download from Google S2 (reliable source, already handles favicon resolution)
$source = 'https://www.google.com/s2/favicons?sz=32&domain=' . urlencode($domain);
$data = favicon_fetch($source);

if ($data && strlen($data) > 0) {
    if (is_dir($cacheDir) && is_writable($cacheDir)) {
        @file_put_contents($cacheFile, $data);
    }
    header('Content-Type: image/png');
    header('Cache-Control: public, max-age=' . $cacheMaxAge);
    header('X-Favicon-Source: downloaded');
    echo $data;
    exit;
}

// Failure: redirect straight to Google as a last resort
header('Location: ' . $source);
exit;

// ----------------------------------------------------------
// Downloads a URL via cURL (preferred, often available
// even when allow_url_fopen is disabled), falling back to
// file_get_contents if cURL isn't installed or fails.
// ----------------------------------------------------------
function favicon_fetch(string $url): ?string {
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT        => 5,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_USERAGENT      => 'Mozilla/5.0 (compatible; LinksFaviconCache/1.0)',
        ]);
        $data = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($data !== false && $httpCode === 200 && strlen($data) > 0) {
            return $data;
        }
        // If cURL fails, still try file_get_contents below
    }

    if (ini_get('allow_url_fopen')) {
        $ctx = stream_context_create([
            'http' => ['timeout' => 5, 'ignore_errors' => true],
        ]);
        $data = @file_get_contents($url, false, $ctx);
        if ($data !== false && strlen($data) > 0) {
            return $data;
        }
    }

    return null;
}
