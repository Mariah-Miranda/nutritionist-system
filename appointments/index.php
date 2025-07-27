<?php
// appointments/index.php - Main Appointment Management Dashboard

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db_connect.php'; // This provides $pdo
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php'; // For sanitizeInput if needed, and date functions

// Include header
$pageTitle = "Appointment Management";
include_once __DIR__ . '/../includes/header.php';

// Determine the current view
$view = $_GET['view'] ?? 'today'; // Default to today's view

// --- Fetch Data for Today's Appointments ---
$today = date('Y-m-d');
$todayAppointments = [];
if ($view === 'today' || $view === 'weekly') {
    $sqlTodayAppointments = "SELECT a.appointment_id, a.appointment_date, a.appointment_time, a.reason, a.status,
                                    p.full_name AS patient_name, p.phone, p.email
                             FROM appointments a
                             JOIN patients p ON a.patient_id = p.patient_id
                             WHERE a.appointment_date = :today
                             ORDER BY a.appointment_time ASC";
    try {
        $stmtToday = $pdo->prepare($sqlTodayAppointments);
        $stmtToday->bindParam(':today', $today);
        $stmtToday->execute();
        $todayAppointments = $stmtToday->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("ERROR: Could not fetch today's appointments: " . $e->getMessage());
        // Show detailed error for debugging
        $_SESSION['error_message'] = "Error fetching today's appointments. Details: " . $e->getMessage();
    }
}


// --- Fetch Data for Upcoming Appointments (Next 7 Days) ---
$next7Days = date('Y-m-d', strtotime('+7 days'));
$upcomingAppointments = [];
if ($view === 'upcoming' || $view === 'weekly') {
    $sqlUpcomingAppointments = "SELECT a.appointment_id, a.appointment_date, a.appointment_time, a.reason, a.status,
                                       p.full_name AS patient_name
                                FROM appointments a
                                JOIN patients p ON a.patient_id = p.patient_id
                                WHERE a.appointment_date > :today AND a.appointment_date <= :next7days
                                ORDER BY a.appointment_date ASC, a.appointment_time ASC";
    try {
        $stmtUpcoming = $pdo->prepare($sqlUpcomingAppointments);
        $stmtUpcoming->bindParam(':today', $today);
        $stmtUpcoming->bindParam(':next7days', $next7Days);
        $stmtUpcoming->execute();
        $upcomingAppointments = $stmtUpcoming->fetchAll(PDO::FETCH_ASSOC);

        // Group upcoming appointments by date for display
        $groupedUpcomingAppointments = [];
        foreach ($upcomingAppointments as $appt) {
            $groupedUpcomingAppointments[$appt['appointment_date']][] = $appt;
        }

    } catch (PDOException $e) {
        error_log("ERROR: Could not fetch upcoming appointments: " . $e->getMessage());
        $_SESSION['error_message'] = "Error fetching upcoming appointments. Details: " . $e->getMessage();
    }
}


// --- Calculate Today's Summary ---
$totalAppointmentsToday = 0;
$completedAppointmentsToday = 0;
$remainingAppointmentsToday = 0;
$cancelledAppointmentsToday = 0;

$sqlSummary = "SELECT status, COUNT(*) as count FROM appointments WHERE appointment_date = :today GROUP BY status";
try {
    $stmtSummary = $pdo->prepare($sqlSummary);
    $stmtSummary->bindParam(':today', $today);
    $stmtSummary->execute();
    $summaryData = $stmtSummary->fetchAll(PDO::FETCH_ASSOC);

    foreach ($summaryData as $row) {
        $totalAppointmentsToday += $row['count'];
        if ($row['status'] === 'Completed') {
            $completedAppointmentsToday = $row['count'];
        } elseif ($row['status'] === 'Scheduled') {
            $remainingAppointmentsToday = $row['count'];
        } elseif ($row['status'] === 'Cancelled') {
            $cancelledAppointmentsToday = $row['count'];
        }
    }
} catch (PDOException $e) {
    error_log("ERROR: Could not fetch appointment summary: " . $e->getMessage());
    $_SESSION['error_message'] = "Error calculating appointment summary. Details: " . $e->getMessage();
}

?>

<div class="flex flex-col lg:flex-row gap-8">
    <!-- Main Content Column -->
    <div class="flex-1">
        <!-- Top Action Bar -->
        <div class="bg-white rounded-lg shadow-md p-4 mb-6 flex justify-between items-center">
            <div class="flex space-x-2">
                <a href="?view=today" class="px-4 py-2 rounded-lg <?php echo ($view === 'today') ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-700'; ?> font-semibold hover:bg-blue-600 hover:text-white transition-colors duration-200">
                    Today's Schedule
                </a>
                <a href="?view=upcoming" class="px-4 py-2 rounded-lg <?php echo ($view === 'upcoming') ? 'bg-green-500 text-white' : 'bg-gray-200 text-gray-700'; ?> font-semibold hover:bg-green-600 hover:text-white transition-colors duration-200">
                    Upcoming
                </a>
                <a href="?view=weekly" class="px-4 py-2 rounded-lg <?php echo ($view === 'weekly') ? 'bg-purple-500 text-white' : 'bg-gray-200 text-gray-700'; ?> font-semibold hover:bg-purple-600 hover:text-white transition-colors duration-200">
                    Weekly View
                </a>
            </div>
            <div class="flex space-x-3">
                <a href="<?php echo BASE_URL; ?>calendar.php" class="px-4 py-2 rounded-lg bg-blue-600 text-white font-semibold hover:bg-blue-700 transition-colors duration-200 flex items-center space-x-2">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Calendar View</span>
                </a>
                <a href="<?php echo BASE_URL; ?>schedule.php" class="px-4 py-2 rounded-lg bg-green-600 text-white font-semibold hover:bg-green-700 transition-colors duration-200 flex items-center space-x-2">
                    <i class="fas fa-plus"></i>
                    <span>Schedule Appointment</span>
                </a>
            </div>
        </div>

        <?php
        // Display success or error messages from session
        if (isset($_SESSION['success_message'])) {
            echo '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">' . htmlspecialchars($_SESSION['success_message']) . '</div>';
            unset($_SESSION['success_message']);
        }
        if (isset($_SESSION['error_message'])) {
            echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">' . htmlspecialchars($_SESSION['error_message']) . '</div>';
            unset($_SESSION['error_message']);
        }
        ?>

        <!-- Today's Appointments Section -->
        <?php if ($view === 'today' || $view === 'weekly'): ?>
        <div id="today-schedule" class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-semibold text-gray-800">Today's Appointments</h3>
                <span class="text-gray-500"><?php echo date('F j, Y'); ?></span>
            </div>
            <div class="space-y-4">
                <?php if (!empty($todayAppointments)): ?>
                    <?php foreach ($todayAppointments as $appointment): ?>
                        <div class="border rounded-lg p-4 flex items-center justify-between shadow-sm
                            <?php
                                switch ($appointment['status']) {
                                    case 'Scheduled': echo 'border-blue-300 bg-blue-50'; break;
                                    case 'Completed': echo 'border-green-300 bg-green-50'; break;
                                    case 'Cancelled': echo 'border-red-300 bg-red-50'; break;
                                    default: echo 'border-gray-300 bg-gray-50'; break;
                                }
                            ?>">
                            <div class="flex items-center space-x-4">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center text-white font-bold
                                    <?php
                                        $initials = strtoupper(substr($appointment['patient_name'], 0, 1));
                                        $colors = ['bg-blue-500', 'bg-green-500', 'bg-purple-500', 'bg-red-500', 'bg-yellow-500'];
                                        echo $colors[array_sum(array_map('ord', str_split($initials))) % count($colors)];
                                    ?>">
                                    <?php echo $initials; ?>
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-800"><?php echo htmlspecialchars($appointment['patient_name']); ?></p>
                                    <p class="text-sm text-gray-600"><?php echo htmlspecialchars($appointment['reason']); ?></p>
                                    <p class="text-xs text-gray-500"><?php echo htmlspecialchars(date('h:i A', strtotime($appointment['appointment_time']))); ?></p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-3">
                                <span class="px-3 py-1 rounded-full text-xs font-semibold
                                    <?php
                                        switch ($appointment['status']) {
                                            case 'Scheduled': echo 'bg-blue-200 text-blue-800'; break;
                                            case 'Completed': echo 'bg-green-200 text-green-800'; break;
                                            case 'Cancelled': echo 'bg-red-200 text-red-800'; break;
                                            default: echo 'bg-gray-200 text-gray-800'; break;
                                        }
                                    ?>">
                                    <?php echo htmlspecialchars($appointment['status']); ?>
                                </span>
                                <div class="relative group">
                                    <button class="text-gray-500 hover:text-gray-700 focus:outline-none">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <div class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-10 hidden group-hover:block">
                                        <a href="<?php echo BASE_URL; ?>appointments/schedule.php?edit_id=<?php echo $appointment['appointment_id']; ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Edit</a>
                                        <a href="<?php echo BASE_URL; ?>appointments/process_appointment.php?delete_id=<?php echo $appointment['appointment_id']; ?>" onclick="return confirm('Are you sure you want to delete this appointment?');" class="block px-4 py-2 text-sm text-red-700 hover:bg-gray-100">Delete</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-gray-500 text-center py-4">No appointments scheduled for today.</p>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Upcoming Appointments Section -->
        <?php if ($view === 'upcoming' || $view === 'weekly'): ?>
        <div id="upcoming-schedule" class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-xl font-semibold text-gray-800 mb-4">Upcoming Appointments (Next 7 Days)</h3>
            <div class="space-y-4">
                <?php if (!empty($groupedUpcomingAppointments)): ?>
                    <?php foreach ($groupedUpcomingAppointments as $date => $appointmentsOnDate): ?>
                        <div class="border rounded-lg p-4 shadow-sm bg-gray-50">
                            <div class="flex justify-between items-center mb-2">
                                <h4 class="font-semibold text-gray-700"><?php echo date('F j, Y', strtotime($date)); ?></h4>
                                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">
                                    <?php echo count($appointmentsOnDate); ?> appointment<?php echo (count($appointmentsOnDate) > 1) ? 's' : ''; ?>
                                </span>
                            </div>
                            <ul class="space-y-2">
                                <?php foreach ($appointmentsOnDate as $appt): ?>
                                    <li class="flex items-center justify-between text-sm text-gray-600">
                                        <span>
                                            <span class="font-medium"><?php echo htmlspecialchars(date('h:i A', strtotime($appt['appointment_time']))); ?></span> -
                                            <?php echo htmlspecialchars($appt['patient_name']); ?> (<?php echo htmlspecialchars($appt['reason']); ?>)
                                        </span>
                                        <?php if ($appt['status'] === 'Scheduled' && strtotime($appt['appointment_date']) <= strtotime('+2 days')): ?>
                                            <span class="px-2 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">Reminder Due</span>
                                        <?php endif; ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-gray-500 text-center py-4">No upcoming appointments in the next 7 days.</p>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

    </div>

    <!-- Right Sidebar Column -->
    <div class="w-full lg:w-80 space-y-6">
        <!-- Today's Summary -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-xl font-semibold text-gray-800 mb-4">Today's Summary</h3>
            <ul class="space-y-2 text-gray-700">
                <li class="flex justify-between">
                    <span>Total Appointments:</span>
                    <span class="font-medium"><?php echo $totalAppointmentsToday; ?></span>
                </li>
                <li class="flex justify-between">
                    <span>Completed:</span>
                    <span class="font-medium text-green-600"><?php echo $completedAppointmentsToday; ?></span>
                </li>
                <li class="flex justify-between">
                    <span>Remaining:</span>
                    <span class="font-medium text-blue-600"><?php echo $remainingAppointmentsToday; ?></span>
                </li>
                <li class="flex justify-between">
                    <span>Cancelled:</span>
                    <span class="font-medium text-red-600"><?php echo $cancelledAppointmentsToday; ?></span>
                </li>
            </ul>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-xl font-semibold text-gray-800 mb-4">Quick Actions</h3>
            <div class="space-y-3">
                <a href="<?php echo BASE_URL; ?>schedule.php" class="flex items-center space-x-3 p-3 rounded-lg bg-green-100 text-green-700 font-medium hover:bg-green-200 transition-colors duration-200">
                    <i class="fas fa-plus-circle text-lg"></i>
                    <span>New Appointment</span>
                </a>
                <a href="<?php echo BASE_URL; ?>reschedule.php" class="flex items-center space-x-3 p-3 rounded-lg bg-blue-100 text-blue-700 font-medium hover:bg-blue-200 transition-colors duration-200">
                    <i class="fas fa-calendar-check text-lg"></i>
                    <span>Reschedule</span>
                </a>
                <a href="<?php echo BASE_URL; ?>send_reminders.php" class="flex items-center space-x-3 p-3 rounded-lg bg-yellow-100 text-yellow-700 font-medium hover:bg-yellow-200 transition-colors duration-200">
                    <i class="fas fa-bell text-lg"></i>
                    <span>Send Reminders</span>
                </a>
                <a href="<?php echo BASE_URL; ?>export_schedule.php" class="flex items-center space-x-3 p-3 rounded-lg bg-purple-100 text-purple-700 font-medium hover:bg-purple-200 transition-colors duration-200">
                    <i class="fas fa-file-export text-lg"></i>
                    <span>Export Schedule</span>
                </a>
            </div>
        </div>
    </div>
</div>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>
