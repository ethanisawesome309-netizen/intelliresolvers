<?php
session_start();

// Return current session for debugging
header("Content-Type: application/json");
echo json_encode([
    "success" => true,
    "session" => $_SESSION
]);
