<?php include('../includes/db_connect.php');
include('../includes/header.php'); 
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Add Product</title>
  <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
  <h2>Add New Product</h2>
  <form action="process_product.php" method="POST" class="form-box">
    <label>Product Name</label>
    <input type="text" name="product_name" required>

    <label>Description</label>
    <textarea name="description" required></textarea>

    <label>Category</label>
    <select name="category" required>
      <option value="Protein">Protein</option>
      <option value="Vitamins">Vitamins</option>
      <option value="Supplements">Supplements</option>
    </select>

    <label>Price (UGX)</label>
    <input type="number" name="price" required>

    <label>Stock Quantity</label>
    <input type="number" name="stock" required>

    <button type="submit" name="save_product">Save Product</button>
    <a href="index.php" class="btn-cancel">Cancel</a>
  </form>
</body>
</html>