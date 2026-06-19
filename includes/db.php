<?php
// ============================================================
// includes/db.php — Singleton PDO connection
// ============================================================

require_once __DIR__ . '/icons.php';

function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            http_response_code(500);
            // Note: t()/setting() can't be used here since they call db(),
            // which just failed — this message is intentionally bilingual.
            die('<div style="font-family:monospace;padding:2rem;color:#f87171">
                <strong>Database connection error / Erreur de connexion à la base de données.</strong><br>
                Check your config.php file / Vérifiez votre fichier config.php<br>
                Detail / Détail : ' . htmlspecialchars($e->getMessage()) . '
            </div>');
        }
    }
    return $pdo;
}

// ============================================================
// Generic helpers
// ============================================================

function setting(string $key, string $default = ''): string {
    static $cache = [];
    if (!isset($cache[$key])) {
        $stmt = db()->prepare('SELECT `value` FROM settings WHERE `key` = ?');
        $stmt->execute([$key]);
        $row = $stmt->fetch();
        $cache[$key] = $row ? $row['value'] : $default;
    }
    return $cache[$key];
}

function setting_set(string $key, string $value): void {
    $stmt = db()->prepare('INSERT INTO settings(`key`,`value`) VALUES(?,?) ON DUPLICATE KEY UPDATE `value`=?');
    $stmt->execute([$key, $value, $value]);
}

function h(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function slug(string $s): string {
    $s = mb_strtolower($s);
    $s = str_replace(['é','è','ê','ë'], 'e', $s);
    $s = str_replace(['à','â','ä'], 'a', $s);
    $s = str_replace(['ù','û','ü'], 'u', $s);
    $s = str_replace(['î','ï'], 'i', $s);
    $s = str_replace(['ô','ö'], 'o', $s);
    $s = str_replace('ç', 'c', $s);
    $s = preg_replace('/[^a-z0-9]+/', '-', $s);
    return trim($s, '-');
}

// Returns the URL of the site icon: the custom one uploaded in Settings
// if set AND still present on disk, otherwise the bundled default icon
// shipped with the app. Unlike the raw 'site_icon' setting, this never
// returns an empty string or a dead link, so callers don't need to check
// for emptiness before rendering it.
function site_icon_url(): string {
    $custom = setting('site_icon', '');
    if ($custom !== '' && is_file(__DIR__ . '/../' . $custom)) {
        return asset_url($custom);
    }
    return asset_url('assets/img/default-icon.png');
}

function favicon_url(string $url): string {
    $parsed = parse_url($url);
    if (!$parsed || empty($parsed['host'])) return '';
    return BASE_URL . '/favicon.php?domain=' . urlencode($parsed['host']);
}

// ============================================================
// Factoring helpers (extracted from index.php / admin/index.php
// to avoid duplication — strictly identical behavior)
// ============================================================

// URL of an asset (CSS/JS) with cache-busting based on its modification
// date. $relPath is relative to the site root (e.g. 'assets/css/style.css').
function asset_url(string $relPath): string {
    $abs = __DIR__ . '/../' . $relPath;
    $v   = @filemtime($abs) ?: time();
    return BASE_URL . '/' . $relPath . '?v=' . $v;
}

// Validates a hex color (#rrggbb) entered via an <input type="color">,
// with the same fallback value as in the original code.
function valid_color(?string $v): string {
    return (is_string($v) && preg_match('/^#[0-9a-fA-F]{6}$/', $v)) ? $v : '#6366f1';
}

// Fetches a category's subcategories, each enriched with its 'links'
// array (logic identical to the one duplicated in index.php and
// admin/index.php).
function get_subcats_with_links(int $catId): array {
    $stmt = db()->prepare('SELECT * FROM subcategories WHERE category_id=? ORDER BY sort_order, name');
    $stmt->execute([$catId]);
    $subcats = $stmt->fetchAll();

    if ($subcats) {
        $subIds = array_column($subcats, 'id');
        $placeholders = implode(',', array_fill(0, count($subIds), '?'));
        $lnk = db()->prepare("SELECT * FROM links WHERE subcategory_id IN ($placeholders) ORDER BY sort_order, title");
        $lnk->execute($subIds);
        $linksBySub = [];
        foreach ($lnk->fetchAll() as $link) {
            $linksBySub[$link['subcategory_id']][] = $link;
        }
        foreach ($subcats as &$sub) {
            $sub['links'] = $linksBySub[$sub['id']] ?? [];
        }
        unset($sub);
    }

    return $subcats;
}

// Renders the "favicon + title + description" HTML block for a link,
// identical in the 2 occurrences on the public site (normal list + search results).
function render_public_link(array $link, bool $showDescriptions): string {
    $html  = '<img class="link-favicon" src="' . h(favicon_url($link['url'])) . '" alt="" width="16" height="16" loading="lazy" onerror="this.style.display=\'none\'">';
    $html .= '<div class="link-text">';
    $html .= '<a class="link-title" href="' . h($link['url']) . '" target="_blank" rel="noopener noreferrer">' . h($link['title']) . '</a>';
    if ($showDescriptions && $link['description']) {
        $html .= '<span class="link-desc" title="' . h($link['description']) . '">' . h($link['description']) . '</span>';
    }
    $html .= '</div>';
    return $html;
}

// Renders the "favicon + title + description" HTML block for a link on
// the admin side, identical in the 2 occurrences (search results + normal list).
function render_admin_link(array $link, bool $showDescriptions): string {
    $html  = '<img class="link-favicon" src="' . h(favicon_url($link['url'])) . '" alt="" width="16" height="16" loading="lazy" onerror="this.style.display=\'none\'">';
    $html .= '<div class="link-text">';
    $html .= '<a class="link-title" href="' . h($link['url']) . '" target="_blank" rel="noopener">' . h($link['title']) . '</a>';
    if ($showDescriptions && $link['description']) {
        $html .= '<span class="link-desc">' . h($link['description']) . '</span>';
    }
    $html .= '</div>';
    return $html;
}

// ============================================================
// i18n — translations
//
// Languages live as plain PHP files in includes/lang/<code>.php,
// each returning an associative array of translation keys plus an
// optional '_meta' => ['name' => 'Display name'] entry. Dropping a
// new file there (e.g. includes/lang/es.php) is enough to make a
// new language available — no other code change is required.
// ============================================================

// Loads (and caches) the translation array for a given language code.
// Returns an empty array if the file doesn't exist.
function load_lang_file(string $code): array {
    static $cache = [];
    if (!isset($cache[$code])) {
        $file = __DIR__ . '/lang/' . basename($code) . '.php';
        $cache[$code] = is_file($file) ? (require $file) : [];
    }
    return $cache[$code];
}

// Current site language (from settings), falling back to 'fr' if the
// configured code has no matching file.
function current_lang_code(): string {
    static $code = null;
    if ($code === null) {
        $configured = setting('site_language', 'fr');
        $code = is_file(__DIR__ . '/lang/' . basename($configured) . '.php') ? $configured : 'fr';
    }
    return $code;
}

// Translates $key in the current language. Falls back to English, then
// to the key itself, if missing. $params replaces ':placeholder' tokens
// (e.g. t('confirm_delete_link', [':name' => $title])).
function t(string $key, array $params = []): string {
    $lang = load_lang_file(current_lang_code());
    $str  = $lang[$key] ?? (load_lang_file('en')[$key] ?? $key);
    return $params ? strtr($str, $params) : $str;
}

// Scans includes/lang/ and returns [code => display name] for every
// available language, used to populate the language picker in Settings.
function available_languages(): array {
    $out = [];
    foreach (glob(__DIR__ . '/lang/*.php') as $file) {
        $code = basename($file, '.php');
        $arr  = load_lang_file($code);
        $out[$code] = $arr['_meta']['name'] ?? strtoupper($code);
    }
    ksort($out);
    return $out;
}

// Inline script to place at the very top of <head>, BEFORE the CSS link,
// to apply the theme (light/dark) before the page's first paint. Without
// this, the browser first displays the dark theme (the default value in
// :root), then switches to light once app.js loads at the bottom of the
// page — hence the flash visible only in light theme. This script is
// intentionally minimal and inline (no external file) so that it runs
// synchronously before the CSS is painted.
function early_theme_script(): string {
    return '<script>(function(){try{var t=localStorage.getItem(\'links_theme\')||'
        . '(matchMedia(\'(prefers-color-scheme: light)\').matches?\'light\':\'dark\');'
        . 'document.documentElement.setAttribute(\'data-theme\',t);}catch(e){}})();</script>';
}

// Theme toggle button (sun/moon SVG), identical at the 3 places
// where it appears (public site, admin, partial header).
function theme_toggle_button(): string {
    return '<button class="theme-toggle" id="theme-toggle" title="' . h(t('theme_toggle_title')) . '">'
        . icon('sun', 22, 'class="icon-sun"')
        . icon('moon', 22, 'class="icon-moon"')
        . '</button>';
}

// ============================================================
// Signed cookie for PIN-unlocking categories
// Payload format: "id1:expiry1,id2:expiry2,..." (expiry = Unix timestamp)
// Each unlocked category has its own independent expiration, so that
// entering the PIN again doesn't extend the others.
// The duration is configurable via the 'pin_unlock_duration' setting (Settings > Security).
// ============================================================

function unlock_cookie_encode(array $entries): string {
    // $entries: associative array [catId => expiryTimestamp]
    $parts = [];
    foreach ($entries as $catId => $expiry) {
        $parts[] = (int)$catId . ':' . (int)$expiry;
    }
    $payload = implode(',', $parts);
    $sig = hash_hmac('sha256', $payload, SESSION_SECRET);
    return base64_encode($payload) . '.' . $sig;
}

function unlock_cookie_decode(string $cookie): ?array {
    $parts = explode('.', $cookie, 2);
    if (count($parts) !== 2) return null;
    [$encoded, $sig] = $parts;
    $payload = base64_decode($encoded, true);
    if ($payload === false) return null;
    $expectedSig = hash_hmac('sha256', $payload, SESSION_SECRET);
    if (!hash_equals($expectedSig, $sig)) return null;
    if ($payload === '') return [];

    $entries = [];
    $now = time();
    foreach (explode(',', $payload) as $part) {
        $pieces = explode(':', $part, 2);
        if (count($pieces) !== 2) continue;
        $catId  = (int)$pieces[0];
        $expiry = (int)$pieces[1];
        // Silently ignore expired entries
        if ($expiry > $now) {
            $entries[$catId] = $expiry;
        }
    }
    return $entries;
}

function is_category_unlocked(int $catId): bool {
    if (empty($_COOKIE['unlocked_cats'])) return false;
    $entries = unlock_cookie_decode($_COOKIE['unlocked_cats']);
    if ($entries === null) return false;
    return isset($entries[$catId]);
}
