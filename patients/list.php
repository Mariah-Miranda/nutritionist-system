    <?php
    // patients/list.php
    require_once __DIR__ . '/../config.php';
    require_once __DIR__ . '/../includes/db_connect.php';
    require_once __DIR__ . '/../includes/auth.php';
    require_once __DIR__ . '/../includes/functions.php'; // For calculateAgeFromDob

    // Set the page title for the header
    $pageTitle = "All Patients";

    // Require login for this page
    requireLogin();
    // Uncomment and refine roles as needed
    // if (!hasAnyRole(['Admin', 'Nutritionist', 'Sales'])) { // Sales might need to see patient list for transactions
    //     header('Location: ' . BASE_URL . 'admin/index.php?message=Access denied. You do not have permission to view patients.');
    //     exit();
    // }

    $message = '';
    if (isset($_GET['message'])) {
        $message = htmlspecialchars($_GET['message']);
    }

    // Fetch all patients from the database
    $patients = [];
    try {
        // IMPORTANT: Ensure your 'patients' table now has a 'date_of_birth' column
        $stmt = $pdo->query("SELECT patient_id, patient_unique_id, full_name, date_of_birth, email, phone, gender, membership_status FROM patients ORDER BY full_name ASC");
        $patients = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching patients: " . $e->getMessage());
        $message = "Error loading patients. Please try again later.";
    }

    // Include the header
    require_once __DIR__ . '/../includes/header.php';
    ?>

    <div class="container mx-auto p-6 bg-white rounded-lg shadow-md">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Patient List</h2>
            <a href="<?php echo BASE_URL; ?>patients/add.php" class="btn-primary flex items-center space-x-2">
                <i class="fas fa-plus"></i>
                <span>Add New Patient</span>
            </a>
        </div>

        <?php if ($message): ?>
            <div class="message <?php echo strpos($message, 'successfully') !== false ? 'success' : 'error'; ?> mb-4">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <?php if (empty($patients)): ?>
            <div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 rounded-md" role="alert">
                <p class="font-bold">No Patients Found</p>
                <p>It looks like there are no patients registered yet. Click "Add New Patient" to get started.</p>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white admin-table">
                    <thead>
                        <tr>
                            <th class="py-3 px-4 text-left">Patient ID</th>
                            <th class="py-3 px-4 text-left">Full Name</th>
                            <th class="py-3 px-4 text-left">Email</th>
                            <th class="py-3 px-4 text-left">Phone</th>
                            <th class="py-3 px-4 text-left">Gender</th>
                            <th class="py-3 px-4 text-left">Age</th> <!-- Changed from Age to DOB -->
                            <th class="py-3 px-4 text-left">Membership</th>
                            <th class="py-3 px-4 text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($patients as $patient): ?>
                            <tr>
                                <td class="py-3 px-4"><?php echo htmlspecialchars($patient['patient_unique_id']); ?></td>
                                <td class="py-3 px-4"><?php echo htmlspecialchars($patient['full_name']); ?></td>
                                <td class="py-3 px-4"><?php echo htmlspecialchars($patient['email'] ?? 'N/A'); ?></td>
                                <td class="py-3 px-4"><?php echo htmlspecialchars($patient['phone'] ?? 'N/A'); ?></td>
                                <td class="py-3 px-4"><?php echo htmlspecialchars($patient['gender'] ?? 'N/A'); ?></td>
                                <td class="py-3 px-4"><?php echo htmlspecialchars(calculateAgeFromDob($patient['date_of_birth']) ?? 'N/A'); ?></td> <!-- Display calculated age -->
                                <td class="py-3 px-4">
                                    <span class="status-badge <?php
                                        // Dynamically apply badge color based on membership status
                                        if ($patient['membership_status'] === 'Premium') echo 'bg-green-100 text-green-800';
                                        else if ($patient['membership_status'] === 'Standard') echo 'bg-blue-100 text-blue-800';
                                        else echo 'bg-gray-100 text-gray-800';
                                    ?>">
                                        <?php echo htmlspecialchars($patient['membership_status']); ?>
                                    </span>
                                </td>
                                <td class="py-3 px-4 action-buttons">
                                    <a href="<?php echo BASE_URL; ?>patients/view.php?id=<?php echo $patient['patient_id']; ?>" class="text-blue-600 hover:text-blue-800 mr-2" title="View Details">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                    <a href="<?php echo BASE_URL; ?>patients/edit.php?id=<?php echo $patient['patient_id']; ?>" class="text-yellow-600 hover:text-yellow-800 mr-2" title="Edit Patient">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <!-- Delete functionality will be added later, typically with a confirmation modal -->
                                    <a href="#" onclick="confirmDelete(<?php echo $patient['patient_id']; ?>, '<?php echo htmlspecialchars($patient['full_name']); ?>'); return false;" class="text-red-600 hover:text-red-800" title="Delete Patient">
                                        <i class="fas fa-trash-alt"></i> Delete
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <?php
    // Include the footer
    require_once __DIR__ . '/../includes/footer.php';
    ?>

    <script>
        // Simple client-side confirmation for delete (server-side handling needed)
        function confirmDelete(patientId, patientName) {
            if (confirm(`Are you sure you want to delete patient "${patientName}"? This action cannot be undone.`)) {
                // In a real application, you would send an AJAX request or redirect to a delete script
                // For now, this is just a placeholder.
                // window.location.href = '<?php echo BASE_URL; ?>patients/delete.php?id=' + patientId;
                console.log(`Delete patient with ID: ${patientId} (${patientName})`);
                alert('Delete functionality not yet implemented. This would trigger a server-side delete.');
            }
        }
    </script>
    