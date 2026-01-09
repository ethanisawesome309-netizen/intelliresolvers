<?php
ob_start();
header("Content-Type: application/json");
error_reporting(E_ALL);
ini_set('display_errors', 0); 

try {
    require_once __DIR__ . "/../includes/session.php";
    require_once __DIR__ . "/../includes/db.php";

    if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
        throw new Exception("Unauthorized access", 403);
    }

    $data = json_decode(file_get_contents("php://input"), true);
    $id = (int)($data['id'] ?? 0);
    $status_id = (int)($data['status_id'] ?? 0);

    if (!$id || !$status_id) {
        throw new Exception("Invalid parameters", 400);
    }

    // CONCEPT: The DB Foreign Key now validates the status_id automatically
    $stmt = $conn->prepare("UPDATE tickets SET status_id = ? WHERE id = ?");
    $stmt->execute([$status_id, $id]);

    ob_clean();
    echo json_encode(["success" => true, "id" => $id, "status_id" => $status_id]);

} catch (Throwable $e) {
    ob_clean();
    http_response_code($e->getCode() ?: 500);
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}