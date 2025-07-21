<?php
include('../includes/db.php');

$id = $_GET['id'] ?? 0;
$sale = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT s.*, c.name, c.phone, c.membership 
    FROM sales s
    JOIN clients c ON s.clients_id = c.id
    WHERE s.id = $id
"));

$items = mysqli_query($conn, "
    SELECT si.*, p.product_name 
    FROM sale_items si
    JOIN products p ON si.product_id = p.id
    WHERE si.sale_id = $id
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Receipt</title>
    <style>
        body { font-family: Arial; padding: 20px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { padding: 10px; border: 1px solid #ccc; text-align: left; }
    </style>
</head>
<body>
    <h2>Receipt</h2>
    <p><strong>Client:</strong> <?= $sale['name'] ?> (<?= $sale['phone'] ?>)</p>
    <p><strong>Membership:</strong> <?= $sale['membership'] ?></p>
    <p><strong>Sale Date:</strong> <?= $sale['sale_date'] ?></p>

    <table>
        <thead>
            <tr>
                <th>Product</th>
                <th>Qty</th>
                <th>Price</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php $total = 0; while ($row = mysqli_fetch_assoc($items)): ?>
                <tr>
                    <td><?= $row['product_name'] ?></td>
                    <td><?= $row['quantity'] ?></td>
                    <td><?= number_format($row['price'], 2) ?></td>
                    <td><?= number_format($row['subtotal'], 2) ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <p><strong>Total Before Discount:</strong> <?= number_format($sale['total_amount'] + $sale['discount_percent'], 2) ?></p>
    <p><strong>Discount Applied:</strong> <?= number_format($sale['discount_percent'], 2) ?></p>
    <h3>Total Paid: <?= number_format($sale['total_amount'], 2) ?></h3>

    <button onclick="window.print()">Print Receipt</button>
</body>
</html>
