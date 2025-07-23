<?php
// admin/settings.php - Main Admin Settings Page

// Include necessary configuration and utility files
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php'; // Assuming this contains calculateAgeFromDob if needed elsewhere

// Set the page title for the header
$pageTitle = "Admin Settings";

// Require login for this page
requireLogin();

// Determine which tab to display
$currentTab = isset($_GET['tab']) ? htmlspecialchars($_GET['tab']) : 'user-management';

// Include the header
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container mx-auto p-6 bg-white rounded-lg shadow-xl w-full max-w-4xl overflow-hidden">
    <!-- Header -->
    <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
        <h2 class="text-2xl font-semibold text-gray-800">Admin Settings</h2>
        <!-- Close button - consider where this links to in your actual application -->
        <button class="text-gray-500 hover:text-gray-700 focus:outline-none" onclick="window.location.href='<?php echo BASE_URL; ?>dashboard.php';">
            <i class="fas fa-times text-xl"></i>
        </button>
    </div>

    <!-- Tab Navigation -->
    <div class="flex border-b border-gray-200 bg-gray-50">
        <a href="?tab=user-management" id="tab-user-management" class="tab-button px-6 py-3 text-sm font-medium text-gray-600 hover:text-gray-900 hover:bg-gray-100 focus:outline-none <?php echo ($currentTab === 'user-management') ? 'active bg-blue-100 text-indigo-700 font-semibold' : ''; ?>">User Management</a>
        <a href="?tab=system-settings" id="tab-system-settings" class="tab-button px-6 py-3 text-sm font-medium text-gray-600 hover:text-gray-900 hover:bg-gray-100 focus:outline-none <?php echo ($currentTab === 'system-settings') ? 'active bg-blue-100 text-indigo-700 font-semibold' : ''; ?>">System Settings</a>
        <a href="?tab=security-settings" id="tab-security-settings" class="tab-button px-6 py-3 text-sm font-medium text-gray-600 hover:text-gray-900 hover:bg-gray-100 focus:outline-none <?php echo ($currentTab === 'security-settings') ? 'active bg-blue-100 text-indigo-700 font-semibold' : ''; ?>">Security</a>
    </div>

    <!-- Tab Content Area -->
    <div class="p-6">
        <?php
        // Dynamically include the content for the selected tab
        switch ($currentTab) {
            case 'user-management':
                require_once __DIR__ . '/settings/users.php';
                break;
            case 'system-settings':
                require_once __DIR__ . '/settings/system.php';
                break;
            case 'security-settings':
                require_once __DIR__ . '/settings/security.php';
                break;
            default:
                // Default to user management if an invalid tab is specified
                require_once __DIR__ . '/settings/users.php';
                break;
        }
        ?>
    </div>
</div>

<!-- Custom Confirmation Modal (Copied from list.php for consistency) -->
<div id="customConfirmModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modalTitle">Confirm Action</h3>
            <div class="mt-2 px-7 py-3">
                <p class="text-sm text-gray-500" id="modalMessage">Are you sure you want to proceed?</p>
            </div>
            <div class="items-center px-4 py-3">
                <button id="confirmBtn" class="px-4 py-2 bg-red-600 text-white text-base font-medium rounded-md w-full shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                    Confirm
                </button>
                <button id="cancelBtn" class="mt-3 px-4 py-2 bg-gray-300 text-gray-800 text-base font-medium rounded-md w-full shadow-sm hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    // Custom confirmation dialog logic (from list.php)
    let confirmCallback = null;

    function showCustomConfirm(message, callback) {
        document.getElementById('modalMessage').innerText = message;
        document.getElementById('customConfirmModal').classList.remove('hidden');
        confirmCallback = callback;
        return false; // Prevent default link action
    }

    document.getElementById('confirmBtn').addEventListener('click', function() {
        document.getElementById('customConfirmModal').classList.add('hidden');
        if (confirmCallback) {
            confirmCallback(true);
        }
    });

    document.getElementById('cancelBtn').addEventListener('click', function() {
        document.getElementById('customConfirmModal').classList.add('hidden');
        if (confirmCallback) {
            confirmCallback(false);
        }
    });
</script>

<?php
// Include the footer
require_once __DIR__ . '/../includes/footer.php';
?>
