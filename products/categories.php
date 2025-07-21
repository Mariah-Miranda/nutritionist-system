<?php include('../includes/db.php'); ?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Categories</title>
  <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
  <h2>Manage Categories</h2>
  <form method="POST">
    <input type="text" name="category_name" placeholder="New category" required>
    <button type="submit" name="add_category">Add</button>
  </form>
  <ul>
    <?php
    if (isset($_POST['add_category'])) {
      $cat = $_POST['category_name'];
      mysqli_query($conn, "INSERT INTO categories (name) VALUES ('$cat')");
      header("Location: categories.php");
    }
    $cats = mysqli_query($conn, "SELECT * FROM categories");
    while ($c = mysqli_fetch_assoc($cats)) {
      echo "<li>{$c['name']}</li>";
    }
    ?>
  </ul>
</body>
</html>