<?php
// sales/process_sale.php

// IMPORTANT: For production, you should set display_errors to 0
// and log errors to a file. For debugging, temporarily enabling E_ALL
// is fine, but for AJAX responses, NO output before JSON is critical.
ini_set('display_errors', 1); // Enable error display for debugging
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/auth.php'; // For session handling and user authentication

// Set content type to JSON early to prevent other output from breaking the response
header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'An unknown error occurred.'];

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method.');
    }
    
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Unauthorized access.');
    }
    
    // Get customer type and related data
    $customer_type = $_POST['customer_type'] ?? 'Patient';
    $patient_id = filter_input(INPUT_POST, 'patient_id', FILTER_VALIDATE_INT);
    $sale_items_json = $_POST['sale_items'] ?? '[]';
    $total_amount = filter_input(INPUT_POST, 'total_amount', FILTER_VALIDATE_FLOAT);

    // Basic validation
    if ($customer_type !== 'Patient') {
        throw new Exception('Unsupported customer type.');
    }
    if (!$patient_id) {
        throw new Exception('Patient ID is required.');
    }
    if ($total_amount === false || $total_amount < 0) {
        throw new Exception('Invalid total amount.');
    }

    $sale_items = json_decode($sale_items_json, true);
    if (json_last_error() !== JSON_ERROR_NONE || !is_array($sale_items)) {
        throw new Exception('Invalid JSON for sale items: ' . json_last_error_msg());
    }

    if (empty($sale_items)) {
        throw new Exception('Sale items cannot be empty.');
    }
    
    // Start a transaction for atomicity
    $pdo->beginTransaction();

    // Insert into sales table
    $stmt_sale = $pdo->prepare("INSERT INTO sales (customer_type, clients_id, total_amount, sale_date) VALUES (?, ?, ?, NOW())");
    $stmt_sale->execute([$customer_type, $patient_id, $total_amount]);
    $sale_id = $pdo->lastInsertId();

    if (!$sale_id) {
        throw new Exception('Failed to create sale record.');
    }

    // Insert sale items and update product stock
    $stmt_item = $pdo->prepare("INSERT INTO sale_items (sale_id, product_id, quantity, product_price, subtotal) VALUES (?, ?, ?, ?, ?)");
    $stmt_update_stock = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?");

    foreach ($sale_items as $item) {
        // Data sanitization and validation within the loop
        $product_id = filter_var($item['id'], FILTER_VALIDATE_INT);
        $quantity = filter_var($item['quantity'], FILTER_VALIDATE_INT);
        $price = filter_var($item['price'], FILTER_VALIDATE_FLOAT);
        $subtotal = $quantity * $price;

        if ($product_id === false || $quantity === false || $quantity <= 0 || $price === false || $price < 0 || $subtotal === false || $subtotal < 0) {
            throw new Exception("Invalid item data for product ID: " . ($item['id'] ?? 'N/A'));
        }

        // Check current stock before updating
        $check_stock_stmt = $pdo->prepare("SELECT stock FROM products WHERE id = ?");
        $check_stock_stmt->execute([$product_id]);
        $current_stock = $check_stock_stmt->fetchColumn();

        if ($current_stock === false || $current_stock < $quantity) {
            throw new Exception("Insufficient stock for product ID: {$product_id}. Available: {$current_stock}, Requested: {$quantity}");
        }

        // Insert sale item
        $stmt_item->execute([$sale_id, $product_id, $quantity, $price, $subtotal]);

        // Update product stock
        $stmt_update_stock->execute([$quantity, $product_id, $quantity]); // Ensure stock is sufficient
    }

    $pdo->commit();
    $response['success'] = true;
    $response['message'] = 'Sale completed successfully!';
    $response['sale_id'] = $sale_id;

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $response['message'] = 'Sale processing failed: ' . $e->getMessage();
    error_log("Sale Process Error: " . $e->getMessage()); // Log the error
} finally {
    echo json_encode($response);
}
?>
