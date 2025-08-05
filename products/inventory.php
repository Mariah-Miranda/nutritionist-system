<?php
include('../includes/db_connect.php');
include('../includes/header.php');

// Handle stock update
if (isset($_POST['update_stock'])) {
  $id = intval($_POST['id']);
  $stock = intval($_POST['stock']);

  $stmt = $pdo->prepare("UPDATE products SET stock = :stock WHERE id = :id");
  $stmt->bindParam(':stock', $stock);
  $stmt->bindParam(':id', $id);
  $stmt->execute();

  header("Location: inventory.php");
  exit();
}
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Inventory</title>
  <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
  <h2>Inventory Adjustment</h2>

  <table>
    <tr><th>Product</th><th>Stock</th><th>Update</th></tr>
    <?php
      $stmt = $pdo->query("SELECT * FROM products");
      $products = $stmt->fetchAll();

      foreach ($products as $row) {
        echo "<tr><form method='POST'>
                <td>" . htmlspecialchars($row['product_name']) . "</td>
                <td><input type='number' name='stock' value='" . htmlspecialchars($row['stock']) . "'></td>
                <td>
                  <input type='hidden' name='id' value='" . htmlspecialchars($row['id']) . "'>
                  <button type='submit' name='update_stock'>Save</button>
                </td>
              </form></tr>";
      }
    ?>
  </table>
</body>
</html>
