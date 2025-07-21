<?php
include('../includes/db.php');

// Collect form data
$name = $_POST['name'];
$phone = $_POST['phone'];
$membership = $_POST['membership'];
$product_ids = $_POST['product_ids'];
$quantities = $_POST['quantities'];

// Find or insert customer
$check = mysqli_query($conn, "SELECT id FROM customers WHERE name = '$name' AND phone = '$phone' LIMIT 1");
if (mysqli_num_rows($check) > 0) {
    $customer = mysqli_fetch_assoc($check);
    $customer_id = $customer['id'];
} else {
    mysqli_query($conn, "INSERT INTO customers (name, phone, membership) VALUES ('$name', '$phone', '$membership')");
    $customer_id = mysqli_insert_id($conn);
}

// Calculate totals
$total = 0;
$items = [];
for ($i = 0; $i < count($product_ids); $i++) {
    $product_id = $product_ids[$i];
    $qty = $quantities[$i];
    $product = mysqli_fetch_assoc(mysqli_query($conn, "SELECT price, quantity FROM products WHERE id = $product_id"));
    $price = $product['price'];
    $subtotal = $price * $qty;
    $items[] = [
        'product_id' => $product_id,
        'quantity' => $qty,
        'price' => $price,
        'subtotal' => $subtotal
    ];
    $total += $subtotal;
}

// Apply discount
$discount_rate = ['Gold' => 0.10, 'Platinum' => 0.15, 'Silver' => 0.05];
$discount = $total * ($discount_rate[$membership] ?? 0);
$final_total = $total - $discount;

// Save to sales table
mysqli_query($conn, "INSERT INTO sales (customer_id, discount_percent, total_amount) VALUES ($customer_id, $discount, $final_total)");
$sale_id = mysqli_insert_id($conn);

// Save each sale item
foreach ($items as $item) {
    mysqli_query($conn, "INSERT INTO sale_items (sale_id, product_id, quantity, price, subtotal)
                         VALUES ($sale_id, {$item['product_id']}, {$item['quantity']}, {$item['price']}, {$item['subtotal']})");

    // Update inventory
    mysqli_query($conn, "UPDATE products SET quantity = quantity - {$item['quantity']} WHERE id = {$item['product_id']}");
}

// Redirect to receipt
header("Location: receipt.php?id=$sale_id");
exit;
?>
