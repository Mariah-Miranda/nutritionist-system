<?php
include('../includes/db_connect.php');
include('../includes/header.php'); // Include header for consistent styling and auth

$pageTitle = 'Sales History'; // Set page title

// Pagination settings
$records_per_page = 10;
$current_page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $records_per_page;

// Search term handling
$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';
$searchCondition = '';
$searchParams = [];

if (!empty($searchTerm)) {
    // Modify search to look for patient_name or customer_name
    $searchCondition = " WHERE p.full_name LIKE ? OR s.customer_name LIKE ? ";
    $searchParams = ["%$searchTerm%", "%$searchTerm%"];
}

// Get total number of records for pagination
$count_stmt = $pdo->prepare("
    SELECT COUNT(*) as total_records
    FROM sales s
    LEFT JOIN patients p ON s.clients_id = p.patient_id
    " . $searchCondition
);
$count_stmt->execute($searchParams);
$total_records = $count_stmt->fetchColumn();
$total_pages = ceil($total_records / $records_per_page);

// Fetch sales records for the current page
$stmt = $pdo->prepare("
    SELECT s.id, s.customer_type, s.customer_name, p.full_name AS patient_name, s.total_amount, s.sale_date
    FROM sales s
    LEFT JOIN patients p ON s.clients_id = p.patient_id
    " . $searchCondition . "
    ORDER BY s.sale_date DESC
    LIMIT ? OFFSET ?
");
$stmt->execute(array_merge($searchParams, [$records_per_page, $offset]));
?>

<div class="container mx-auto p-4 md:p-8">
    <div class="bg-white rounded-lg shadow-lg p-6 md:p-8">
        
        <!-- Header Row -->
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-semibold text-gray-800 m-0">Sales History</h2>
            <a href="export_sales_history_pdf.php" class="inline-flex items-center space-x-2 px-4 py-2 rounded-lg bg-green-600 text-white font-semibold hover:bg-green-700 transition-colors duration-200 shadow-md">
                <i class="fas fa-download"></i>
                <span>Download</span>
            </a>
        </div>

        <!-- Search Form -->
        <form method="get" class="mb-6">
            <div class="flex items-center space-x-4">
                <input type="text" name="search" placeholder="Search by client name"
                       value="<?= htmlspecialchars($searchTerm) ?>"
                       class="w-full md:w-1/3 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition duration-200">
                    Search
                </button>
            </div>
        </form>

        <!-- Sales Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white rounded-lg overflow-hidden">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="py-3 px-4 text-left text-sm font-semibold text-gray-600">Sale ID</th>
                        <th class="py-3 px-4 text-left text-sm font-semibold text-gray-600">Customer Type</th>
                        <th class="py-3 px-4 text-left text-sm font-semibold text-gray-600">Customer Name</th>
                        <th class="py-3 px-4 text-left text-sm font-semibold text-gray-600">Total</th>
                        <th class="py-3 px-4 text-left text-sm font-semibold text-gray-600">Date</th>
                        <th class="py-3 px-4 text-left text-sm font-semibold text-gray-600">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php if ($stmt->rowCount() > 0): ?>
                        <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="py-3 px-4 text-gray-800"><?= htmlspecialchars($row['id']) ?></td>
                            <td class="py-3 px-4 text-gray-800"><?= htmlspecialchars($row['customer_type']) ?></td>
                            <td class="py-3 px-4 text-gray-800">
                                <?php
                                    if ($row['customer_type'] === 'Patient') {
                                        echo htmlspecialchars($row['patient_name'] ?? 'N/A');
                                    } else {
                                        echo htmlspecialchars($row['customer_name'] ?? 'N/A');
                                    }
                                ?>
                            </td>
                            <td class="py-3 px-4 text-gray-800"><?= DEFAULT_CURRENCY . ' ' . number_format($row['total_amount'], 2) ?></td>
                            <td class="py-3 px-4 text-gray-800"><?= htmlspecialchars($row['sale_date']) ?></td>
                            <td class="py-3 px-4">
                                <a href="receipt.php?sale_id=<?= urlencode($row['id']) ?>" class="text-blue-600 hover:text-blue-800 font-medium">View</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-4 text-gray-500">No sales records found.</td>
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
                $prev_page_link = 'history.php?page=' . ($current_page - 1) . (!empty($searchTerm) ? '&search=' . urlencode($searchTerm) : '');
                $next_page_link = 'history.php?page=' . ($current_page + 1) . (!empty($searchTerm) ? '&search=' . urlencode($searchTerm) : '');
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
                        <a href="history.php?page=<?= $i ?><?= !empty($searchTerm) ? '&search=' . urlencode($searchTerm) : '' ?>"
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
</div>

<?php include('../includes/footer.php'); ?>
