<?php
// admin/settings/system.php - System Settings content for Admin Settings

// This file is intended to be included by admin/settings.php
// It assumes BASE_URL, SITE_NAME, DEFAULT_CURRENCY, TAX_RATE_PERCENT are already defined by the including script.

// In a real application, these values would be fetched from a 'system_settings' table
// For now, they are defined in config.php (or could be fetched from DB here)

// Dummy values for demonstration if not defined by config.php
if (!defined('SITE_NAME')) define('SITE_NAME', 'SmartFoods Inc.');
if (!defined('DEFAULT_CURRENCY')) define('DEFAULT_CURRENCY', 'USD');
if (!defined('TAX_RATE_PERCENT')) define('TAX_RATE_PERCENT', 7.50);

?>

<h3 class="text-xl font-semibold text-gray-800 mb-4">System Settings</h3>
<form action="<?php echo BASE_URL; ?>admin/process_settings.php" method="POST" class="space-y-6">
    <div class="form-group">
        <label for="site_name" class="block text-gray-700 font-semibold mb-2">Site Name</label>
        <input type="text" id="site_name" name="site_name" value="<?php echo htmlspecialchars(SITE_NAME); ?>" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
    </div>
    <div class="form-group">
        <label for="default_currency" class="block text-gray-700 font-semibold mb-2">Default Currency</label>
        <input type="text" id="default_currency" name="default_currency" value="<?php echo htmlspecialchars(DEFAULT_CURRENCY); ?>" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
    </div>
    <div class="form-group">
        <label for="tax_rate" class="block text-gray-700 font-semibold mb-2">Tax Rate (%)</label>
        <input type="number" step="0.01" id="tax_rate" name="tax_rate" value="<?php echo htmlspecialchars(TAX_RATE_PERCENT); ?>" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
    </div>
    <div class="flex justify-end">
        <button type="submit" class="px-6 py-2 rounded-lg bg-green-600 text-white font-semibold hover:bg-green-700 transition-colors duration-200">Save System Settings</button>
    </div>
</form>
