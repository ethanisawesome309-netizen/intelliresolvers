<?php
$host = "intelliresolvers.mysql.database.azure.com";
$db   = "appdb";
$user = "ethanwang";
$pass = "Wzxwzx07"; // use env var
$port = 3306;

$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4;sslmode=verify_ca";

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::MYSQL_ATTR_SSL_CA => __DIR__ . "/DigiCertGlobalRootCA.crt.pem",
];

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $conn = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}