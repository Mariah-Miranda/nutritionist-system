<?php
// admin/users/edit_user.php - Edit User Page

// Include necessary configuration and utility files
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/db_connect.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php'; // For any utility functions

// Set the page title for the header
$pageTitle = "Edit User";

// Require login and admin role for this page
requireLogin();
if (!hasRole('Admin')) {
    $_SESSION['error_message'] = "You do not have permission to edit users.";
    header("Location: " . BASE_URL . "admin/settings.php?tab=user-management");
    exit();
}

$user = null;
$user_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$message = '';

if ($user_id) {
    try {
        $stmt = $pdo->prepare("SELECT user_id, full_name, email, role, status FROM users WHERE user_id = :user_id");
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $_SESSION['error_message'] = "User not found.";
            header("Location: " . BASE_URL . "admin/settings.php?tab=user-management");
            exit();
        }
    } catch (PDOException $e) {
        error_log("Error fetching user for edit: " . $e->getMessage());
        $_SESSION['error_message'] = "Error loading user data for editing. Please try again later.";
        header("Location: " . BASE_URL . "admin/settings.php?tab=user-management");
        exit();
    }
} else {
    $_SESSION['error_message'] = "No user ID provided for editing.";
    header("Location: " . BASE_URL . "admin/settings.php?tab=user-management");
    exit();
}

// Handle form submission for updating user
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = filter_input(INPUT_POST, 'full_name', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $role = filter_input(INPUT_POST, 'role', FILTER_SANITIZE_STRING);
    $status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING);
    $password = $_POST['password'] ?? ''; // Get password if provided
    $confirm_password = $_POST['confirm_password'] ?? ''; // Get confirm password

    // Basic validation
    if (empty($full_name) || empty($email) || empty($role) || empty($status)) {
        $message = '<div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded" role="alert">All fields are required.</div>';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = '<div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded" role="alert">Invalid email format.</div>';
    } elseif (!in_array($role, ['Admin', 'Nutritionist', 'Staff', 'Sales'])) {
        $message = '<div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded" role="alert">Invalid role selected.</div>';
    } elseif (!in_array($status, ['Active', 'Inactive'])) {
        $message = '<div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded" role="alert">Invalid status selected.</div>';
    } elseif (!empty($password) && $password !== $confirm_password) {
        $message = '<div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded" role="alert">Passwords do not match.</div>';
    } else {
        try {
            $sql = "UPDATE users SET full_name = :full_name, email = :email, role = :role, status = :status";
            if (!empty($password)) {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $sql .= ", password = :password";
            }
            $sql .= ", updated_at = CURRENT_TIMESTAMP WHERE user_id = :user_id";

            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':full_name', $full_name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':role', $role);
            $stmt->bindParam(':status', $status);
            if (!empty($password)) {
                $stmt->bindParam(':password', $hashed_password);
            }
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);

            if ($stmt->execute()) {
                $_SESSION['success_message'] = "User updated successfully!";
                header("Location: " . BASE_URL . "../settings.php?tab=user-management");
                exit();
            } else {
                $message = '<div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded" role="alert">Error updating user.</div>';
            }
        } catch (PDOException $e) {
            error_log("Error updating user: " . $e->getMessage());
            $message = '<div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded" role="alert">Database error: ' . $e->getMessage() . '</div>';
        }
    }
}

// Include the header
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="container mx-auto p-6 bg-white rounded-lg shadow-md">
    <h2 class="text-2xl font-bold text-gray-800 mb-6">Edit User: <?php echo htmlspecialchars($user['full_name'] ?? ''); ?></h2>

    <?php echo $message; ?>

    <form action="<?php echo BASE_URL; ?>edit_user.php?id=<?php echo $user_id; ?>" method="POST" class="space-y-6">
        <div class="form-group">
            <label for="full_name" class="block text-gray-700 font-semibold mb-2">Full Name</label>
            <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
        </div>

        <div class="form-group">
            <label for="email" class="block text-gray-700 font-semibold mb-2">Email</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
        </div>

        <div class="form-group">
            <label for="role" class="block text-gray-700 font-semibold mb-2">Role</label>
            <select id="role" name="role" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                <option value="Admin" <?php echo (isset($user['role']) && $user['role'] === 'Admin') ? 'selected' : ''; ?>>Admin</option>
                <option value="Nutritionist" <?php echo (isset($user['role']) && $user['role'] === 'Nutritionist') ? 'selected' : ''; ?>>Nutritionist</option>
                <option value="Staff" <?php echo (isset($user['role']) && $user['role'] === 'Staff') ? 'selected' : ''; ?>>Staff</option>
                <option value="Sales" <?php echo (isset($user['role']) && $user['role'] === 'Sales') ? 'selected' : ''; ?>>Sales</option>
            </select>
        </div>

        <div class="form-group">
            <label for="status" class="block text-gray-700 font-semibold mb-2">Status</label>
            <select id="status" name="status" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                <option value="Active" <?php echo (isset($user['status']) && $user['status'] === 'Active') ? 'selected' : ''; ?>>Active</option>
                <option value="Inactive" <?php echo (isset($user['status']) && $user['status'] === 'Inactive') ? 'selected' : ''; ?>>Inactive</option>
            </select>
        </div>

        <div class="form-group">
            <label for="password" class="block text-gray-700 font-semibold mb-2">New Password (leave blank to keep current)</label>
            <input type="password" id="password" name="password" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div class="form-group">
            <label for="confirm_password" class="block text-gray-700 font-semibold mb-2">Confirm New Password</label>
            <input type="password" id="confirm_password" name="confirm_password" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div class="flex justify-end space-x-4">
            <a href="<?php echo BASE_URL; ?>admin/settings.php?tab=user-management" class="px-6 py-2 rounded-lg bg-gray-300 text-gray-800 font-semibold hover:bg-gray-400 transition-colors duration-200">Cancel</a>
            <button type="submit" class="px-6 py-2 rounded-lg bg-indigo-600 text-white font-semibold hover:bg-indigo-700 transition-colors duration-200">Update User</button>
        </div>
    </form>
</div>

<?php
// Include the footer
require_once __DIR__ . '/../../includes/footer.php';
?>

