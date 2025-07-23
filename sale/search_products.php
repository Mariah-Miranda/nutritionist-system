<?php
// sales/search_products.php

// Ensure configuration and database connection are loaded
// Paths are relative to this file's location (sales/)
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db_connect.php';

header('Content-Type: application/json'); // Set header to indicate JSON response

$searchTerm = $_GET['search_term'] ?? ''; // Get the search term from the GET request, default to empty string

$products = []; // Initialize an empty array to store products

// Only perform search if the search term has at least 2 characters to avoid broad searches
if (strlen($searchTerm) >= 2) {
    try {
        // Prepare SQL statement to search for products by name and ensure stock is greater than 0
        // Using LIKE for partial matching and prepared statements for security
        $stmt = $pdo->prepare("SELECT id, product_name, price, stock 
                               FROM products 
                               WHERE product_name LIKE ? AND stock > 0
                               ORDER BY product_name ASC 
                               LIMIT 10"); // Limit results for performance

        $searchParam = '%' . $searchTerm . '%'; // Add wildcards for LIKE operator
        $stmt->execute([$searchParam]); // Execute the statement with the search term
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC); // Fetch all matching products as associative array

    } catch (PDOException $e) {
        // Log the error for debugging purposes (check your server's error logs)
        error_log("Error searching products: " . $e->getMessage());
        // Return an empty JSON array in case of a database error to prevent frontend breakage
        echo json_encode([]);
        exit(); // Stop script execution
    }
}

// Encode the products array (which might be empty) to JSON and output it
echo json_encode($products);
?>
