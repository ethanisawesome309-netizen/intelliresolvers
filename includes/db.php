<?php
$host = "intelliresolvers.mysql.database.azure.com";
$db   = "appdb";
$user = "ethanwang";
$pass = "Wzxwzx07";
$port = 3306;

$caPath = __DIR__ . "/certs/DigiCertGlobalRootCA.crt.pem";

// DEBUG CHECK (remove later)
if (!file_exists($caPath)) {
    die("SSL CA file not found at: " . $caPath);
}

$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,

    // 🔐 REQUIRED FOR AZURE
    PDO::MYSQL_ATTR_SSL_CA => $caPath,
];

try {
    $conn = new PDO($dsn, $user, $pass, $options);
    echo "CONNECTED SECURELY";
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
