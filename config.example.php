<?php
// ============================================================
// Configuration template — copy this file to config.php and
// fill in your own values. config.php is gitignored on purpose:
// never commit real credentials or secrets.
// ============================================================

define('DB_HOST', 'localhost');
define('DB_NAME', 'your_database_name');     // Your MySQL database name
define('DB_USER', 'your_database_user');     // MySQL user
define('DB_PASS', 'your_database_password'); // MySQL password
define('DB_CHARSET', 'utf8mb4');

// Base URL of your site (no trailing slash)
define('BASE_URL', 'https://your-domain.example.com');

// Secret key used to sign the PIN unlock cookies — REQUIRED, must be
// changed to a long random value. Generate one with:
//   php -r "echo bin2hex(random_bytes(32));"
define('SESSION_SECRET', 'change-this-to-a-long-random-value');

// Admin session lifetime in seconds (8 hours)
define('SESSION_LIFETIME', 28800);
