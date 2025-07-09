<?php
// Database credentials
define('DB_HOST', 'localhost'); // Your database host (e.g., localhost or IP address)
define('DB_NAME', 'nutritionist_db'); // The name of the database we created
define('DB_USER', 'root'); // Your MySQL username (e.g., 'root')
define('DB_PASS', ''); // Your MySQL password (often empty for 'root' on local setups, but use a strong one in production)

// Other system configurations (from system_settings table, but defaults here)
define('SITE_NAME', 'Nutritionist System');
define('DEFAULT_CURRENCY', 'UGX');
define('TAX_RATE_PERCENT', 8); // Example default, will be fetched from DB later

// Set default timezone (important for date/time functions)
date_default_timezone_set('Africa/Kampala'); // Set to your local timezone 

// Error reporting (for development - turn off or limit in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start session
session_start();
?>