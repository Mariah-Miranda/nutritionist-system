<?php
// Include configuration file
require_once __DIR__ . '/../config.php';

// Database connection variables
$host = DB_HOST;
$db   = DB_NAME;
$user = DB_USER;
$pass = DB_PASS;
$charset = 'utf8mb4'; // Recommended for wider character support

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Throw exceptions on errors
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Fetch rows as associative arrays
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Disable emulation for better performance and security
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    // echo "Database connection successful!"; // For testing - remove in production
} catch (\PDOException $e) {
    // Log the error for debugging purposes (e.g., to a file)
    error_log("Database Connection Error: " . $e->getMessage(), 0);

    // Display a user-friendly error message
    die("<h1>Database connection failed. Please try again later.</h1><p>Error: " . $e->getMessage() . "</p>");
}
?>