<?php
require __DIR__ . "/../includes/session.php";
require __DIR__ . "/../includes/db.php";

header("Content-Type: application/json");

// Check user session
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized. Please log in."]);
    exit;
}

// Get raw input
$raw = file_get_contents("php://input");

// Decode JSON safely
$data = json_decode($raw, true);
if (!is_array($data)) {
    http_response_code(400);
    echo json_encode([
        "error" => "Invalid JSON. Please send Content-Type: application/json",
        "received" => $raw
    ]);
    exit;
}

// Validate title and message
$title = trim($data['title'] ?? '');
$message = trim($data['message'] ?? '');
if ($title === '' || $message === '') {
    http_response_code(400);
    echo json_encode([
        "error" => "Title and message are required",
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
    echo json_encode(["error" => "Failed to create ticket: " . $e->getMessage()]);
}
