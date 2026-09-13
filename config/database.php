<?php
// Determine if running locally (XAMPP) or on live hosting
$http_host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$is_local  = (strpos($http_host, 'localhost') !== false || strpos($http_host, '127.0.0.1') !== false);

if ($is_local) {
    // Local XAMPP Environment
    $base_url = '/lost-found';
    $host     = 'localhost';
    $dbname   = 'unifind_db';
    $username = 'root';
    $password = '';
} else {
    // Live Hosting Environment (InfinityFree)
    $base_url = '';
    $host     = 'sql302.infinityfree.com';
    $dbname   = 'if0_42903501_unifind';
    $username = 'if0_42903501';
    $password = 'PXyN6VNuxX9M';
}

// Optional: Custom override if config/local.php exists
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
