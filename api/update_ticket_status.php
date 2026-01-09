<?php
ob_start();
header("Content-Type: application/json");

try {
    require_once __DIR__ . "/../includes/session.php";
    require_once __DIR__ . "/../includes/db.php";

    if (empty($_SESSION['is_admin'])) {
        http_response_code(403);
        echo json_encode(["success" => false, "error" => "Unauthorized"]);
        exit;
    }

    // 1. Try to get data from JSON body
    $raw = file_get_contents("php://input");
    $data = json_decode($raw, true);

    // 2. Fallback to $_POST or $_GET if JSON failed
    $id = (int)($data['id'] ?? $_POST['id'] ?? $_GET['id'] ?? 0);
    $status_id = (int)($data['status_id'] ?? $_POST['status_id'] ?? $_GET['status_id'] ?? 0);

    $map = [
        1 => "Open",
        2 => "In Progress",
        3 => "Closed"
    ];

    if (!$id || !isset($map[$status_id])) {
        http_response_code(400);
        echo json_encode([
            "success" => false, 
            "error" => "Invalid parameters. Received ID: $id, StatusID: $status_id"
        ]);
        exit;
    }

    $final_status = $map[$status_id];

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