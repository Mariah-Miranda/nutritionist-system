<?php
<<<<<<< Updated upstream
<<<<<<< Updated upstream
include('../includes/db_connect.php');

$today = date('Y-m-d');

// Get today's sales summary
$stmt = $pdo->prepare("
    SELECT COUNT(*) as sales_count, SUM(total_amount) as total_revenue 
    FROM sales 
    WHERE DATE(sale_date) = ?
");
$stmt->execute([$today]);
$todayStats = $stmt->fetch(PDO::FETCH_ASSOC);

// Get total customers
$stmt = $pdo->query("SELECT COUNT(*) as total FROM clients");
$totalCustomers = $stmt->fetchColumn();

// Get product count
$stmt = $pdo->query("SELECT COUNT(*) as total FROM products");
$totalProducts = $stmt->fetchColumn();
=======
=======
>>>>>>> Stashed changes
    require_once __DIR__ . '/../config.php';
    require_once __DIR__ . '/../includes/db_connect.php';
    require_once __DIR__ . '/../includes/auth.php';

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
<<<<<<< Updated upstream
>>>>>>> Stashed changes
=======
>>>>>>> Stashed changes
?>

<!DOCTYPE html>
<html>
<head>
    <title>Sales Dashboard</title>
<<<<<<< Updated upstream
<<<<<<< Updated upstream
    <link rel="stylesheet" href="../assets/css/style.css">
=======
   <link rel="stylesheet" href="../assets/css/style.css">
>>>>>>> Stashed changes
=======
   <link rel="stylesheet" href="../assets/css/style.css">
>>>>>>> Stashed changes
</head>

<body>
    <div class="sales-index-page">

        <h1 class="sales-index-title">Sales Dashboard</h1>

<<<<<<< Updated upstream
<<<<<<< Updated upstream
        <!-- Navigation buttons -->
=======
                <!-- Navigation buttons -->
>>>>>>> Stashed changes
=======
                <!-- Navigation buttons -->
>>>>>>> Stashed changes
        <div class="sales-index-nav">
            <a href="new.php" class="sales-index-link"> + New Sale</a>
            <a href="history.php" class="sales-index-link">📜 Sales History</a>
            <a href="summary.php" class="sales-index-link">📊 Sales Summary</a>
        </div>

<<<<<<< Updated upstream
<<<<<<< Updated upstream
=======

>>>>>>> Stashed changes
=======

>>>>>>> Stashed changes
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
<<<<<<< Updated upstream
<<<<<<< Updated upstream
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
=======
=======
>>>>>>> Stashed changes
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
<<<<<<< Updated upstream
>>>>>>> Stashed changes
=======
>>>>>>> Stashed changes
                    </tr>
                </tbody>
            </table>
        </div>

<<<<<<< Updated upstream
<<<<<<< Updated upstream
=======

>>>>>>> Stashed changes
=======

>>>>>>> Stashed changes
    </div>
</body>
</html>
