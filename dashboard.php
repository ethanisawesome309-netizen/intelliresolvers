<?php
// ==================================================
// CANONICAL HOST (MATCH SIGNIN PAGE)
// ==================================================
$canonical_host = 'intelliresolvers.com';
if ($_SERVER['HTTP_HOST'] !== $canonical_host) {
    header("Location: https://$canonical_host" . $_SERVER['REQUEST_URI'], true, 301);
    exit;
}

// ==================================================
// SESSION CONFIG — MUST MATCH signinpage.php EXACTLY
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
// AUTH CHECK
// ==================================================
if (!isset($_SESSION['user_id'])) {
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
