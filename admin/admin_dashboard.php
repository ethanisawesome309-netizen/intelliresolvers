<?php
declare(strict_types=1);
require __DIR__ . '/../includes/session.php';

// Check for admin status
if (
    empty($_SESSION['user_id']) ||
    empty($_SESSION['is_admin']) ||
    $_SESSION['is_admin'] !== true
) {
    http_response_code(403);
    exit('403 Forbidden');
}
$display_role = $_SESSION['role'] ?? 'Administrator';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <style>
        /* GLOBAL STYLES - No backticks needed here */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Kumbh Sans', sans-serif;
        }

        body {
            background: #141414 !important; /* !important ensures it overrides any JS delays */
            color: #fff;
        }

        .page {
            max-width: 1200px;
            margin: 0 auto;
            padding: 4rem 1rem;
        }

        .header-flex {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 3rem;
        }

        h1 {
            font-size: 2.5rem;
            background: linear-gradient(to top, #ff0844, #ffb199);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .card {
            background: #1f1f1f;
            border-radius: 8px;
            padding: 2rem;
            margin-bottom: 2.5rem;
        }

        .ticket {
            background: #141414;
            border-radius: 6px;
            padding: 1.2rem;
            margin-bottom: 1rem;
            border-left: 4px solid #333;
        }

        .ticket-title {
            font-weight: 600;
            background: linear-gradient(to top, #f77062, #fe5196);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .logout-btn {
            background: #ff4d4d;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 4px;
            font-weight: bold;
        }

        /* Additional styles from your snippet */
        .stat-card { background: #1f1f1f; padding: 1.5rem; border-radius: 8px; text-align: center; }
        .stat-number { font-size: 2rem; font-weight: bold; color: #fe5196; }
        select, button { background: #1f1f1f; border: 1px solid #333; color: #fff; padding: 8px; border-radius: 4px; }
    </style>
</head>
<body>
    <div id="root"></div>
    <script type="module" src="../assets/admin.js"></script>
</body>
</html>