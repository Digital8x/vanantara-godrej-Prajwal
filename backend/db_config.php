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

// Load .env file
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim(trim($value), '"\'');
            $_ENV[$name] = $value;
        }
    }
}
?>
