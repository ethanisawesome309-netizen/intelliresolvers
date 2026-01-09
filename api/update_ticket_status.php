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

    $data = json_decode(file_get_contents("php://input"), true);
    $id = (int)($data['id'] ?? 0);
    $status = $data['status'] ?? '';

    // Convert hyphen back to space for Database consistency
    if ($status === "In-Progress") {
        $status = "In Progress";
    }

    $allowed = ['Open', 'In Progress', 'Closed'];

    if (!$id || !in_array($status, $allowed)) {
        http_response_code(400);
        echo json_encode(["success" => false, "error" => "Invalid status"]);
        exit;
    }

    $stmt = $conn->prepare("UPDATE tickets SET status = ? WHERE id = ?");
    $stmt->execute([$status, $id]);

    ob_clean();
    echo json_encode(["success" => true]);
    exit;

} catch (Throwable $e) {
    ob_clean();
    http_response_code(500);
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
    exit;
}