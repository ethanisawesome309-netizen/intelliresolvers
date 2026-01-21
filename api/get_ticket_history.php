<?php
// /home/site/wwwroot/api/get_ticket_history.php
require __DIR__ . "/../includes/session.php";
require __DIR__ . "/../includes/db.php";

header("Content-Type: application/json");

// Ensure only logged in users can see history
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$ticketId = $_GET['ticket_id'] ?? null;

if (!$ticketId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing ticket ID']);
    exit;
}

try {
    $stmt = $conn->prepare("
        SELECT 
            a.id,
            a.field_changed,
            a.old_value,
            a.new_value,
            a.changed_at,
            u.username as changed_by
        FROM ticket_audit_log a 
        JOIN users u ON a.user_id = u.id 
        WHERE a.ticket_id = ? 
        ORDER BY a.changed_at DESC
    ");
    $stmt->execute([$ticketId]);
    $history = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'data' => $history]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}