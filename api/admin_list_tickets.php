<?php
ob_start();
header("Content-Type: application/json");

// DEBUG MODE
ini_set('display_errors', 1);
error_reporting(E_ALL);

$response = [
    "success" => false,
    "step" => null,
    "details" => null
];

try {
    /* =========================
       STEP 1: SESSION START
    ========================== */
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $response["step"] = "session_started";
    $response["session_id"] = session_id();
    $response["session_data"] = $_SESSION;

    /* =========================
       STEP 2: ADMIN CHECK
    ========================== */
    if (!isset($_SESSION['is_admin'])) {
        throw new Exception("Session exists but is_admin is NOT set");
    }

    if ($_SESSION['is_admin'] !== true) {
        throw new Exception("User is logged in but NOT an admin");
    }

    $response["step"] = "admin_verified";

    /* =========================
       STEP 3: DB INCLUDE
    ========================== */
    require __DIR__ . "/../includes/db.php";

    if (!isset($pdo)) {
        throw new Exception("db.php included but \$pdo is missing");
    }

    $response["step"] = "database_connected";

    /* =========================
       STEP 4: QUERY EXECUTION
    ========================== */
    $sql = "
        SELECT t.id, t.title, t.message, t.status, t.created_at,
               u.email
        FROM tickets t
        JOIN users u ON u.id = t.user_id
        ORDER BY t.created_at DESC
    ";

    $stmt = $pdo->query($sql);
    if (!$stmt) {
        throw new Exception("SQL query failed");
    }

    $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $response["step"] = "query_success";
    $response["tickets_count"] = count($tickets);

    /* =========================
       SUCCESS RESPONSE
    ========================== */
    ob_clean();
    echo json_encode([
        "success" => true,
        "message" => "Admin tickets loaded successfully",
        "debug" => $response,
        "tickets" => $tickets
    ]);

} catch (Throwable $e) {

    ob_clean();
    http_response_code(403);

    echo json_encode([
        "success" => false,
        "message" => "Admin ticket load failed",
        "failed_step" => $response["step"],
        "error" => $e->getMessage(),
        "session_id" => session_id() ?: null,
        "session_data" => $_SESSION ?? null
    ]);
}
