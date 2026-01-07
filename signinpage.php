<?php
// --------------------------
// SESSION FIX FOR CSRF (works locally and in prod)
// --------------------------

// Detect if running on HTTPS
$is_https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') 
            || $_SERVER['SERVER_PORT'] == 443;

// Use .intelliresolvers.com for prod, null for localhost
$cookie_domain = (strpos($_SERVER['HTTP_HOST'], 'intelliresolvers.com') !== false) 
                 ? '.intelliresolvers.com' 
                 : null;

ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);

session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => $cookie_domain,
    'secure' => $is_https, // only secure on HTTPS
    'httponly' => true,
    'samesite' => 'Lax'
]);

session_start();

require "includes/db.php";

// --------------------------
// CSRF TOKEN
// --------------------------
if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

// --------------------------
// LOGIN PROCESS
// --------------------------
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!hash_equals($_SESSION["csrf_token"], $_POST["csrf_token"] ?? "")) {
        die("Invalid request");
    }

    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    $stmt = $conn->prepare("SELECT id, password_hash FROM users WHERE email = :email");
    $stmt->execute(["email" => $email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user["password_hash"])) {
        session_regenerate_id(true);
        $_SESSION["user_id"] = $user["id"];
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Invalid email or password";
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
/* GLOBAL */
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

/* NAVBAR */
.navbar {
  background: #131313;
  height: 80px;
  display: flex;
  justify-content: center;
  align-items: center;
  font-size: 1.2rem;
  position: sticky;
  top: 0;
  z-index: 999;
}

.navbar__container {
  display: flex;
  justify-content: space-between;
  width: 100%;
  max-width: 1300px;
  padding: 0 50px;
  align-items: center;
}

#navbar__logo {
  background: linear-gradient(to top, #ff0844 0%, #ffb199 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  font-size: 2rem;
  display: flex;
  align-items: center;
  cursor: pointer;
  text-decoration: none;
}

.navbar__menu {
  display: flex;
  list-style: none;
}

.navbar__links {
  color: #fff;
  text-decoration: none;
  padding: 0 1rem;
  display: flex;
  align-items: center;
  height: 80px;
}

.navbar__links:hover {
  color: #f77062;
}

.navbar__btn .button {
  background: linear-gradient(to right, #f77062, #fe5196);
  padding: 10px 20px;
  border-radius: 4px;
  color: #fff;
  text-decoration: none;
}

/* LOGIN SECTION */
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
  max-width: 420px;
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

.signin-card p {
  color: #bbb;
  margin-bottom: 2rem;
}

.error {
  color: #ff6b6b;
  margin-bottom: 1rem;
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

input:focus {
  outline: 2px solid #f77062;
}

button {
  width: 100%;
  padding: 14px;
  background: linear-gradient(to right, #f77062, #fe5196);
  border: none;
  border-radius: 5px;
  color: #fff;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
}

/* FOOTER */
.footer__container {
  background-color: #141414;
  padding: 4rem 0;
  text-align: center;
}

.website__rights {
  color: #777;
  font-size: 0.85rem;
}
</style>
</head>

<body>

<!-- NAVBAR -->
<nav class="navbar">
  <div class="navbar__container">
    <a href="index.html" id="navbar__logo">
      <i class="fas fa-gem"></i>INTELLI RESOLVERS
    </a>
    <ul class="navbar__menu">
      <li><a href="index.html" class="navbar__links">Home</a></li>
      <li><a href="services.html" class="navbar__links">Services</a></li>
      <li><a href="team.html" class="navbar__links">Team</a></li>
      <li class="navbar__btn"><a href="signinpage.php" class="button">Sign In</a></li>
    </ul>
  </div>
</nav>

<!-- SIGN IN -->
<div class="signin-wrapper">
  <div class="signin-card">
    <h1>Sign In</h1>
    <p>Access your IntelliResolvers dashboard</p>

    <?php if (isset($error)): ?>
      <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
      <input type="hidden" name="csrf_token" value="<?= $_SESSION["csrf_token"] ?>">
      <input type="email" name="email" placeholder="Email address" required>
      <input type="password" name="password" placeholder="Password" required>
      <button type="submit">Sign In</button>
    </form>
  </div>
</div>

<!-- FOOTER -->
<div class="footer__container">
  <p class="website__rights">
    © INTELLI RESOLVERS <?= date("Y") ?>. All rights reserved.
  </p>
</div>

</body>
</html>
