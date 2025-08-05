<?php
include('../includes/db_connect.php'); // This sets up $pdo

// Initialize response data
$response = [
    'success' => false,
    'message' => ''
];

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int) $_GET['id'];

    try {
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = :id");
        $stmt->execute([':id' => $id]);

        // Check if a row was actually deleted
        if ($stmt->rowCount() > 0) {
            $response['success'] = true;
            $response['message'] = 'Product deleted successfully.';
        } else {
            $response['message'] = 'Product not found or already deleted.';
        }
    } catch (PDOException $e) {
        error_log("Delete Error: " . $e->getMessage(), 0);
        $response['message'] = "Error deleting product.";
    }
} else {
    $response['message'] = "Invalid product ID.";
}

// Decide whether to respond with JSON or redirect
// If an AJAX call, respond with JSON. Otherwise, perform a redirect.
if (
    !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'
) {
    // Return JSON response
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
} else {
    // Optionally, you could store $response['message'] in session and display it on index.php
    header("Location: index.php");
    exit();
}
?>
