<?php
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.use_strict_mode', 1);
session_start();

header("Content-Type: application/json");

if (!isset($_SESSION["user_id"])) {
  http_response_code(401);
  echo json_encode(["error" => "Unauthorized"]);
  exit;
}

$data = json_decode(file_get_contents("php://input"), true);

if (empty($data["title"]) || empty($data["message"])) {
  http_response_code(400);
  echo json_encode(["error" => "Missing fields"]);
  exit;
}

require __DIR__ . "/../includes/db.php";

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
