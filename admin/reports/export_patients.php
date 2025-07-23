<?php
// admin/reports/export_patients.php - Exports patient data to CSV

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/db_connect.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php'; // For calculateAgeFromDob

requireLogin();
// Optional: Add role-based access control if only certain roles can export reports
// if (!hasRole('Admin')) {
//     $_SESSION['error_message'] = "Access denied. You do not have permission to export patient reports.";
//     header('Location: ' . BASE_URL . 'admin/reports/report.php');
//     exit();
// }

try {
    $stmt = $pdo->query("SELECT patient_unique_id, full_name, date_of_birth, gender, height_cm, address, email, phone, health_conditions, membership_status, created_at FROM patients ORDER BY full_name ASC");
    $patients = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Set headers for CSV download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="patient_report_' . date('Y-m-d') . '.csv"');

    // Create a file pointer connected to the output stream
    $output = fopen('php://output', 'w');

    // Output the column headings
    fputcsv($output, ['Patient ID', 'Full Name', 'Date of Birth', 'Gender', 'Height (cm)', 'Address', 'Email', 'Phone', 'Health Conditions', 'Membership Status', 'Created At']);

    // Loop through the patients and output each row
    if (!empty($patients)) {
        foreach ($patients as $patient) {
            // Format date_of_birth and created_at if necessary
            $patient['date_of_birth'] = $patient['date_of_birth'] ? date('Y-m-d', strtotime($patient['date_of_birth'])) : '';
            $patient['created_at'] = $patient['created_at'] ? date('Y-m-d H:i:s', strtotime($patient['created_at'])) : '';
            fputcsv($output, $patient);
        }
    }
    
    fclose($output);
    exit();

} catch (PDOException $e) {
    error_log("ERROR: Could not export patient report: " . $e->getMessage());
    $_SESSION['error_message'] = "Error exporting patient report. Please try again later.";
    header('Location: ' . BASE_URL . 'reports/report.php');
    exit();
}
?>
