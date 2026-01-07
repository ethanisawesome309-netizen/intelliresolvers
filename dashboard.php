<?php
// ==================================================
// FORCE HTTPS + CANONICAL HOST (CRITICAL)
// ==================================================
$canonical_host = 'intelliresolvers.com';

if (
    $_SERVER['HTTP_HOST'] !== $canonical_host ||
    empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off'
) {
    header("Location: https://$canonical_host" . $_SERVER['REQUEST_URI'], true, 301);
    exit;
}

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
// DEBUG INFO
// ==================================================
$debug = [
    'Session ID' => session_id(),
    'Has user_id' => isset($_SESSION['user_id']) ? 'YES' : 'NO',
    'Cookie received' => isset($_COOKIE[session_name()]) ? 'YES' : 'NO',
    'HTTPS' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'YES' : 'NO',
];

// ==================================================
// AUTH CHECK
// ==================================================
if (!isset($_SESSION['user_id'])) {
    $debug_message = "🚨 AUTH FAILURE<br><br>";
    foreach ($debug as $k => $v) {
        $debug_message .= "<strong>$k:</strong> $v<br>";
    }

    // Show error instead of silent redirect
    echo <<<HTML
<!DOCTYPE html>
<html>
<head>
  <title>Authentication Error</title>
  <style>
    body {
      background:#141414;
      color:#fff;
      font-family:Arial;
      padding:40px;
    }
    .box {
      background:#1f1f1f;
      padding:30px;
      border-radius:8px;
      max-width:600px;
      margin:auto;
    }
    a {
      color:#f77062;
      text-decoration:none;
      font-weight:bold;
    }
  </style>
</head>
<body>
  <div class="box">
    <h2>Authentication Error</h2>
    <p>$debug_message</p>
    <p>
      <a href="signinpage.php">Return to Sign In</a>
    </p>
  </div>
</body>
</html>
HTML;
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
