<?php
// /includes/session.php

if (session_status() === PHP_SESSION_NONE) {

    ini_set('session.save_handler', 'files');
    ini_set('session.save_path', __DIR__ . '/../sessions');

    session_set_cookie_params([
        'path' => '/',
        'secure' => true,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);

    session_start();
}
