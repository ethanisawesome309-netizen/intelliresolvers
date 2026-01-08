<?php
declare(strict_types=1);

require __DIR__ . '/../includes/session.php';

if (
    empty($_SESSION['user_id']) ||
    empty($_SESSION['is_admin']) ||
    $_SESSION['is_admin'] !== true
) {
    http_response_code(403);
    exit('403 Forbidden');
}
?>
<!DOCTYPE html>
<html>
<body>
  <div id="root"></div>
  <script type="module" src="/assets/admin.js"></script>
</body>
</html>
