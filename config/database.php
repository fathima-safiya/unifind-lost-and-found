<?php
// Database connection settings
$base_url = '/lost-found';

$host     = 'localhost';
$dbname   = 'unifind_db';
$username = 'root';
$password = '';

// Optional: Load server or local configuration override if present
if (file_exists(__DIR__ . '/local.php')) {
    include __DIR__ . '/local.php';
}

try {
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
} catch (PDOException $e) {
    die("Database connection failed. Please try again later.");
}
?>
