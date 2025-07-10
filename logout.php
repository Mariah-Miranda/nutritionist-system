<?php
// logout.php
require_once __DIR__ . '/config.php'; // Includes session_start()
require_once __DIR__ . '/includes/auth.php'; // Includes logoutUser() function

logoutUser(); // Call the logout function from auth.php
?>
