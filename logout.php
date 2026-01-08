<?php
// logout.php
require __DIR__ . '/includes/session.php';

// 1. Clear all session variables
$_SESSION = [];

// 2. Terminate the session cookie in the browser
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), 
        '', 
        time() - 42000,
        $params["path"], 
        $params["domain"],
        $params["secure"], 
        $params["httponly"]
    );
}

// 3. Destroy the session on the server
session_destroy();

// 4. Redirect to login page with a success message
header("Location: signinpage.php?loggedout=1");
exit;