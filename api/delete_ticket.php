<?php
// api/delete_ticket.php
ob_start();
header("Content-Type: application/json");
error_reporting(E_ALL);
ini_set('display_errors', 0); 

try {
    require_once __DIR__ . "/../includes/session.php";
    require_once __DIR__ . "/../includes/db.php";
    require_once __DIR__ . "/../includes/RedisClient.php"; // ✅ Import your manual client

    // 1. Basic Admin check
    if (empty($_SESSION['is_admin'])) {
        throw new Exception("Unauthorized access", 403);
    }

    // 2. Role-based check: Block Junior and Intermediate from deleting
    $user_role = $_SESSION['role'] ?? '';
    if ($user_role === 'Junior' || $user_role === 'Intermediate') {
        throw new Exception("Insufficient permissions to archive tickets.", 403);
    }

    $data = json_decode(file_get_contents("php://input"), true);
    $id = (int)($data['id'] ?? 0);

    if (!$id) throw new Exception("Invalid ID", 400);

    // 3. Execute Delete
    $stmt = $conn->prepare("DELETE FROM tickets WHERE id = ?");
    $stmt->execute([$id]);

    // 4. --- REAL-TIME NOTIFICATION ---
    try {
        $redis = new RedisClient('127.0.0.1', 6379);
        
        $payload = json_encode([
            'ticket_id' => $id,
            'field'     => 'delete_ticket', // Logic identifier
            'value'     => null,
            'time'      => time()
        ]);

        $redis->publish('ticket_updates', $payload);
    } catch (Throwable $redisError) {
        // Log it, but don't stop the response. The ticket is already deleted in DB.
        error_log("REDIS DELETE NOTIFY ERROR: " . $redisError->getMessage());
    }

    if (ob_get_length()) ob_clean();
    echo json_encode(['success' => true]);

} catch (Throwable $e) {
    if (ob_get_length()) ob_clean();
    // Use the error code from the exception if available, else 403
    $code = ($e->getCode() >= 400 && $e->getCode() < 600) ? $e->getCode() : 403;
    http_response_code($code);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}