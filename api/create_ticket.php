<?php
// Allow requests from both www and non-www
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

// Handle preflight OPTIONS request for CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit;
}

header("Content-Type: application/json");

// Secure session settings
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.use_strict_mode', 1);

// Force cookies for .intelliresolvers.com so www and non-www share the session
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '.intelliresolvers.com',
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Lax'
]);

session_start();

// Check login
if (!isset($_SESSION["user_id"])) {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}

// Get JSON input
$data = json_decode(file_get_contents("php://input"), true);

// Validate input
if (empty($data["title"]) || empty($data["message"])) {
    http_response_code(400);
    echo json_encode(["error" => "Missing fields"]);
    exit;
}

// Include database connection
require __DIR__ . "/../includes/db.php";

// Insert ticket into database
$stmt = $pdo->prepare(
    "INSERT INTO tickets (user_id, title, message, status)
     VALUES (?, ?, ?, 'open')"
);

$stmt->execute([
    $_SESSION["user_id"],
    $data["title"],
    $data["message"]
]);

echo json_encode(["success" => true]);
