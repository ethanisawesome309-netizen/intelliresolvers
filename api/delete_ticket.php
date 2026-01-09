<?php
ob_start();
header("Content-Type: application/json");

try {
    require_once __DIR__ . "/../includes/session.php";
    require_once __DIR__ . "/../includes/db.php";

    // Security: Strict Admin Guard
    if (empty($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
        http_response_code(403);
        echo json_encode(["success" => false, "error" => "Unauthorized"]);
        exit;
    }

    $data = json_decode(file_get_contents("php://input"), true);
    $id = (int)($data['id'] ?? 0);

    if (!$id) {
        throw new Exception("Invalid ticket ID.", 400);
    }

    // CONCEPT: Prepared statement to prevent SQL injection
    $stmt = $conn->prepare("DELETE FROM tickets WHERE id = ?");
    $stmt->execute([$id]);

    if ($stmt->rowCount() === 0) {
        throw new Exception("Ticket not found or already deleted.");
    }

    ob_clean();
    echo json_encode(["success" => true, "message" => "Deleted successfully"]);

} catch (Throwable $e) {
    ob_clean();
    http_response_code($e->getCode() ?: 500);
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}