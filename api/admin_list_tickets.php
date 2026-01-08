<?php
ob_start();
header("Content-Type: application/json");

// ================= DEBUG MODE =================
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Allow seeing output even on 403
http_response_code(200);

$debug = [];

try {

    // ================= SESSION =================
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $debug['step'] = 'session_started';
    $debug['session_id'] = session_id();
    $debug['session_name'] = session_name();
    $debug['session'] = $_SESSION;

    // ================= COOKIE DEBUG =================
    $debug['cookies'] = $_COOKIE ?? [];
    $debug['headers_sent'] = headers_sent();

    // ================= ADMIN CHECK =================
    if (!isset($_SESSION['is_admin'])) {
        throw new Exception("is_admin NOT SET in session");
    }

    if (!$_SESSION['is_admin']) {
        throw new Exception("is_admin is set but FALSE/EMPTY");
    }

    $debug['step'] = 'admin_verified';

    // ================= DB =================
    require __DIR__ . "/../includes/db.php";

    if (!isset($conn)) {
        throw new Exception("Database connection missing");
    }

    $debug['step'] = 'db_connected';

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
        "tickets" => $tickets,
        "debug" => $debug
    ], JSON_PRETTY_PRINT);
    exit;

} catch (Throwable $e) {

    ob_clean();
    echo json_encode([
        "success" => false,
        "error" => $e->getMessage(),
        "debug" => $debug
    ], JSON_PRETTY_PRINT);
    exit;
}
