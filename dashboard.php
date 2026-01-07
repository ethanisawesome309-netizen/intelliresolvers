<?php
// ================= CANONICAL DOMAIN =================
if ($_SERVER['HTTP_HOST'] !== 'intelliresolvers.com') {
    header(
        "Location: https://intelliresolvers.com" . $_SERVER['REQUEST_URI'],
        true,
        301
    );
    exit;
}

require __DIR__ . "/includes/session.php";

// ================= AUTH GUARD =================
if (!isset($_SESSION['user_id'])) {
    header("Location: signinpage.php");
    exit;
}

// Prevent caching
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard | IntelliResolvers</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <div id="root">Loading dashboard…</div>

    <noscript style="color:red">
        JavaScript is required to use this application.
    </noscript>

    <!-- React bundle -->
    <script type="module" src="/assets/index.js?v=<?= time() ?>"></script>
</body>
</html>
