<?php

require __DIR__ . "/../includes/session.php"; // session_start() included here
require __DIR__ . "/../includes/db.php";

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: https://www.intelliresolvers.com"); // allow your frontend origin
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode([
        "error" => "Unauthorized. Please log in first."
    ]);
    exit;
}

// Get raw input
$raw = file_get_contents("php://input");
if (!$raw) {
    http_response_code(400);
    echo json_encode([
        "error" => "Empty request body. Make sure you send JSON.",
        "received" => $raw
    ]);
    exit;
}

// Decode JSON
$data = json_decode($raw, true);
if (!is_array($data)) {
    http_response_code(400);
    echo json_encode([
        "error" => "Invalid JSON. Please send Content-Type: application/json",
        "received" => $raw
    ]);
    exit;
}

// Validate required fields
$title = trim($data['title'] ?? '');
$message = trim($data['message'] ?? '');

if (!$title || !$message) {
    http_response_code(400);
    echo json_encode([
        "error" => "Title and message are required.",
        "received" => $data
    ]);
    exit;
}

// Insert into DB
$stmt = $conn->prepare(
    "INSERT INTO tickets (user_id, title, message, status)
     VALUES (:uid, :title, :message, 'open')"
);

$stmt->execute([
    "uid" => $_SESSION['user_id'],
    "title" => $title,
    "message" => $message
]);

$ticket_id = $conn->lastInsertId();

echo json_encode([
    "success" => true,
    "ticket_id" => $ticket_id
]);
