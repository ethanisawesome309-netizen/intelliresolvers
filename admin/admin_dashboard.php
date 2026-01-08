<?php
require __DIR__ . "/../includes/session.php";

if (
    !isset($_SESSION['user_id']) ||
    !isset($_SESSION['is_admin']) ||
    $_SESSION['is_admin'] !== true
) {
    http_response_code(403);
    exit("Access denied");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard</title>
</head>
<body>
  <div id="root">Loading admin dashboard…</div>
  <script type="module" src="/assets/admin.js"></script>
</body>
</html>
