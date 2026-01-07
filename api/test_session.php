<?php
session_start();

// Output current session for debugging
header("Content-Type: application/json");

echo json_encode([
    "success" => true,
    "session" => $_SESSION
]);
