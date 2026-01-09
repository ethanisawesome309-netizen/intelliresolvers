<?php
ob_start();
header("Content-Type: application/json");
error_reporting(E_ALL);
ini_set('display_errors', 0);

require_once __DIR__ . "/../includes/session.php";
require_once __DIR__ . "/../includes/db.php";

/* --- ADMIN CHECK --- */
if (empty($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    http_response_code(403);
    echo json_encode([
        "success" => false,
        "error" => "Unauthorized access",
        "debug" => [
            "session" => $_SESSION
        ]
    ]);
    exit;
}

/* --- FETCH TICKETS --- */
try {
    $stmt = $conn->prepare("SELECT id, title, email, message, status_id FROM tickets ORDER BY id DESC");
    $stmt->execute();
    $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "tickets" => $tickets
    ]);
    exit;
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "error" => $e->getMessage(),
        "trace" => $e->getTraceAsString()
    ]);
    exit;
}
