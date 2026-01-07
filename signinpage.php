<?php
// =============================================
// CANONICAL HOST
// =============================================
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

// =============================================
// CSRF TOKEN
// =============================================
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = "";

// =============================================
// LOGIN HANDLER
// =============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (
        empty($_POST['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
    ) {
        $error = "🚨 SECURITY ERROR<br>
                  Your session expired.<br>
                  Please refresh the page and try again.";
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
    <link
        href="https://fonts.googleapis.com/css2?family=Kumbh+Sans:wght@100..900&display=swap"
        rel="stylesheet"
    >
</head>

<body style="background:#141414;color:#fff;font-family:'Kumbh Sans',sans-serif">

    <h1 style="text-align:center;margin-top:5rem">
        Sign In
    </h1>

    <?php if ($error): ?>
        <div style="
            max-width:420px;
            margin:1.5rem auto;
            background:#2a0000;
            border:1px solid #ff6b6b;
            padding:1rem;
            border-radius:6px;
        ">
            <?= $error ?>
        </div>
    <?php endif; ?>

    <form method="POST" style="max-width:420px;margin:2rem auto;">
        <input
            type="hidden"
            name="csrf_token"
            value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>"
        >

        <input
            type="email"
            name="email"
            placeholder="Email"
            required
            style="width:100%;padding:14px;margin-bottom:1rem"
        >

        <input
            type="password"
            name="password"
            placeholder="Password"
            required
            style="width:100%;padding:14px;margin-bottom:1rem"
        >

        <button style="width:100%;padding:14px">
            Sign In
        </button>
    </form>

</body>
</html>
