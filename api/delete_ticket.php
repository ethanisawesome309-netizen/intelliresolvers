<?php
// api/delete_ticket.php
ob_start();
header("Content-Type: application/json");

// ================= DEBUG MODE (Optional - Remove in production) =================
ini_set('display_errors', 1);
error_reporting(E_ALL);

try {
    // 1. Pathing: Go up one level from /api/ to find /includes/
    require_once __DIR__ . "/../includes/session.php";
    require_once __DIR__ . "/../includes/db.php";

    // 2. Security: Verify Admin Session
    if (empty($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
        http_response_code(403);
        echo json_encode(["success" => false, "error" => "Unauthorized access: Admin only."]);
        exit;
    }

    // 3. Input: Parse JSON body from fetch()
    $json = file_get_contents("php://input");
    $data = json_decode($json, true);
    $id = (int)($data['id'] ?? 0);

    if (!$id) {
        http_response_code(400);
        echo json_encode(["success" => false, "error" => "Missing or invalid ticket ID."]);
        exit;
    }

    // 4. Database: Ensure $conn exists (defined in db.php)
    if (!isset($conn)) {
        throw new Exception("Critical: Database connection variable '\$conn' is missing.");
    }

    // 5. Execution: Prepare and Execute
    $stmt = $conn->prepare("DELETE FROM tickets WHERE id = ?");
    $stmt->execute([$id]);

    // Check if a row was actually deleted
    if ($stmt->rowCount() === 0) {
        throw new Exception("Ticket not found or already deleted.");
    }

    // 6. Success Output
    ob_clean();
    echo json_encode(["success" => true, "message" => "Ticket #$id deleted successfully."]);
    exit;

} catch (Throwable $e) {
    // Catch any database or code errors and return them as JSON
    ob_clean();
    http_response_code(500);
    echo json_encode([
        "success" => false, 
        "error" => $e->getMessage()
    ]);
    exit;
}