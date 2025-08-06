<?php
// Include FPDF library manually
// You will need to download FPDF and place it in the specified path.
// For example, if you place the fpdf.php file in 'nutritionist-system/includes/fpdf/fpdf.php'
require_once __DIR__ . '/../includes/fpdf/fpdf.php';

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/auth.php';


requireLogin(); // Ensure user is logged in to download

// Get selected date and search term from GET parameters
$selected_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';

// Ensure the selected date is a Saturday (optional, but good for validation)
if (date('N', strtotime($selected_date)) != 6) { // 6 = Saturday
    $selected_date = date('Y-m-d', strtotime('last Saturday', strtotime($selected_date)));
}

// Prepare search conditions for fetching patients
$searchCondition = '';
$searchParams = [];

if (!empty($searchTerm)) {
    $searchCondition = " WHERE full_name LIKE ? OR patient_unique_id LIKE ? ";
    $searchParams = ["%$searchTerm%", "%$searchTerm%"];
}

// Fetch all patients matching search criteria
$patients_sql = "SELECT patient_id, full_name, patient_unique_id, phone FROM patients" . $searchCondition . " ORDER BY full_name ASC";
$patients_stmt = $pdo->prepare($patients_sql);
$patients_stmt->execute($searchParams);
$all_patients = $patients_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch attendance records for the selected date
$attendance_records = [];
if (!empty($all_patients)) {
    $attended_stmt = $pdo->prepare("SELECT patient_id FROM saturday_attendance WHERE attendance_date = ? AND status = 'Present'");
    $attended_stmt->execute([$selected_date]);
    $attended_patient_ids = $attended_stmt->fetchAll(PDO::FETCH_COLUMN);
    $attendance_records = array_flip($attended_patient_ids); // Flip for easy lookup
}

// --- FPDF Generation ---
// Create new PDF document
$pdf = new FPDF();
$pdf->AddPage();

// Title
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, 'Saturday Attendance Report', 0, 1, 'C');
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 10, 'Date: ' . date('l, F j, Y', strtotime($selected_date)), 0, 1, 'C');
if (!empty($searchTerm)) {
    $pdf->Cell(0, 10, 'Search Term: ' . htmlspecialchars($searchTerm), 0, 1, 'C');
}
$pdf->Ln(10);

// Table Header
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(40, 7, 'Client ID', 1, 0, 'C');
$pdf->Cell(80, 7, 'Full Name', 1, 0, 'C');
$pdf->Cell(40, 7, 'Phone', 1, 0, 'C');
$pdf->Cell(30, 7, 'Status', 1, 1, 'C'); // Last cell, new line

// Table Data
$pdf->SetFont('Arial', '', 10);
if (!empty($all_patients)) {
    foreach ($all_patients as $patient) {
        $status = isset($attendance_records[$patient['patient_id']]) ? 'Present' : 'Absent';
        $pdf->Cell(40, 7, htmlspecialchars($patient['patient_unique_id']), 1, 0, 'L');
        $pdf->Cell(80, 7, htmlspecialchars($patient['full_name']), 1, 0, 'L');
        $pdf->Cell(40, 7, htmlspecialchars($patient['phone'] ?? 'N/A'), 1, 0, 'L');
        $pdf->Cell(30, 7, $status, 1, 1, 'C'); // Last cell, new line
    }
} else {
    $pdf->Cell(190, 7, 'No clients found for this date or search criteria.', 1, 1, 'C');
}

// Close and output PDF document
$pdf->Output('D', 'Saturday_Attendance_' . $selected_date . '.pdf'); // 'D' for download
?>
