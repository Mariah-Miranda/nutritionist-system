<?php
// patients/process_patient.php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php'; // For general utility functions like sanitization
require_once __DIR__ . '/../ai/recommendation_engine.php'; // Include the AI recommendation engine

// Ensure only POST requests are processed
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . 'patients/add.php?message=Invalid request method.');
    exit();
}

// Require login for this page (e.g., Nutritionist or Admin)
requireLogin();
// Uncomment and refine roles as needed
// if (!hasAnyRole(['Admin', 'Nutritionist'])) {
//     header('Location: ' . BASE_URL . 'admin/index.php?message=Access denied. You do not have permission to process patient data.');
//     exit();
// }

$message = '';
$redirectUrl = BASE_URL . 'patients/add.php'; // Default redirect back to add page on error
$patient_id_for_redirect = null; // To store the patient_id for redirection

try {
    // Determine if it's an ADD or EDIT operation
    $patient_id = filter_var($_POST['patient_id'] ?? null, FILTER_VALIDATE_INT);
    $is_edit_mode = ($patient_id !== false && $patient_id > 0);

    // If in edit mode, set patient_id_for_redirect
    if ($is_edit_mode) {
        $patient_id_for_redirect = $patient_id;
    }


    // 1. Sanitize and Validate Input
    $full_name = sanitizeInput($_POST['full_name'] ?? '');
    $date_of_birth = sanitizeInput($_POST['date_of_birth'] ?? null);
    $gender = sanitizeInput($_POST['gender'] ?? '');
    $height_cm = filter_var($_POST['height_cm'] ?? null, FILTER_VALIDATE_FLOAT);
    $weight_kg = filter_var($_POST['weight_kg'] ?? null, FILTER_VALIDATE_FLOAT);
    $systolic_bp = filter_var($_POST['systolic_bp'] ?? null, FILTER_VALIDATE_INT);
    $diastolic_bp = filter_var($_POST['diastolic_bp'] ?? null, FILTER_VALIDATE_INT);
    $blood_sugar_level_mg_dL = filter_var($_POST['blood_sugar_level_mg_dL'] ?? null, FILTER_VALIDATE_FLOAT);
    $blood_sugar_fasting_status = sanitizeInput($_POST['blood_sugar_fasting_status'] ?? '');
    $address = sanitizeInput($_POST['address'] ?? '');
    $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
    $phone = sanitizeInput($_POST['phone'] ?? '');
    $health_conditions = sanitizeInput($_POST['health_conditions'] ?? '');
    $membership_status = sanitizeInput($_POST['membership_status'] ?? 'No Membership');

    // Basic validation
    if (empty($full_name)) {
        throw new Exception('Full Name is required.');
    }
    if ($email === false && !empty($_POST['email'])) {
        throw new Exception('Invalid email format.');
    }
    if (!empty($date_of_birth) && !preg_match("/^\d{4}-\d{2}-\d{2}$/", $date_of_birth)) {
        throw new Exception('Invalid Date of Birth format. Please use YYYY-MM-DD.');
    }

    // 2. Calculate BMI (server-side for robustness)
    $bmi = null;
    if ($height_cm > 0 && $weight_kg > 0) {
        $height_m = $height_cm / 100;
        $bmi = $weight_kg / ($height_m * $height_m);
    }

    $pdo->beginTransaction(); // Start transaction

    if ($is_edit_mode) {
        // UPDATE existing patient
        $stmt = $pdo->prepare("UPDATE patients SET
                                full_name = :full_name,
                                date_of_birth = :date_of_birth,
                                gender = :gender,
                                height_cm = :height_cm,
                                address = :address,
                                email = :email,
                                phone = :phone,
                                health_conditions = :health_conditions,
                                membership_status = :membership_status,
                                updated_at = CURRENT_TIMESTAMP
                                WHERE patient_id = :patient_id");

        $stmt->bindParam(':patient_id', $patient_id, PDO::PARAM_INT);
        $stmt->bindParam(':full_name', $full_name);
        $stmt->bindParam(':date_of_birth', $date_of_birth);
        $stmt->bindParam(':gender', $gender);
        $stmt->bindParam(':height_cm', $height_cm);
        $stmt->bindParam(':address', $address);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':health_conditions', $health_conditions);
        $stmt->bindParam(':membership_status', $membership_status);
        $stmt->execute();

        $message = 'Patient updated successfully!';

        // For health metrics, we insert a NEW record to keep history, not update the old one
        // Only insert if relevant health data is provided/changed
        if ($weight_kg > 0 || $bmi !== null || $systolic_bp > 0 || $diastolic_bp > 0 || $blood_sugar_level_mg_dL > 0) {
            $stmt_metrics = $pdo->prepare("INSERT INTO patient_health_metrics (patient_id, record_date, weight_kg, bmi, systolic_bp, diastolic_bp, blood_sugar_level_mg_dL, blood_sugar_fasting_status)
                                           VALUES (:patient_id, NOW(), :weight_kg, :bmi, :systolic_bp, :diastolic_bp, :blood_sugar_level_mg_dL, :blood_sugar_fasting_status)");

            $stmt_metrics->bindParam(':patient_id', $patient_id, PDO::PARAM_INT);
            $stmt_metrics->bindParam(':weight_kg', $weight_kg);
            $stmt_metrics->bindParam(':bmi', $bmi);
            $stmt_metrics->bindParam(':systolic_bp', $systolic_bp, PDO::PARAM_INT);
            $stmt_metrics->bindParam(':diastolic_bp', $diastolic_bp, PDO::PARAM_INT);
            $stmt_metrics->bindParam(':blood_sugar_level_mg_dL', $blood_sugar_level_mg_dL);
            $stmt_metrics->bindParam(':blood_sugar_fasting_status', $blood_sugar_fasting_status);
            $stmt_metrics->execute();
        }

    } else {
        // INSERT new patient
        $patient_unique_id = 'PAT-' . date('Ymd') . '-' . uniqid();

        $stmt = $pdo->prepare("INSERT INTO patients (patient_unique_id, full_name, date_of_birth, gender, height_cm, address, email, phone, health_conditions, membership_status)
                               VALUES (:patient_unique_id, :full_name, :date_of_birth, :gender, :height_cm, :address, :email, :phone, :health_conditions, :membership_status)");

        $stmt->bindParam(':patient_unique_id', $patient_unique_id);
        $stmt->bindParam(':full_name', $full_name);
        $stmt->bindParam(':date_of_birth', $date_of_birth);
        $stmt->bindParam(':gender', $gender);
        $stmt->bindParam(':height_cm', $height_cm);
        $stmt->bindParam(':address', $address);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':health_conditions', $health_conditions);
        $stmt->bindParam(':membership_status', $membership_status);

        $stmt->execute();
        $new_patient_id = $pdo->lastInsertId(); // Get the auto-generated patient_id
        $patient_id_for_redirect = $new_patient_id; // Set patient_id for redirection

        // Insert Initial Health Metrics
        if ($weight_kg > 0 || $bmi !== null || $systolic_bp > 0 || $diastolic_bp > 0 || $blood_sugar_level_mg_dL > 0) {
            $stmt_metrics = $pdo->prepare("INSERT INTO patient_health_metrics (patient_id, record_date, weight_kg, bmi, systolic_bp, diastolic_bp, blood_sugar_level_mg_dL, blood_sugar_fasting_status)
                                           VALUES (:patient_id, NOW(), :weight_kg, :bmi, :systolic_bp, :diastolic_bp, :blood_sugar_level_mg_dL, :blood_sugar_fasting_status)");

            $stmt_metrics->bindParam(':patient_id', $new_patient_id, PDO::PARAM_INT);
            $stmt_metrics->bindParam(':weight_kg', $weight_kg);
            $stmt_metrics->bindParam(':bmi', $bmi);
            $stmt_metrics->bindParam(':systolic_bp', $systolic_bp, PDO::PARAM_INT);
            $stmt_metrics->bindParam(':diastolic_bp', $diastolic_bp, PDO::PARAM_INT);
            $stmt_metrics->bindParam(':blood_sugar_level_mg_dL', $blood_sugar_level_mg_dL);
            $stmt_metrics->bindParam(':blood_sugar_fasting_status', $blood_sugar_fasting_status);

            $stmt_metrics->execute();
        }

        $message = 'Patient added successfully!';
        $redirectUrl = BASE_URL . 'patients/list.php?message=' . urlencode($message); // Redirect to patient list
    }

    $pdo->commit(); // Commit the transaction

    // --- AI Recommendation Integration ---
    // Fetch the patient's full data (personal + latest metrics) after save/update
    // This ensures we have the most current data for recommendations
    $currentPatientData = [];
    $currentLatestMetrics = [];

    if ($patient_id_for_redirect) {
        $stmt_current_patient = $pdo->prepare("SELECT * FROM patients WHERE patient_id = :patient_id");
        $stmt_current_patient->bindParam(':patient_id', $patient_id_for_redirect, PDO::PARAM_INT);
        $stmt_current_patient->execute();
        $currentPatientData = $stmt_current_patient->fetch(PDO::FETCH_ASSOC);

        $stmt_current_metrics = $pdo->prepare("SELECT * FROM patient_health_metrics WHERE patient_id = :patient_id ORDER BY record_date DESC LIMIT 1");
        $stmt_current_metrics->bindParam(':patient_id', $patient_id_for_redirect, PDO::PARAM_INT);
        $stmt_current_metrics->execute();
        $currentLatestMetrics = $stmt_current_metrics->fetch(PDO::FETCH_ASSOC);
    }

    // Generate recommendations
    $aiRecommendations = [];
    if (!empty($currentPatientData) && !empty($currentLatestMetrics)) {
        $aiRecommendations = getAIRecommendations($currentPatientData, $currentLatestMetrics);
    }

    // Store recommendations in session to pass to the view page
    if (!empty($aiRecommendations)) {
        $_SESSION['ai_recommendations'] = $aiRecommendations;
    } else {
        unset($_SESSION['ai_recommendations']); // Clear if no recommendations
    }

    // Set the final redirect URL to the patient's view page
    $redirectUrl = BASE_URL . 'patients/view.php?id=' . $patient_id_for_redirect . '&message=' . urlencode($message);


} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack(); // Rollback on error
    }
    $message = 'Error processing patient data: ' . $e->getMessage();
    // Redirect back with error message and form data to re-fill
    // For edit mode, redirect back to edit page with ID
    $redirectUrl = BASE_URL . 'patients/' . ($is_edit_mode ? 'edit.php?id=' . $patient_id : 'add.php') . '?message=' . urlencode($message) . '&' . http_build_query($_POST);
}

// Redirect with message
header('Location: ' . $redirectUrl);
exit();
?>
