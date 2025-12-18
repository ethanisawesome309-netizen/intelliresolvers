<?php
$host = "intelliresolvers-server.mysql.database.azure.com"; 
$db   = "intelliresolvers-database";
$user = "rnobncovhs@intelliresolvers-server";
$pass = "Leafsarethebest1!";
$charset = "utf8mb4";

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $conn = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    die("Database connection failed");
}