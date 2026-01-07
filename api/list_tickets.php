<?php
// CORS
$allowed_origins = [
    "https://www.intelliresolvers.com",
    "https://intelliresolvers.com"
];

if (isset($_SERVER['HTTP_ORIGIN']) && in_array($_SERVER['HTTP_ORIGIN'], $allowed_origins)) {
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
    header("Access-Control-Allow-Credentials: true");
}

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;

header("Content-Type: application/json");

// Secure session
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.use_strict_mode', 1);
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}

require __DIR__ . "/../includes/db.php";

$stmt = $pdo->prepare("SELECT id, title, message, status, created_at FROM tickets WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);

echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
