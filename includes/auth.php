<?php
// ============================================================
// includes/auth.php — Admin session management
// ============================================================

function session_start_secure(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_set_cookie_params([
            'lifetime' => SESSION_LIFETIME,
            'path'     => '/',
            'secure'   => true,
            'httponly' => true,
            'samesite' => 'Strict',
        ]);
        session_name('links_admin');
        session_start();
    }
}

function is_logged_in(): bool {
    session_start_secure();
    return !empty($_SESSION['admin_logged_in'])
        && !empty($_SESSION['admin_ip'])
        && $_SESSION['admin_ip'] === $_SERVER['REMOTE_ADDR']
        && !empty($_SESSION['admin_time'])
        && (time() - $_SESSION['admin_time']) < SESSION_LIFETIME;
}

function require_login(): void {
    if (!is_logged_in()) {
        header('Location: ' . BASE_URL . '/admin/login.php');
        exit;
    }
}

function login(string $password): bool {
    $hash = setting('admin_password_hash');
    if (hash('sha256', $password) === $hash) {
        session_start_secure();
        session_regenerate_id(true);
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_ip']        = $_SERVER['REMOTE_ADDR'];
        $_SESSION['admin_time']      = time();
        return true;
    }
    return false;
}

function logout(): void {
    session_start_secure();
    $_SESSION = [];
    session_destroy();
}

function csrf_token(): string {
    session_start_secure();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_check(): void {
    if (!hash_equals(csrf_token(), $_POST['csrf_token'] ?? '')) {
        http_response_code(403);
        die(h(t('err_csrf_invalid')));
    }
}
