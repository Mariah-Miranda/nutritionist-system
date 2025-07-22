<?php
<<<<<<< Updated upstream
<<<<<<< Updated upstream
include('../includes/db_connect.php');

// Daily summary query
$dailyStmt = $pdo->query("
=======
=======
>>>>>>> Stashed changes
    require_once __DIR__ . '/../config.php';
    require_once __DIR__ . '/../includes/db_connect.php';
    require_once __DIR__ . '/../includes/auth.php';

// Daily summary
$daily = mysqli_query($conn, "
<<<<<<< Updated upstream
>>>>>>> Stashed changes
=======
>>>>>>> Stashed changes
    SELECT DATE(sale_date) as day, COUNT(*) as total_sales, SUM(total_amount) as revenue
    FROM sales
    GROUP BY day
    ORDER BY day DESC
");

<<<<<<< Updated upstream
<<<<<<< Updated upstream
// Best sellers query
$topStmt = $pdo->query("
=======
// Best sellers
$top = mysqli_query($conn, "
>>>>>>> Stashed changes
=======
// Best sellers
$top = mysqli_query($conn, "
>>>>>>> Stashed changes
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
<<<<<<< Updated upstream
<<<<<<< Updated upstream
            <?php while ($row = $dailyStmt->fetch(PDO::FETCH_ASSOC)): ?>
            <tr>
                <td><?= htmlspecialchars($row['day']) ?></td>
                <td><?= (int)$row['total_sales'] ?></td>
                <td><?= number_format((float)$row['revenue'], 2) ?></td>
=======
=======
>>>>>>> Stashed changes
            <?php while ($row = mysqli_fetch_assoc($daily)): ?>
            <tr>
                <td><?= $row['day'] ?></td>
                <td><?= $row['total_sales'] ?></td>
                <td><?= number_format($row['revenue'], 2) ?></td>
<<<<<<< Updated upstream
>>>>>>> Stashed changes
=======
>>>>>>> Stashed changes
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
<<<<<<< Updated upstream
<<<<<<< Updated upstream
            <?php while ($row = $topStmt->fetch(PDO::FETCH_ASSOC)): ?>
            <tr>
                <td><?= htmlspecialchars($row['product_name']) ?></td>
                <td><?= (int)$row['total_sold'] ?></td>
=======
=======
>>>>>>> Stashed changes
            <?php while ($row = mysqli_fetch_assoc($top)): ?>
            <tr>
                <td><?= $row['product_name'] ?></td>
                <td><?= $row['total_sold'] ?></td>
<<<<<<< Updated upstream
>>>>>>> Stashed changes
=======
>>>>>>> Stashed changes
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>
</html>
