<?php
include('../includes/db_connect.php'); // sets up $pdo

$id = $_GET['id'] ?? null;
if (!$id || !is_numeric($id)) {
    die("Invalid product ID.");
}

// Process form submission BEFORE output
if (isset($_POST['update_product'])) {
    $name  = $_POST['product_name'];
    $desc  = $_POST['description'];
    $cat   = $_POST['category'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];

    try {
        $stmt = $pdo->prepare("UPDATE products SET product_name = :name, description = :desc, category = :cat, price = :price, stock = :stock WHERE id = :id");
        $stmt->execute([
            ':name'  => $name,
            ':desc'  => $desc,
            ':cat'   => $cat,
            ':price' => $price,
            ':stock' => $stock,
            ':id'    => $id
        ]);
        header("Location: index.php");
        exit();
    } catch (PDOException $e) {
        error_log("Update Error: " . $e->getMessage(), 0);
        $error = "Error updating product.";
    }
}

// Now fetch product for form display
try {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $product = $stmt->fetch();

    if (!$product) {
        die("Product not found.");
    }
} catch (PDOException $e) {
    error_log("Fetch Error: " . $e->getMessage(), 0);
    die("Error loading product.");
}

include('../includes/header.php'); // HTML starts here, after all headers
?>

<h2>Edit Product</h2>

<?php if (!empty($error)) : ?>
    <p style="color:red;"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<form action="" method="POST" class="form-box">
    <label>Product Name</label>
    <input type="text" name="product_name" value="<?= htmlspecialchars($product['product_name']) ?>" required>

    <label>Description</label>
    <textarea name="description" required><?= htmlspecialchars($product['description']) ?></textarea>

    <label>Category</label>
    <input type="text" name="category" value="<?= htmlspecialchars($product['category']) ?>" required>

    <label>Price</label>
    <input type="number" step="0.01" name="price" value="<?= $product['price'] ?>" required>

    <label>Stock</label>
    <input type="number" name="stock" value="<?= $product['stock'] ?>" required>

    <button type="submit" name="update_product">Update Product</button>
    <a href="index.php" class="btn-cancel">Cancel</a>
</form>

<?php include('../includes/footer.php'); ?>
