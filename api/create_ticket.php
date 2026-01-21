<?php
// Start output buffering to prevent partial HTML output on errors
ob_start();

require __DIR__ . "/../includes/session.php";
require __DIR__ . "/../includes/db.php";
require __DIR__ . "/../includes/RedisClient.php";

header("Content-Type: application/json");

// Helper function to handle clean exits on error
function sendError($message, $code = 500) {
    ob_clean();
    http_response_code($code);
    echo json_encode(['success' => false, 'error' => $message]);
    // Important: release session lock before exiting to prevent "Perma 404/Hangs"
    if (session_id()) {
        session_write_close();
    }
    exit;
}

if (!isset($_SESSION['user_id'])) {
    sendError('Not authenticated', 401);
}

$title   = trim($_POST['title'] ?? '');
$message = trim($_POST['message'] ?? '');
$filePath = null;

if ($title === '' || $message === '') {
    sendError('Missing fields', 400);
}

// --- FILE UPLOAD LOGIC ---
if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = __DIR__ . "/../uploads/tickets/";
    
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0777, true)) {
            sendError('Failed to create upload directory. Check permissions.', 500);
        }
    }

    $fileTmpPath = $_FILES['attachment']['tmp_name'];
    // Sanitize filename to prevent path injection
    $fileName = time() . '_' . preg_replace("/[^a-zA-Z0-9.]/", "_", basename($_FILES['attachment']['name']));
    $destPath = $uploadDir . $fileName;

    if (move_uploaded_file($fileTmpPath, $destPath)) {
        $filePath = "uploads/tickets/" . $fileName;
    } else {
        sendError('Failed to move uploaded file.', 500);
    }
}

try {
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

    // --- REAL-TIME NOTIFICATION ---
    try {
        $redis = new RedisClient('127.0.0.1', 6379);
        $payload = json_encode([
            'ticket_id' => (int)$newTicketId,
            'field' => 'new_ticket',
            'value' => 1,
            'time' => time()
        ]);
        $redis->publish('ticket_updates', $payload);
    } catch (Throwable $e) {
        // We log this but don't stop execution because the DB insert worked
        error_log("REDIS ERROR: " . $e->getMessage());
    }

    // Success: Clear buffer and send JSON
    ob_end_clean();
    echo json_encode(['success' => true, 'id' => $newTicketId]);
    
    // Explicitly close session to release file lock
    session_write_close();

} catch (Exception $e) {
    sendError('Server Error: ' . $e->getMessage(), 500);
}