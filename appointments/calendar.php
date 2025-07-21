<?php
// appointments/calendar.php - Full Calendar View

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/auth.php';

// Fetch all appointments to display on the calendar
$events = [];
$sql = "SELECT 
            a.appointment_id, 
            p.full_name as title, 
            a.appointment_date as start,
            a.status 
        FROM appointments a 
        JOIN patients p ON a.patient_id = p.patient_id";

try {
    $stmt = $pdo->query($sql);
    $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($appointments as $appointment) {
        $color = '#3b82f6'; // Default blue for scheduled
        if ($appointment['status'] === 'Completed') {
            $color = '#16a34a'; // Green
        } elseif ($appointment['status'] === 'Cancelled') {
            $color = '#dc2626'; // Red
        }
        
        $events[] = [
            'title' => $appointment['title'],
            'start' => $appointment['start'],
            'color' => $color,
            'url' => BASE_URL . 'appointments/schedule.php?edit_id=' . $appointment['appointment_id']
        ];
    }

} catch (PDOException $e) {
    error_log("ERROR: Could not fetch appointments for calendar: " . $e->getMessage());
    $_SESSION['error_message'] = "Error fetching calendar data.";
    // Redirect or display an error, but for simplicity, we'll just have an empty calendar.
}


$pageTitle = "Calendar View";
include_once __DIR__ . '/../includes/header.php';
?>

<!-- Include FullCalendar CSS and JS from a CDN -->
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>

<div class="bg-white rounded-lg shadow-md p-6">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-2xl font-bold text-gray-800">Appointment Calendar</h2>
        <a href="<?php echo BASE_URL; ?>appointments/" class="px-4 py-2 rounded-lg bg-blue-600 text-white font-semibold hover:bg-blue-700 transition-colors duration-200">
            <i class="fas fa-arrow-left mr-2"></i>
            Back to Dashboard
        </a>
    </div>

    <div id='calendar'></div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
      initialView: 'dayGridMonth',
      headerToolbar: {
        left: 'prev,next today',
        center: 'title',
        right: 'dayGridMonth,timeGridWeek,timeGridDay'
      },
      events: <?php echo json_encode($events); ?>,
      eventClick: function(info) {
        info.jsEvent.preventDefault(); // don't let the browser navigate
        if (info.event.url) {
          window.location.href = info.event.url;
        }
      }
    });
    calendar.render();
  });
</script>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>
