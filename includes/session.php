<?php
declare(strict_types=1);

/**
 * Azure HTTPS fix
 */
if (
    (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) &&
     $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
    || (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
) {
    $_SERVER['HTTPS'] = 'on';
}

/**
 * Use ONE session name only
 */
session_name('INTELLISESSID');

/**
 * Session cookie settings
 */
ini_set('session.cookie_domain', '.intelliresolvers.com');
ini_set('session.cookie_path', '/');
ini_set('session.use_only_cookies', '1');
ini_set('session.use_strict_mode', '1');
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Lax');

/**
 * Only set Secure if HTTPS is truly on
 */
if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    ini_set('session.cookie_secure', '1');
}

session_start();

/**
 * CSRF token
 */
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
