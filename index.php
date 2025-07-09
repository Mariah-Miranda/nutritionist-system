<?php
require_once __DIR__ . '/includes/db_connect.php'; // Attempt to connect to the database

// If the script reaches here, it means the connection was successful (no 'die' occurred in db_connect.php)

echo "<h1>Welcome to the Nutritionist System</h1>";
echo "<p>Database connection established successfully!</p>";
echo "<p>This will eventually be your login page.</p>";

// You can perform a simple query to further confirm
try {
    $stmt = $pdo->query("SELECT setting_name, setting_value FROM system_settings LIMIT 1");
    $setting = $stmt->fetch();
    if ($setting) {
        echo "<p>System setting example: " . htmlspecialchars($setting['setting_name']) . " = " . htmlspecialchars($setting['setting_value']) . "</p>";
    } else {
        echo "<p>No settings found in the system_settings table (this is normal if you just created it).</p>";
    }
} catch (\PDOException $e) {
    echo "<p>Error fetching settings: " . $e->getMessage() . "</p>";
}

?>