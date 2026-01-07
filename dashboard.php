<?php
// Start the session
require __DIR__ . "/includes/session.php";

// Optional: prevent caching of old pages
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
    <!-- Show loading placeholder until React renders -->
    <div id="root">Loading dashboard…</div>
    <noscript style="color:red">
        JavaScript is required to use this application.
    </noscript>

    <!-- Load latest React bundle with cache-busting -->
    <script type="module" src="/assets/index.js?v=<?=time()?>"></script>
</body>
</html>
