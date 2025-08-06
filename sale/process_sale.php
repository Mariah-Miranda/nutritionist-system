<?php
// sales/process_sale.php

// This is a conceptual file. You need to create this file on your server
// and implement the actual database logic.

// Ensure configuration and database connection are loaded
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/auth.php'; // For authentication checks

header('Content-Type: application/json'); // Respond with JSON

$response = ['success' => false, 'message' => 'An unknown error occurred.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // 1. Validate and sanitize input
        $patient_id = filter_input(INPUT_POST, 'patient_id', FILTER_VALIDATE_INT);
        $sale_items_json = filter_input(INPUT_POST, 'sale_items', FILTER_UNSAFE_RAW); // Get raw JSON string
        $total_amount = filter_input(INPUT_POST, 'total_amount', FILTER_VALIDATE_FLOAT);

        if (!$patient_id || !$sale_items_json || !$total_amount) {
            throw new Exception('Invalid or missing sale data provided.');
        }

        $sale_items = json_decode($sale_items_json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON for sale items: ' . json_last_error_msg());
        }

        if (empty($sale_items)) {
            throw new Exception('No items in the sale.');
        }

        // Start a transaction for atomicity
        $pdo->beginTransaction();

        // 2. Insert into 'sales' table
        // You might want to get customer_name and customer_phone from the patients table
        // based on patient_id if not already passed from frontend.
        // For simplicity, we'll assume customer_type is 'Patient' and use patient_id.
        $stmt = $pdo->prepare("
            INSERT INTO sales (customer_type, clients_id, discount_percent, total_amount, payment_method, sale_date)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        // Assuming 0% discount and 'Cash' payment method for now.
        // You should add form fields for these if needed.
        $discount_percent = 0.00;
        $payment_method = 'Cash';

        $stmt->execute([
            'Patient',
            $patient_id,
            $discount_percent,
            $total_amount,
            $payment_method
        ]);

        $sale_id = $pdo->lastInsertId();

        if (!$sale_id) {
            throw new Exception('Failed to create sale record.');
        }

        // 3. Insert into 'sale_items' and update 'products' stock
        foreach ($sale_items as $item) {
            $product_id = filter_var($item['id'], FILTER_VALIDATE_INT);
            $quantity = filter_var($item['quantity'], FILTER_VALIDATE_INT);
            $price = filter_var($item['price'], FILTER_VALIDATE_FLOAT);

            if (!$product_id || !$quantity || !$price) {
                throw new Exception('Invalid product item data.');
            }

            // Check product stock before inserting (double-check on server-side)
            $stock_stmt = $pdo->prepare("SELECT stock FROM products WHERE id = ? FOR UPDATE"); // FOR UPDATE to lock row
            $stock_stmt->execute([$product_id]);
            $current_stock = $stock_stmt->fetchColumn();

            if ($current_stock === false || $current_stock < $quantity) {
                throw new Exception("Insufficient stock for product ID {$product_id}. Available: {$current_stock}, Requested: {$quantity}.");
            }

            // Insert into sale_items
            $item_stmt = $pdo->prepare("
                INSERT INTO sale_items (sale_id, product_id, quantity, price, subtotal)
                VALUES (?, ?, ?, ?, ?)
            ");
            $subtotal = $quantity * $price;
            $item_stmt->execute([$sale_id, $product_id, $quantity, $price, $subtotal]);

            // Update product stock
            $update_stock_stmt = $pdo->prepare("
                UPDATE products SET stock = stock - ? WHERE id = ?
            ");
            $update_stock_stmt->execute([$quantity, $product_id]);
        }

        // If all successful, commit the transaction
        $pdo->commit();

        $response['success'] = true;
        $response['message'] = 'Sale completed successfully!';
        $response['sale_id'] = $sale_id; // Return sale_id for redirection to receipt
        
    } catch (Exception $e) {
        // Rollback transaction on error
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $response['message'] = 'Error processing sale: ' . $e->getMessage();
        error_log("Sale processing error: " . $e->getMessage()); // Log error for debugging
    }
} else {
    $response['message'] = 'Invalid request method.';
}

echo json_encode($response);
?>
