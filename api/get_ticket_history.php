<?php
// Enable error reporting to see the crash in the browser
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require __DIR__ . "/../includes/session.php";
require __DIR__ . "/../includes/db.php";

header("Content-Type: application/json");

$ticketId = $_GET['ticket_id'] ?? null;

if (!$ticketId) {
    echo json_encode(['success' => false, 'error' => 'Missing ticket ID']);
    exit;
}

try {
    // Check if $conn actually exists from db.php
    if (!isset($conn)) {
        throw new Exception("Database connection variable not found.");
    }

    $stmt = $conn->prepare("
        SELECT 
            a.id,
            a.field_changed,
            a.old_value,
            a.new_value,
            a.changed_at,
            u.email as changed_by
        FROM ticket_audit_log a 
        JOIN users u ON a.user_id = u.id 
        WHERE a.ticket_id = ? 
        ORDER BY a.changed_at DESC
    ");
    $stmt->execute([$ticketId]);
    $history = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'data' => $history]);
} catch (Exception $e) {
    // This will now return the EXACT SQL or PHP error to your console
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}