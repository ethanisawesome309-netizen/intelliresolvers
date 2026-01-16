<?php
// debug_api.php
header('Content-Type: text/plain');
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "--- START DATABASE DIAGNOSTIC ---\n";

try {
    // 1. Test Connection
    require_once __DIR__ . '/../includes/db.php'; 
    echo "1. Database Connected Successfully via PDO.\n";

    // 2. Test the JOIN logic
    // We are checking if columns 'color_code' and 'label' exist in your dictionary tables
    $sql = "SELECT 
                t.id, 
                p.label AS priority_label, 
                p.color_code AS priority_color,
                s.label AS status_label
            FROM tickets t
            LEFT JOIN priorities p ON t.priority_id = p.id
            LEFT JOIN statuses s ON t.status_id = s.id
            LIMIT 1";

    $stmt = $conn->query($sql);
    $row = $stmt->fetch();

    if (!$row) {
        echo "2. Query worked, but the 'tickets' table is empty.\n";
    } else {
        echo "2. SQL Query Success.\n";
        echo "3. Sample Data Result:\n";
        print_r($row);
    }

} catch (PDOException $e) {
    echo "STOP: SQL Error: " . $e->getMessage() . "\n";
    echo "Possible cause: Table 'priorities' or 'statuses' is missing, or column 'color_code' is named differently.\n";
} catch (Throwable $e) {
    echo "STOP: General Error: " . $e->getMessage() . "\n";
}

echo "\n--- END DIAGNOSTIC ---";
?>