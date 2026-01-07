<?php
session_start();

if (isset($_SESSION['user_id'])) {
    echo json_encode(['user_id' => $_SESSION['user_id']]);
} else {
    http_response_code(401);
    echo json_encode(['error' => 'Not logged in']);
}
