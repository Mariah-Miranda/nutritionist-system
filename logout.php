<?php
// logout.php — safely logs out the user and redirects to index.php

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Unset all session variables
$_SESSION = [];

// Destroy the session
session_destroy();

// Redirect to the public home page
header("Location: /nutritionist-system/index.php?message=You have been logged out.");
exit();
