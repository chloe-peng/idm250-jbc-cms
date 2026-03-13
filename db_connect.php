<?php

$env_file = __DIR__ . '/.env.php';
$env = file_exists($env_file) ? require $env_file : [];

define('DB_SERVER', $env['DB_SERVER'] ?? 'localhost');
define('DB_USER',   $env['DB_USER'] ?? 'cp3282');
define('DB_PASS',   $env['DB_PASS'] ?? 'WZU8qQPgdwXPxmG0');
define('DB_NAME',   $env['DB_NAME'] ?? 'cp3282_db');

$connection = new mysqli(DB_SERVER, DB_USER, DB_PASS, DB_NAME);

if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}
?>