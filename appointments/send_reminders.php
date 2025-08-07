<?php
// appointments/send_reminders.php - Send Appointment Reminders

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/auth.php';

$pageTitle = "Send Reminders";
include_once __DIR__ . '/../includes/header.php';

// Fetch appointments that are due for a reminder (e.g., scheduled for tomorrow)
$reminderDate = date('Y-m-d', strtotime('+1 day'));
$remindersDue = [];
$sql = "SELECT a.appointment_id, a.appointment_date, a.appointment_time, p.full_name, p.email
        FROM appointments a
        JOIN patients p ON a.patient_id = p.patient_id
        WHERE a.appointment_date = :reminder_date AND a.status = 'Scheduled'";
try {
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':reminder_date', $reminderDate);
    $stmt->execute();
    $remindersDue = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("ERROR: Could not fetch appointments for reminders: " . $e->getMessage());
    $_SESSION['error_message'] = "Error fetching appointments for reminders.";
}

?>

<div class="max-w-4xl mx-auto bg-white p-8 rounded-lg shadow-md">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Send Appointment Reminders</h2>
        <a href="<?php echo BASE_URL; ?>index.php" class="px-4 py-2 rounded-lg bg-blue-600 text-white font-semibold hover:bg-blue-700 transition-colors">Back to Dashboard</a>
    </div>

    <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-6" role="alert">
      <p class="font-bold">Demonstration Page</p>
      <p>Clicking 'Send Reminder' will simulate sending an email (no actual email is sent).</p>
    </div>

    <div class="space-y-4">
        <?php if (!empty($remindersDue)): ?>
            <?php foreach ($remindersDue as $appt): ?>
                <div class="border rounded-lg p-4 flex items-center justify-between shadow-sm bg-gray-50">
                    <div>
                        <p class="font-semibold text-gray-800"><?php echo htmlspecialchars($appt['full_name']); ?></p>
                        <p class="text-sm text-gray-600">
                            Appointment for <?php echo date('F j, Y', strtotime($appt['appointment_date'])); ?>
                            at <?php echo date('h:i A', strtotime($appt['appointment_time'])); ?>
                        </p>
                        <p class="text-xs text-gray-500">Email: <?php echo htmlspecialchars($appt['email']); ?></p>
                    </div>
                    <button onclick="alert('Simulating sending reminder to <?php echo htmlspecialchars($appt['email']); ?>.')" class="px-4 py-2 rounded-lg bg-yellow-500 text-white font-semibold hover:bg-yellow-600 transition-colors">
                        <i class="fas fa-paper-plane mr-2"></i>
                        Send Reminder
                    </button>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-gray-500 text-center py-4">No appointments due for a reminder for tomorrow.</p>
        <?php endif; ?>
    </div>
</div>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>
