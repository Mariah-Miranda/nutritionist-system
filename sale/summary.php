<?php
include('../includes/db_connect.php');
include('../includes/header.php'); // Include header for consistent styling and auth

$pageTitle = 'Sales Summary'; // Set page title

// Daily summary query
$dailyStmt = $pdo->query("
    SELECT DATE(sale_date) as day, COUNT(*) as total_sales, SUM(total_amount) as revenue
    FROM sales
    GROUP BY day
    ORDER BY day DESC
");

// Best sellers query
$topStmt = $pdo->query("
    SELECT p.product_name, SUM(si.quantity) as total_sold
    FROM sale_items si
    JOIN products p ON si.product_id = p.id
    GROUP BY si.product_id
    ORDER BY total_sold DESC
    LIMIT 5
");
?>

<div class="container mx-auto p-4 md:p-8">
    <div class="bg-white rounded-lg shadow-lg p-6 md:p-8">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-semibold text-gray-800 m-0">Sales Summary</h2>
            <a href="export_summary_pdf.php" class="inline-flex items-center space-x-2 px-4 py-2 rounded-lg bg-green-600 text-white font-semibold hover:bg-blue-700 transition-colors duration-200 shadow-md">
                <i class="fas fa-download"></i>
                <span>Download </span>
            </a>
        </div>
        <!-- rest of your content -->

        <h3 class="text-xl font-medium text-gray-700 mb-4">Daily Sales</h3>
        <div class="overflow-x-auto mb-8">
            <table class="min-w-full bg-white rounded-lg overflow-hidden">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="py-3 px-4 text-left text-sm font-semibold text-gray-600">Date</th>
                        <th class="py-3 px-4 text-left text-sm font-semibold text-gray-600">Total Sales</th>
                        <th class="py-3 px-4 text-left text-sm font-semibold text-gray-600">Revenue (<?php echo DEFAULT_CURRENCY; ?>)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php while ($row = $dailyStmt->fetch(PDO::FETCH_ASSOC)): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="py-3 px-4 text-gray-800"><?= htmlspecialchars($row['day']) ?></td>
                        <td class="py-3 px-4 text-gray-800"><?= (int)$row['total_sales'] ?></td>
                        <td class="py-3 px-4 text-gray-800"><?= number_format((float)$row['revenue'], 2) ?></td>
                    </tr>
                    <?php endwhile; ?>
                    <?php if ($dailyStmt->rowCount() === 0): ?>
                        <tr>
                            <td colspan="3" class="text-center py-4 text-gray-500">No daily sales data found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <h3 class="text-xl font-medium text-gray-700 mb-4">Top 5 Best-Selling Products</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white rounded-lg overflow-hidden">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="py-3 px-4 text-left text-sm font-semibold text-gray-600">Product</th>
                        <th class="py-3 px-4 text-left text-sm font-semibold text-gray-600">Units Sold</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php while ($row = $topStmt->fetch(PDO::FETCH_ASSOC)): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="py-3 px-4 text-gray-800"><?= htmlspecialchars($row['product_name']) ?></td>
                        <td class="py-3 px-4 text-gray-800"><?= (int)$row['total_sold'] ?></td>
                    </tr>
                    <?php endwhile; ?>
                    <?php if ($topStmt->rowCount() === 0): ?>
                        <tr>
                            <td colspan="2" class="text-center py-4 text-gray-500">No best-selling products data found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
include('../includes/footer.php'); // Include footer
?>
