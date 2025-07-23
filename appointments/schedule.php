<?php
// appointments/schedule.php - Form to schedule or edit an appointment

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db_connect.php'; // This provides $pdo
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

// --- INITIALIZE VARIABLES ---
$edit_mode = false;
$appointment_id = null;
$patient_id = '';
$appointment_date = '';
$appointment_time = '';
$reason = '';
$status = 'Scheduled'; // Default status
$pageTitle = "Schedule New Appointment";
$form_action = BASE_URL . "process_appointment.php";

// --- CHECK FOR EDIT MODE ---
if (isset($_GET['edit_id']) && !empty($_GET['edit_id'])) {
    $edit_mode = true;
    $appointment_id = $_GET['edit_id'];
    $pageTitle = "Edit Appointment";

    // Fetch existing appointment data
    $sql = "SELECT * FROM appointments WHERE appointment_id = :appointment_id";
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':appointment_id', $appointment_id, PDO::PARAM_INT);
        $stmt->execute();
        $appointment = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($appointment) {
            $patient_id = $appointment['patient_id'];
            $appointment_date = $appointment['appointment_date'];
            $appointment_time = $appointment['appointment_time'];
            $reason = $appointment['reason'];
            $status = $appointment['status'];
        } else {
            $_SESSION['error_message'] = "Appointment not found.";
            header("Location: " . BASE_URL . "appointments/");
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Error fetching appointment data: " . $e->getMessage();
        header("Location: " . BASE_URL . "appointments/");
        exit();
    }
}

// --- FETCH PATIENTS FOR DROPDOWN ---
$patients = [];
try {
    $stmt = $pdo->query("SELECT patient_id, full_name FROM patients ORDER BY full_name ASC");
    $patients = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $_SESSION['error_message'] = "Error fetching patients list.";
    // Don't exit, just show an empty list
}


include_once __DIR__ . '/../includes/header.php';
?>

<div class="max-w-2xl mx-auto bg-white p-8 rounded-lg shadow-md">
    <h2 class="text-2xl font-bold text-gray-800 mb-6"><?php echo $pageTitle; ?></h2>

    <?php
    // Display any error messages
    if (isset($_SESSION['error_message'])) {
        echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">' . htmlspecialchars($_SESSION['error_message']) . '</div>';
        unset($_SESSION['error_message']);
    }
    ?>

    <form action="<?php echo $form_action; ?>" method="POST">
        <?php if ($edit_mode): ?>
            <input type="hidden" name="appointment_id" value="<?php echo $appointment_id; ?>">
        <?php endif; ?>

        <!-- Patient Selection -->
        <div class="mb-4">
            <label for="patient_id" class="block text-gray-700 font-semibold mb-2">Patient</label>
            <select id="patient_id" name="patient_id" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                <option value="">Select a Patient</option>
                <?php foreach ($patients as $p): ?>
                    <option value="<?php echo $p['patient_id']; ?>" <?php echo ($p['patient_id'] == $patient_id) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($p['full_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Date and Time -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
                <label for="appointment_date" class="block text-gray-700 font-semibold mb-2">Date</label>
                <input type="date" id="appointment_date" name="appointment_date" value="<?php echo htmlspecialchars($appointment_date); ?>" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>
            <div>
                <label for="appointment_time" class="block text-gray-700 font-semibold mb-2">Time</label>
                <input type="time" id="appointment_time" name="appointment_time" value="<?php echo htmlspecialchars($appointment_time); ?>" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>
        </div>

        <!-- Reason for Appointment -->
        <div class="mb-4">
            <label for="reason" class="block text-gray-700 font-semibold mb-2">Reason for Appointment</label>
            <textarea id="reason" name="reason" rows="3" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"><?php echo htmlspecialchars($reason); ?></textarea>
        </div>

        <!-- Status -->
        <div class="mb-6">
            <label for="status" class="block text-gray-700 font-semibold mb-2">Status</label>
            <select id="status" name="status" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="Scheduled" <?php echo ($status == 'Scheduled') ? 'selected' : ''; ?>>Scheduled</option>
                <option value="Completed" <?php echo ($status == 'Completed') ? 'selected' : ''; ?>>Completed</option>
                <option value="Cancelled" <?php echo ($status == 'Cancelled') ? 'selected' : ''; ?>>Cancelled</option>
            </select>
        </div>

        <!-- Submit Button -->
        <div class="flex justify-end space-x-4">
            <a href="<?php echo BASE_URL; ?>index.php" class="px-6 py-2 rounded-lg bg-gray-300 text-gray-800 font-semibold hover:bg-gray-400 transition-colors">Cancel</a>
            <button type="submit" class="px-6 py-2 rounded-lg bg-blue-600 text-white font-semibold hover:bg-blue-700 transition-colors">
                <?php echo $edit_mode ? 'Update Appointment' : 'Schedule Appointment'; ?>
            </button>
        </div>
    </form>
</div>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>
