<?php
$path = basename($_SERVER['REQUEST_URI']);

$file = __DIR__ . '/' . $path;

if (is_file($file)) {
    require $file;
    exit;
}

http_response_code(404);
echo json_encode(["error" => "API route not found"]);
