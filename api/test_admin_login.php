<?php
// Start session
session_start();

// Set admin session for testing
$_SESSION['is_admin'] = true;
$_SESSION['email'] = 'admin@example.com';

// Ensure cookie path is /
session_set_cookie_params(['path' => '/']);
session_write_close();

// Return JSON response
header("Content-Type: application/json");
echo json_encode([
    "success" => true,
    "message" => "Admin session set",
    "session" => $_SESSION
]);
