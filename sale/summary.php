<?php
include('../includes/db.php');

// Daily summary
$daily = mysqli_query($conn, "
    SELECT DATE(sale_date) as day, COUNT(*) as total_sales, SUM(total_amount) as revenue
    FROM sales
    GROUP BY day
    ORDER BY day DESC
");

// Best sellers
$top = mysqli_query($conn, "
    SELECT p.product_name, SUM(si.quantity) as total_sold
    FROM sale_items si
    JOIN products p ON si.product_id = p.id
    GROUP BY si.product_id
    ORDER BY total_sold DESC
    LIMIT 5
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Sales Summary</title>
    <style>
        table { border-collapse: collapse; width: 100%; margin: 20px 0; }
        th, td { border: 1px solid #ccc; padding: 10px; text-align: left; }
    </style>
</head>
<body>
    <h2>Sales Summary</h2>

    <h3>Daily Sales</h3>
    <table>
        <thead>
            <tr><th>Date</th><th>Total Sales</th><th>Revenue</th></tr>
        </thead>
        <tbody>
            <?php while ($row = mysqli_fetch_assoc($daily)): ?>
            <tr>
                <td><?= $row['day'] ?></td>
                <td><?= $row['total_sales'] ?></td>
                <td><?= number_format($row['revenue'], 2) ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <h3>Top 5 Best-Selling Products</h3>
    <table>
        <thead>
            <tr><th>Product</th><th>Units Sold</th></tr>
        </thead>
        <tbody>
            <?php while ($row = mysqli_fetch_assoc($top)): ?>
            <tr>
                <td><?= $row['product_name'] ?></td>
                <td><?= $row['total_sold'] ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>
</html>
