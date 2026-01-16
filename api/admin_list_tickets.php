<?php
// api/admin_list_tickets.php
ob_start();
header("Content-Type: application/json");
error_reporting(E_ALL);
ini_set('display_errors', 0); 

try {
    require_once __DIR__ . '/../includes/session.php';
    require_once __DIR__ . '/../includes/db.php';

    if (empty($_SESSION['is_admin'])) {
        throw new Exception("Unauthorized access", 403);
    }

    $user_role = $_SESSION['role'] ?? '';
    $current_user_id = $_SESSION['user_id'] ?? 0;
    $current_user_email = $_SESSION['email'] ?? ''; // Added email fetch
    
    $role_map = [
        'Junior'       => 1,
        'Intermediate' => 2,
        'Senior'       => 3
    ];

    $where_clause = "";
    $params = [];

    if (isset($role_map[$user_role])) {
        $where_clause = " 
            WHERE (t.priority_id <= ? AND t.assigned_to IS NULL AND t.claimed_by IS NULL) 
            OR (t.assigned_to = ? OR t.claimed_by = ?) 
        ";
        $params[] = $role_map[$user_role];
        $params[] = $current_user_id;
        $params[] = $current_user_id;
    }

    $query = "
        SELECT 
            t.id, 
            t.title, 
            t.message, 
            t.status_id, 
            t.priority_id, 
            t.assigned_to,
            t.claimed_by,
            u_creator.email, 
            COALESCE(s.label, 'Open') as status,
            COALESCE(p.label, 'Low') as priority_label, 
            COALESCE(p.color_code, '#333333') as priority_color,
            u_assign.name as assigned_to_name,
            u_claim.name as claimed_by_name
        FROM tickets t
        JOIN users u_creator ON u_creator.id = t.user_id
        LEFT JOIN statuses s ON s.id = t.status_id
        LEFT JOIN priorities p ON p.id = t.priority_id
        LEFT JOIN users u_assign ON u_assign.id = t.assigned_to
        LEFT JOIN users u_claim ON u_claim.id = t.claimed_by
        $where_clause
        ORDER BY t.id DESC
    ";

    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (ob_get_length()) ob_clean();
    echo json_encode([
        "success" => true, 
        "tickets" => $tickets, 
        "tier" => $user_role,
        "current_user_id" => $current_user_id,
        "user_email" => $current_user_email // Added for React logic
    ]);

} catch (Throwable $e) {
    if (ob_get_length()) ob_clean();
    http_response_code(500);
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}