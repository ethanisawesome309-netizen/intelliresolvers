<?php
ob_start();
header("Content-Type: application/json");
error_reporting(E_ALL);
ini_set('display_errors', 0); 

try {
    require_once __DIR__ . '/../includes/session.php';
    require_once __DIR__ . '/../includes/db.php';

    // Verify user is logged in and is admin/staff
    if (empty($_SESSION['is_admin'])) {
        throw new Exception("Unauthorized access", 403);
    }

    // Select developers, including their tier/role
    // We filter out the main system admin email to keep the assignment list clean
    $stmt = $conn->query("
        SELECT 
            id, 
            name, 
            role 
        FROM users 
        WHERE is_admin = 1 
        AND email != 'admin@intelliresolvers.com' 
        ORDER BY 
            CASE 
                WHEN role = 'Senior' THEN 1
                WHEN role = 'Intermediate' THEN 2
                WHEN role = 'Junior' THEN 3
                ELSE 4 
            END ASC, 
            name ASC
    ");

    $developers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (ob_get_length()) ob_clean();
    echo json_encode([
        "success" => true, 
        "developers" => $developers
    ]);

} catch (Throwable $e) {
    if (ob_get_length()) ob_clean();
    $code = $e->getCode();
    $status_code = (is_int($code) && $code >= 400 && $code < 600) ? $code : 500;
    http_response_code($status_code);
    echo json_encode([
        "success" => false, 
        "error" => $e->getMessage()
    ]);
}