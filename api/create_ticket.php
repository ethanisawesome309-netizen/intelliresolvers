<?php
require __DIR__ . "/../includes/session.php";
require __DIR__ . "/../includes/db.php";

header("Content-Type: application/json");

// ==================================================
// AUTH CHECK
// ==================================================
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode([
        "error" => "Unauthorized – please log in"
    ]);
    exit;
}

// ==================================================
// INPUT
// ==================================================
$data = json_decode(file_get_contents("php://input"), true);

if (
    empty($data['title']) ||
    empty($data['message'])
) {
    http_response_code(400);
    echo json_encode([
        "error" => "Title and message are required"
    ]);
    exit;
}

// ==================================================
// INSERT TICKET
// ==================================================
$stmt = $conn->prepare(
    "INSERT INTO tickets (user_id, title, message, status)
     VALUES (:uid, :title, :message, 'open')"
);

$stmt->execute([
    'uid'     => $_SESSION['user_id'],
    'title'   => $data['title'],
    'message' => $data['message']
]);

echo json_encode([
    "success" => true
]);
