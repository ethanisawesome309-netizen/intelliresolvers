<?php
// CORS
$allowed_origins = [
    "https://www.intelliresolvers.com",
    "https://intelliresolvers.com"
];

if (isset($_SERVER['HTTP_ORIGIN']) && in_array($_SERVER['HTTP_ORIGIN'], $allowed_origins)) {
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
    header("Access-Control-Allow-Credentials: true");
    header("Access-Control-Allow-Methods: POST, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type");
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

// Get JSON input
$data = json_decode(file_get_contents("php://input"), true);

if (empty($data["title"]) || empty($data["message"])) {
    http_response_code(400);
    echo json_encode(["error" => "Missing fields"]);
    exit;
}

require __DIR__ . "/../includes/db.php";

$stmt = $pdo->prepare(
    "INSERT INTO tickets (user_id, title, message, status) VALUES (?, ?, ?, 'open')"
);
$stmt->execute([$_SESSION['user_id'], $data["title"], $data["message"]]);

echo json_encode(["success" => true]);
