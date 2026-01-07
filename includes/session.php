<?php
// includes/session.php

// Force HTTPS cookies only
ini_set('session.use_strict_mode', 1);

session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => 'intelliresolvers.com', // 🔥 MUST MATCH DOMAIN
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Lax',
]);

session_name('INTELLISESSID');
session_start();
?>