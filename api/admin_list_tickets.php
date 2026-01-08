<?php
ob_start();
header("Content-Type: application/json");

// ================= DEBUG MODE =================
ini_set('display_errors', 1);
error_reporting(E_ALL);
http_response_code(200);

$debug = [];

try {
    // USE THE SHARED SESSION FILE
    require __DIR__ . '/../includes/session.php';

    $debug['step'] = 'session_started';
    $debug['session_id'] = session_id();
    $debug['session'] = $_SESSION;
    $debug['cookies'] = $_COOKIE ?? [];

    // ================= ADMIN CHECK =================
    if (!isset($_SESSION['is_admin'])) {
        throw new Exception("Access Denied: is_admin NOT SET in session. Please log in again.");
    }

    if ($_SESSION['is_admin'] !== true) {
        throw new Exception("Access Denied: You do not have administrator privileges.");
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
        SELECT t.id, t.title, t.message, t.status, t.created_at, u.email
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