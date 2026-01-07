<?php
require __DIR__ . "/../includes/session.php";
require __DIR__ . "/../includes/db.php";

header("Content-Type: application/json");

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode([
        "error" => "Unauthorized"
    ]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

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
