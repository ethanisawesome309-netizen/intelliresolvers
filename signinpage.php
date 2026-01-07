<?php
// ================= CANONICAL DOMAIN =================
if ($_SERVER['HTTP_HOST'] !== 'intelliresolvers.com') {
    header(
        "Location: https://intelliresolvers.com" . $_SERVER['REQUEST_URI'],
        true,
        301
    );
    exit;
}

require __DIR__ . "/includes/session.php";
require __DIR__ . "/includes/db.php";

// CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (
        empty($_POST['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
    ) {
        $error = "Security error. Refresh and try again.";
    } else {

        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        $stmt = $conn->prepare(
            "SELECT id, password_hash FROM users WHERE email = :email LIMIT 1"
        );
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['is_admin'] = (bool)$user['is_admin'];

            header("Location: dashboard.php");
            exit;
        } else {
            $error = "Invalid email or password.";
        }
    }
}
?>
<!-- HTML BELOW UNCHANGED -->

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Sign In | INTELLI RESOLVERS</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<script src="https://kit.fontawesome.com/9f925d73ef.js" crossorigin="anonymous"></script>
<link href="https://fonts.googleapis.com/css2?family=Kumbh+Sans:wght@100..900&display=swap" rel="stylesheet">

<style>
/* =================== RESET =================== */
* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
    font-family: 'Kumbh Sans', sans-serif;
}

body {
    background: #141414;
    color: #fff;
    display: flex;
    flex-direction: column;
    min-height: 100vh;
}

/* =================== NAVBAR =================== */
.navbar {
    background: #141414;
    height: 80px;
    display: flex;
    justify-content: center;
    align-items: center;
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
    background: linear-gradient(to top, #ff0844, #ffb199);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    font-size: 2rem;
    text-decoration: none;
    display: flex;
    align-items: center;
}

.navbar__menu {
    display: flex;
    list-style: none;
}

.navbar__links {
    color: #fff;
    text-decoration: none;
    padding: 0 1rem;
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

/* =================== SIGN IN =================== */
.signin-wrapper {
    flex: 1;
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 5rem 1rem;
}

.signin-card {
    background: #1f1f1f;
    padding: 3rem;
    border-radius: 10px;
    width: 100%;
    max-width: 420px;
    box-shadow: 0 10px 40px rgba(0,0,0,.6);
    text-align: center;
}

.signin-card h1 {
    font-size: 2rem;
    margin-bottom: .5rem;
    background: linear-gradient(to top, #ff0844, #ffb199);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.signin-card p {
    color: #bbb;
    margin-bottom: 2rem;
}

.error {
    background: #2a0000;
    border: 1px solid #ff6b6b;
    color: #ffb3b3;
    padding: 1rem;
    border-radius: 6px;
    margin-bottom: 1.5rem;
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

/* =================== FOOTER =================== */
.footer__container {
    background: #141414;
    padding: 5rem 0;
    display: flex;
    flex-direction: column;
    align-items: center;
}

#footer__logo {
    color: #fff;
    font-size: 2rem;
    text-decoration: none;
    margin-bottom: 1.5rem;
}

.footer__links {
    display: flex;
    gap: 3rem;
    margin-bottom: 2rem;
}

.footer__link--items h3 {
    color: #f77062;
    margin-bottom: 1rem;
}

.footer__link--items a {
    color: #fff;
    text-decoration: none;
    display: block;
    margin-bottom: 0.5rem;
}

.footer__link--items a:hover {
    color: #f77062;
}

.website__rights {
    color: #777;
    font-size: 0.8rem;
}
</style>
</head>

<body>

<!-- NAVBAR -->
<nav class="navbar">
    <div class="navbar__container">
        <a href="index.html" id="navbar__logo">
            <i class="fas fa-gem"></i>&nbsp;INTELLI RESOLVERS
        </a>
        <ul class="navbar__menu">
            <li><a href="index.html" class="navbar__links">Home</a></li>
            <li><a href="services.html" class="navbar__links">Services</a></li>
            <li><a href="team.html" class="navbar__links">Team</a></li>
            <li class="navbar__btn">
                <a href="signinpage.php" class="button">Sign In</a>
            </li>
        </ul>
    </div>
</nav>

<!-- SIGN IN -->
<div class="signin-wrapper">
    <div class="signin-card">
        <h1>Sign In</h1>
        <p>Access your IntelliResolvers dashboard</p>

        <?php if ($error): ?>
            <div class="error"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="csrf_token"
                   value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Sign In</button>
        </form>
    </div>
</div>

<!-- FOOTER -->
<div class="footer__container">
    <a href="index.html" id="footer__logo">
        <i class="fas fa-gem"></i>INTELLI RESOLVERS
    </a>
    <div class="footer__links">
        <div class="footer__link--items">
            <h3>Support</h3>
            <a href="privacypolicy.html">Privacy Policy</a>
            <a href="termsofservice.html">Terms of Service</a>
        </div>
        <div class="footer__link--items">
            <h3>About Us</h3>
            <a href="contactus.html">Contact Us</a>
            <a href="websitecredits.html">Website Credits</a>
        </div>
    </div>
    <p class="website__rights">
        © INTELLI RESOLVERS <?= date('Y') ?>. All rights reserved.
    </p>
</div>

</body>
</html>
