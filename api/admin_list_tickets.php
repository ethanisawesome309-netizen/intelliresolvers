<?php
ob_start();
header("Content-Type: application/json");
error_reporting(E_ALL);
ini_set('display_errors', 0);

try {
    require_once __DIR__ . '/../includes/session.php';
    require_once __DIR__ . '/../includes/db.php';

    if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
        throw new Exception("Unauthorized access", 403);
    }

    // CONCEPT: Relational Join to get Labels and IDs in one go
    $stmt = $conn->query("
        SELECT t.id, t.title, t.message, t.status_id, u.email, s.label as status
        FROM tickets t
        JOIN users u ON u.id = t.user_id
        JOIN statuses s ON s.id = t.status_id
        ORDER BY t.created_at DESC
    ");

    $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

    ob_clean();
    echo json_encode(["success" => true, "tickets" => $tickets]);

} catch (Throwable $e) {
    ob_clean();
    http_response_code($e->getCode() ?: 500);
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}