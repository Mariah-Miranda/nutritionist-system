<?php
// appointments/export_schedule.php - Export schedule to CSV

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db_connect.php'; 
require_once __DIR__ . '/../includes/auth.php';

try {
    // Fetch all appointments
    $sql = "SELECT a.appointment_date, a.appointment_time, p.full_name, a.reason, a.status 
            FROM appointments a 
            JOIN patients p ON a.patient_id = p.patient_id 
            ORDER BY a.appointment_date, a.appointment_time";
    $stmt = $pdo->query($sql);
    $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Set headers for CSV download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="appointment_schedule_' . date('Y-m-d') . '.csv"');

    // Create a file pointer connected to the output stream
    $output = fopen('php://output', 'w');

    // Output the column headings
    fputcsv($output, ['Date', 'Time', 'Patient Name', 'Reason', 'Status']);

    // Loop through the appointments and output each row
    if (!empty($appointments)) {
        foreach ($appointments as $appointment) {
            fputcsv($output, $appointment);
        }
    }
    
    fclose($output);
    exit();

} catch (PDOException $e) {
    error_log("ERROR: Could not export schedule: " . $e->getMessage());
    // If there's an error, you might want to redirect back with a message
    $_SESSION['error_message'] = "Could not export the schedule.";
    header('Location: ' . BASE_URL . 'appointments/');
    exit();
}
