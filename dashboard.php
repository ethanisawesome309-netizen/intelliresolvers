<?php
// session.php ensures $_SESSION works
require __DIR__ . "/includes/session.php";
// NO REDIRECT HERE. Let React handle login UI.
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard | IntelliResolvers</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <div id="root"></div>
    <noscript style="color:red">
        JavaScript is required to use this application.
    </noscript>
    <script type="module" src="/assets/index.js"></script>
</body>
</html>

