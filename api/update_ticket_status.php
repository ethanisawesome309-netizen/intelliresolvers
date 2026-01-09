<?php
ob_start();
header("Content-Type: application/json");

// Enable internal error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0); 

try {
    require_once __DIR__ . "/../includes/session.php";
    require_once __DIR__ . "/../includes/db.php";

    // --- DEBUGGING BLOCK ---
    $rawInput = file_get_contents("php://input");
    $decodedData = json_decode($rawInput, true);
    
    $debugInfo = [
        "session_active" => session_id() ? 'Yes' : 'No',
        "session_data" => $_SESSION, // Shows exactly what is in your session
        "is_admin_check" => isset($_SESSION['is_admin']) ? $_SESSION['is_admin'] : 'NOT SET',
        "raw_input" => $rawInput,
        "method" => $_SERVER['REQUEST_METHOD'],
        "content_type" => $_SERVER['CONTENT_TYPE'] ?? 'N/A'
    ];
    // -----------------------

    // 1. Session verification with Verbose Error
    if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
        http_response_code(403);
        echo json_encode([
            "success" => false, 
            "error" => "Unauthorized access",
            "debug" => $debugInfo // This tells React WHY it failed
        ]);
        exit;
    }

    // 2. Parse Logic
    $id = (int)($decodedData['id'] ?? 0);
    $status_id = (int)($decodedData['status_id'] ?? 0);

    $map = [
        1 => "Open",
        2 => "In Progress",
        3 => "Closed"
    ];

    if (!$id || !isset($map[$status_id])) {
        http_response_code(400);
        echo json_encode([
            "success" => false, 
            "error" => "Invalid parameters",
            "debug" => $debugInfo
        ]);
        exit;
    }

    $final_status = $map[$status_id];

    // 3. Database Update
    if (!isset($conn)) {
        throw new Exception("Database connection variable (\$conn) is missing.");
    }

    $stmt = $conn->prepare("UPDATE tickets SET status = ? WHERE id = ?");
    $stmt->execute([$final_status, $id]);

    ob_clean();
    echo json_encode([
        "success" => true,
        "updated_id" => $id,
        "new_status" => $final_status,
        "debug" => $debugInfo
    ]);
    exit;

} catch (Throwable $e) {
    ob_clean();
    http_response_code(500);
    echo json_encode([
        "success" => false, 
        "error" => $e->getMessage(),
        "trace" => $e->getTraceAsString()
    ]);
    exit;
}