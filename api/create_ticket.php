<?php
require __DIR__ . "/../includes/session.php";
require __DIR__ . "/../includes/db.php";
require __DIR__ . "/../includes/RedisClient.php"; // ✅ Import your tiny client

header("Content-Type: application/json");

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$title   = trim($data['title'] ?? '');
$message = trim($data['message'] ?? '');

if ($title === '' || $message === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Missing fields']);
    exit;
}

try {
    // 1. Insert into Database
    $stmt = $conn->prepare(
        "INSERT INTO tickets (user_id, title, message, status_id)
         VALUES (:uid, :title, :message, 1)"
    );

    $stmt->execute([
        'uid' => $_SESSION['user_id'],
        'title' => $title,
        'message' => $message
    ]);

    $newTicketId = $conn->lastInsertId();

    // 2. --- REAL-TIME NOTIFICATION ---
    try {
        // ✅ Use the manual socket client (no extension required)
        $redis = new RedisClient('127.0.0.1', 6379);
        
        $payload = json_encode([
            'ticket_id' => (int)$newTicketId,
            'field'     => 'new_ticket',
            'value'     => 1,
            'time'      => time()
        ]);

        $redis->publish('ticket_updates', $payload);
        
    } catch (Throwable $e) {
        // Log error but don't stop the PHP script
        error_log("REDIS PUSH ERROR: " . $e->getMessage());
    }

    echo json_encode(['success' => true, 'id' => $newTicketId]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}