<?php
include('../includes/db_connect.php');

// Get search term safely
$search = $_GET['search'] ?? '';

// Prepare and execute the query using LIKE
$stmt = $pdo->prepare("SELECT * FROM products WHERE product_name LIKE :search ORDER BY id DESC");
$searchTerm = '%' . $search . '%';
$stmt->bindParam(':search', $searchTerm);
$stmt->execute();
$products = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html>
<head>
  
  <meta charset="UTF-8">
  <title>Product List</title>
  <link rel="stylesheet" href="../assets/css/style.css">
   <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>
  <?php
  require_once __DIR__ . '/../includes/header.php';
  ?>
  <h2>Product Inventory</h2>

  <form method="GET" class="search-bar">
    <input type="text" name="search" placeholder="Search..." value="<?= htmlspecialchars($search) ?>">
    <button type="submit">Search</button>
    <a href="add.php">+ Add Product</a>
  </form>

  <table>
    <thead>
      <tr>
        <th>Name</th><th>Category</th><th>Price</th><th>Stock</th><th>Status</th><th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($products as $row): ?>
        <tr>
          <td>
            <?= htmlspecialchars($row['product_name']) ?><br>
            <small><?= htmlspecialchars($row['description']) ?></small>
          </td>
          <td><?= htmlspecialchars($row['category']) ?></td>
          <td>UGX <?= number_format($row['price']) ?></td>
          <td><?= htmlspecialchars($row['stock']) ?></td>
          <td class="<?= $row['stock'] <= 10 ? 'low-stock' : 'in-stock' ?>">
            <?= $row['stock'] <= 10 ? 'Low Stock' : 'In Stock' ?>
          </td>
          <td>
            <a href="edit.php?id=<?= $row['id'] ?>">Edit</a> |
            <a href="delete.php?id=<?= $row['id'] ?>" onclick="return confirm('Delete this product?')">Delete</a>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</body>
</html>
