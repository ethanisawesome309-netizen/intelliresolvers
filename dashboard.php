<?php
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
  <title>Customer Dashboard</title>
</head>
<body>
  <div id="root"></div>

  <noscript style="color:red">
    JavaScript is required.
  </noscript>

  <script type="module" src="/assets/index.js"></script>
</body>
</html>
