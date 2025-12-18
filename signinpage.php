<?php
session_start();
require "db_connect.php";

if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

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
<title>Sign In</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>

<div class="container">
    <div class="login-card">
        <h2>Sign In</h2>

        <?php if (isset($error)): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION["csrf_token"] ?>">
            <input type="email" name="email" required placeholder="Email">
            <input type="password" name="password" required placeholder="Password">
            <button type="submit">Sign In</button>
        </form>
    </div>
</div>

</body>
</html>

<head>
<meta charset="UTF-8">
<title>Sign In</title>
<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: Arial, sans-serif;
}

body {
    background: #f6f8fb;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
}

.container {
    width: 100%;
    max-width: 400px;
    padding: 20px;
}

.login-card {
    background: white;
    padding: 30px;
    border-radius: 8px;
    box-shadow: 0 4px 14px rgba(0,0,0,0.1);
    text-align: center;
}

.login-card h2 {
    font-size: 24px;
    margin-bottom: 10px;
    color: #333;
}

input {
    width: 100%;
    padding: 12px;
    margin: 10px 0;
    border: 1px solid #ccc;
    border-radius: 4px;
    font-size: 16px;
}

button {
    width: 100%;
    background: #0057ff;
    color: white;
    padding: 12px;
    border: none;
    border-radius: 4px;
    font-size: 16px;
    cursor: pointer;
}

button:hover {
    background: #0046d5;
}

.error {
    color: #d8000c;
    margin-bottom: 10px;
}
</style>
</head>