<?php
// api/patch_ticket.php
ob_start();
header("Content-Type: application/json");
error_reporting(E_ALL);
ini_set('display_errors', 0); 

try {
    require_once __DIR__ . "/../includes/session.php";
    require_once __DIR__ . "/../includes/db.php";
    require_once __DIR__ . "/../includes/RedisClient.php";

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

    // --- 1. CAPTURE OLD VALUE FOR AUDIT ---
    $oldValQuery = "SELECT $updateField FROM tickets WHERE id = ?";
    $oldStmt = $conn->prepare($oldValQuery);
    $oldStmt->execute([$id]);
    $oldValue = $oldStmt->fetchColumn();

    // --- 2. UPDATE DATABASE ---
    $sql = "UPDATE tickets SET $updateField = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$val, $id]);

    // --- 3. LOG TO AUDIT TABLE ---
    // We only log if the value actually changed
    if ($oldValue != $val) {
        $logSql = "INSERT INTO ticket_audit_log (ticket_id, user_id, field_changed, old_value, new_value) VALUES (?, ?, ?, ?, ?)";
        $logStmt = $conn->prepare($logSql);
        $logStmt->execute([
            $id, 
            $_SESSION['user_id'], 
            $updateField, 
            $oldValue, 
            $val
        ]);
    }

    // --- 4. REAL-TIME NOTIFICATION ---
    try {
        $redis = new RedisClient('127.0.0.1', 6379);
        $payload = json_encode([
            'ticket_id' => $id,
            'field'     => $updateField,
            'value'     => $val,
            'time'      => time()
        ]);
        $redis->publish('ticket_updates', $payload);
    } catch (Throwable $e) {
        error_log("REDIS PATCH ERROR: " . $e->getMessage());
    }

    if (ob_get_length()) ob_clean();
    echo json_encode(['success' => true, 'message' => "Update synced and logged"]);

    // ✅ Release session lock to prevent "Perma 404"
    session_write_close();

} catch (Throwable $e) {
    if (session_id()) session_write_close(); 
    if (ob_get_length()) ob_clean();
    $code = ($e->getCode() >= 400 && $e->getCode() < 600) ? $e->getCode() : 400;
    http_response_code($code);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}