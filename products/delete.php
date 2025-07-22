<?php
<<<<<<< Updated upstream
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
=======
    require_once __DIR__ . '/../config.php';
    require_once __DIR__ . '/../includes/db_connect.php';
    require_once __DIR__ . '/../includes/auth.php';

  
if (isset($_GET['id'])) {
  mysqli_query($conn, "DELETE FROM products WHERE id = {$_GET['id']}");
}
header("Location: index.php");
>>>>>>> Stashed changes
?>
