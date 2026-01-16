<?php
declare(strict_types=1);
require __DIR__ . '/../includes/session.php';

// Check for admin status
if (
    empty($_SESSION['user_id']) ||
    empty($_SESSION['is_admin']) ||
    $_SESSION['is_admin'] !== true
) {
    http_response_code(403);
    exit('403 Forbidden');
}
$display_role = $_SESSION['role'] ?? 'Administrator';
?>
<!DOCTYPE html>
<html>
<body>
  <div id="root"></div>
  <script type="module" src="../assets/admin.js"></script>
</body>
</html>
