<?php
declare(strict_types=1);

require __DIR__ . '/includes/session.php';

if (empty($_SESSION['user_id'])) {
    header("Location: /signinpage.php");
    exit;
}

if (!empty($_SESSION['is_admin'])) {
    header("Location: /admin/admin_dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<body>
  <div id="root"></div>
  <script type="module" src="/assets/index.js"></script>
</body>
</html>
