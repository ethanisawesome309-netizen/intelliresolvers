<?php
// api/update_ticket_status.php
ob_start();
header("Content-Type: application/json");

try {
    // 1. Correct relative paths to includes
    require_once __DIR__ . "/../includes/session.php";
    require_once __DIR__ . "/../includes/db.php";

    // 2. Admin Check
    if (empty($_SESSION['is_admin'])) {
        http_response_code(403);
        echo json_encode(["success" => false, "error" => "Unauthorized access"]);
        exit;
    }

    // 3. Parse JSON Input from React
    $data = json_decode(file_get_contents("php://input"), true);
    
    // Explicitly cast and sanitize
    $id = isset($data['id']) ? (int)$data['id'] : 0;
    $status = isset($data['status']) ? trim($data['status']) : '';

    // 4. Validation
    $allowed = ['Open', 'In Progress', 'Closed'];

    if (!$id || !in_array($status, $allowed)) {
        http_response_code(400);
        echo json_encode([
            "success" => false, 
            "error" => "Invalid ticket ID or status: " . $status
        ]);
        exit;
    }

    // 5. Update Database (Fixed $pdo to $conn)
    if (!isset($conn)) {
        throw new Exception("Database connection variable '\$conn' is missing.");
    }

    $stmt = $conn->prepare("UPDATE tickets SET status = ? WHERE id = ?");
    $stmt->execute([$status, $id]);

    // 6. Return Success
    ob_clean();
    echo json_encode(["success" => true, "id" => $id, "new_status" => $status]);
    exit;

} catch (Throwable $e) {
    ob_clean();
    http_response_code(500);
    echo json_encode([
        "success" => false, 
        "error" => $e->getMessage()
    ]);
    exit;
}