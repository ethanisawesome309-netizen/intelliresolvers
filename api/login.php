<?php
// Allow CORS from both www and non-www
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

// Secure session settings
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.use_strict_mode', 1);
session_start();

// Get JSON input
$data = json_decode(file_get_contents("php://input"), true);
$email = $data['email'] ?? '';
$password = $data['password'] ?? '';

if (!$email || !$password) {
    http_response_code(400);
    echo json_encode(["error" => "Missing email or password"]);
    exit;
}

require __DIR__ . "/../includes/db.php";

// Verify user
$stmt = $pdo->prepare("SELECT id, password_hash FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, $user['password_hash'])) {
    http_response_code(401);
    echo json_encode(["error" => "Invalid credentials"]);
    exit;
}

// Login successful, store user_id in session
$_SESSION['user_id'] = $user['id'];

echo json_encode(["success" => true]);
