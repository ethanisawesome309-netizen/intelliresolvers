<?php
// Start output buffering to prevent HTML warnings breaking JSON
ob_start();

// JSON response header
header("Content-Type: application/json");

// Show errors for development (set to 0 in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start session only if none exists
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include DB connection and session helpers
require __DIR__ . "/../includes/db.php";       // make sure this defines $pdo
require __DIR__ . "/../includes/session.php";  // optional session helpers

try {
    // Check if admin
    if (empty($_SESSION['is_admin'])) {
        throw new Exception("Forbidden: not admin", 403);
    }

    // Ensure $pdo exists
    if (!isset($pdo)) {
        throw new Exception("Database connection not found");
    }

    // Fetch tickets
    $stmt = $pdo->query("
        SELECT t.id, t.title, t.message, t.status, t.created_at,
               u.email
        FROM tickets t
        JOIN users u ON u.id = t.user_id
        ORDER BY t.created_at DESC
    ");

    $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Clear any accidental HTML output
    ob_clean();

    echo json_encode([
        "success" => true,
        "tickets" => $tickets
    ]);

} catch (Exception $e) {
    // Clear buffer in case warnings/notices output HTML
    ob_clean();

    $code = $e->getCode() ?: 500;
    http_response_code($code);

    echo json_encode([
        "success" => false,
        "error" => $e->getMessage()
    ]);
}
