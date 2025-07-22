<?php
// index.php - Main Dashboard

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db_connect.php'; // This provides $pdo
require_once __DIR__ . '/../includes/auth.php';

// Include header
$pageTitle = "Dashboard";
include_once __DIR__ . '/../includes/header.php';

requireLogin(); // Ensure user is logged in

// Fetch counts for dashboard summary using PDO
$totalPatients = 0;
$totalAppointments = 0;
$totalProducts = 0;

try {
    // Get total patients
    $sqlPatients = "SELECT COUNT(*) AS total FROM patients";
    $stmtPatients = $pdo->query($sqlPatients);
    $totalPatients = $stmtPatients->fetchColumn();

    // Get total appointments (scheduled or completed)
    $sqlAppointments = "SELECT COUNT(*) AS total FROM appointments WHERE status IN ('Scheduled', 'Completed')";
    $stmtAppointments = $pdo->query($sqlAppointments);
    $totalAppointments = $stmtAppointments->fetchColumn();

    // Get total products
    $sqlProducts = "SELECT COUNT(*) AS total FROM products";
    $stmtProducts = $pdo->query($sqlProducts);
    $totalProducts = $stmtProducts->fetchColumn();

} catch (PDOException $e) {
    error_log("ERROR: Could not fetch dashboard counts in index.php: " . $e->getMessage());
    echo "<p class='text-red-500'>Error fetching dashboard data. Please try again.</p>";
}
?>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <!-- Card 1: Total Patients -->
    <div class="bg-white rounded-lg shadow-md p-6 flex flex-col items-center justify-center">
        <div class="text-5xl font-bold text-blue-600 mb-2"><?php echo $totalPatients; ?></div>
        <div class="text-lg text-gray-600">Total Patients</div>
        <a href="../patients/list.php" class="mt-4 text-blue-500 hover:underline">View All Patients</a>
    </div>

    <!-- Card 2: Total Appointments -->
    <div class="bg-white rounded-lg shadow-md p-6 flex flex-col items-center justify-center">
        <div class="text-5xl font-bold text-green-600 mb-2"><?php echo $totalAppointments; ?></div>
        <div class="text-lg text-gray-600">Total Appointments</div>
        <a href="../appointments/index.php" class="mt-4 text-green-500 hover:underline">View Appointments</a>
    </div>

    <!-- Card 3: Total Products -->
    <div class="bg-white rounded-lg shadow-md p-6 flex flex-col items-center justify-center">
        <div class="text-5xl font-bold text-purple-600 mb-2"><?php echo $totalProducts; ?></div>
        <div class="text-lg text-gray-600">Total Products</div>
        <a href="../products/index.php" class="mt-4 text-purple-500 hover:underline">View All Products</a>
    </div>
</div>

<!-- Quick Links / Recent Activity (Placeholder) -->
<div class="bg-white rounded-lg shadow-md p-6">
    <h3 class="text-xl font-semibold text-gray-800 mb-4">Quick Actions</h3>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <a href="../patients/add.php" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-3 px-4 rounded-lg text-center transition duration-200">Add New Patient</a>
        <a href="../appointments/schedule.php" class="bg-green-500 hover:bg-green-600 text-white font-bold py-3 px-4 rounded-lg text-center transition duration-200">Schedule Appointment</a>
        <a href="../products/add.php" class="bg-purple-500 hover:bg-purple-600 text-white font-bold py-3 px-4 rounded-lg text-center transition duration-200">Add New Product</a>
    </div>
</div>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>
