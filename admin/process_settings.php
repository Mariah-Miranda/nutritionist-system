<?php
// process_settings.php - Handles saving System Settings

// Include necessary configuration and utility files
require_once __DIR__ . '/../config.php'; // For BASE_URL, DB_HOST, DB_NAME, DB_USER, DB_PASS
require_once __DIR__ . '/../includes/db_connect.php'; // For $pdo connection
require_once __DIR__ . '/../includes/auth.php'; // For requireLogin() and hasRole()

// Require admin role for this page
requireLogin();
if (!hasRole('Admin')) {
    $_SESSION['error_message'] = "You do not have permission to access this page.";
    header("Location: " . BASE_URL . "index.php"); // Redirect to a suitable page
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate input
    $site_name = filter_input(INPUT_POST, 'site_name', FILTER_SANITIZE_STRING);
    $default_currency = filter_input(INPUT_POST, 'default_currency', FILTER_SANITIZE_STRING);
    $tax_rate_percent = filter_input(INPUT_POST, 'tax_rate', FILTER_VALIDATE_FLOAT);

    // Basic validation
    if (empty($site_name) || empty($default_currency) || $tax_rate_percent === false || $tax_rate_percent < 0) {
        $_SESSION['error_message'] = "Invalid input for system settings. Please check your entries.";
        header("Location: " . BASE_URL . "settings.php?tab=system-settings");
        exit();
    }

    try {
        // Prepare SQL to update settings. Use INSERT ... ON DUPLICATE KEY UPDATE
        // This handles both inserting new settings if they don't exist and updating existing ones.
        $stmt = $pdo->prepare("
            INSERT INTO system_settings (setting_name, setting_value)
            VALUES (:setting_name, :setting_value)
            ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
        ");

        // Update Site Name
        $stmt->execute([':setting_name' => 'site_name', ':setting_value' => $site_name]);

        // Update Default Currency
        $stmt->execute([':setting_name' => 'default_currency', ':setting_value' => $default_currency]);

        // Update Tax Rate
        $stmt->execute([':setting_name' => 'tax_rate_percent', ':setting_value' => $tax_rate_percent]);

        $_SESSION['success_message'] = "System settings updated successfully!";
    } catch (PDOException $e) {
        error_log("Error saving system settings: " . $e->getMessage());
        $_SESSION['error_message'] = "Error saving system settings: " . $e->getMessage();
    }
} else {
    $_SESSION['error_message'] = "Invalid request method.";
}

// Redirect back to the system settings tab
header("Location: " . BASE_URL . "settings.php?tab=system-settings");
exit();
?>
