<?php
// admin/reports/report.php - Combined Patient and Sales Reports

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/db_connect.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php'; // For calculateAgeFromDob

$pageTitle = "Reports Overview";
requireLogin();

// --- Fetch Patient Data ---
$patients = [];
try {
    $stmt = $pdo->query("SELECT patient_id, patient_unique_id, full_name, date_of_birth, email, phone, gender, membership_status FROM patients ORDER BY full_name ASC");
    $patients = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching patients for report: " . $e->getMessage());
    $_SESSION['error_message'] = "Error loading patient report data. Please try again later.";
}

// --- Fetch Sales Data ---
$sales = [];
try {
    $sql_sales = "SELECT s.id, s.sale_date, s.total_amount, s.discount_percent, c.name AS client_name
                  FROM sales s
                  LEFT JOIN clients c ON s.clients_id = c.id
                  ORDER BY s.sale_date DESC";
    $stmt_sales = $pdo->query($sql_sales);
    $sales = $stmt_sales->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching sales for report: " . $e->getMessage());
    $_SESSION['error_message'] = "Error loading sales report data. Please try again later.";
}

include_once __DIR__ . '/../../includes/header.php';
?>

<div class="container mx-auto p-6 bg-white rounded-lg shadow-md">
    <h2 class="text-2xl font-bold text-gray-800 mb-6 border-b-2 border-green-600 pb-2">Reports Overview</h2>

    <?php
    // Display any error messages
    if (isset($_SESSION['error_message'])) {
        echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">' . htmlspecialchars($_SESSION['error_message']) . '</div>';
        unset($_SESSION['error_message']);
    }
    ?>

    <!-- Patient Report Section -->
    <div class="mb-10">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-semibold text-gray-800">Patient Report</h3>
            <a href="<?php echo BASE_URL; ?>export_patients.php" class="inline-flex items-center space-x-2 px-4 py-2 rounded-lg bg-blue-600 text-white font-semibold hover:bg-blue-700 transition-colors duration-200 shadow-md">
                <i class="fas fa-download"></i>
                <span>Download Patient Report</span>
            </a>
        </div>

        <?php if (empty($patients)): ?>
            <div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 rounded-md" role="alert">
                <p class="font-bold">No Patients Found</p>
                <p>There are no patient records to display in this report.</p>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto bg-white rounded-lg shadow-sm border border-gray-200">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Patient ID</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Full Name</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Gender</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Age</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Membership</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($patients as $patient): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo htmlspecialchars($patient['patient_unique_id']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700"><?php echo htmlspecialchars($patient['full_name']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700"><?php echo htmlspecialchars($patient['email'] ?? 'N/A'); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700"><?php echo htmlspecialchars($patient['phone'] ?? 'N/A'); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700"><?php echo htmlspecialchars($patient['gender'] ?? 'N/A'); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700"><?php echo htmlspecialchars(calculateAgeFromDob($patient['date_of_birth']) ?? 'N/A'); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                        <?php
                                            if ($patient['membership_status'] === 'Premium') echo 'bg-green-100 text-green-800';
                                            else if ($patient['membership_status'] === 'Standard') echo 'bg-blue-100 text-blue-800';
                                            else echo 'bg-gray-100 text-gray-800';
                                        ?>">
                                        <?php echo htmlspecialchars($patient['membership_status']); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- Sales Report Section -->
    <div>
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-semibold text-gray-800">Sales Report</h3>
            <a href="<?php echo BASE_URL; ?>export_sales.php" class="inline-flex items-center space-x-2 px-4 py-2 rounded-lg bg-green-600 text-white font-semibold hover:bg-green-700 transition-colors duration-200 shadow-md">
                <i class="fas fa-download"></i>
                <span>Download Sales Report</span>
            </a>
        </div>

        <?php if (empty($sales)): ?>
            <div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 rounded-md" role="alert">
                <p class="font-bold">No Sales Found</p>
                <p>There are no sales records to display in this report.</p>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto bg-white rounded-lg shadow-sm border border-gray-200">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sale ID</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sale Date</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client Name</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Amount (<?php echo DEFAULT_CURRENCY; ?>)</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Discount (%)</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($sales as $sale): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo htmlspecialchars($sale['id']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700"><?php echo htmlspecialchars(date('Y-m-d H:i', strtotime($sale['sale_date']))); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700"><?php echo htmlspecialchars($sale['client_name'] ?? 'N/A'); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700"><?php echo htmlspecialchars(number_format($sale['total_amount'], 2)); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700"><?php echo htmlspecialchars($sale['discount_percent'] ?? '0.00'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include_once __DIR__ . '/../../includes/footer.php'; ?>
