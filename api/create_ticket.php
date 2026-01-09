<?php
require __DIR__ . "/../includes/session.php";
require __DIR__ . "/../includes/db.php";

header("Content-Type: application/json");

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$title   = trim($data['title'] ?? '');
$message = trim($data['message'] ?? '');

if ($title === '' || $message === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Missing fields']);
    exit;
}

// CONCEPT: Insert numeric status_id (1 = Open) 
// instead of the 'status' string.
$stmt = $conn->prepare(
    "INSERT INTO tickets (user_id, title, message, status_id)
     VALUES (:uid, :title, :message, 1)"
);

$stmt->execute([
    'uid' => $_SESSION['user_id'],
    'title' => $title,
    'message' => $message
]);

echo json_encode(['success' => true]);