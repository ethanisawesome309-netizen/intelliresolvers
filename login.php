<?php
require __DIR__ . "/../includes/db.php"; // your PDO connection

// Set session cookie params for www and non-www
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '.intelliresolvers.com', // leading dot for www + non-www
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();

header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);
$email = $data['email'] ?? '';
$password = $data['password'] ?? '';

if (!$email || !$password) {
    http_response_code(400);
    echo json_encode(["error" => "Missing email or password"]);
    exit;
}

// Fetch user
$stmt = $pdo->prepare("SELECT id, password_hash FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, $user['password_hash'])) {
    http_response_code(401);
    echo json_encode(["error" => "Invalid credentials"]);
    exit;
}

// Login success
$_SESSION['user_id'] = $user['id'];
echo json_encode(["success" => true]);
