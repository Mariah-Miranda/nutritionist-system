<?php
// admin/users/delete_user.php - Handles deleting a user

// Include necessary configuration and utility files
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/db_connect.php';
require_once __DIR__ . '/../../includes/auth.php';

// Require login and admin role for this page
requireLogin();
if (!hasRole('Admin')) {
    $_SESSION['error_message'] = "You do not have permission to delete users.";
    header("Location: " . BASE_URL . "admin/settings.php?tab=user-management");
    exit();
}

$user_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if ($user_id) {
    try {
        // Prevent deletion of the currently logged-in user (optional, but recommended)
        if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $user_id) {
            $_SESSION['error_message'] = "You cannot delete your own account.";
            header("Location: " . BASE_URL . "admin/settings.php?tab=user-management");
            exit();
        }

        $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = :user_id");
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            $_SESSION['success_message'] = "User deleted successfully!";
        } else {
            $_SESSION['error_message'] = "Error deleting user.";
        }
    } catch (PDOException $e) {
        error_log("Error deleting user: " . $e->getMessage());
        $_SESSION['error_message'] = "Database error: " . $e->getMessage();
    }
} else {
    $_SESSION['error_message'] = "No user ID provided for deletion.";
}

// Redirect back to the user management tab
header("Location: " . BASE_URL . "../settings.php?tab=user-management");
exit();
?>
