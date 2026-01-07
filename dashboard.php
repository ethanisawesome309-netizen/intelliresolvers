<?php
// ==================================================
// SESSION CONFIG (MUST MATCH signinpage.php)
// ==================================================
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 0);

session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '.intelliresolvers.com',
    'secure' => true,
    'httponly' => true,
    'samesite' => 'None'
]);

session_start();

// ==================================================
// AUTH GUARD
// ==================================================
if (!isset($_SESSION['user_id'])) {
    header("Location: https://intelliresolvers.com/signinpage.php");
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

<!-- React mounts here -->
<div id="root"></div>

<noscript style="color:red">
  JavaScript is required to use this dashboard.
</noscript>

<!-- EXISTING REACT ENTRY -->
<script type="module" src="/assets/index.js"></script>

</body>
</html>
