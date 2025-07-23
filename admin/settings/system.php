<?php
// admin/settings/system.php - System Settings content for Admin Settings

// This file is intended to be included by admin/settings.php
// It assumes $pdo and BASE_URL are already defined by the including script.

// Initialize variables with default values in case database fetch fails
$site_name = 'Smart Food';
$default_currency = 'UGX';
$tax_rate_percent = 8.00;

// Fetch settings from the 'system_settings' table
try {
    // Ensure $pdo is available from the including script (admin/settings.php)
    if (isset($pdo)) {
        $stmt = $pdo->query("SELECT setting_name, setting_value FROM system_settings");
        $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR); // Fetches as an associative array [setting_name => setting_value]

        if (isset($settings['site_name'])) {
            $site_name = htmlspecialchars($settings['site_name']);
        }
        if (isset($settings['default_currency'])) {
            $default_currency = htmlspecialchars($settings['default_currency']);
        }
        if (isset($settings['tax_rate_percent'])) {
            $tax_rate_percent = (float)$settings['tax_rate_percent']; // Cast to float for number input
        }
    } else {
        error_log("PDO object not available in admin/settings/system.php");
        // Optionally set a user-friendly error message
        // $_SESSION['error_message'] = "Database connection error. System settings could not be loaded.";
    }
} catch (PDOException $e) {
    error_log("Error fetching system settings: " . $e->getMessage());
    // Optionally set a user-friendly error message
    // $_SESSION['error_message'] = "Error loading system settings. Please try again later.";
}

?>

<h3 class="text-xl font-semibold text-gray-800 mb-4">System Settings</h3>
<form action="<?php echo BASE_URL; ?>admin/process_settings.php" method="POST" class="space-y-6">
    <div class="form-group">
        <label for="site_name" class="block text-gray-700 font-semibold mb-2">Site Name</label>
        <input type="text" id="site_name" name="site_name" value="<?php echo $site_name; ?>" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
    </div>
    <div class="form-group">
        <label for="default_currency" class="block text-gray-700 font-semibold mb-2">Default Currency</label>
        <input type="text" id="default_currency" name="default_currency" value="<?php echo $default_currency; ?>" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
    </div>
    <div class="form-group">
        <label for="tax_rate" class="block text-gray-700 font-semibold mb-2">Tax Rate (%)</label>
        <input type="number" step="0.01" id="tax_rate" name="tax_rate" value="<?php echo $tax_rate_percent; ?>" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
    </div>
    <div class="flex justify-end">
        <button type="submit" class="px-6 py-2 rounded-lg bg-green-600 text-white font-semibold hover:bg-green-700 transition-colors duration-200">Save System Settings</button>
    </div>
</form>
