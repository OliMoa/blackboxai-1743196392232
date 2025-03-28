<?php
// Database configuration for OMSYSTEM V2
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'omsystem_v2');

// Create connection
try {
    $conn = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ATTR_ERRMODE_EXCEPTION);
    $conn->exec("SET NAMES 'utf8'");
} catch(PDOException $e) {
    die("ERROR: Could not connect. " . $e->getMessage());
}
?>