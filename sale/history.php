<?php
include('../includes/db_connect.php');
include('../includes/header.php'); // Include header for consistent styling and auth

$pageTitle = 'Sales History'; // Set page title

// Run query using PDO
// Updated to select customer_type and customer_name for display
// The join is still on the patients table since that is where the client info is stored
$stmt = $pdo->query("
    SELECT s.id, s.customer_type, p.full_name AS client_name, s.total_amount, s.sale_date
    FROM sales s
    LEFT JOIN patients p ON s.clients_id = p.patient_id
    ORDER BY s.sale_date DESC
");
?>

<div class="container mx-auto p-4 md:p-8">
    <div class="bg-white rounded-lg shadow-lg p-6 md:p-8">
        <h2 class="text-2xl font-semibold text-gray-800 mb-6">Sales History</h2>

        <div class="overflow-x-auto">
            <table class="min-w-full bg-white rounded-lg overflow-hidden">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="py-3 px-4 text-left text-sm font-semibold text-gray-600">Sale ID</th>
                        <th class="py-3 px-4 text-left text-sm font-semibold text-gray-600">Customer Type</th>
                        <th class="py-3 px-4 text-left text-sm font-semibold text-gray-600">Client Name</th>
                        <th class="py-3 px-4 text-left text-sm font-semibold text-gray-600">Total Amount</th>
                        <th class="py-3 px-4 text-left text-sm font-semibold text-gray-600">Date</th>
                        <th class="py-3 px-4 text-left text-sm font-semibold text-gray-600">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="py-3 px-4 text-gray-800"><?= htmlspecialchars($row['id']) ?></td>
                        <td class="py-3 px-4 text-gray-800">Client</td>
                        <td class="py-3 px-4 text-gray-800">
                            <?= htmlspecialchars($row['client_name'] ?? 'N/A') ?>
                        </td>
                        <td class="py-3 px-4 text-gray-800"><?= DEFAULT_CURRENCY . ' ' . number_format($row['total_amount'], 2) ?></td>
                        <td class="py-3 px-4 text-gray-800"><?= htmlspecialchars($row['sale_date']) ?></td>
                        <td class="py-3 px-4">
                            <a href="receipt.php?sale_id=<?= urlencode($row['id']) ?>" class="text-blue-600 hover:text-blue-800 font-medium">View</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    <?php if ($stmt->rowCount() === 0): ?>
                        <tr>
                            <td colspan="6" class="text-center py-4 text-gray-500">No sales records found.</td>
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
