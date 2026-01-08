<?php
ob_start();
header("Content-Type: application/json");

// DEBUG (disable in prod)
ini_set('display_errors', 1);
error_reporting(E_ALL);

$response = [
    "success" => false,
    "step" => null
];

try {

    // ================= SESSION =================
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $response["step"] = "session_started";

    // ================= ADMIN CHECK =================
    if (empty($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
        http_response_code(403);
        throw new Exception("Admin access required");
    }

    $response["step"] = "admin_verified";

    // ================= DB =================
    require __DIR__ . "/../includes/db.php";

    if (!isset($conn)) {
        throw new Exception("Database connection missing");
    }

    $response["step"] = "db_connected";

    // ================= QUERY =================
    $stmt = $conn->query("
        SELECT t.id, t.title, t.message, t.status, t.created_at,
               u.email
        FROM tickets t
        JOIN users u ON u.id = t.user_id
        ORDER BY t.created_at DESC
    ");

    $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

    ob_clean();
    echo json_encode([
        "success" => true,
        "tickets" => $tickets
    ]);

} catch (Throwable $e) {

    ob_clean();
    http_response_code(403);

    echo json_encode([
        "success" => false,
        "error" => $e->getMessage(),
        "step" => $response["step"],
        "session" => $_SESSION ?? null
    ]);
}
