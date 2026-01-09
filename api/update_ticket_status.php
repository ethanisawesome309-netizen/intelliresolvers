<?php
ob_start();
header("Content-Type: application/json");

try {
    require_once __DIR__ . "/../includes/session.php";
    require_once __DIR__ . "/../includes/db.php";

    // 1. Session verification
    if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
        http_response_code(403);
        echo json_encode(["success" => false, "error" => "Unauthorized access"]);
        exit;
    }

    // 2. Parse JSON
    $raw = file_get_contents("php://input");
    $data = json_decode($raw, true);

    $id = (int)($data['id'] ?? 0);
    $status_id = (int)($data['status_id'] ?? 0);

    $map = [
        1 => "Open",
        2 => "In Progress",
        3 => "Closed"
    ];

    if (!$id || !isset($map[$status_id])) {
        http_response_code(400);
        echo json_encode(["success" => false, "error" => "Invalid parameters"]);
        exit;
    }

    $final_status = $map[$status_id];

    // 3. Database Update
    $stmt = $conn->prepare("UPDATE tickets SET status = ? WHERE id = ?");
    $stmt->execute([$final_status, $id]);

    ob_clean();
    echo json_encode(["success" => true]);
    exit;

} catch (Throwable $e) {
    ob_clean();
    http_response_code(500);
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
    exit;
}