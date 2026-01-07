<?php
// ==================================================
// SESSION CONFIG (MUST MATCH signinpage.php EXACTLY)
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
// AUTH CHECK WITH DEBUG OUTPUT
// ==================================================
if (!isset($_SESSION['user_id'])) {

    $debug = [
        'Session ID' => session_id(),
        'Session cookie received' => isset($_COOKIE[session_name()]) ? 'YES' : 'NO',
        'HTTPS' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'YES' : 'NO',
        'Host' => $_SERVER['HTTP_HOST']
    ];

    $debug_html = '';
    foreach ($debug as $k => $v) {
        $debug_html .= "<strong>$k:</strong> $v<br>";
    }

    echo <<<HTML
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
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
  font-weight:bold;
  text-decoration:none;
}
</style>
</head>
<body>
<div class="box">
  <h2>🚨 Authentication Failed</h2>
  <p>Your login session could not be verified.</p>
  <p>$debug_html</p>
  <p><a href="signinpage.php">Return to Sign In</a></p>
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
