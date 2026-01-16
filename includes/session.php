<?php

if (session_status() === PHP_SESSION_NONE) {
    // Set cookie parameters for security and persistence
    session_set_cookie_params([
        'lifetime' => 0, // Cookie expires when browser closes
        'path' => '/',
        'domain' => '', 
        'secure' => false, // Set to true if your site uses HTTPS
        'httponly' => true, // Prevents JS from accessing session ID
        'samesite' => 'Lax'
    ]);

    session_start();
}

// ==================== TIMEOUT LOGIC ====================
$timeout_duration = 1800; // 30 minutes in seconds

if (isset($_SESSION['last_activity'])) {
    $elapsed_time = time() - $_SESSION['last_activity'];
    
    if ($elapsed_time > $timeout_duration) {
        // Clear and destroy the session
        session_unset();
        session_destroy();
        
        // Redirect to login with a timeout flag
        header("Location: /signinpage.php?error=timeout");
        exit;
    }
}

// Update the timestamp so the 30-minute clock restarts on every page load
$_SESSION['last_activity'] = time();