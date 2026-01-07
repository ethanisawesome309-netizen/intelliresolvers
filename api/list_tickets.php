<?php
// Allow requests from both www and non-www domains
$allowed_origins = [
    "https://www.intelliresolvers.com",
    "https://intelliresolvers.com"
];

if (isset($_SERVER['HTTP_ORIGIN']) && in_array($_SERVER['HTTP_ORIGIN'], $allowed_origins)) {
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
    header("Access-Control-Allow-Credentials: true");
}

header("Content-Type: application/json");

// Secure session settings
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.use_strict_mode', 1);
session_start();

// Check if user is logged in
if (!isset($_SESSION["user_id"])) {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}

// Include your database connection
require __DIR__ . "/../includes/db.php";

// Fetch tickets for logged-in user
$stmt = $pdo->prepare(
    "SELECT id, title, message, status, created_at
     FROM tickets
     WHERE user_id = ?
     ORDER BY created_at DESC"
);
$stmt->execute([$_SESSION["user_id"]]);

$tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($tickets);
