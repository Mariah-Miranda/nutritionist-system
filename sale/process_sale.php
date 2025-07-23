<?php
include('../includes/db_connect.php');

$name = $_POST['name'];
$phone = $_POST['phone'];
$membership = $_POST['membership'];
$product_ids = $_POST['product_ids'];
$quantities = $_POST['quantities'];

try {
    $pdo->beginTransaction();

    // Find or insert client
    $stmt = $pdo->prepare("SELECT id FROM clients WHERE name = ? AND phone = ? LIMIT 1");
    $stmt->execute([$name, $phone]);
    $client = $stmt->fetch();

    if ($client) {
        $clients_id = $client['id'];
    } else {
        $stmt = $pdo->prepare("INSERT INTO clients (name, phone, membership) VALUES (?, ?, ?)");
        $stmt->execute([$name, $phone, $membership]);
        $clients_id = $pdo->lastInsertId();
    }

    // Calculate totals
    $total = 0;
    $items = [];

    for ($i = 0; $i < count($product_ids); $i++) {
        $product_id = $product_ids[$i];
        $qty = $quantities[$i];

        $stmt = $pdo->prepare("SELECT price, stock FROM products WHERE id = ?");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch();

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
    $stmt = $pdo->prepare("INSERT INTO sales (clients_id, discount_percent, total_amount) VALUES (?, ?, ?)");
    $stmt->execute([$clients_id, $discount, $final_total]);
    $sale_id = $pdo->lastInsertId();

    // Save each sale item and update inventory
    foreach ($items as $item) {
        $stmt = $pdo->prepare("INSERT INTO sale_items (sale_id, product_id, quantity, price, subtotal)
                               VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$sale_id, $item['product_id'], $item['quantity'], $item['price'], $item['subtotal']]);

        // Update stock
        $stmt = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
        $stmt->execute([$item['quantity'], $item['product_id']]);
    }

    $pdo->commit();

    header("Location: receipt.php?id=$sale_id");
    exit;

} catch (Exception $e) {
    $pdo->rollBack();
    error_log("Sale processing failed: " . $e->getMessage());
    die("<h1>Error: " . $e->getMessage() . "</h1>");
}
