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

// Since we are now sending multipart/form-data for files, 
// we use $_POST instead of php://input
$title   = trim($_POST['title'] ?? '');
$message = trim($_POST['message'] ?? '');
$filePath = null;

if ($title === '' || $message === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Missing fields']);
    exit;
}

// --- FILE UPLOAD LOGIC ---
if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = __DIR__ . "/../uploads/tickets/";
    
    // Create directory if it doesn't exist
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $fileTmpPath = $_FILES['attachment']['tmp_name'];
    $fileName = time() . '_' . basename($_FILES['attachment']['name']);
    $destPath = $uploadDir . $fileName;

    // Optional: Validate file size (e.g., 20MB) or type here
    if (move_uploaded_file($fileTmpPath, $destPath)) {
        // Relative path for database storage and frontend access
        $filePath = "uploads/tickets/" . $fileName;
    }
}

try {
    // 1. Insert into Database (Added file_path column)
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