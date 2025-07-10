<?php
// includes/auth.php

// Function to hash passwords securely
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT);
}

// Function to verify password
function verifyPassword($password, $hashedPassword) {
    return password_verify($password, $hashedPassword);
}

// Function to log in a user
function loginUser($pdo, $email, $password) {
    try {
        // Prepare SQL statement to prevent SQL injection
        $stmt = $pdo->prepare("SELECT user_id, full_name, email, password, role, status FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && verifyPassword($password, $user['password'])) {
            // Check if user is active
            if ($user['status'] === 'Inactive') {
                return ['success' => false, 'message' => 'Your account is inactive. Please contact an administrator.'];
            }

            // Set session variables
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['logged_in'] = true;

            // Update last login timestamp
            $updateStmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE user_id = :user_id");
            $updateStmt->bindParam(':user_id', $user['user_id']);
            $updateStmt->execute();

            return ['success' => true, 'message' => 'Login successful!'];
        } else {
            return ['success' => false, 'message' => 'Invalid email or password.'];
        }
    } catch (PDOException $e) {
        error_log("Login Error: " . $e->getMessage());
        return ['success' => false, 'message' => 'An error occurred during login. Please try again.'];
    }
}

// Function to check if a user is logged in
function isLoggedIn() {
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

// Function to require login for a page
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . BASE_URL . 'index.php?message=Please log in to access this page.');
        exit();
    }
}

// Function to check if the logged-in user has a specific role
function hasRole($requiredRole) {
    if (!isLoggedIn()) {
        return false; // Not logged in, so no role
    }
    // For single role check
    return $_SESSION['role'] === $requiredRole;
}

// Function to check if the logged-in user has one of the specified roles
function hasAnyRole(array $requiredRoles) {
    if (!isLoggedIn()) {
        return false; // Not logged in, so no role
    }
    return in_array($_SESSION['role'], $requiredRoles);
}

// Function to log out a user
function logoutUser() {
    // Unset all session variables
    $_SESSION = array();

    // Destroy the session
    session_destroy();

    // Redirect to login page
    header('Location: /nutritionist-system/index.php?message=You have been logged out.');
    exit();
}
?>
