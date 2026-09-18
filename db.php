<?php
$host = getenv('DB_HOST') ?: 'localhost';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') ?: '';
$dbName = getenv('DB_NAME') ?: 'aclc_sams';

$conn = new mysqli($host, $user, $pass, $dbName);

if ($conn->connect_error) {
    error_log('Database connection failed: ' . $conn->connect_error);
    die('Database connection failed. Please contact the administrator.');
}

$conn->set_charset('utf8mb4');
?>
