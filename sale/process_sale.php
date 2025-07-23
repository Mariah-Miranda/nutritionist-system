<?php
// sales/process_sale.php

// TEMPORARY: Enable detailed error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/auth.php'; // For session handling and user authentication

header('Content-Type: application/json'); // Respond with JSON

$response = ['success' => false, 'message' => 'An unknown error occurred.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if user is logged in and has appropriate role (e.g., 'Sales', 'Admin')
    if (!isset($_SESSION['user_id'])) {
        $response['message'] = 'Unauthorized access.';
        echo json_encode($response);
        exit();
    }

    // Get customer type and related data
    $customer_type = filter_input(INPUT_POST, 'customer_type', FILTER_SANITIZE_STRING);
    $patient_id = filter_input(INPUT_POST, 'patient_id', FILTER_VALIDATE_INT); // Will be null if not 'Patient'
    $customer_name = filter_input(INPUT_POST, 'customer_name', FILTER_SANITIZE_STRING); // Will be null if 'Patient'
    $customer_phone = filter_input(INPUT_POST, 'customer_phone', FILTER_SANITIZE_STRING); // Will be null if 'Patient'

    // Get sale details
    $total_amount = filter_input(INPUT_POST, 'total_amount', FILTER_VALIDATE_FLOAT);
    $discount_percent = filter_input(INPUT_POST, 'discount_percent', FILTER_VALIDATE_FLOAT);
    $payment_method = filter_input(INPUT_POST, 'payment_method', FILTER_SANITIZE_STRING);
    $sale_items_json = $_POST['sale_items'] ?? '[]'; // JSON string of sale items

    // Basic validation for sale details
    if ($total_amount === false || $total_amount < 0 || $discount_percent === false || $discount_percent < 0 || empty($payment_method)) {
        $response['message'] = 'Invalid sale details provided (total amount, discount, or payment method).';
        echo json_encode($response);
        exit();
    }

    // Validate customer type specific inputs
    if (!in_array($customer_type, ['Patient', 'Client', 'Visitor'])) {
        $response['message'] = 'Invalid customer type selected.';
        echo json_encode($response);
        exit();
    }

    if ($customer_type === 'Patient') {
        if (!$patient_id) {
            $response['message'] = 'Patient not selected for Patient type sale.';
            echo json_encode($response);
            exit();
        }
        // For 'Patient' type, customer_name and customer_phone should be null in DB
        $customer_name = null;
        $customer_phone = null;
    } else { // 'Client' or 'Visitor'
        // For 'Client' type, name is mandatory
        if ($customer_type === 'Client' && empty($customer_name)) {
            $response['message'] = 'Client name is required for Client type sale.';
            echo json_encode($response);
            exit();
        }
        // For 'Client' or 'Visitor' type, patient_id should be null in DB
        $patient_id = null;
    }

    $sale_items = json_decode($sale_items_json, true);
    if (json_last_error() !== JSON_ERROR_NONE || !is_array($sale_items) || empty($sale_items)) {
        $response['message'] = 'Invalid or empty sale items data.';
        echo json_encode($response);
        exit();
    }

    // Start a PDO transaction for atomicity
    $pdo->beginTransaction();

    try {
        // 1. Insert into sales table with new customer fields
        $stmt = $pdo->prepare("INSERT INTO sales (customer_type, customer_name, customer_phone, clients_id, discount_percent, total_amount, payment_method, sale_date) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
        
        // Execute based on customer type
        $stmt->execute([
            $customer_type,
            $customer_name,
            $customer_phone,
            $patient_id, // This will be NULL for Client/Visitor, and patient_id for Patient
            $discount_percent,
            $total_amount,
            $payment_method // This column is now expected in the table
        ]);
        $sale_id = $pdo->lastInsertId();

        if (!$sale_id) {
            throw new Exception("Failed to insert into sales table.");
        }

        // 2. Insert into sale_items table and update product stock
        $stmt_item = $pdo->prepare("INSERT INTO sale_items (sale_id, product_id, quantity, price, subtotal) VALUES (?, ?, ?, ?, ?)");
        $stmt_update_stock = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?");

        foreach ($sale_items as $item) {
            $product_id = filter_var($item['product_id'], FILTER_VALIDATE_INT);
            $quantity = filter_var($item['quantity'], FILTER_VALIDATE_INT);
            $price = filter_var($item['price'], FILTER_VALIDATE_FLOAT);
            $subtotal = filter_var($item['subtotal'], FILTER_VALIDATE_FLOAT);

            if (!$product_id || !$quantity || $quantity < 1 || $price === false || $price < 0 || $subtotal === false || $subtotal < 0) {
                throw new Exception("Invalid item data for product ID: " . ($item['product_id'] ?? 'N/A'));
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
        $pdo->rollBack();
        $response['message'] = 'Sale processing failed: ' . $e->getMessage();
        error_log("Sale Process Error: " . $e->getMessage());
    }
} else {
    $response['message'] = 'Invalid request method.';
}

echo json_encode($response);
?>
