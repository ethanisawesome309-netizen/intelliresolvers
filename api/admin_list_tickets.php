<?php
ob_start();
header("Content-Type: application/json");

ini_set('display_errors', 1);
error_reporting(E_ALL);

try {
    require_once __DIR__ . '/../includes/session.php';
    require_once __DIR__ . '/../includes/db.php';

    if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
        http_response_code(403);
        throw new Exception("Unauthorized access. Admin privileges required.");
    }

    $stmt = $conn->query("
        SELECT t.id, t.title, t.message, t.status, t.created_at, u.email
        FROM tickets t
        JOIN users u ON u.id = t.user_id
        ORDER BY t.created_at DESC
    ");

    $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

    ob_clean();
    echo json_encode([
        "success" => true,
        "tickets" => $tickets
    ], JSON_PRETTY_PRINT);
    exit;

} catch (Throwable $e) {
    ob_clean();
    echo json_encode([
        "success" => false,
        "error" => $e->getMessage()
    ], JSON_PRETTY_PRINT);
    exit;
}