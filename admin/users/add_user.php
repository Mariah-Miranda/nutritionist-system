<?php
// users/add_user.php - Add New User Page

// Include necessary configuration and utility files
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/db_connect.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php'; // For any utility functions

// Set the page title for the header
$pageTitle = "Add New User";

// Require login and admin role for this page
requireLogin();
if (!hasRole('Admin')) {
    $_SESSION['error_message'] = "You do not have permission to add users.";
    header("Location: " . BASE_URL . "settings.php?tab=user-management");
    exit();
}

$message = '';

// Handle form submission for adding new user
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = filter_input(INPUT_POST, 'full_name', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $role = filter_input(INPUT_POST, 'role', FILTER_SANITIZE_STRING);
    $status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING);

    // Basic validation
    if (empty($full_name) || empty($email) || empty($password) || empty($confirm_password) || empty($role) || empty($status)) {
        $message = '<div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded" role="alert">All fields are required.</div>';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = '<div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded" role="alert">Invalid email format.</div>';
    } elseif ($password !== $confirm_password) {
        $message = '<div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded" role="alert">Passwords do not match.</div>';
    } elseif (!in_array($role, ['Admin', 'Nutritionist', 'Staff', 'Sales'])) {
        $message = '<div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded" role="alert">Invalid role selected.</div>';
    } elseif (!in_array($status, ['Active', 'Inactive'])) {
        $message = '<div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded" role="alert">Invalid status selected.</div>';
    } else {
        try {
            // Check if email already exists
            $stmt_check_email = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = :email");
            $stmt_check_email->bindParam(':email', $email);
            $stmt_check_email->execute();
            if ($stmt_check_email->fetchColumn() > 0) {
                $message = '<div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded" role="alert">Email already registered. Please use a different email.</div>';
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password, role, status, created_at, updated_at) VALUES (:full_name, :email, :password, :role, :status, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)");
                $stmt->bindParam(':full_name', $full_name);
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':password', $hashed_password);
                $stmt->bindParam(':role', $role);
                $stmt->bindParam(':status', $status);

                if ($stmt->execute()) {
                    $_SESSION['success_message'] = "New user added successfully!";
                    header("Location: " . BASE_URL . "settings.php?tab=user-management");
                    exit();
                } else {
                    $message = '<div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded" role="alert">Error adding user.</div>';
                }
            }
        } catch (PDOException $e) {
            error_log("Error adding user: " . $e->getMessage());
            $message = '<div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded" role="alert">Database error: ' . $e->getMessage() . '</div>';
        }
    }
}

// Include the header
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="container mx-auto p-6 bg-white rounded-lg shadow-md">
    <h2 class="text-2xl font-bold text-gray-800 mb-6">Add New User</h2>

    <?php echo $message; ?>

    <form action="<?php echo BASE_URL; ?>users/add_user.php" method="POST" class="space-y-6">
        <div class="form-group">
            <label for="full_name" class="block text-gray-700 font-semibold mb-2">Full Name</label>
            <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
        </div>

        <div class="form-group">
            <label for="email" class="block text-gray-700 font-semibold mb-2">Email</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
        </div>

        <div class="form-group">
            <label for="password" class="block text-gray-700 font-semibold mb-2">Password</label>
            <input type="password" id="password" name="password" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
        </div>

        <div class="form-group">
            <label for="confirm_password" class="block text-gray-700 font-semibold mb-2">Confirm Password</label>
            <input type="password" id="confirm_password" name="confirm_password" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
        </div>

        <div class="form-group">
            <label for="role" class="block text-gray-700 font-semibold mb-2">Role</label>
            <select id="role" name="role" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                <option value="">Select Role</option>
                <option value="Admin" <?php echo (isset($_POST['role']) && $_POST['role'] === 'Admin') ? 'selected' : ''; ?>>Admin</option>
                <option value="Nutritionist" <?php echo (isset($_POST['role']) && $_POST['role'] === 'Nutritionist') ? 'selected' : ''; ?>>Nutritionist</option>
                <option value="Staff" <?php echo (isset($_POST['role']) && $_POST['role'] === 'Staff') ? 'selected' : ''; ?>>Staff</option>
                <option value="Sales" <?php echo (isset($_POST['role']) && $_POST['role'] === 'Sales') ? 'selected' : ''; ?>>Sales</option>
            </select>
        </div>

        <div class="form-group">
            <label for="status" class="block text-gray-700 font-semibold mb-2">Status</label>
            <select id="status" name="status" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                <option value="Active" <?php echo (isset($_POST['status']) && $_POST['status'] === 'Active') ? 'selected' : ''; ?>>Active</option>
                <option value="Inactive" <?php echo (isset($_POST['status']) && $_POST['status'] === 'Inactive') ? 'selected' : ''; ?>>Inactive</option>
            </select>
        </div>

        <div class="flex justify-end space-x-4">
            <a href="settings.php?tab=user-management" class="px-6 py-2 rounded-lg bg-gray-300 text-gray-800 font-semibold hover:bg-gray-400 transition-colors duration-200">Cancel</a>
            <button type="submit" class="px-6 py-2 rounded-lg bg-green-600 text-white font-semibold hover:bg-green-700 transition-colors duration-200">Add User</button>
        </div>
    </form>
</div>

<?php
// Include the footer
require_once __DIR__ . '/../../includes/footer.php';
?>
