<?php
include('../includes/db_connect.php');
include('../includes/header.php');

$pageTitle = "Top Clients";

// Pagination settings
$records_per_page = 10;
$current_page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $records_per_page;

// Build the base query for top clients
$base_query = "
    SELECT
        CASE 
            WHEN customer_type = 'Patient' THEN (SELECT full_name FROM patients WHERE patient_id = sales.clients_id)
            ELSE customer_name
        END AS client_name,
        SUM(total_amount) AS total_spent,
        COUNT(*) AS purchase_count
    FROM sales
    GROUP BY client_name
";

// Get total number of records for pagination
$count_stmt = $pdo->query("SELECT COUNT(*) FROM (" . $base_query . ") AS sub_count");
$total_records = $count_stmt->fetchColumn();
$total_pages = ceil($total_records / $records_per_page);

// Get top clients by total purchases with pagination
$stmt = $pdo->prepare("
    " . $base_query . "
    ORDER BY total_spent DESC
    LIMIT ? OFFSET ?
");
$stmt->execute([$records_per_page, $offset]);
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

        <!-- Pagination Controls -->
        <div class="flex justify-between items-center mt-6">
            <div>
                Page <?= $current_page ?> of <?= $total_pages ?>
            </div>
            <div class="flex space-x-2">
                <?php
                $prev_page_link = 'top_clients.php?page=' . ($current_page - 1);
                $next_page_link = 'top_clients.php?page=' . ($current_page + 1);
                ?>
                <a href="<?= $prev_page_link ?>"
                   class="px-4 py-2 rounded-lg font-semibold transition duration-200
                   <?= $current_page <= 1 ? 'bg-gray-300 text-gray-600 cursor-not-allowed' : 'bg-blue-600 text-white hover:bg-blue-700' ?>
                   <?= $current_page <= 1 ? 'pointer-events-none' : '' ?>">
                    Previous
                </a>

                <!-- Page numbers -->
                <div class="flex space-x-1">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="top_clients.php?page=<?= $i ?>"
                           class="px-3 py-2 rounded-lg font-semibold transition duration-200
                           <?= $i === $current_page ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>
                </div>

                <a href="<?= $next_page_link ?>"
                   class="px-4 py-2 rounded-lg font-semibold transition duration-200
                   <?= $current_page >= $total_pages ? 'bg-gray-300 text-gray-600 cursor-not-allowed' : 'bg-blue-600 text-white hover:bg-blue-700' ?>
                   <?= $current_page >= $total_pages ? 'pointer-events-none' : '' ?>">
                    Next
                </a>
            </div>
        </div>
    </div>
</body>
</html>
