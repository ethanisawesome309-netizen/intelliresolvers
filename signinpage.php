<?php
// =====================================
// SESSION CONFIG — FINAL FIX
// =====================================

// Detect HTTPS
$is_https = (
    (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || $_SERVER['SERVER_PORT'] == 443
);

// Determine cookie domain
$cookie_domain = null;
if (strpos($_SERVER['HTTP_HOST'], 'intelliresolvers.com') !== false) {
    $cookie_domain = '.intelliresolvers.com';
    $is_https = true;
}

// CRITICAL FIXES
ini_set('session.use_strict_mode', 0);
ini_set('session.cookie_httponly', 1);

// SameSite MUST be None for cross-subdomain POSTs
$samesite = $is_https ? 'None' : 'Lax';

session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => $cookie_domain,
    'secure' => $is_https,
    'httponly' => true,
    'samesite' => $samesite
]);

session_start();

require "includes/db.php";

// =====================================
// CSRF TOKEN
// =====================================
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// =====================================
// LOGIN LOGIC
// =====================================
$error = "";
$debug = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $posted = $_POST['csrf_token'] ?? '';
    $session = $_SESSION['csrf_token'] ?? '';

    if (!$posted || !$session || !hash_equals($session, $posted)) {

        // VERY CLEAR DIAGNOSTIC MESSAGE
        $error = "🚨 SECURITY ERROR: CSRF TOKEN MISMATCH";

        $debug = "
        <strong>Posted CSRF:</strong><br>$posted<br><br>
        <strong>Session CSRF:</strong><br>$session<br><br>
        <strong>Session ID:</strong><br>" . session_id() . "<br><br>
        <strong>HTTPS:</strong> " . ($is_https ? 'YES' : 'NO') . "<br>
        <strong>SameSite:</strong> $samesite<br>
        <strong>Cookie Domain:</strong> " . ($cookie_domain ?: 'localhost') . "<br>
        <strong>Host:</strong> {$_SERVER['HTTP_HOST']}
        ";

    } else {

        $email = trim($_POST["email"] ?? "");
        $password = $_POST["password"] ?? "";

        $stmt = $conn->prepare(
            "SELECT id, password_hash FROM users WHERE email = :email LIMIT 1"
        );
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user["password_hash"])) {
            session_regenerate_id(true);
            $_SESSION["user_id"] = $user["id"];
            header("Location: dashboard.php");
            exit;
        } else {
            $error = "❌ Invalid email or password";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Sign In | INTELLI RESOLVERS</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<script src="https://kit.fontawesome.com/9f925d73ef.js" crossorigin="anonymous"></script>
<link href="https://fonts.googleapis.com/css2?family=Kumbh+Sans:wght@100..900&display=swap" rel="stylesheet">

<style>
* {
  box-sizing: border-box;
  margin: 0;
  padding: 0;
  font-family: 'Kumbh Sans', sans-serif;
}

body {
  background: #141414;
  color: #fff;
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

.navbar {
  background: #131313;
  height: 80px;
  display: flex;
  justify-content: center;
  align-items: center;
}

.signin-wrapper {
  flex: 1;
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 3rem 1rem;
}

.signin-card {
  background: #1f1f1f;
  padding: 3rem;
  border-radius: 10px;
  width: 100%;
  max-width: 480px;
  text-align: center;
  box-shadow: 0 10px 40px rgba(0,0,0,0.6);
}

.signin-card h1 {
  font-size: 2rem;
  margin-bottom: 0.5rem;
  background: linear-gradient(to top, #ff0844, #ffb199);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

.error {
  color: #ff6b6b;
  margin-bottom: 1rem;
  font-weight: bold;
}

.debug {
  background: #141414;
  border: 1px solid #333;
  padding: 1rem;
  margin-bottom: 1.5rem;
  font-size: 0.85rem;
  text-align: left;
  word-break: break-word;
}

input {
  width: 100%;
  padding: 14px;
  margin-bottom: 1rem;
  border-radius: 5px;
  border: none;
  background: #2b2b2b;
  color: #fff;
}

button {
  width: 100%;
  padding: 14px;
  background: linear-gradient(to right, #f77062, #fe5196);
  border: none;
  border-radius: 5px;
  color: #fff;
  font-size: 1rem;
  cursor: pointer;
}
</style>
</head>

<body>

<div class="signin-wrapper">
  <div class="signin-card">
    <h1>Sign In</h1>

    <?php if ($error): ?>
      <div class="error"><?= $error ?></div>
    <?php endif; ?>

    <?php if ($debug): ?>
      <div class="debug"><?= $debug ?></div>
    <?php endif; ?>

    <form method="POST">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
      <input type="email" name="email" placeholder="Email address" required>
      <input type="password" name="password" placeholder="Password" required>
      <button type="submit">Sign In</button>
    </form>
  </div>
</div>

</body>
</html>
