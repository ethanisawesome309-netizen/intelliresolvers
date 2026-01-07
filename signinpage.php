<?php
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

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (
        empty($_POST['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
    ) {
        $error = "🚨 Security error. Please refresh and try again.";
    } else {

        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        $stmt = $conn->prepare(
            "SELECT id, password_hash
             FROM users
             WHERE email = :email
             LIMIT 1"
        );
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            header("Location: dashboard.php");
            exit;
        } else {
            $error = "❌ Invalid email or password.";
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
}

.signin-wrapper {
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
</style>
</head>

<body>

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

</body>
</html>
