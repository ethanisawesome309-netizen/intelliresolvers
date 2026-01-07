<?php
require __DIR__ . "/../includes/session.php";
require __DIR__ . "/../includes/db.php";

if (empty($_SESSION['is_admin'])) {
    http_response_code(403);
    echo json_encode(["error" => "Forbidden"]);
    exit;
}

$stmt = $pdo->query(
    "SELECT t.id, t.title, t.message, t.status, t.created_at,
            u.email
     FROM tickets t
     JOIN users u ON u.id = t.user_id
     ORDER BY t.created_at DESC"
);

echo json_encode($stmt->fetchAll());
