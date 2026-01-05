<?php
session_start();

// Redirect if not logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: signinpage.php");
    exit;
}

// Server path to your assets folder
$assetsDir = __DIR__ . '/assets';

// Find the main JS file in /assets dynamically
$jsFile = '';
foreach (scandir($assetsDir) as $file) {
    if (preg_match('/^index.*\.js$/', $file)) {
        $jsFile = '/assets/' . $file;
        break;
    }
}

// If no JS found, throw an error
if (!$jsFile) {
    die("React build not found. Please upload files from dist/assets/");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Dashboard</title>
</head>
<body>
  <div id="root"></div>
  <p style="color:red;">If you see this, React did not load.</p>

  <!-- Load React JS dynamically -->
  <script src="<?= htmlspecialchars($jsFile) ?>"></script>
</body>
</html>
