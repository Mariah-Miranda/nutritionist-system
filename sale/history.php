<?php
include('../includes/db_connect.php');
include('../includes/header.php');

$pageTitle = 'Sales History';

// Handle search
$searchTerm = $_GET['search'] ?? '';
$searchTerm = trim($searchTerm);

// Prepare query with or without search
if (!empty($searchTerm)) {
    $stmt = $pdo->prepare("
        SELECT 
            s.id AS sale_id,
            s.customer_name,
            s.customer_type,
            s.total_amount,
            s.sale_date,
            p.full_name AS patient_name,
            pr.product_name,
            si.quantity
        FROM sales s
        LEFT JOIN patients p ON s.clients_id = p.patient_id
        LEFT JOIN sale_items si ON s.id = si.sale_id
        LEFT JOIN products pr ON si.product_id = pr.id
        WHERE 
            (s.customer_type = 'Patient' AND p.full_name LIKE :search1)
            OR (s.customer_type != 'Patient' AND s.customer_name LIKE :search2)
        ORDER BY s.sale_date DESC, s.id, pr.product_name
    ");
    $stmt->execute([
        'search1' => '%' . $searchTerm . '%',
        'search2' => '%' . $searchTerm . '%'
    ]);
} else {
    $stmt = $pdo->query("
        SELECT 
            s.id AS sale_id,
            s.customer_name,
            s.customer_type,
            s.total_amount,
            s.sale_date,
            p.full_name AS patient_name,
            pr.product_name,
            si.quantity
        FROM sales s
        LEFT JOIN patients p ON s.clients_id = p.patient_id
        LEFT JOIN sale_items si ON s.id = si.sale_id
        LEFT JOIN products pr ON si.product_id = pr.id
        ORDER BY s.sale_date DESC, s.id, pr.product_name
    ");
}

// Group by sale_id
$sales = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $id = $row['sale_id'];
    if (!isset($sales[$id])) {
        $sales[$id] = [
            'sale_id' => $id,
            'client_name' => $row['customer_type'] === 'Patient'
                ? ($row['patient_name'] ?? 'N/A')
                : ($row['customer_name'] ?? 'N/A'),
            'total_amount' => $row['total_amount'],
            'sale_date' => $row['sale_date'],
            'products' => [],
            'quantities' => []
        ];
    }
    if ($row['product_name']) {
        $sales[$id]['products'][] = $row['product_name'];
        $sales[$id]['quantities'][] = $row['quantity'];
    }
}
?>

<div class="container mx-auto p-4 md:p-8">
    <div class="bg-white rounded-lg shadow-lg p-6 md:p-8">
        
        <!-- Header Row -->
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-semibold text-gray-800 m-0">Sales History</h2>
            <a href="export_sales_history_pdf.php" class="inline-flex items-center space-x-2 px-4 py-2 rounded-lg bg-green-600 text-white font-semibold hover:bg-blue-700 transition-colors duration-200 shadow-md">
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
                        <th class="py-3 px-4 text-left text-sm font-semibold text-gray-600">Client Name</th>
                        <th class="py-3 px-4 text-left text-sm font-semibold text-gray-600">Products</th>
                        <th class="py-3 px-4 text-left text-sm font-semibold text-gray-600">Quantities</th>
                        <th class="py-3 px-4 text-left text-sm font-semibold text-gray-600">Total</th>
                        <th class="py-3 px-4 text-left text-sm font-semibold text-gray-600">Date</th>
                        <th class="py-3 px-4 text-left text-sm font-semibold text-gray-600">Receipt</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php if (count($sales) > 0): ?>
                        <?php foreach ($sales as $sale): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="py-3 px-4 text-gray-800"><?= htmlspecialchars($sale['sale_id']) ?></td>
                                <td class="py-3 px-4 text-gray-800"><?= htmlspecialchars($sale['client_name']) ?></td>
                                <td class="py-3 px-4 text-gray-800">
                                    <?php foreach ($sale['products'] as $product): ?>
                                        <div><?= htmlspecialchars($product) ?></div>
                                    <?php endforeach; ?>
                                </td>
                                <td class="py-3 px-4 text-gray-800">
                                    <?php foreach ($sale['quantities'] as $qty): ?>
                                        <div><?= htmlspecialchars($qty) ?></div>
                                    <?php endforeach; ?>
                                </td>
                                <td class="py-3 px-4 text-gray-800"><?= DEFAULT_CURRENCY . ' ' . number_format($sale['total_amount'], 2) ?></td>
                                <td class="py-3 px-4 text-gray-800"><?= htmlspecialchars($sale['sale_date']) ?></td>
                                <td class="py-3 px-4">
                                    <a href="receipt.php?sale_id=<?= urlencode($sale['sale_id']) ?>" class="text-blue-600 hover:text-blue-800 font-medium">View</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-4 text-gray-500">No sales records found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include('../includes/footer.php'); ?>
