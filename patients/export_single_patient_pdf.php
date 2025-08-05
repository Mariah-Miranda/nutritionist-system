<?php
ob_start();

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
requireLogin();

require_once __DIR__ . '/../vendor/autoload.php'; // TCPDF via Composer

$patient_id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
if (!$patient_id) {
    die('Invalid patient ID.');
}

try {
    // Fetch patient info
    $stmt = $pdo->prepare("SELECT * FROM patients WHERE patient_id = ?");
    $stmt->execute([$patient_id]);
    $patient = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$patient) {
        die('Patient not found.');
    }

    // Fetch latest health metrics
    $stmt_metrics = $pdo->prepare("SELECT * FROM patient_health_metrics WHERE patient_id = ? ORDER BY record_date DESC LIMIT 1");
    $stmt_metrics->execute([$patient_id]);
    $latestMetrics = $stmt_metrics->fetch(PDO::FETCH_ASSOC);

    // Initialize PDF
    $pdf = new TCPDF();
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('Your App Name');
    $pdf->SetTitle('Client Profile - ' . $patient['full_name']);
    $pdf->SetMargins(10, 10, 10);
    $pdf->AddPage();

    // Build HTML for PDF
    $html = '<h1 style="text-align:center;">Client Profile</h1>';
    $html .= '<h2>' . htmlspecialchars($patient['full_name']) . '</h2>';
    $html .= '<table border="1" cellpadding="5" cellspacing="0" width="100%">';
    
    // Personal info rows
    $html .= '<tr><td><strong>Client ID</strong></td><td>' . htmlspecialchars($patient['patient_unique_id']) . '</td></tr>';
    $html .= '<tr><td><strong>Date of Birth</strong></td><td>' . htmlspecialchars($patient['date_of_birth'] ?? 'N/A') . '</td></tr>';
    $html .= '<tr><td><strong>Age</strong></td><td>' . htmlspecialchars(calculateAgeFromDob($patient['date_of_birth']) ?? 'N/A') . '</td></tr>';
    $html .= '<tr><td><strong>Gender</strong></td><td>' . htmlspecialchars($patient['gender'] ?? 'N/A') . '</td></tr>';
    $html .= '<tr><td><strong>Email</strong></td><td>' . htmlspecialchars($patient['email'] ?? 'N/A') . '</td></tr>';
    $html .= '<tr><td><strong>Phone</strong></td><td>' . htmlspecialchars($patient['phone'] ?? 'N/A') . '</td></tr>';
    $html .= '<tr><td><strong>Address</strong></td><td>' . nl2br(htmlspecialchars($patient['address'] ?? 'N/A')) . '</td></tr>';
    $html .= '<tr><td><strong>Health Conditions</strong></td><td>' . nl2br(htmlspecialchars($patient['health_conditions'] ?? 'N/A')) . '</td></tr>';
    $html .= '<tr><td><strong>Membership</strong></td><td>' . htmlspecialchars($patient['membership_status'] ?? 'N/A') . '</td></tr>';

    $html .= '</table><br>';

    // Latest health metrics
    $html .= '<h3>Latest Health Metrics</h3>';
    if ($latestMetrics) {
        $html .= '<table border="1" cellpadding="5" cellspacing="0" width="100%">';
        $html .= '<tr><td><strong>Recorded On</strong></td><td>' . htmlspecialchars(date('Y-m-d H:i', strtotime($latestMetrics['record_date']))) . '</td></tr>';
        $html .= '<tr><td><strong>Height</strong></td><td>' . htmlspecialchars($latestMetrics['height_cm'] ?? 'N/A') . ' cm</td></tr>';
        $html .= '<tr><td><strong>Weight</strong></td><td>' . htmlspecialchars($latestMetrics['weight_kg'] ?? 'N/A') . ' kg</td></tr>';
        $html .= '<tr><td><strong>BMI</strong></td><td>' . htmlspecialchars($latestMetrics['bmi'] ?? 'N/A') . '</td></tr>';
        $html .= '<tr><td><strong>Blood Pressure</strong></td><td>' . htmlspecialchars($latestMetrics['systolic_bp'] ?? 'N/A') . '/' . htmlspecialchars($latestMetrics['diastolic_bp'] ?? 'N/A') . ' mmHg</td></tr>';
        $html .= '<tr><td><strong>Blood Sugar</strong></td><td>' . htmlspecialchars($latestMetrics['blood_sugar_level_mg_dL'] ?? 'N/A') . ' mg/dL (' . htmlspecialchars($latestMetrics['blood_sugar_fasting_status'] ?? 'N/A') . ')</td></tr>';
        $html .= '</table>';
    } else {
        $html .= '<p>No health metrics recorded for this client yet.</p>';
    }

    $pdf->writeHTML($html, true, false, true, false, '');

    $pdf->Output('Client_Profile_' . preg_replace('/[^a-zA-Z0-9]/', '_', $patient['full_name']) . '.pdf', 'D');

    ob_end_flush();
    exit();

} catch (PDOException $e) {
    die("Error generating PDF: " . $e->getMessage());
}
