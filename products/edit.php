<?php include('../includes/db.php'); 
$id = $_GET['id'];
$result = mysqli_query($conn, "SELECT * FROM products WHERE id = $id");
$product = mysqli_fetch_assoc($result);
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Edit Product</title>
  <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
  <h2>Edit Product</h2>
  <form action="" method="POST" class="form-box">
    <label>Product Name</label>
    <input type="text" name="product_name" value="<?= $product['product_name'] ?>" required>

    <label>Description</label>
    <textarea name="description" required><?= $product['description'] ?></textarea>

    <label>Category</label>
    <input type="text" name="category" value="<?= $product['category'] ?>" required>

    <label>Price</label>
    <input type="number" name="price" value="<?= $product['price'] ?>" required>

    <label>Stock</label>
    <input type="number" name="stock" value="<?= $product['stock'] ?>" required>

    <button type="submit" name="update_product">Update Product</button>
    <a href="index.php" class="btn-cancel">Cancel</a>
  </form>
  <?php
    if (isset($_POST['update_product'])) {
      $name = $_POST['product_name'];
      $desc = $_POST['description'];
      $cat = $_POST['category'];
      $price = $_POST['price'];
      $stock = $_POST['stock'];
      mysqli_query($conn, "UPDATE products SET product_name='$name', description='$desc', category='$cat', price='$price', stock='$stock' WHERE id=$id");
      header("Location: index.php");
    }
  ?>
</body>
</html>
