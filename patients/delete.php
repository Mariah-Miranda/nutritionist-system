<?php
// patients/delete.php - Handles deleting a patient record

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db_connect.php'; // This provides $pdo
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php'; // For sanitizeInput

// Require login for this page
requireLogin();
// Optional: Add role-based access control if only certain roles can delete patients
// if (!hasRole('Admin')) {
//     $_SESSION['error_message'] = "Access denied. You do not have permission to delete patients.";
//     header('Location: ' . BASE_URL . 'patients/list.php');
//     exit();
// }

// Check if a patient ID is provided for deletion
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $patient_id = sanitizeInput($_GET['id']);

    // Validate that the ID is an integer
    if (!filter_var($patient_id, FILTER_VALIDATE_INT)) {
        $_SESSION['error_message'] = "Invalid patient ID provided for deletion.";
        header('Location: ' . BASE_URL . 'patients/list.php');
        exit();
    }

    try {
        // Start a transaction to ensure data integrity
        $pdo->beginTransaction();

        // First, delete related health metrics (if patient_health_metrics has a foreign key constraint with CASCADE DELETE)
        // If not, you'd need to explicitly delete from patient_health_metrics first:
        // $stmt_delete_metrics = $pdo->prepare("DELETE FROM patient_health_metrics WHERE patient_id = :patient_id");
        // $stmt_delete_metrics->bindParam(':patient_id', $patient_id, PDO::PARAM_INT);
        // $stmt_delete_metrics->execute();

        // Then, delete the patient record
        $sql = "DELETE FROM patients WHERE patient_id = :patient_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':patient_id', $patient_id, PDO::PARAM_INT);
        $stmt->execute();

        // Commit the transaction
        $pdo->commit();

        $_SESSION['success_message'] = "Patient and associated data deleted successfully.";

    } catch (PDOException $e) {
        // Rollback the transaction if something went wrong
        $pdo->rollBack();
        error_log("ERROR: Could not delete patient (ID: $patient_id): " . $e->getMessage());
        $_SESSION['error_message'] = "Error deleting patient. Details: " . $e->getMessage();
    }

} else {
    $_SESSION['error_message'] = "No patient ID provided for deletion.";
}

// Redirect back to the patient list page
header("Location: " . BASE_URL . "list.php");
exit();
?>
