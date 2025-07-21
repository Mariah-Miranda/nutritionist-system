<?php
include('../includes/db.php');

// Get today's sales summary
$today = date('Y-m-d');
$todaySales = mysqli_query($conn, "
    SELECT COUNT(*) as sales_count, SUM(total_amount) as total_revenue 
    FROM sales 
    WHERE DATE(sale_date) = '$today'
");
$todayStats = mysqli_fetch_assoc($todaySales);

// Get total customers
$totalCustomers = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM clients"))['total'];

// Get product count
$totalProducts = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM products"))['total'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Sales Dashboard</title>
   <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
    <div class="sales-index-page">

        <h1 class="sales-index-title">Sales Dashboard</h1>

                <!-- Navigation buttons -->
        <div class="sales-index-nav">
            <a href="new.php" class="sales-index-link"> + New Sale</a>
            <a href="history.php" class="sales-index-link">📜 Sales History</a>
            <a href="summary.php" class="sales-index-link">📊 Sales Summary</a>
        </div>


        <!-- Table section -->
        <div class="sales-index-table-wrapper">
            <table class="sales-index-table">
                <thead>
                    <tr>
                        <th>Metric</th>
                        <th>Value</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Sales Made Today</td>
                        <td><?= $todayStats['sales_count'] ?? 0 ?></td>
                    </tr>
                    <tr>
                        <td>Revenue Today</td>
                        <td>UGX <?= number_format($todayStats['total_revenue'] ?? 0, 2) ?></td>
                    </tr>
                    <tr>
                        <td>Total Customers</td>
                        <td><?= $totalCustomers ?></td>
                    </tr>
                    <tr>
                        <td>Products Available</td>
                        <td><?= $totalProducts ?></td>
                    </tr>
                </tbody>
            </table>
        </div>


    </div>
</body>
</html>
