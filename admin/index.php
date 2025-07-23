<?php
// admin/index.php
require_once __DIR__ . '/../config.php'; // Includes session_start()
require_once __DIR__ . '/../includes/db_connect.php'; // Establishes $pdo connection
require_once __DIR__ . '/../includes/auth.php'; // Includes authentication functions

// Set the page title for the header
$pageTitle = "Admin Dashboard";

// Require login for this page
requireLogin();

// User is logged in and authorized
$userName = $_SESSION['full_name'] ?? 'User';
$userRole = $_SESSION['role'] ?? 'Unknown Role';

// --- Fetch Dashboard Data ---

// 1. Total Patients
$totalPatients = 0;
try {
    $stmt = $pdo->query("SELECT COUNT(*) AS total FROM patients");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $totalPatients = $result['total'];
} catch (PDOException $e) {
    error_log("Error fetching total patients: " . $e->getMessage());
    // Fallback or display an error on the dashboard
}

// 2. Upcoming Appointments (e.g., in the next 7 days)
$upcomingAppointments = 0;
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) AS total FROM appointments WHERE appointment_date >= CURDATE() AND appointment_date <= DATE_ADD(CURDATE(), INTERVAL 7 DAY) AND status = 'Scheduled'");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $upcomingAppointments = $result['total'];
} catch (PDOException $e) {
    error_log("Error fetching upcoming appointments: " . $e->getMessage());
}

// 3. Products in Stock (Total quantity)
$productsInStock = 0;
try {
    $stmt = $pdo->query("SELECT SUM(stock) AS total_stock FROM products");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $productsInStock = $result['total_stock'] ?? 0; // Use null coalescing to handle NULL if no products
} catch (PDOException $e) {
    error_log("Error fetching products in stock: " . $e->getMessage());
}

// 4. Total Sales (e.g., sum of total_amount from the sales table for all time)
$totalSalesAmount = 0;
try {
    $stmt = $pdo->query("SELECT SUM(total_amount) AS total_sales FROM sales");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $totalSalesAmount = $result['total_sales'] ?? 0;
} catch (PDOException $e) {
    error_log("Error fetching total sales: " . $e->getMessage());
}

// --- Fetch Sales Analytics Data ---

// Sales for Current Month
$currentMonthSales = 0;
$currentMonthSalesCount = 0;
try {
    $stmt = $pdo->prepare("SELECT SUM(total_amount) AS monthly_sales, COUNT(*) AS monthly_sales_count FROM sales WHERE DATE_FORMAT(sale_date, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m')");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $currentMonthSales = $result['monthly_sales'] ?? 0;
    $currentMonthSalesCount = $result['monthly_sales_count'] ?? 0;
} catch (PDOException $e) {
    error_log("Error fetching current month sales: " . $e->getMessage());
}

// Include the header (which contains the opening <html>, <head>, <body> and navigation)
require_once __DIR__ . '/../includes/header.php';
?>

<!-- Main content for the Admin Dashboard page -->
<div class="bg-white p-8 rounded-xl shadow-lg border border-gray-200">
    <h2 class="text-3xl font-extrabold text-gray-900 mb-6 border-b-2 border-green-600 pb-2">Dashboard Overview</h2>
    <p class="text-lg mb-4 text-gray-700">Welcome, <span class="font-semibold text-green-800"><?php echo htmlspecialchars($userName); ?></span>!</p>
    <p class="text-md text-gray-600 mb-8">Your role: <span class="font-medium text-purple-800"><?php echo htmlspecialchars($userRole); ?></span></p>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mt-8">
        <!-- Dashboard Card: Total Patients -->
        <div class="bg-blue-100 text-blue-800 p-6 rounded-xl shadow-lg transform hover:scale-105 transition-transform duration-300 ease-in-out cursor-pointer">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-semibold">Total Patients</h3>
                <i class="fas fa-users text-3xl text-blue-700 opacity-85"></i>
            </div>
            <p class="text-4xl font-bold"><?php echo htmlspecialchars($totalPatients); ?></p>
            <p class="text-sm opacity-90 mt-2 text-blue-700">All registered patients in the system.</p>
            <a href="<?php echo BASE_URL; ?>../patients/list.php" class="text-blue-600 hover:text-blue-800 text-sm mt-4 block underline">
                Go to Patients <i class="fas fa-arrow-right text-xs ml-1"></i>
            </a>
        </div>

        <!-- Dashboard Card: Upcoming Appointments -->
        <div class="bg-emerald-100 text-emerald-800 p-6 rounded-xl shadow-lg transform hover:scale-105 transition-transform duration-300 ease-in-out cursor-pointer">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-semibold">Upcoming Appointments</h3>
                <i class="fas fa-calendar-check text-3xl text-emerald-700 opacity-85"></i>
            </div>
            <p class="text-4xl font-bold"><?php echo htmlspecialchars($upcomingAppointments); ?></p>
            <p class="text-sm opacity-90 mt-2 text-emerald-700">Appointments scheduled for the next 7 days.</p>
            <a href="<?php echo BASE_URL; ?>../appointments/index.php" class="text-emerald-600 hover:text-emerald-800 text-sm mt-4 block underline">
                View Appointments <i class="fas fa-arrow-right text-xs ml-1"></i>
            </a>
        </div>

        <!-- Dashboard Card: Products in Stock -->
        <div class="bg-amber-100 text-amber-800 p-6 rounded-xl shadow-lg transform hover:scale-105 transition-transform duration-300 ease-in-out cursor-pointer">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-semibold">Products in Stock</h3>
                <i class="fas fa-boxes text-3xl text-amber-700 opacity-85"></i>
            </div>
            <p class="text-4xl font-bold"><?php echo htmlspecialchars($productsInStock); ?></p>
            <p class="text-sm opacity-90 mt-2 text-amber-700">Total quantity of all products available.</p>
            <a href="<?php echo BASE_URL; ?>../products/inventory.php" class="text-amber-600 hover:text-amber-800 text-sm mt-4 block underline">
                Manage Inventory <i class="fas fa-arrow-right text-xs ml-1"></i>
            </a>
        </div>

        <!-- Dashboard Card: Total Sales Amount (All Time) -->
        <div class="bg-rose-100 text-rose-800 p-6 rounded-xl shadow-lg transform hover:scale-105 transition-transform duration-300 ease-in-out cursor-pointer">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-semibold">Total Sales (All Time)</h3>
                <i class="fas fa-dollar-sign text-3xl text-rose-700 opacity-85"></i>
            </div>
            <p class="text-4xl font-bold"><?php echo htmlspecialchars(DEFAULT_CURRENCY . ' ' . number_format($totalSalesAmount, 2)); ?></p>
            <p class="text-sm opacity-90 mt-2 text-rose-700">Cumulative revenue generated from sales.</p>
            <a href="<?php echo BASE_URL; ?>admin/reports/sales_report.php" class="text-rose-600 hover:text-rose-800 text-sm mt-4 block underline">
                View Sales Report <i class="fas fa-arrow-right text-xs ml-1"></i>
            </a>
        </div>
    </div>

    <!-- Sales Analytics Section -->
    <div class="mt-12 p-8 bg-gray-50 rounded-xl shadow-inner border border-gray-200">
        <h3 class="text-2xl font-extrabold text-gray-900 mb-6 border-b-2 border-green-600 pb-2">Sales Analytics</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white p-6 rounded-lg shadow-md border border-gray-200">
                <div class="flex items-center justify-between mb-3">
                    <h4 class="text-lg font-semibold text-gray-800">Sales This Month</h4>
                    <i class="fas fa-chart-line text-2xl text-green-600"></i>
                </div>
                <p class="text-3xl font-bold text-green-700"><?php echo htmlspecialchars(DEFAULT_CURRENCY . ' ' . number_format($currentMonthSales, 2)); ?></p>
                <p class="text-sm text-gray-600 mt-2">Total revenue for the current month.</p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-md border border-gray-200">
                <div class="flex items-center justify-between mb-3">
                    <h4 class="text-lg font-semibold text-gray-800">Transactions This Month</h4>
                    <i class="fas fa-receipt text-2xl text-green-600"></i>
                </div>
                <p class="text-3xl font-bold text-green-700"><?php echo htmlspecialchars($currentMonthSalesCount); ?></p>
                <p class="text-sm text-gray-600 mt-2">Number of sales transactions this month.</p>
            </div>
            <!-- You can add more analytics cards here, e.g., Top Selling Products, Sales by Category etc. -->
        </div>
    </div>

    <!-- Quick Actions Section -->
    <div class="mt-12 p-6 bg-gray-50 rounded-lg border border-gray-200">
        <h3 class="text-2xl font-semibold text-gray-800 mb-4">Quick Actions</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <a href="<?php echo BASE_URL; ?>../patients/add.php" class="flex items-center justify-center space-x-2 p-4 bg-gray-100 text-gray-700 rounded-lg shadow hover:bg-gray-200 transition-colors duration-200">
                <i class="fas fa-user-plus text-xl text-green-600"></i>
                <span class="font-medium">Add New Patient</span>
            </a>
            <a href="<?php echo BASE_URL; ?>../appointments/schedule.php" class="flex items-center justify-center space-x-2 p-4 bg-gray-100 text-gray-700 rounded-lg shadow hover:bg-gray-200 transition-colors duration-200">
                <i class="fas fa-calendar-plus text-xl text-green-600"></i>
                <span class="font-medium">Schedule Appointment</span>
            </a>
            <a href="<?php echo BASE_URL; ?>../sale/new.php" class="flex items-center justify-center space-x-2 p-4 bg-gray-100 text-gray-700 rounded-lg shadow hover:bg-gray-200 transition-colors duration-200">
                <i class="fas fa-cash-register text-xl text-green-600"></i>
                <span class="font-medium">Process New Sale</span>
            </a>
        </div>
    </div>
</div>

<?php
// Include the footer (which contains the closing </main>, </div>, </body>, </html>)
require_once __DIR__ . '/../includes/footer.php';
?>
