<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/auth.php'; // Ensure auth is included for requireLogin()
require_once __DIR__ . '/../includes/functions.php'; // Assuming this might contain calculateAgeFromDob or similar if needed for future product features

requireLogin(); // Ensure the user is logged in
$pageTitle = "Product Inventory";

// Pagination settings
$records_per_page = 10;
$current_page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $records_per_page;

// Handle search filter
$search = $_GET['search'] ?? '';

$params = [];
$where = [];

if ($search) {
    $where[] = 'product_name LIKE ? OR description LIKE ? OR category LIKE ?';
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

// Base SQL query for counting total records
$count_sql = "SELECT COUNT(*) FROM products";
if (!empty($where)) {
    $count_sql .= ' WHERE ' . implode(' AND ', $where);
}

// Execute count query
$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute($params);
$total_records = $count_stmt->fetchColumn();
$total_pages = ceil($total_records / $records_per_page);

// Base SQL query for fetching paginated records
$sql = "SELECT id, product_name, description, category, price, stock FROM products";
if (!empty($where)) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}
$sql .= " ORDER BY product_name ASC LIMIT ? OFFSET ?"; // Order by product_name

// Add LIMIT and OFFSET for pagination
$params[] = $records_per_page;
$params[] = $offset;

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Function to build pagination links while preserving current filters
function buildPaginationLink($page, $search) {
    $query_params = ['page' => $page];
    if (!empty($search)) {
        $query_params['search'] = $search;
    }
    return 'index.php?' . http_build_query($query_params);
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container mx-auto p-6 bg-white rounded-lg shadow-md">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Product Inventory</h2>
        <a href="add.php" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 shadow">
            <i class="fas fa-plus mr-2"></i>Add New Product
        </a>
    </div>

    <form method="get" class="mb-4 flex flex-wrap items-center gap-4">
        <input type="text" name="search" placeholder="Search products..." value="<?= htmlspecialchars($search) ?>"
            class="px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring w-full md:w-1/3 lg:w-1/4">
        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md shadow hover:bg-blue-700">
            Filter
        </button>
    </form>

    <?php if (empty($products) && !empty($search)): ?>
        <div class="text-blue-700 bg-blue-100 p-4 rounded">No products match your search criteria.</div>
    <?php elseif (empty($products)): ?>
        <div class="text-blue-700 bg-blue-100 p-4 rounded">No products found.</div>
    <?php else: ?>
        <div class="overflow-x-auto border border-gray-200 rounded">
            <table class="min-w-full divide-y divide-gray-200 admin-table">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Product Name</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Category</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Price (UGX)</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Stock</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    <?php foreach ($products as $product): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?= htmlspecialchars($product['product_name']) ?><br>
                                <small class="text-gray-500"><?= htmlspecialchars($product['description']) ?></small>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($product['category']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">UGX <?= number_format($product['price']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($product['stock']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="status-badge <?= $product['stock'] <= 10 ? 'inactive' : 'active' ?>">
                                    <?= $product['stock'] <= 10 ? 'Low Stock' : 'In Stock' ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <div class="flex justify-center space-x-2">
                                    <a href="edit.php?id=<?= $product['id'] ?>" class="text-yellow-600 hover:text-yellow-800" title="Edit"><i class="fas fa-edit"></i></a>
                                    <a href="#" onclick="showCustomConfirm('Are you sure you want to delete <?= htmlspecialchars($product['product_name']) ?>?', function(c) { if(c) location.href='delete.php?id=<?= $product['id'] ?>'; });" class="text-red-600 hover:text-red-800" title="Delete"><i class="fas fa-trash-alt"></i></a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
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
                $prev_page_link = buildPaginationLink($current_page - 1, $search);
                $next_page_link = buildPaginationLink($current_page + 1, $search);
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
                        <a href="<?= buildPaginationLink($i, $search) ?>"
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
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
