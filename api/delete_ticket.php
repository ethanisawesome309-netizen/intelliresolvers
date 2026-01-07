<?php
require __DIR__ . "/../includes/session.php";
require __DIR__ . "/../includes/db.php";

if (empty($_SESSION['is_admin'])) {
    http_response_code(403);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$id = (int)($data['id'] ?? 0);

if (!$id) {
    http_response_code(400);
    exit;
}

$stmt = $pdo->prepare("DELETE FROM tickets WHERE id = ?");
$stmt->execute([$id]);

echo json_encode(["success" => true]);
