<?php
include('../includes/db_connect.php'); // This file should define $pdo

if (isset($_POST['save_product'])) {
    $name = $_POST['product_name'];
    $desc = $_POST['description'];
    $cat = $_POST['category'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];

    try {
        $stmt = $pdo->prepare("INSERT INTO products (product_name, description, category, price, stock) 
                               VALUES (:name, :desc, :cat, :price, :stock)");
        $stmt->execute([
            ':name'  => $name,
            ':desc'  => $desc,
            ':cat'   => $cat,
            ':price' => $price,
            ':stock' => $stock
        ]);

        header('Location: index.php');
        exit();
    } catch (PDOException $e) {
        // Log and show error (you can customize this for production)
        error_log("Insert Error: " . $e->getMessage(), 0);
        die("There was an error saving the product.");
    }
}
?>
