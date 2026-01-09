<?php
// api/admin_list_tickets.php
ob_start();
header("Content-Type: application/json");

// Keep these for debugging, but they won't break the JSON thanks to ob_clean()
ini_set('display_errors', 1);
error_reporting(E_ALL);

try {
    require_once __DIR__ . '/../includes/session.php';
    require_once __DIR__ . '/../includes/db.php';

    // Check admin session
    if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
        http_response_code(403);
        throw new Exception("Unauthorized access. Admin privileges required.");
    }

    // Ensure $conn exists from db.php
    if (!isset($conn)) {
        throw new Exception("Database connection failed.");
    }

    // Join with users to get the email of the ticket creator
    $stmt = $conn->query("
        SELECT t.id, t.title, t.message, t.status, t.created_at, u.email
        FROM tickets t
        JOIN users u ON u.id = t.user_id
        ORDER BY t.created_at DESC
    ");

    $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Final clean up before outputting JSON
    if (ob_get_length()) ob_clean();
    
    echo json_encode([
        "success" => true,
        "tickets" => $tickets
    ], JSON_PRETTY_PRINT);
    exit;

} catch (Throwable $e) {
    if (ob_get_length()) ob_clean();
    
    // Set a 500 code if it wasn't a 403
    if (http_response_code() === 200) http_response_code(500);

    echo json_encode([
        "success" => false,
        "error" => $e->getMessage()
    ], JSON_PRETTY_PRINT);
    exit;
}