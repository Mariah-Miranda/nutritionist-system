<?php
// appointments/process_appointment.php - Handles creating, updating, and deleting appointments

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db_connect.php'; // This provides $pdo
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

// --- DELETE APPOINTMENT ---
if (isset($_GET['delete_id']) && !empty($_GET['delete_id'])) {
    $appointment_id = $_GET['delete_id'];

    // Prepare and execute the delete statement
    $sql = "DELETE FROM appointments WHERE appointment_id = :appointment_id";
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':appointment_id', $appointment_id, PDO::PARAM_INT);
        $stmt->execute();

        $_SESSION['success_message'] = "Appointment deleted successfully.";

    } catch (PDOException $e) {
        error_log("ERROR: Could not delete appointment: " . $e->getMessage());
        $_SESSION['error_message'] = "Error deleting appointment. Please try again.";
    }

    // Redirect back to the main appointments page
    header("Location: " . BASE_URL . "appointments/");
    exit();
}

// --- CREATE OR UPDATE APPOINTMENT ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and retrieve form data
    $patient_id = sanitizeInput($_POST['patient_id']);
    $appointment_date = sanitizeInput($_POST['appointment_date']);
    $appointment_time = sanitizeInput($_POST['appointment_time']);
    $reason = sanitizeInput($_POST['reason']);
    $status = sanitizeInput($_POST['status']);
    $appointment_id = isset($_POST['appointment_id']) ? sanitizeInput($_POST['appointment_id']) : null;

    // Basic validation
    if (empty($patient_id) || empty($appointment_date) || empty($appointment_time)) {
        $_SESSION['error_message'] = "Patient, date, and time are required fields.";
        header("Location: " . BASE_URL . "appointments/schedule.php" . ($appointment_id ? "?edit_id=$appointment_id" : ""));
        exit();
    }

    if ($appointment_id) {
        // --- UPDATE EXISTING APPOINTMENT ---
        $sql = "UPDATE appointments SET patient_id = :patient_id, appointment_date = :appointment_date, appointment_time = :appointment_time, reason = :reason, status = :status WHERE appointment_id = :appointment_id";
        $action = "updated";
    } else {
        // --- INSERT NEW APPOINTMENT ---
        $sql = "INSERT INTO appointments (patient_id, appointment_date, appointment_time, reason, status) VALUES (:patient_id, :appointment_date, :appointment_time, :reason, :status)";
        $action = "scheduled";
    }

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':patient_id', $patient_id, PDO::PARAM_INT);
        $stmt->bindParam(':appointment_date', $appointment_date);
        $stmt->bindParam(':appointment_time', $appointment_time);
        $stmt->bindParam(':reason', $reason);
        $stmt->bindParam(':status', $status);
        if ($appointment_id) {
            $stmt->bindParam(':appointment_id', $appointment_id, PDO::PARAM_INT);
        }
        $stmt->execute();

        $_SESSION['success_message'] = "Appointment " . $action . " successfully.";

    } catch (PDOException $e) {
        error_log("ERROR: Could not $action appointment: " . $e->getMessage());
        $_SESSION['error_message'] = "Error " . $action . " appointment. Details: " . $e->getMessage();
    }
    
    // Redirect back to the main appointments page
    header("Location: " . BASE_URL . "index.php");
    exit();

} else {
    // If accessed directly without a valid method, redirect
    header("Location: " . BASE_URL . "index.php");
    exit();
}

?>
