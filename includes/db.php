<?php
$host = "intelliresolvers.mysql.database.azure.com";
$db   = "appdb";
$user = "ethanwang";
$pass = getenv("DB_PASSWORD");
$port = 3306;

$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4;sslmode=required";

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,

    // 🔐 REQUIRED FOR require_secure_transport=ON
    PDO::MYSQL_ATTR_SSL_CA => __DIR__ . "/certs/DigiCertGlobalRootCA.crt",

    // Optional but recommended
    PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
];

try {
    $conn = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}