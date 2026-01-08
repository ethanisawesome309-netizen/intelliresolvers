<?php
require __DIR__ . "/includes/session.php";

echo "<h1>SESSION DEBUG</h1>";

echo "<h2>SESSION</h2>";
echo "<pre>";
var_dump($_SESSION);
echo "</pre>";

echo "<h2>COOKIES</h2>";
echo "<pre>";
var_dump($_COOKIE);
echo "</pre>";

echo "<h2>HEADERS</h2>";
echo "<pre>";
var_dump(headers_list());
echo "</pre>";

echo "<h2>SERVER</h2>";
echo "<pre>";
echo "HTTPS: " . ($_SERVER['HTTPS'] ?? 'no') . "\n";
echo "HOST: " . $_SERVER['HTTP_HOST'] . "\n";
echo "URI: " . $_SERVER['REQUEST_URI'] . "\n";
echo "</pre>";
