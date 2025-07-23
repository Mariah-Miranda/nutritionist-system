<?php
// list.php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php'; // For calculateAgeFromDob

// Set the page title for the header
$pageTitle = "All Patients";

// Require login for this page
requireLogin();

$message = '';
if (isset($_GET['message'])) {
    $message = htmlspecialchars($_GET['message']);
}

// Fetch all patients from the database
$patients = [];
try {
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
        <a href="<?php echo BASE_URL; ?>add.php" class="inline-flex items-center space-x-2 px-4 py-2 rounded-lg bg-green-600 text-white font-semibold hover:bg-green-700 transition-colors duration-200 shadow-md">
            <i class="fas fa-plus"></i>
            <span>Add New Patient</span>
        </a>
    </div>

    <?php if ($message): ?>
        <div class="bg-<?php echo strpos($message, 'successfully') !== false ? 'green' : 'red'; ?>-100 border-l-4 border-<?php echo strpos($message, 'successfully') !== false ? 'green' : 'red'; ?>-500 text-<?php echo strpos($message, 'successfully') !== false ? 'green' : 'red'; ?>-700 p-4 mb-4 rounded" role="alert">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <?php if (empty($patients)): ?>
        <div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 rounded-md" role="alert">
            <p class="font-bold">No Patients Found</p>
            <p>It looks like there are no patients registered yet. Click "Add New Patient" to get started.</p>
        </div>
    <?php else: ?>
        <div class="overflow-x-auto bg-white rounded-lg shadow-sm border border-gray-200">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Patient ID</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Full Name</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Gender</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Age</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Membership</th>
                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($patients as $patient): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo htmlspecialchars($patient['patient_unique_id']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700"><?php echo htmlspecialchars($patient['full_name']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700"><?php echo htmlspecialchars($patient['email'] ?? 'N/A'); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700"><?php echo htmlspecialchars($patient['phone'] ?? 'N/A'); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700"><?php echo htmlspecialchars($patient['gender'] ?? 'N/A'); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700"><?php echo htmlspecialchars(calculateAgeFromDob($patient['date_of_birth']) ?? 'N/A'); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                    <?php
                                        if ($patient['membership_status'] === 'Premium') echo 'bg-green-100 text-green-800';
                                        else if ($patient['membership_status'] === 'Standard') echo 'bg-blue-100 text-blue-800';
                                        else echo 'bg-gray-100 text-gray-800';
                                    ?>">
                                    <?php echo htmlspecialchars($patient['membership_status']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                <div class="flex items-center justify-center space-x-2">
                                    <a href="<?php echo BASE_URL; ?>view.php?id=<?php echo $patient['patient_id']; ?>" class="text-blue-600 hover:text-blue-900" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="<?php echo BASE_URL; ?>edit.php?id=<?php echo $patient['patient_id']; ?>" class="text-yellow-600 hover:text-yellow-900" title="Edit Patient">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="#" onclick="showCustomConfirm('Are you sure you want to delete patient &quot;<?php echo htmlspecialchars($patient['full_name']); ?>&quot;? This action cannot be undone.', function(confirmed) { if(confirmed) { window.location.href = '<?php echo BASE_URL; ?>delete.php?id=<?php echo $patient['patient_id']; ?>'; } }); return false;" class="text-red-600 hover:text-red-900" title="Delete Patient">
                                        <i class="fas fa-trash-alt"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- Custom Confirmation Modal (Copied from appointments/index.php for consistency) -->
<div id="customConfirmModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modalTitle">Confirm Action</h3>
            <div class="mt-2 px-7 py-3">
                <p class="text-sm text-gray-500" id="modalMessage">Are you sure you want to proceed?</p>
            </div>
            <div class="items-center px-4 py-3">
                <button id="confirmBtn" class="px-4 py-2 bg-red-600 text-white text-base font-medium rounded-md w-full shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                    Confirm
                </button>
                <button id="cancelBtn" class="mt-3 px-4 py-2 bg-gray-300 text-gray-800 text-base font-medium rounded-md w-full shadow-sm hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    // Custom confirmation dialog logic
    let confirmCallback = null;

    function showCustomConfirm(message, callback) {
        document.getElementById('modalMessage').innerText = message;
        document.getElementById('customConfirmModal').classList.remove('hidden');
        confirmCallback = callback;
        return false; // Prevent default link action
    }

    document.getElementById('confirmBtn').addEventListener('click', function() {
        document.getElementById('customConfirmModal').classList.add('hidden');
        if (confirmCallback) {
            confirmCallback(true);
        }
    });

    document.getElementById('cancelBtn').addEventListener('click', function() {
        document.getElementById('customConfirmModal').classList.add('hidden');
        if (confirmCallback) {
            confirmCallback(false);
        }
    });

    // Intercept all confirm calls (if any remain, though direct calls are replaced)
    const originalConfirm = window.confirm;
    window.confirm = function(message) {
        return new Promise((resolve) => {
            showCustomConfirm(message, resolve);
        });
    };
</script>

<?php
// Include the footer
require_once __DIR__ . '/../includes/footer.php';
?>
