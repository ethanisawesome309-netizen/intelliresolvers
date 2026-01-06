<?php
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.use_strict_mode', 1);
session_start();

if (!isset($_SESSION["user_id"])) {
  header("Location: signinpage.php");
  exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>IntelliResolvers Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
  <div id="root"></div>

  <noscript style="color:red">
    JavaScript is required.
  </noscript>

  <script type="module" src="/assets/index.js"></script>
</body>
</html>
