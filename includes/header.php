<?php
// includes/header.php
// This file should be included at the beginning of every page that requires a consistent header.

// Ensure config.php is loaded for SITE_NAME and BASE_URL
if (!defined('SITE_NAME')) {
    require_once __DIR__ . '/../config.php';
}
// Ensure authentication functions are available
if (!function_exists('isLoggedIn')) {
    require_once __DIR__ . '/auth.php';
}

// Ensure the user is logged in for most pages that include the header
// This can be commented out or modified if some pages (e.g., public landing pages) don't require login
requireLogin();

// Get current user's role for conditional navigation display
$currentUserRole = $_SESSION['role'] ?? 'Guest';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - <?php echo $pageTitle ?? 'Dashboard'; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Custom CSS files -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>../assets/css/style.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>../assets/css/dashboard.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>../assets/css/forms.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>../assets/css/admin.css">
</head>
<body class="bg-gray-100 font-inter">
    <div class="flex h-screen">
        <!-- Sidebar Navigation -->
        <aside class="w-64 bg-green-700 text-white p-6 space-y-6 flex flex-col rounded-r-xl shadow-lg">
            <div class="text-2xl font-bold text-center mb-8">
                <?php echo SITE_NAME; ?>
            </div>
            <nav class="flex-1">
                <ul class="space-y-3">
                    <li>
                        <a href="<?php echo BASE_URL; ?>../admin/index.php" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-green-600 transition-colors duration-200">
                            <i class="fas fa-tachometer-alt text-xl"></i>
                            <span class="text-lg">Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo BASE_URL; ?>../patients/list.php" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-green-600 transition-colors duration-200">
                            <i class="fas fa-users text-xl"></i>
                            <span class="text-lg">Patients</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo BASE_URL; ?>../appointments/index.php" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-green-600 transition-colors duration-200">
                            <i class="fas fa-calendar-alt text-xl"></i>
                            <span class="text-lg">Appointments</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo BASE_URL; ?>../products/index.php" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-green-600 transition-colors duration-200">
                            <i class="fas fa-box-open text-xl"></i>
                            <span class="text-lg">Products</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo BASE_URL; ?>../sale/new.php" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-green-600 transition-colors duration-200">
                            <i class="fas fa-cash-register text-xl"></i>
                            <span class="text-lg">Sales</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo BASE_URL; ?>../admin/reports/report.php" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-green-600 transition-colors duration-200">
                            <i class="fas fa-chart-line text-xl"></i>
                            <span class="text-lg">Analytics</span>
                        </a>
                    </li>
                    <?php if ($currentUserRole === 'Admin'): // Admin specific link ?>
                    <li>
                        <a href="<?php echo BASE_URL; ?>../../admin/settings/users.php" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-green-600 transition-colors duration-200">
                            <i class="fas fa-cog text-xl"></i>
                            <span class="text-lg">Admin Settings</span>
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
            </nav>
            <div class="mt-auto">
                <a href="<?php echo BASE_URL; ?>logout.php" class="flex items-center space-x-3 p-3 rounded-lg bg-green-600 hover:bg-green-500 transition-colors duration-200">
                    <i class="fas fa-sign-out-alt text-xl"></i>
                    <span class="text-lg">Logout</span>
                </a>
            </div>
        </aside>

        <!-- Main Content Area -->
        <main class="flex-1 p-8 overflow-y-auto">
            <!-- Top Bar (User Info) -->
            <header class="bg-white p-4 rounded-lg shadow-md mb-8 flex justify-between items-center">
                <h1 class="text-2xl font-semibold text-gray-800"><?php echo $pageTitle ?? 'Dashboard'; ?></h1>
                <div class="flex items-center space-x-4">
                    <span class="text-gray-700 font-medium"><?php echo htmlspecialchars($_SESSION['full_name'] ?? 'Guest'); ?> (<?php echo htmlspecialchars($_SESSION['role'] ?? 'Role'); ?>)</span>
                    <i class="fas fa-user-circle text-3xl text-green-700"></i>
                </div>
            </header>
            <!-- Page content will be inserted here -->
