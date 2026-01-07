<?php
// Start output buffering to prevent accidental HTML output
ob_start();

// Force JSON headers
header("Content-Type: application/json");

// Enable full PHP error reporting (development only)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start session
if (!isset($_SESSION)) session_start();

// Include DB and session files
require __DIR__ . "/../includes/db.php";
require __DIR__ . "/../includes/session.php";

try {
    // Check admin session
    if (empty($_SESSION['is_admin'])) {
        throw new Exception("Forbidden: not admin", 403);
    }

    // Fetch tickets
    $stmt = $pdo->query(
        "SELECT t.id, t.title, t.message, t.status, t.created_at,
                u.email
         FROM tickets t
         JOIN users u ON u.id = t.user_id
         ORDER BY t.created_at DESC"
    );

    $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Clean buffer and output JSON
    ob_clean();
    echo json_encode([
        "success" => true,
        "tickets" => $tickets
    ]);

} catch (Exception $e) {
    // Clean output buffer
    ob_clean();

    // Set HTTP status code if available
    $code = $e->getCode() ?: 500;
    http_response_code($code);

    // Return JSON error
    echo json_encode([
        "success" => false,
        "error" => $e->getMessage()
    ]);
}
