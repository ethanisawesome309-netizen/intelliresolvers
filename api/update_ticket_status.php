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
        "debug" => ["session" => $_SESSION]
    ]);
    exit;
}

/* --- PARSE JSON --- */
$data = json_decode(file_get_contents("php://input"), true);

$id = (int)($data['id'] ?? 0);
$status_id = (int)($data['status_id'] ?? 0);

if (!$id || !in_array($status_id, [1,2,3])) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "error" => "Invalid parameters"
    ]);
    exit;
}

/* --- UPDATE --- */
try {
    $stmt = $conn->prepare("UPDATE tickets SET status_id = ? WHERE id = ?");
    $stmt->execute([$status_id, $id]);

    echo json_encode([
        "success" => true,
        "id" => $id,
        "status_id" => $status_id
    ]);
    exit;
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "error" => $e->getMessage(),
        "trace" => $e->getTraceAsString()
    ]);
    exit;
}
