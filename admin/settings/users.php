<?php
// admin/settings/users.php - User Management content for Admin Settings

// This file is intended to be included by admin/settings.php
// It assumes $pdo, requireLogin(), hasRole(), and BASE_URL are already defined by the including script.

// Fetch User Data from the database
$users = [];
try {
    // $pdo is available from the parent scope (admin/settings.php)
    $stmt_users = $pdo->query("SELECT user_id, full_name, email, role, status, last_login FROM users ORDER BY full_name ASC");
    $users = $stmt_users->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching users for admin settings: " . $e->getMessage());
    // You might want to set a session error message here to display to the user
    // For example: $_SESSION['error_message'] = "Error loading user data. Please try again later.";
}
?>

<h3 class="text-xl font-semibold text-gray-800 mb-4">User Management</h3>
<div class="flex justify-end mb-4">
    <a href="<?php echo BASE_URL; ?>users/add_user.php" class="inline-flex items-center px-4 py-2 bg-green-600 text-white font-semibold rounded-lg shadow-md hover:bg-green-700 transition-colors duration-200">
        <i class="fas fa-plus-circle mr-2"></i> Add New User
    </a>
</div>
<?php if (empty($users)): ?>
    <div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 rounded-md" role="alert">
        <p class="font-bold">No Users Found</p>
        <p>It looks like there are no users registered yet. Click "Add New User" to get started.</p>
    </div>
<?php else: ?>
    <div class="overflow-x-auto bg-white rounded-lg shadow-sm border border-gray-200">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Login</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($users as $user): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            <div class="flex items-center">
                                <span class="inline-flex items-center justify-center h-8 w-8 rounded-full
                                    <?php
                                        // Assign background color based on the first letter of the name
                                        $firstChar = strtoupper(substr($user['full_name'], 0, 1));
                                        $bgColor = '';
                                        $textColor = '';
                                        if (in_array($firstChar, ['A', 'E', 'I', 'O', 'U'])) {
                                            $bgColor = 'bg-red-200';
                                            $textColor = 'text-red-800';
                                        } else if (in_array($firstChar, ['B', 'C', 'D', 'F', 'G'])) {
                                            $bgColor = 'bg-blue-200';
                                            $textColor = 'text-blue-800';
                                        } else if (in_array($firstChar, ['H', 'J', 'K', 'L', 'M'])) {
                                            $bgColor = 'bg-green-200';
                                            $textColor = 'text-green-800';
                                        } else if (in_array($firstChar, ['N', 'P', 'Q', 'R', 'S'])) {
                                            $bgColor = 'bg-purple-200';
                                            $textColor = 'text-purple-800';
                                        } else {
                                            $bgColor = 'bg-yellow-200';
                                            $textColor = 'text-yellow-800';
                                        }
                                        echo "$bgColor $textColor";
                                    ?> font-bold text-sm mr-3">
                                    <?php echo htmlspecialchars($firstChar); ?>
                                </span>
                                <?php echo htmlspecialchars($user['full_name']); ?>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700"><?php echo htmlspecialchars($user['email']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                <?php
                                    if ($user['role'] === 'Admin') echo 'bg-red-100 text-red-800';
                                    else if ($user['role'] === 'Nutritionist') echo 'bg-blue-100 text-blue-800';
                                    else if ($user['role'] === 'Sales') echo 'bg-yellow-100 text-yellow-800';
                                    else echo 'bg-gray-100 text-gray-800'; // Default for 'Staff' or other roles
                                ?>">
                                <?php echo htmlspecialchars($user['role']); ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                <?php echo ($user['status'] === 'Active') ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                <?php echo htmlspecialchars($user['status']); ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                            <?php echo $user['last_login'] ? htmlspecialchars(date('M d, Y H:i', strtotime($user['last_login']))) : 'N/A'; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
    <a href="<?php echo BASE_URL; ?>users/edit_user.php?id=<?php echo $user['user_id']; ?>" 
       class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</a>
    
    <a href="#" 
       onclick="showCustomConfirm(
           'Are you sure you want to delete user &quot;<?php echo htmlspecialchars(addslashes($user['full_name'])); ?>&quot;?', 
           function(confirmed) {
               if (confirmed) {
                   window.location.href = '<?php echo BASE_URL; ?>admin/users/delete_user.php?id=<?php echo $user['user_id']; ?>';
               }
           }); return false;" 
       class="text-red-600 hover:text-red-900">Delete</a>
</td>

                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
