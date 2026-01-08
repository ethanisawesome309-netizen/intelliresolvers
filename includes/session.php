<?php
declare(strict_types=1);

ini_set('session.use_strict_mode', '1');
ini_set('session.cookie_secure', '1');
ini_set('session.cookie_httponly', '1');

if (session_status() === PHP_SESSION_NONE) {

    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'domain'   => 'intelliresolvers.com',
        'secure'   => true,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);

    session_start();
}

// DEBUG FLAG
if (!defined('SESSION_DEBUG')) {
    define('SESSION_DEBUG', true);
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
