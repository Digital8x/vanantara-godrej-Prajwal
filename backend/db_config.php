<?php
// Production Database Credentials
define('DB_HOST', 'localhost');
define('DB_USER', 'a1679hju_GodrejPrajwal');
define('DB_PASS', 'ArjunEswar');
define('DB_NAME', 'a1679hju_GodrejPrajwal');

// Establish Connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check Connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
