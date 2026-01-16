<?php
require __DIR__ . "/../includes/session.php";

header("Content-Type: application/json");

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode([
        "error" => "Not authenticated"
    ]);
    exit;
}

echo json_encode([
    "user_id" => $_SESSION['user_id']
]);
