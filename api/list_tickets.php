<?php
require __DIR__ . "/../includes/session.php";
require __DIR__ . "/../includes/db.php";

header("Content-Type: application/json");

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["error" => "Not authenticated"]);
    exit;
}

$stmt = $conn->prepare("SELECT id, title, message, status FROM tickets WHERE user_id = :uid ORDER BY id DESC");
$stmt->execute(['uid' => $_SESSION['user_id']]);
$tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($tickets);
?>
