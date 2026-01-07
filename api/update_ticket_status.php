<?php
require __DIR__ . "/../includes/session.php";
require __DIR__ . "/../includes/db.php";

if (empty($_SESSION['is_admin'])) {
    http_response_code(403);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

$id = (int)($data['id'] ?? 0);
$status = $data['status'] ?? '';

$allowed = ['Open', 'In Progress', 'Closed'];

if (!$id || !in_array($status, $allowed)) {
    http_response_code(400);
    exit;
}

$stmt = $pdo->prepare(
    "UPDATE tickets SET status = ? WHERE id = ?"
);
$stmt->execute([$status, $id]);

echo json_encode(["success" => true]);
