<?php
require __DIR__ . "/includes/session.php";

echo "<pre style='color:white;background:#111;padding:20px'>";
echo "DASHBOARD DEBUG\n";
echo "=================\n";
echo "SESSION ID: " . session_id() . "\n";
echo "SESSION NAME: " . session_name() . "\n";
echo "COOKIE RECEIVED: ";
var_dump($_COOKIE[session_name()] ?? null);
echo "\nUSER ID: ";
var_dump($_SESSION['user_id'] ?? null);
echo "</pre>";

exit;

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
