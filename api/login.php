<?php
session_start();
header("Content-Type: application/json");

require_once __DIR__ . "/includes/db.php";

$data = json_decode(file_get_contents("php://input"), true);
$email = trim($data["email"] ?? "");
$password = $data["password"] ?? "";

$stmt = $conn->prepare("SELECT id, password_hash FROM users WHERE email = ? LIMIT 1");
$stmt->execute([$email]);
$user = $stmt->fetch();

if ($user && password_verify($password, $user["password_hash"])) {
    $_SESSION["user_id"] = $user["id"];
    echo json_encode(["success" => true, "user_id" => $user["id"]]);
} else {
    http_response_code(401);
    echo json_encode(["error" => "Invalid credentials"]);
}
