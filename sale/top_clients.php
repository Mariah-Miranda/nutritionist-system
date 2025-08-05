<?php
include('../includes/db_connect.php');
include('../includes/header.php');

$pageTitle = "Top Clients";

// Get top clients by total purchases
$stmt = $pdo->query("
    SELECT
        client_name,
        SUM(total_amount) AS total_spent,
        COUNT(*) AS purchase_count
    FROM (
        SELECT
            CASE 
                WHEN customer_type = 'Patient' THEN (SELECT full_name FROM patients WHERE patient_id = sales.clients_id)
                ELSE customer_name
            END AS client_name,
            total_amount
        FROM sales
    ) AS sub
    GROUP BY client_name
    ORDER BY total_spent DESC
    LIMIT 10
");
$topClients = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title><?= $pageTitle ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="sales-index-page">
        <h1 class="sales-index-title"><?= $pageTitle ?></h1>

        <div class="bg-white rounded-lg shadow-md p-4 mb-6 flex justify-start items-center">
            <a href="index.php" class="sales-index-link">← Back to Dashboard</a>
        </div>

        <div class="sales-index-table-wrapper">
            <table class="sales-index-table">
                <thead>
                    <tr>
                        <th>Client Name</th>
                        <th>Total Spent (UGX)</th>
                        <th>Number of Purchases</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($topClients) > 0): ?>
                        <?php foreach ($topClients as $client): ?>
                            <tr>
                                <td><?= htmlspecialchars($client['client_name']) ?></td>
                                <td><?= number_format((float)$client['total_spent'], 2) ?></td>
                                <td><?= (int)$client['purchase_count'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3" class="text-center">No purchases found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
