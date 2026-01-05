<?php
session_start();

// Redirect if not logged in
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
  <!-- React will render inside this div -->
  <div id="root"></div>

  <!-- Optional: red warning if React fails -->
  <noscript style="color:red;">
    JavaScript is required to load the dashboard.
  </noscript>

  <!-- Load React JS -->
  <script src="/assets/index.js"></script>
</body>
</html>
