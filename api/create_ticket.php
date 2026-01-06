<?php
session_start();
header("Content-Type: application/json");

if (!isset($_SESSION["user_id"])) {
  http_response_code(401);
  echo json_encode(["success" => false]);
  exit;
}

$data = json_decode(file_get_contents("php://input"), true);

$title = trim($data["title"] ?? "");
$message = trim($data["message"] ?? "");

if ($title === "" || $message === "") {
  echo json_encode(["success" => false]);
  exit;
}

require "/includes/db.php";

$stmt = $pdo->prepare(
  "INSERT INTO tickets (user_id, title, message)
   VALUES (?, ?, ?)"
);
$stmt->execute([
  $_SESSION["user_id"],
  $title,
  $message
]);

echo json_encode(["success" => true]);
?>