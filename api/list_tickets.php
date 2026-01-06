<?php
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.use_strict_mode', 1);
session_start();

header("Content-Type: application/json");

if (!isset($_SESSION["user_id"])) {
  http_response_code(401);
  echo json_encode([]);
  exit;
}

require __DIR__ . "/../includes/db.php";

$stmt = $pdo->prepare(
  "SELECT id, title, message, status, created_at
   FROM tickets
   WHERE user_id = ?
   ORDER BY created_at DESC"
);

$stmt->execute([$_SESSION["user_id"]]);

echo json_encode($stmt->fetchAll());
