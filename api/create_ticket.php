<?php
require __DIR__ . "/../includes/session.php";
require __DIR__ . "/../includes/db.php";

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
        if (!class_exists('Redis')) {
            error_log("CRITICAL: Redis extension not loaded for create_ticket.php");
        } else {
            $redis = new Redis();
            // Use '127.0.0.1' explicitly
            $connected = $redis->connect('127.0.0.1', 6379);

            if ($connected) {
                $payload = json_encode([
                    'ticket_id' => (int)$newTicketId,
                    'field'     => 'new_ticket',
                    'value'     => 1,
                    'time'      => time()
                ]);

                $redis->publish('ticket_updates', $payload);
            } else {
                error_log("REDIS: Connection failed in create_ticket.php");
            }
        }
    } catch (Throwable $e) {
        // This catches ALL errors, including those that usually crash PHP
        error_log("REDIS EXCEPTION: " . $e->getMessage());
    }

    echo json_encode(['success' => true, 'id' => $newTicketId]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}