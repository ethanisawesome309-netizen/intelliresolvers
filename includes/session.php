<?php
declare(strict_types=1);

// Enforce strict session handling
ini_set('session.use_strict_mode', '1');

if (session_status() === PHP_SESSION_NONE) {

    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'domain'   => 'intelliresolvers.com', // IMPORTANT
        'secure'   => true,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);

    session_start();
}

// CSRF token (safe to include everywhere)
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
