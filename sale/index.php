<?php
include('../includes/db_connect.php');
include('../includes/header.php');

$today = date('Y-m-d');

// Get today's sales summary
$stmt = $pdo->prepare("
    SELECT COUNT(*) as sales_count, SUM(total_amount) as total_revenue 
    FROM sales 
    WHERE DATE(sale_date) = ?
");
$stmt->execute([$today]);
$todayStats = $stmt->fetch(PDO::FETCH_ASSOC);

// Get total customers (now considering patients as primary clients)
$stmt = $pdo->query("SELECT COUNT(*) as total FROM patients");
$totalClients = $stmt->fetchColumn();

// Get product count
$stmt = $pdo->query("SELECT COUNT(*) as total FROM products");
$totalProducts = $stmt->fetchColumn();
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
        <div class="bg-white rounded-lg shadow-md p-4 mb-6 flex justify-between items-center">
            <div class="sales-index-nav">
                <a href="new.php" class="sales-index-link"> + New Sale</a>
                <a href="history.php" class="sales-index-link">View Sales History</a>
                <a href="summary.php" class="sales-index-link">View Sales Summary</a>
                <a href="top_clients.php" class="sales-index-link">View Top Clients</a>
            </div>
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
                        <td><?= (int)($todayStats['sales_count'] ?? 0) ?></td>
                    </tr>
                    <tr>
                        <td>Revenue Today</td>
                        <td>UGX <?= number_format((float)($todayStats['total_revenue'] ?? 0), 2) ?></td>
                    </tr>
                    <tr>
                        <td>Total Customers</td>
                        <td><?= (int)$totalCustomers ?></td>
                    </tr>
                    <tr>
                        <td>Products Available</td>
                        <td><?= (int)$totalProducts ?></td>
                    </tr>
                </tbody>
            </table>
        </div>

    </div>
</body>
</html>
