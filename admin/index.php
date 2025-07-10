<?php
// admin/index.php
require_once __DIR__ . '/../config.php'; // Includes session_start()
require_once __DIR__ . '/../includes/db_connect.php'; // Establishes $pdo connection
require_once __DIR__ . '/../includes/auth.php'; // Includes authentication functions

// Set the page title for the header
$pageTitle = "Admin Dashboard";

// Require login for this page
requireLogin();

// Optional: Check for specific roles if this dashboard is only for admins
// if (!hasRole('Admin')) {
//     header('Location: ' . BASE_URL . 'index.php?message=Access denied. You do not have permission to view this page.');
//     exit();
// }

// User is logged in and authorized
$userName = $_SESSION['full_name'] ?? 'User';
$userRole = $_SESSION['role'] ?? 'Unknown Role';

// Include the header (which contains the opening <html>, <head>, <body> and navigation)
require_once __DIR__ . '/../includes/header.php';
?>

<!-- Main content for the Admin Dashboard page -->
<div class="bg-white p-6 rounded-lg shadow-md">
    <h2 class="text-2xl font-bold text-gray-800 mb-4">Dashboard Overview</h2>
    <p class="text-lg mb-2">Welcome, <span class="font-semibold text-blue-600"><?php echo htmlspecialchars($userName); ?></span>!</p>
    <p class="text-md text-gray-600">Your role: <span class="font-medium text-purple-600"><?php echo htmlspecialchars($userRole); ?></span></p>

    <p class="mt-6 text-gray-700">This is your main administrative dashboard. Here you will find summaries and quick links to manage the system.</p>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mt-8">
        <!-- Example Dashboard Cards -->
        <div class="bg-blue-50 p-6 rounded-lg shadow-sm border border-blue-200">
            <h3 class="text-xl font-semibold text-blue-800 mb-2">Total Patients</h3>
            <p class="text-3xl font-bold text-blue-600">120</p>
            <p class="text-sm text-gray-500 mt-2">View all registered patients.</p>
            <a href="<?php echo BASE_URL; ?>patients/list.php" class="text-blue-500 hover:underline text-sm mt-3 block">Go to Patients <i class="fas fa-arrow-right text-xs ml-1"></i></a>
        </div>

        <div class="bg-green-50 p-6 rounded-lg shadow-sm border border-green-200">
            <h3 class="text-xl font-semibold text-green-800 mb-2">Upcoming Appointments</h3>
            <p class="text-3xl font-bold text-green-600">5</p>
            <p class="text-sm text-gray-500 mt-2">Appointments in the next 7 days.</p>
            <a href="<?php echo BASE_URL; ?>appointments/upcoming.php" class="text-green-500 hover:underline text-sm mt-3 block">View Appointments <i class="fas fa-arrow-right text-xs ml-1"></i></a>
        </div>

        <div class="bg-yellow-50 p-6 rounded-lg shadow-sm border border-yellow-200">
            <h3 class="text-xl font-semibold text-yellow-800 mb-2">Products in Stock</h3>
            <p class="text-3xl font-bold text-yellow-600">85</p>
            <p class="text-sm text-gray-500 mt-2">Total quantity across all products.</p>
            <a href="<?php echo BASE_URL; ?>products/inventory.php" class="text-yellow-500 hover:underline text-sm mt-3 block">Manage Inventory <i class="fas fa-arrow-right text-xs ml-1"></i></a>
        </div>
    </div>

    <a href="<?php echo BASE_URL; ?>logout.php" class="btn-logout">Logout</a>
</div>

<?php
// Include the footer (which contains the closing </main>, </div>, </body>, </html>)
require_once __DIR__ . '/../includes/footer.php';
?>
