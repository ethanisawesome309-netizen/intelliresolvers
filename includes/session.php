<?php
// =============================================
// GLOBAL SESSION BOOTSTRAP
// =============================================

ini_set('session.use_strict_mode', 0);
ini_set('session.cookie_httponly', 1);

session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'domain'   => '.intelliresolvers.com',
    'secure'   => true,
    'httponly' => true,
    'samesite' => 'None'
]);

session_start();
