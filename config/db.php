<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Read database settings from environment variables
$dbHost = getenv('DB_HOST') ?: 'database-medi.chqe2ei0yzi8.ap-south-1.rds.amazonaws.com';
$dbName = getenv('DB_NAME') ?: 'doctor_booking_system';
$dbUser = getenv('DB_USER') ?: 'admin';
$dbPass = getenv('DB_PASS') ?: 'medi0012';

define('DB_HOST', $dbHost);
define('DB_NAME', $dbName);
define('DB_USER', $dbUser);
define('DB_PASS', $dbPass);

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $exception) {
    http_response_code(500);
    exit("Database connection failed: " . $exception->getMessage());
}

require_once __DIR__ . '/../includes/functions.php';

bootstrap_default_data($pdo);