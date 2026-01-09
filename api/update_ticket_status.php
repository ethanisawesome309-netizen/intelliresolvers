<?php
ob_start();
header("Content-Type: application/json");
error_reporting(E_ALL);
ini_set('display_errors', 0);

require_once __DIR__ . "/../includes/session.php";
require_once __DIR__ . "/../includes/db.php";

/* --- ENFORCE POST --- */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        "success" => false,
        "error" => "POST required"
    ]);
    exit;
}

/* --- ADMIN CHECK --- */
if (empty($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    http_response_code(403);
    echo json_encode([
        "success" => false,
        "error" => "Unauthorized access",
        "debug" => [
            "session" => $_SESSION
        ]
    ]);
    exit;
}

/* --- PARSE JSON --- */
$data = json_decode(file_get_contents("php://input"), true);

$id = (int)($data['id'] ?? 0);
$status_id = (int)($data['status_id'] ?? 0);

$map = [
    1 => "Open",
    2 => "In Progress",
    3 => "Closed"
];

if (!$id || !isset($map[$status_id])) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "error" => "Invalid parameters"
    ]);
    exit;
}

/* --- UPDATE --- */
$stmt = $conn->prepare("UPDATE tickets SET status = ? WHERE id = ?");
$stmt->execute([$map[$status_id], $id]);

ob_clean();
echo json_encode([
    "success" => true,
    "id" => $id,
    "status" => $map[$status_id]
]);
exit;
