<?php
// sales/search_clients.php

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db_connect.php';

header('Content-Type: application/json');

$searchTerm = $_GET['search_term'] ?? '';
$clients = [];

if (strlen($searchTerm) >= 2) {
    try {
        // Search the patients table by full_name or patient_unique_id
        $stmt = $pdo->prepare("SELECT patient_id, full_name, patient_unique_id, membership_status 
                               FROM patients 
                               WHERE full_name LIKE ? OR patient_unique_id LIKE ? 
                               ORDER BY full_name ASC 
                               LIMIT 10");
        $searchParam = '%' . $searchTerm . '%';
        $stmt->execute([$searchParam, $searchParam]);
        $clients = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error searching clients: " . $e->getMessage());
        // Return empty array or error message as JSON
        echo json_encode([]);
        exit();
    }
}

echo json_encode($clients);
?>
