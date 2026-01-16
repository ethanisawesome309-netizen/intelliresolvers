<?php

$servername = "<mysql-host>.mysql.database.azure.com";
$username = "<rnobncovhs>";
$password = "<uc8l$oGa7KLwvbX9>";
$dbname = "<users_db>";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

?>