<?php
require __DIR__ . "/../includes/session.php";

if (empty($_SESSION['user_id']) || empty($_SESSION['is_admin'])) {
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
  <script type="module" src="/admin/AdminApp.js?v=<?= time() ?>"></script>
</body>
</html>
