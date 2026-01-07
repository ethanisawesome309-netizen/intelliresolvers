<?php
if ($_SERVER['HTTP_HOST'] !== 'intelliresolvers.com') {
    header(
        "Location: https://intelliresolvers.com" . $_SERVER['REQUEST_URI'],
        true,
        301
    );
    exit;
}

require __DIR__ . "/includes/session.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: signinpage.php");
    exit;
}

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
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
