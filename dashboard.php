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
  <title>Dashboard</title>
</head>
<body>
  <div id="root"></div>

  <!-- IMPORTANT: use the exact filename from /assets -->
  <script src="/assets/index-CCa6eI2g.js"></script>
</body>
</html>
