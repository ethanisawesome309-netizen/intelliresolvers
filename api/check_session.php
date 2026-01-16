<?php
// FORCE CANONICAL DOMAIN FIRST
if ($_SERVER['HTTP_HOST'] !== 'intelliresolvers.com') {
    header(
        "Location: https://intelliresolvers.com" . $_SERVER['REQUEST_URI'],
        true,
        301
    );
    exit;
}

require __DIR__ . "/../includes/session.php";

header("Content-Type: application/json");

// DO NOT ADD CORS HEADERS ❌
// Same-origin requests do not need them

echo json_encode([
    'authenticated' => isset($_SESSION['user_id']),
    'user_id' => $_SESSION['user_id'] ?? null
]);
