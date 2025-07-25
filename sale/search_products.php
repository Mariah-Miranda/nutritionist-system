<?php
// sales/search_products.php

// TEMPORARY: Enable detailed error reporting for debugging
// IMPORTANT: Remove these lines in a production environment for security
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db_connect.php';
// require_once __DIR__ . '/../includes/auth.php'; // Removed explicit session check for search functionality

header('Content-Type: application/json'); // Respond with JSON

$searchTerm = $_GET['search_term'] ?? '';
$products = []; // Initialize products array

// Check if PDO connection object is available
if (!isset($pdo) || $pdo === null) {
    // Log error but return empty array to match search_patients.php behavior
    error_log('Database connection not established in search_products.php.');
    echo json_encode([]);
    exit();
}

// Require a minimum search term length to prevent overly broad queries
if (empty($searchTerm) || strlen($searchTerm) < 2) {
    echo json_encode([]); // Return empty array if search term is too short
    exit();
}

try {
    // Prepare a search query to find products by name that are in stock
    // Using LIKE with wildcards for flexible searching
    $stmt = $pdo->prepare("SELECT id, product_name, price, stock FROM products WHERE product_name LIKE ? AND stock > 0 ORDER BY product_name ASC LIMIT 10");
    $searchParam = '%' . $searchTerm . '%';
    $stmt->execute([$searchParam]);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($products); // Directly echo the products array

} catch (PDOException $e) {
    // Log the detailed database error for debugging purposes
    error_log("Database Error in search_products.php: " . $e->getMessage());
    // Return an empty array in case of a database error, similar to search_patients.php
    echo json_encode([]);
} catch (Exception $e) {
    // Catch any other unexpected errors
    error_log("General Error in search_products.php: " . $e->getMessage());
    // Return an empty array in case of a general error
    echo json_encode([]);
}
?>
