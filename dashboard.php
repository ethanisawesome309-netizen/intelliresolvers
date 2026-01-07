<?php
require __DIR__ . "/includes/session.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: signinpage.php");
    exit;
}
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
