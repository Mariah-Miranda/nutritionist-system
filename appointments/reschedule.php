<?php
// appointments/reschedule.php - Reschedule an Appointment

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php'; // Assuming sanitizeInput is here

$pageTitle = "Reschedule Appointment";
include_once __DIR__ . '/../includes/header.php';

$appointments = [];
$message = '';
$messageType = ''; // 'success' or 'error'

// Fetch existing appointments to populate the dropdown
try {
    $sql = "SELECT a.appointment_id, a.appointment_date, a.appointment_time, a.reason, p.full_name AS patient_name
            FROM appointments a
            JOIN patients p ON a.patient_id = p.patient_id
            WHERE a.status = 'Scheduled'
            ORDER BY a.appointment_date ASC, a.appointment_time ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("ERROR: Could not fetch appointments for rescheduling: " . $e->getMessage());
    $message = "Error loading appointments: " . $e->getMessage();
    $messageType = 'error';
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $appointment_id = sanitizeInput($_POST['appointment_id'] ?? '');
    $new_date = sanitizeInput($_POST['new_date'] ?? '');
    $new_time = sanitizeInput($_POST['new_time'] ?? '');

    if (empty($appointment_id)) {
        $message = "Please select an appointment from the list.";
        $messageType = 'error';
    } elseif (empty($new_date) || empty($new_time)) {
        $message = "Please provide a new date and time for the appointment.";
        $messageType = 'error';
    } else {
        // Validate date and time format (basic validation)
        if (!isValidDate($new_date) || !isValidTime($new_time)) {
            $message = "Invalid date or time format.";
            $messageType = 'error';
        } else {
            try {
                $sql_update = "UPDATE appointments SET appointment_date = :new_date, appointment_time = :new_time, updated_at = NOW() WHERE appointment_id = :appointment_id";
                $stmt_update = $pdo->prepare($sql_update);
                $stmt_update->bindParam(':new_date', $new_date);
                $stmt_update->bindParam(':new_time', $new_time);
                $stmt_update->bindParam(':appointment_id', $appointment_id, PDO::PARAM_INT);

                if ($stmt_update->execute()) {
                    $_SESSION['success_message'] = "Appointment rescheduled successfully!";
                    header('Location: ' . BASE_URL . 'appointments/index.php');
                    exit();
                } else {
                    $message = "Failed to reschedule appointment. Please try again.";
                    $messageType = 'error';
                }
            } catch (PDOException $e) {
                error_log("ERROR: Could not reschedule appointment: " . $e->getMessage());
                $message = "Database error: " . $e->getMessage();
                $messageType = 'error';
            }
        }
    }
}

// Helper functions (add these to includes/functions.php if not already there)
if (!function_exists('isValidDate')) {
    function isValidDate($dateString) {
        return (bool)strtotime($dateString);
    }
}

if (!function_exists('isValidTime')) {
    function isValidTime($timeString) {
        return (bool)strtotime("1970-01-01 " . $timeString);
    }
}

?>

<div class="max-w-2xl mx-auto bg-white p-8 rounded-lg shadow-md">
    <h2 class="text-2xl font-bold text-gray-800 mb-6">Reschedule an Appointment</h2>
    
    <?php if ($message): ?>
        <div class="bg-<?php echo $messageType === 'success' ? 'green' : 'red'; ?>-100 border-l-4 border-<?php echo $messageType === 'success' ? 'green' : 'red'; ?>-500 text-<?php echo $messageType === 'success' ? 'green' : 'red'; ?>-700 p-4 mb-6" role="alert">
            <p class="font-bold"><?php echo ucfirst($messageType); ?>!</p>
            <p><?php echo htmlspecialchars($message); ?></p>
        </div>
    <?php endif; ?>

    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" id="rescheduleForm">
        <!-- Select Appointment -->
        <div class="mb-4">
            <label for="appointment_id" class="block text-gray-700 font-semibold mb-2">Select Appointment to Reschedule</label>
            <select id="appointment_id" name="appointment_id" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                <option value="">-- Select an Appointment --</option>
                <?php if (!empty($appointments)): ?>
                    <?php foreach ($appointments as $appt): ?>
                        <option 
                            value="<?php echo htmlspecialchars($appt['appointment_id']); ?>"
                            data-date="<?php echo htmlspecialchars($appt['appointment_date']); ?>"
                            data-time="<?php echo htmlspecialchars($appt['appointment_time']); ?>"
                        >
                            <?php echo htmlspecialchars(date('M j, Y', strtotime($appt['appointment_date']))); ?> at <?php echo htmlspecialchars(date('h:i A', strtotime($appt['appointment_time']))); ?> - <?php echo htmlspecialchars($appt['patient_name']); ?> (<?php echo htmlspecialchars($appt['reason']); ?>)
                        </option>
                    <?php endforeach; ?>
                <?php else: ?>
                    <option value="" disabled>No scheduled appointments found.</option>
                <?php endif; ?>
            </select>
        </div>

        <!-- New Date -->
        <div class="mb-4">
            <label for="new_date" class="block text-gray-700 font-semibold mb-2">New Appointment Date</label>
            <input type="date" id="new_date" name="new_date" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
        </div>

        <!-- New Time -->
        <div class="mb-6">
            <label for="new_time" class="block text-gray-700 font-semibold mb-2">New Appointment Time</label>
            <input type="time" id="new_time" name="new_time" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
        </div>
        
        <!-- Submit Button -->
        <div class="flex justify-end space-x-4">
            <a href="<?php echo BASE_URL; ?>appointments/" class="px-6 py-2 rounded-lg bg-gray-300 text-gray-800 font-semibold hover:bg-gray-400 transition-colors">Cancel</a>
            <button type="submit" class="px-6 py-2 rounded-lg bg-blue-600 text-white font-semibold hover:bg-blue-700 transition-colors">Reschedule</button>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const appointmentSelect = document.getElementById('appointment_id');
        const newDateInput = document.getElementById('new_date');
        const newTimeInput = document.getElementById('new_time');
        const rescheduleForm = document.getElementById('rescheduleForm');

        // Function to update date and time fields
        function updateDateTimeFields() {
            const selectedOption = appointmentSelect.options[appointmentSelect.selectedIndex];
            const appointmentDate = selectedOption.getAttribute('data-date');
            const appointmentTime = selectedOption.getAttribute('data-time');

            if (appointmentDate && appointmentTime) {
                newDateInput.value = appointmentDate;
                newTimeInput.value = appointmentTime;
            } else {
                // Clear fields if no valid appointment is selected (e.g., "-- Select an Appointment --")
                newDateInput.value = '';
                newTimeInput.value = '';
            }
        }

        // Event listener for dropdown change to pre-fill fields
        appointmentSelect.addEventListener('change', updateDateTimeFields);

        // Initial call to populate if an option is pre-selected (e.g., from a previous submission attempt)
        updateDateTimeFields();

        // Client-side validation before form submission
        rescheduleForm.addEventListener('submit', function(event) {
            if (appointmentSelect.value === "") {
                event.preventDefault(); // Prevent form submission
                alert("Please select an appointment to reschedule."); // Use alert for simplicity, consider a custom modal in production
                appointmentSelect.focus();
            }
        });
    });
</script>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>
