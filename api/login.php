<?php
require __DIR__ . "/../includes/session.php";
require __DIR__ . "/../includes/db.php";

header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

$email = trim($data['email'] ?? '');
$password = $data['password'] ?? '';

if (!$email || !$password) {
    http_response_code(400);
    echo json_encode(["error" => "Email and password are required."]);
    exit;
}

$stmt = $conn->prepare("SELECT id, password_hash FROM users WHERE email = :email LIMIT 1");
$stmt->execute(['email' => $email]);
$user = $stmt->fetch();

if ($user && password_verify($password, $user['password_hash'])) {
    session_regenerate_id(true);
    $_SESSION['user_id'] = $user['id'];
    echo json_encode(["user_id" => $user['id']]);
} else {
    http_response_code(401);
    echo json_encode(["error" => "Invalid email or password."]);
}
?>
