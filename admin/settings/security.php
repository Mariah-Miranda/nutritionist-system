<?php
// settings/security.php - Security Settings content for Admin Settings

// This file is intended to be included by settings.php
// It assumes BASE_URL is already defined by the including script.
// In a real application, these values would be fetched from a 'system_settings' or 'security_settings' table
?>

<h3 class="text-xl font-semibold text-gray-800 mb-4">Security Settings</h3>
<form action="<?php echo BASE_URL; ?>process_security_settings.php" method="POST" class="space-y-6">
    <!-- Password Policy -->
    <div class="bg-gray-50 p-6 rounded-lg shadow-sm border border-gray-200">
        <h4 class="text-lg font-semibold text-gray-800 mb-4">Password Policy</h4>
        <div class="mb-4">
            <label for="min_password_length" class="block text-gray-700 mb-2">Minimum password length</label>
            <select id="min_password_length" name="min_password_length" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                <?php foreach ([8, 10, 12, 14, 16] as $len): ?>
                    <option value="<?php echo $len; ?>" <?php echo (10 == $len) ? 'selected' : ''; ?>><?php echo $len; ?> characters</option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-4 flex items-center">
            <input type="checkbox" id="require_uppercase" name="require_uppercase" class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" checked>
            <label for="require_uppercase" class="ml-2 text-gray-700">Require uppercase letters</label>
        </div>
        <div class="mb-4 flex items-center">
            <input type="checkbox" id="require_numbers" name="require_numbers" class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" checked>
            <label for="require_numbers" class="ml-2 text-gray-700">Require numbers</label>
        </div>
        <div class="mb-4 flex items-center">
            <input type="checkbox" id="require_special_chars" name="require_special_chars" class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" checked>
            <label for="require_special_chars" class="ml-2 text-gray-700">Require special characters</label>
        </div>
        <div class="mb-4">
            <label for="password_expiry_period" class="block text-gray-700 mb-2">Password expiry period</label>
            <select id="password_expiry_period" name="password_expiry_period" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                <?php foreach ([30, 60, 90, 180, 365] as $days): ?>
                    <option value="<?php echo $days; ?>" <?php echo (90 == $days) ? 'selected' : ''; ?>><?php echo $days; ?> days</option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <!-- Account Security -->
    <div class="bg-gray-50 p-6 rounded-lg shadow-sm border border-gray-200">
        <h4 class="text-lg font-semibold text-gray-800 mb-4">Account Security</h4>
        <div class="mb-4 flex items-center">
            <input type="checkbox" id="two_factor_auth" name="two_factor_auth" class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
            <label for="two_factor_auth" class="ml-2 text-gray-700">Two-factor authentication</label>
        </div>
        <div class="mb-4">
            <label for="session_timeout" class="block text-gray-700 mb-2">Session timeout (minutes)</label>
            <select id="session_timeout" name="session_timeout" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                <?php foreach ([15, 30, 60, 120] as $minutes): ?>
                    <option value="<?php echo $minutes; ?>" <?php echo (30 == $minutes) ? 'selected' : ''; ?>><?php echo $minutes; ?> minutes</option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-4">
            <label for="failed_login_attempts" class="block text-gray-700 mb-2">Failed login attempts before lockout</label>
            <select id="failed_login_attempts" name="failed_login_attempts" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                <?php foreach ([3, 5, 10] as $attempts): ?>
                    <option value="<?php echo $attempts; ?>" <?php echo (5 == $attempts) ? 'selected' : ''; ?>><?php echo $attempts; ?> attempts</option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    <div class="flex justify-end">
        <button type="submit" class="px-6 py-2 rounded-lg bg-green-600 text-white font-semibold hover:bg-green-700 transition-colors duration-200">Save Security Settings</button>
    </div>
</form>
