<?php
require __DIR__ . "/../includes/session.php";
require __DIR__ . "/../includes/db.php";

header("Content-Type: application/json");

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized. Please log in."]);
    exit;
}

// Debug incoming raw JSON
$raw = file_get_contents("php://input");
$data = json_decode($raw, true);

// If JSON is invalid, return error
if ($data === null) {
    http_response_code(400);
    echo json_encode([
        "error" => "Invalid JSON. Received: " . $raw
    ]);
    exit;
}

// Trim and validate
$title = trim($data['title'] ?? '');
$message = trim($data['message'] ?? '');

if ($title === '' || $message === '') {
    http_response_code(400);
    echo json_encode([
        "error" => "Title and message are required.",
        "received" => $data
    ]);
    exit;
}

// Insert ticket
try {
    $stmt = $conn->prepare(
        "INSERT INTO tickets (user_id, title, message, status)
         VALUES (:uid, :title, :message, 'open')"
    );
    $stmt->execute([
        'uid' => $_SESSION['user_id'],
        'title' => $title,
        'message' => $message
    ]);

    echo json_encode([
        "success" => true,
        "ticket_id" => $conn->lastInsertId()
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "error" => "Failed to create ticket: " . $e->getMessage()
    ]);
}
