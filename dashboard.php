<?php
declare(strict_types=1);

require __DIR__ . "/includes/session.php";

// Must be logged in
if (empty($_SESSION['user_id'])) {
    header("Location: /signinpage.php");
    exit;
}

// Admins should NOT see user dashboard
if (!empty($_SESSION['is_admin'])) {
    header("Location: /admin/admin_dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Dashboard</title>
</head>
<body>
  <div id="root">Loading…</div>
  <script type="module" src="/assets/index.js?v=<?= time() ?>"></script>
</body>
</html>
