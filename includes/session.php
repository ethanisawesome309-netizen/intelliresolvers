<?php
declare(strict_types=1);

// Strict session handling
ini_set('session.use_strict_mode', '1');

// Define cookie params ONCE
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'domain'   => 'intelliresolvers.com',
    'secure'   => true,
    'httponly' => true,
    'samesite' => 'Lax'
]);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// CSRF token for authenticated actions
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
