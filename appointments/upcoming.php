<?php
// appointments/upcoming.php - Displays upcoming appointments

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db_connect.php'; // This provides $pdo
require_once __DIR__ . '/../includes/auth.php';

// Include header
$pageTitle = "Upcoming Appointments";
include_once __DIR__ . '/../includes/header.php';

requireLogin();
// requireRole('Nutritionist'); // Example: Only nutritionists can view upcoming appointments

$today = date('Y-m-d');
$upcomingAppointments = [];

// Fetch appointments from today onwards, ordered by date and time using PDO
$sql = "SELECT a.appointment_id, a.appointment_date, a.appointment_time, a.reason, a.status, p.full_name AS patient_name, p.patient_unique_id
        FROM appointments a
        JOIN patients p ON a.patient_id = p.patient_id
        WHERE a.appointment_date >= :today AND a.status = 'Scheduled'
        ORDER BY a.appointment_date ASC, a.appointment_time ASC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':today', $today);
    $stmt->execute();

    $upcomingAppointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("ERROR: Could not fetch upcoming appointments in upcoming.php: " . $e->getMessage());
    echo "<p class='text-red-500'>Error fetching upcoming appointments. Please try again.</p>";
}
?>

<div class="bg-white rounded-lg shadow-md p-6">
    <h2 class="text-2xl font-semibold text-gray-800 mb-6">Upcoming Appointments</h2>

    <?php if (empty($upcomingAppointments)): ?>
        <p class="text-gray-600">No upcoming appointments scheduled.</p>
    <?php else: ?>
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white rounded-lg shadow-md">
                <thead>
                    <tr class="bg-gray-200 text-gray-600 uppercase text-sm leading-normal rounded-t-lg">
                        <th class="py-3 px-6 text-left">Date</th>
                        <th class="py-3 px-6 text-left">Time</th>
                        <th class="py-3 px-6 text-left">Patient Name</th>
                        <th class="py-3 px-6 text-left">Patient ID</th>
                        <th class="py-3 px-6 text-left">Reason</th>
                        <th class="py-3 px-6 text-left">Status</th>
                        <th class="py-3 px-6 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-gray-600 text-sm font-light">
                    <?php foreach ($upcomingAppointments as $appointment): ?>
                        <tr class="border-b border-gray-200 hover:bg-gray-100">
                            <td class="py-3 px-6 text-left whitespace-nowrap"><?php echo htmlspecialchars($appointment['appointment_date']); ?></td>
                            <td class="py-3 px-6 text-left"><?php echo htmlspecialchars(date('h:i A', strtotime($appointment['appointment_time']))); ?></td>
                            <td class="py-3 px-6 text-left"><?php echo htmlspecialchars($appointment['patient_name']); ?></td>
                            <td class="py-3 px-6 text-left"><?php echo htmlspecialchars($appointment['patient_unique_id']); ?></td>
                            <td class="py-3 px-6 text-left"><?php echo htmlspecialchars($appointment['reason']); ?></td>
                            <td class="py-3 px-6 text-left">
                                <?php
                                $status_class = '';
                                switch ($appointment['status']) {
                                    case 'Scheduled': $status_class = 'bg-blue-200 text-blue-800'; break;
                                    case 'Completed': $status_class = 'bg-green-200 text-green-800'; break;
                                    case 'Cancelled': $status_class = 'bg-red-200 text-red-800'; break;
                                }
                                echo '<span class="relative inline-block px-3 py-1 font-semibold leading-tight ' . $status_class . ' rounded-full">' . htmlspecialchars($appointment['status']) . '</span>';
                                ?>
                            </td>
                            <td class="py-3 px-6 text-center">
                                <div class="flex item-center justify-center">
                                    <a href="<?php echo BASE_URL; ?>/appointments/schedule.php?edit_id=<?php echo $appointment['appointment_id']; ?>" class="w-4 mr-2 transform hover:text-purple-500 hover:scale-110" title="Edit">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.5L15.232 5.232z"></path></svg>
                                    </a>
                                    <a href="<?php echo BASE_URL; ?>/appointments/process_appointment.php?delete_id=<?php echo $appointment['appointment_id']; ?>" onclick="return confirm('Are you sure you want to delete this appointment?');" class="w-4 mr-2 transform hover:text-red-500 hover:scale-110" title="Delete">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
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

<?php include_once __DIR__ . '/../includes/footer.php'; ?>
