<?php
// api/create_ticket.php
ob_start(); // Buffer output to prevent headers already sent errors
header("Content-Type: application/json");

try {
    require __DIR__ . "/../includes/session.php";
    require __DIR__ . "/../includes/db.php";
    require __DIR__ . "/../includes/RedisClient.php";

    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Not authenticated', 401);
    }

    $title   = trim($_POST['title'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $filePath = null;

    if ($title === '' || $message === '') {
        throw new Exception('Missing fields', 400);
    }

    // --- FILE UPLOAD LOGIC ---
    if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . "/../uploads/tickets/";
        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0777, true)) {
                throw new Exception("Failed to create upload directory");
            }
        }

        $fileTmpPath = $_FILES['attachment']['tmp_name'];
        $fileName = time() . '_' . preg_replace("/[^a-zA-Z0-9.]/", "_", basename($_FILES['attachment']['name']));
        $destPath = $uploadDir . $fileName;

        if (move_uploaded_file($fileTmpPath, $destPath)) {
            $filePath = "uploads/tickets/" . $fileName;
        } else {
            throw new Exception("Failed to move uploaded file");
        }
    }

    // 1. Insert into Database
    $stmt = $conn->prepare(
        "INSERT INTO tickets (user_id, title, message, status_id, file_path)
         VALUES (:uid, :title, :message, 1, :file_path)"
    );

    $stmt->execute([
        'uid' => $_SESSION['user_id'],
        'title' => $title,
        'message' => $message,
        'file_path' => $filePath
    ]);

    $newTicketId = $conn->lastInsertId();

    // 2. REAL-TIME NOTIFICATION
    try {
        $redis = new RedisClient('127.0.0.1', 6379);
        $payload = json_encode([
            'ticket_id' => (int)$newTicketId,
            'field'     => 'new_ticket',
            'value'     => 1,
            'time'      => time()
        ]);
        $redis->publish('ticket_updates', $payload);
    } catch (Throwable $e) {
        error_log("REDIS PUSH ERROR: " . $e->getMessage());
    }

    if (ob_get_length()) ob_clean();
    echo json_encode(['success' => true, 'id' => $newTicketId]);

} catch (Exception $e) {
    if (ob_get_length()) ob_clean();
    $code = $e->getCode() ?: 500;
    http_response_code(is_numeric($code) && $code >= 400 ? $code : 500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}