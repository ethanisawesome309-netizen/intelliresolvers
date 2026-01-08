<?php
ob_start();
header("Content-Type: application/json");

// Enable full PHP error reporting (development only)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

require __DIR__ . "/../includes/db.php";
require __DIR__ . "/../includes/session.php";

try {
    // Check admin session
    if (empty($_SESSION['is_admin'])) {
        throw new Exception("Forbidden: not admin", 403);
    }

    // Fetch tickets from database
    $stmt = $pdo->query(
        "SELECT t.id, t.title, t.message, t.status, t.created_at,
                u.email
         FROM tickets t
         JOIN users u ON u.id = t.user_id
         ORDER BY t.created_at DESC"
    );

    $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Clean output buffer and return JSON
    ob_clean();
    echo json_encode([
        "success" => true,
        "tickets" => $tickets
    ]);

} catch (Exception $e) {
    ob_clean();
    $code = $e->getCode() ?: 500;
    http_response_code($code);
    echo json_encode([
        "success" => false,
        "error" => $e->getMessage()
    ]);
}
