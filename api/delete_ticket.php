<?php
// api/delete_ticket.php
ob_start();
header("Content-Type: application/json");
error_reporting(E_ALL);
ini_set('display_errors', 0); 

try {
    require_once __DIR__ . "/../includes/session.php";
    require_once __DIR__ . "/../includes/db.php";

    // 1. Basic Admin check
    if (empty($_SESSION['is_admin'])) {
        throw new Exception("Unauthorized access", 403);
    }

    // 2. Role-based check: Block Junior and Intermediate from deleting
    $user_role = $_SESSION['role'] ?? '';
    if ($user_role === 'Junior' || $user_role === 'Intermediate') {
        throw new Exception("Insufficient permissions to archive tickets.", 403);
    }

    $data = json_decode(file_get_contents("php://input"), true);
    $id = (int)($data['id'] ?? 0);

    if (!$id) throw new Exception("Invalid ID", 400);

    $stmt = $conn->prepare("DELETE FROM tickets WHERE id = ?");
    $stmt->execute([$id]);

    if (ob_get_length()) ob_clean();
    echo json_encode(['success' => true]);

} catch (Throwable $e) {
    if (ob_get_length()) ob_clean();
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}