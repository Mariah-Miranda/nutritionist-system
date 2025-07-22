<?php
include('../includes/db_connect.php'); // This sets up $pdo

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int) $_GET['id'];

    try {
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = :id");
        $stmt->execute([':id' => $id]);
    } catch (PDOException $e) {
        error_log("Delete Error: " . $e->getMessage(), 0);
        // Optional: you could redirect to an error page or show a message
        die("Error deleting product.");
    }
}

header("Location: index.php");
exit();
?>
