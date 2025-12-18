<?php
$host = "intelliresolvers-server.mysql.database.azure.com"; 
$port = 3306;
$db   = "intelliresolvers-database";
$user = "rnobncovhs";
$pass = "Wzxwzx07";
$charset = "utf8mb4";

$ssl_ca = __DIR__ . '/DigiCertGlobalRootG2.crt.pem'; 

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, 
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
    PDO::MYSQL_ATTR_SSL_CA       => $ssl_ca, 
];

try {
    $conn = new PDO($dsn, $user, $pass, $options);
    echo "Connected successfully!";
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}