<?php
require __DIR__ . "/../includes/session.php";
require __DIR__ . "/../includes/db.php";

header("Content-Type: application/json");

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode([]);
    exit;
}

// CONCEPT: Join with 'statuses' table so the user sees 
// "Open" instead of just "1"
$stmt = $conn->prepare(
    "SELECT t.id, t.title, t.message, s.label as status
     FROM tickets t
     INNER JOIN statuses s ON t.status_id = s.id
     WHERE t.user_id = :uid
     ORDER BY t.id DESC"
);

$stmt->execute(['uid' => $_SESSION['user_id']]);
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));