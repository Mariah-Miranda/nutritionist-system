<?php
include('../includes/db.php');

// Query using 'clients' and 'clients_id' as per your database
$sales = mysqli_query($conn, "
    SELECT s.id, c.name, s.total_amount, s.sale_date
    FROM sales s
    JOIN clients c ON s.clients_id = c.id
    ORDER BY s.sale_date DESC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Sales History</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <h2>Sales History</h2>
  <table>
        <thead>
            <tr>
                <th>Sale ID</th>
                <th>Customer</th>
                <th>Total</th>
                <th>Date</th>
                <th>Receipt</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = mysqli_fetch_assoc($sales)): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['name']) ?></td>
                <td><?= number_format($row['total_amount'], 2) ?></td>
                <td><?= $row['sale_date'] ?></td>
                <td><a href="receipt.php?id=<?= $row['id'] ?>">View</a></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>
</html>
