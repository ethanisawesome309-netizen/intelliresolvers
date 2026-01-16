<?php
// api/patch_ticket.php
ob_start();
header("Content-Type: application/json");
error_reporting(E_ALL);
ini_set('display_errors', 0); 

try {
    require_once __DIR__ . "/../includes/session.php";
    require_once __DIR__ . "/../includes/db.php";

    if (empty($_SESSION['is_admin'])) {
        throw new Exception("Unauthorized access", 403);
    }

    $data = json_decode(file_get_contents("php://input"), true);
    if (!$data) throw new Exception("No data provided", 400);

    $id = (int)($data['id'] ?? 0);
    $allowedFields = ['status_id', 'priority_id', 'assigned_to', 'claimed_by'];
    $updateField = null;
    $val = null;

    foreach ($allowedFields as $field) {
        if (array_key_exists($field, $data)) {
            $updateField = $field;
            $val = ($data[$field] === "" || $data[$field] === null) ? null : (int)$data[$field];
            break;
        }
    }

    if (!$id || !$updateField) throw new Exception("Invalid ID or field", 400);

    // Update Database
    $sql = "UPDATE tickets SET $updateField = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$val, $id]);

    // --- REAL-TIME NOTIFICATION ---
    // This sends the signal to your Memurai monitor
    try {
        $redis = new Redis();
        $redis->connect('127.0.0.1', 6379);
        $payload = json_encode([
            'ticket_id' => $id,
            'field' => $updateField,
            'value' => $val,
            'time' => time()
        ]);
        $redis->publish('ticket_updates', $payload);
    } catch (Exception $e) {
        // Log error internally but don't stop the user response
    }

    if (ob_get_length()) ob_clean();
    echo json_encode(['success' => true, 'message' => "Update synced"]);

} catch (Throwable $e) {
    if (ob_get_length()) ob_clean();
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}