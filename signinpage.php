<?php
declare(strict_types=1);

require __DIR__ . '/includes/session.php'; 
require __DIR__ . '/includes/db.php';

$error = null;
$info_message = null;

// ==================== UI ALERTS ====================
if (isset($_GET['error']) && $_GET['error'] === 'timeout') {
    $error = "Your session has expired due to inactivity. Please sign in again.";
}
if (isset($_GET['loggedout'])) {
    $info_message = "You have been logged out successfully.";
}

// ==================== CSRF TOKEN ====================
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf_post = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'], $csrf_post)) {
        $error = "Invalid CSRF token";
    } else {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        // UPDATED: Added 'role' to the SELECT statement
        $stmt = $conn->prepare("SELECT id, password_hash, is_admin, role FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true); 

            $_SESSION['user_id']  = (int)$user['id'];
            $_SESSION['is_admin'] = (bool)$user['is_admin'];
            $_SESSION['email']    = $email;
            
            // NEW: Save the tier role to the session for dashboard filtering
            $_SESSION['role']     = $user['role'] ?? 'Staff'; 
            
            $_SESSION['last_activity'] = time();

            session_write_close();

            $redirect = $_SESSION['is_admin'] ? "/admin/admin_dashboard.php" : "/dashboard.php";
            header("Location: " . $redirect);
            exit;
        }
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

.website__rights {
    color: #777;
    font-size: 0.8rem;
}
</style>
</head>

<body>
<nav class="navbar">
    <div class="navbar__container">
        <a href="index.html" id="navbar__logo"><i class="fas fa-gem"></i>&nbsp;INTELLI RESOLVERS</a>
        <ul class="navbar__menu">
            <li><a href="index.html" class="navbar__links">Home</a></li>
            <li class="navbar__btn"><a href="signinpage.php" class="button">Sign In</a></li>
        </ul>
    </div>
</nav>
<div class="signin-wrapper">
    <div class="signin-card">
        <h1>Sign In</h1>
        <p>Access your IntelliResolvers dashboard</p>
        
        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($info_message): ?>
            <div class="success"><?= htmlspecialchars($info_message) ?></div>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Sign In</button>
        </form>
    </div>
</div>
<div class="footer__container">
    <p class="website__rights">© INTELLI RESOLVERS <?= date('Y') ?>. All rights reserved.</p>
</div>
</body>
</html>