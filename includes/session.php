<?php
// /includes/session.php

if (session_status() === PHP_SESSION_NONE) {

    session_set_cookie_params([
        'path' => '/',
        'secure' => true,     // requires HTTPS (you have it)
        'httponly' => true,
        'samesite' => 'Lax'
    ]);

    session_start();
}
