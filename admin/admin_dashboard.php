<?php
require __DIR__ . "/../includes/session.php";

if (
    empty($_SESSION['user_id']) ||
    empty($_SESSION['is_admin']) ||
    $_SESSION['is_admin'] !== true
) {
    http_response_code(403);
    exit("403 Forbidden");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard</title>
</head>
<body>
  <div id="root"></div>
  <script type="module" src="/assets/admin.js"></cript>
</body>
</html>
