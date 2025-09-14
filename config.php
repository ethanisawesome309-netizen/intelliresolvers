<?php

$servername = "<mysql-host>.mysql.database.azure.com";
$username = "<your-username>@<mysql-host>";
$password = "<your-password>";
$dbname = "<your-db-name>";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

?>