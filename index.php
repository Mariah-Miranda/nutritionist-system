<?php
// index.php
require_once __DIR__ . '/config.php'; // Includes session_start() and DB constants
require_once __DIR__ . '/includes/db_connect.php'; // Establishes $pdo connection
require_once __DIR__ . '/includes/auth.php'; // Includes authentication functions

// If user is already logged in, redirect to their dashboard
if (isLoggedIn()) {
    // Redirect based on role, for now, just to admin dashboard
    header('Location: ' . BASE_URL . 'admin/index.php'); // Using BASE_URL
    exit();
}

$message = '';
if (isset($_GET['message'])) {
    $message = htmlspecialchars($_GET['message']);
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $message = 'Please enter both email and password.';
    } else {
        $loginResult = loginUser($pdo, $email, $password);
        if ($loginResult['success']) {
            // Login successful, redirect to dashboard
            header('Location: ' . BASE_URL . 'admin/index.php'); // Using BASE_URL
            exit();
        } else {
            $message = $loginResult['message'];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f3f4f6; /* Light gray background */
        }
        .login-container {
            background-color: #ffffff;
            border-radius: 1rem; /* Rounded corners */
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            padding: 2.5rem;
            max-width: 400px;
            width: 90%;
        }
        input[type="email"],
        input[type="password"] {
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            padding: 0.75rem 1rem;
            width: 100%;
            transition: border-color 0.2s ease-in-out;
        }
        input[type="email"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: #3b82f6; /* Blue focus ring */
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.25);
        }
        .btn-primary {
            background-color: #22c55e; /* Green button */
            color: #ffffff;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.2s ease-in-out;
            width: 100%;
        }
        .btn-primary:hover {
            background-color: #16a34a; /* Darker green on hover */
        }
        .message {
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
            font-weight: 500;
        }
        .message.error {
            background-color: #fee2e2; /* Red background */
            color: #ef4444; /* Red text */
            border: 1px solid #fca5a5;
        }
        .message.success {
            background-color: #d1fae5; /* Green background */
            color: #10b981; /* Green text */
            border: 1px solid #a7f3d0;
        }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen">
    <div class="login-container">
        <h2 class="text-3xl font-bold text-center text-gray-800 mb-8">Login</h2>

        <?php if ($message): ?>
            <div class="message <?php echo strpos($message, 'successful') !== false || strpos($message, 'logged out') !== false ? 'success' : 'error'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <form action="index.php" method="POST" class="space-y-6">
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                <input type="email" id="email" name="email" required placeholder="your.email@example.com">
            </div>
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                <input type="password" id="password" name="password" required placeholder="••••••••">
            </div>
            <div>
                <button type="submit" class="btn-primary">Login</button>
            </div>
        </form>
    </div>
</body>
</html>
