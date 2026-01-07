<?php
// Enable full error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// JSON response header
header("Content-Type: application/json");

// Start session
if (!isset($_SESSION)) session_start();

// Include DB connection and session helpers
require __DIR__ . "/../includes/db.php";
require __DIR__ . "/../includes/session.php";

// Check admin session
if (empty($_SESSION['is_admin'])) {
    http_response_code(403);
    echo json_encode([
        "error" => "Forbidden",
        "session" => $_SESSION ?? null
    ]);
    exit;
}

// Fetch tickets safely
try {
    $stmt = $pdo->query(
        "SELECT t.id, t.title, t.message, t.status, t.created_at,
                u.email
         FROM tickets t
         JOIN users u ON u.id = t.user_id
         ORDER BY t.created_at DESC"
    );

    $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "tickets" => $tickets
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "error" => "Database error",
        "message" => $e->getMessage()
    ]);
}
