<?php
// generate_hash.php
// TEMPORARY FILE — DELETE AFTER USE

$password = "Wzxwzx07"; // CHANGE THIS PASSWORD

$hash = password_hash($password, PASSWORD_DEFAULT);

echo "Password: " . htmlspecialchars($password) . "<br>";
echo "Hash:<br>";
echo $hash;

?>