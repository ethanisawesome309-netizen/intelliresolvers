<?php
session_start();

// For testing purposes: force admin session
$_SESSION['is_admin'] = true;

// Optional: store other user info
$_SESSION['email'] = 'admin@example.com';

echo json_encode([
    "success" => true,
    "message" => "Admin session set",
    "session" => $_SESSION
]);
