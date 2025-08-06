<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php'; // Assuming calculateAgeFromDob is here

requireLogin();
$pageTitle = "Saturday Attendance";

// --- Database Schema for Attendance (Conceptual - You'll need to run this SQL once) ---
/*
CREATE TABLE IF NOT EXISTS `saturday_attendance` (
  `attendance_id` INT(11) NOT NULL AUTO_INCREMENT,
  `patient_id` INT(11) NOT NULL,
  `attendance_date` DATE NOT NULL,
  `status` ENUM('Present','Absent') NOT NULL DEFAULT 'Absent',
  `recorded_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`attendance_id`),
  UNIQUE KEY `uq_patient_date` (`patient_id`, `attendance_date`),
  FOREIGN KEY (`patient_id`) REFERENCES `patients`(`patient_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
*/
// --- End of Conceptual Schema ---


// Handle form submission for saving attendance
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['attendance_date'])) {
    $attendance_date = $_POST['attendance_date'];
    $attended_patients = $_POST['attended_patients'] ?? []; // Array of patient_ids who were present

    try {
        $pdo->beginTransaction();

        // First, mark all patients as absent for this date (or remove existing records)
        // This simplifies the logic: delete all for the date, then re-insert 'Present' ones
        $delete_stmt = $pdo->prepare("DELETE FROM saturday_attendance WHERE attendance_date = ?");
        $delete_stmt->execute([$attendance_date]);

        // Insert 'Present' records for selected patients
        if (!empty($attended_patients)) {
            $insert_stmt = $pdo->prepare("
                INSERT INTO saturday_attendance (patient_id, attendance_date, status)
                VALUES (?, ?, 'Present')
                ON DUPLICATE KEY UPDATE status = 'Present'
            ");
            foreach ($attended_patients as $patient_id) {
                $insert_stmt->execute([$patient_id, $attendance_date]);
            }
        }
        $pdo->commit();
        $_SESSION['success_message'] = "Attendance for " . htmlspecialchars($attendance_date) . " saved successfully!";
    } catch (PDOException $e) {
        $pdo->rollBack();
        $_SESSION['error_message'] = "Error saving attendance: " . $e->getMessage();
        error_log("Attendance save error: " . $e->getMessage());
    }
    // Redirect to prevent form resubmission and show message
    header("Location: saturday_attendance.php?date=" . urlencode($attendance_date));
    exit();
}


// Get selected date from GET parameter or default to today's Saturday
$selected_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

// Ensure the selected date is a Saturday (optional, but good for validation)
// Find the most recent Saturday if the selected date isn't one
if (date('N', strtotime($selected_date)) != 6) { // 6 = Saturday
    $selected_date = date('Y-m-d', strtotime('last Saturday', strtotime($selected_date)));
}

// Determine if the current view is in "edit" mode
$is_edit_mode = isset($_GET['edit']) && $_GET['edit'] === 'true';

// Determine if the selected date is in the past
$is_past_date = strtotime($selected_date) < strtotime(date('Y-m-d'));

// Handle search term
$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';
$searchCondition = '';
$searchParams = [];

if (!empty($searchTerm)) {
    $searchCondition = " WHERE full_name LIKE ? OR patient_unique_id LIKE ? ";
    $searchParams = ["%$searchTerm%", "%$searchTerm%"];
}

// Pagination settings
$records_per_page = 10;
$current_page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $records_per_page;

// Get total number of patients matching search criteria for pagination
$count_patients_sql = "SELECT COUNT(*) FROM patients" . $searchCondition;
$count_patients_stmt = $pdo->prepare($count_patients_sql);
$count_patients_stmt->execute($searchParams);
$total_patients = $count_patients_stmt->fetchColumn();
$total_pages = ceil($total_patients / $records_per_page);

// Fetch patients for the current page (with search filter and pagination)
$patients_sql = "SELECT patient_id, full_name, patient_unique_id, phone FROM patients" . $searchCondition . " ORDER BY full_name ASC LIMIT ? OFFSET ?";
$patients_stmt = $pdo->prepare($patients_sql);
$patients_stmt->execute(array_merge($searchParams, [$records_per_page, $offset]));
$all_patients = $patients_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch attendance records for the selected date
$attendance_records = [];
if (!empty($all_patients)) {
    $attended_stmt = $pdo->prepare("SELECT patient_id FROM saturday_attendance WHERE attendance_date = ? AND status = 'Present'");
    $attended_stmt->execute([$selected_date]);
    $attended_patient_ids = $attended_stmt->fetchAll(PDO::FETCH_COLUMN);
    $attendance_records = array_flip($attended_patient_ids); // Flip for easy lookup
}

// Function to build pagination links while preserving current filters and edit mode
function buildAttendancePaginationLink($page, $date, $search, $edit_mode) {
    $query_params = ['page' => $page, 'date' => $date];
    if (!empty($search)) {
        $query_params['search'] = $search;
    }
    if ($edit_mode) {
        $query_params['edit'] = 'true';
    }
    return 'saturday_attendance.php?' . http_build_query($query_params);
}


require_once __DIR__ . '/../includes/header.php';
?>

<div class="container mx-auto p-6 bg-white rounded-lg shadow-md">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Saturday Attendance</h2>
    </div>

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline"><?= htmlspecialchars($_SESSION['success_message']); ?></span>
            <?php unset($_SESSION['success_message']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline"><?= htmlspecialchars($_SESSION['error_message']); ?></span>
            <?php unset($_SESSION['error_message']); ?>
        </div>
    <?php endif; ?>

    <!-- Date Selection and Search Form -->
    <form method="get" action="saturday_attendance.php" class="mb-6 flex flex-wrap items-center gap-4">
        <label for="attendance_date_select" class="block text-sm font-medium text-gray-700">Select Saturday:</label>
        <input type="date" id="attendance_date_select" name="date" 
               value="<?= htmlspecialchars($selected_date) ?>"
               class="px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring w-full md:w-auto">
        
        <input type="text" name="search" placeholder="Search client name or ID..."
               value="<?= htmlspecialchars($searchTerm) ?>"
               class="px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring w-full md:flex-1">

        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md shadow hover:bg-blue-700">
            View / Search
        </button>
        <?php if ($is_past_date && !$is_edit_mode): ?>
            <a href="saturday_attendance.php?date=<?= urlencode($selected_date) ?>&edit=true<?= !empty($searchTerm) ? '&search=' . urlencode($searchTerm) : '' ?>"
               class="px-4 py-2 bg-yellow-500 text-white rounded-md shadow hover:bg-yellow-600">
                <i class="fas fa-edit mr-2"></i>Edit Attendance
            </a>
        <?php elseif ($is_edit_mode): ?>
            <a href="saturday_attendance.php?date=<?= urlencode($selected_date) ?><?= !empty($searchTerm) ? '&search=' . urlencode($searchTerm) : '' ?>"
               class="px-4 py-2 bg-gray-500 text-white rounded-md shadow hover:bg-gray-600">
                Cancel Edit
            </a>
        <?php endif; ?>
        
        <!-- Download Button -->
        <a href="export_attendance_pdf.php?date=<?= urlencode($selected_date) ?><?= !empty($searchTerm) ? '&search=' . urlencode($searchTerm) : '' ?>"
           class="px-4 py-2 bg-purple-600 text-white rounded-md shadow hover:bg-purple-700">
            <i class="fas fa-download mr-2"></i>Download PDF
        </a>
    </form>

    <h3 class="text-xl font-semibold text-gray-800 mb-4">Attendance for <?= date('l, F j, Y', strtotime($selected_date)) ?></h3>

    <?php if (empty($all_patients) && empty($searchTerm)): ?>
        <div class="text-blue-700 bg-blue-100 p-4 rounded">No patients found in the system.</div>
    <?php elseif (empty($all_patients) && !empty($searchTerm)): ?>
        <div class="text-blue-700 bg-blue-100 p-4 rounded">No patients found matching your search criteria.</div>
    <?php else: ?>
        <form method="post" action="saturday_attendance.php" id="attendanceForm">
            <input type="hidden" name="attendance_date" value="<?= htmlspecialchars($selected_date) ?>">
            <div class="overflow-x-auto border border-gray-200 rounded mb-4">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500">Client ID</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500">Full Name</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500">Phone</th>
                            <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500">Present</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        <?php foreach ($all_patients as $patient): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4"><?= htmlspecialchars($patient['patient_unique_id']) ?></td>
                                <td class="px-6 py-4"><?= htmlspecialchars($patient['full_name']) ?></td>
                                <td class="px-6 py-4"><?= htmlspecialchars($patient['phone'] ?? 'N/A') ?></td>
                                <td class="px-6 py-4 text-center">
                                    <input type="checkbox" name="attended_patients[]" value="<?= $patient['patient_id'] ?>"
                                           <?= isset($attendance_records[$patient['patient_id']]) ? 'checked' : '' ?>
                                           <?= ($is_past_date && !$is_edit_mode) ? 'disabled' : '' ?>
                                           class="form-checkbox h-5 w-5 text-blue-600 rounded focus:ring-blue-500">
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination Controls -->
            <div class="flex justify-between items-center mt-6">
                <div>
                    Page <?= $current_page ?> of <?= $total_pages ?>
                </div>
                <div class="flex space-x-2">
                    <?php
                    $prev_page_link = buildAttendancePaginationLink($current_page - 1, $selected_date, $searchTerm, $is_edit_mode);
                    $next_page_link = buildAttendancePaginationLink($current_page + 1, $selected_date, $searchTerm, $is_edit_mode);
                    ?>
                    <a href="<?= $prev_page_link ?>"
                       class="px-4 py-2 rounded-lg font-semibold transition duration-200
                       <?= $current_page <= 1 ? 'bg-gray-300 text-gray-600 cursor-not-allowed' : 'bg-blue-600 text-white hover:bg-blue-700' ?>
                       <?= $current_page <= 1 ? 'pointer-events-none' : '' ?>">
                        Previous
                    </a>

                    <!-- Page numbers -->
                    <div class="flex space-x-1">
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <a href="<?= buildAttendancePaginationLink($i, $selected_date, $searchTerm, $is_edit_mode) ?>"
                               class="px-3 py-2 rounded-lg font-semibold transition duration-200
                               <?= $i === $current_page ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' ?>">
                                <?= $i ?>
                            </a>
                        <?php endfor; ?>
                    </div>

                    <a href="<?= $next_page_link ?>"
                       class="px-4 py-2 rounded-lg font-semibold transition duration-200
                       <?= $current_page >= $total_pages ? 'bg-gray-300 text-gray-600 cursor-not-allowed' : 'bg-blue-600 text-white hover:bg-blue-700' ?>
                       <?= $current_page >= $total_pages ? 'pointer-events-none' : '' ?>">
                        Next
                    </a>
                </div>
            </div>

            <?php if (!$is_past_date || $is_edit_mode): ?>
                <button type="button" id="saveAttendanceBtn" class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 shadow font-semibold">
                    Save Attendance for this Saturday
                </button>
            <?php else: ?>
                <p class="text-gray-600 text-sm mt-4">Attendance for past dates can only be modified by clicking the "Edit Attendance" button.</p>
            <?php endif; ?>
        </form>
    <?php endif; ?>
</div>

<!-- Custom Confirmation Box -->
<div id="customConfirmBox" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white animate-fade-in">
        <h3 class="text-lg font-bold text-gray-800" id="confirmBoxTitle">Confirm Action</h3>
        <div class="mt-2 px-7 py-3">
            <p class="text-sm text-gray-600" id="confirmBoxContent">Are you sure you want to save these attendance changes?</p>
        </div>
        <div class="mt-4 flex justify-center space-x-4">
            <button id="confirmBoxYes" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded transition duration-200">
                Yes
            </button>
            <button id="confirmBoxNo" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded transition duration-200">
                No
            </button>
        </div>
    </div>
</div>

<style>
    @keyframes fade-in {
        from { opacity: 0; transform: translateY(-20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fade-in {
        animation: fade-in 0.3s ease-out forwards;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const saveAttendanceBtn = document.getElementById('saveAttendanceBtn');
        const customConfirmBox = document.getElementById('customConfirmBox');
        const confirmBoxYes = document.getElementById('confirmBoxYes');
        const confirmBoxNo = document.getElementById('confirmBoxNo');
        const attendanceForm = document.getElementById('attendanceForm');

        if (saveAttendanceBtn) {
            saveAttendanceBtn.addEventListener('click', function() {
                customConfirmBox.classList.remove('hidden');
            });
        }

        confirmBoxYes.addEventListener('click', function() {
            customConfirmBox.classList.add('hidden');
            attendanceForm.submit(); // Submit the form if confirmed
        });

        confirmBoxNo.addEventListener('click', function() {
            customConfirmBox.classList.add('hidden');
        });
    });
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
