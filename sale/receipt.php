<?php
<<<<<<< Updated upstream
<<<<<<< Updated upstream
include('../includes/db_connect.php');



$id = $_GET['id'] ?? 0;

// Fetch sale and client info
$stmt = $pdo->prepare("
    SELECT s.*, c.name, c.phone, c.membership 
    FROM sales s
    JOIN clients c ON s.clients_id = c.id
    WHERE s.id = ?
");
$stmt->execute([$id]);
$sale = $stmt->fetch();

// Fetch sale items
$stmt = $pdo->prepare("
    SELECT si.*, p.product_name 
    FROM sale_items si
    JOIN products p ON si.product_id = p.id
    WHERE si.sale_id = ?
");
$stmt->execute([$id]);
$items = $stmt->fetchAll();
=======
=======
>>>>>>> Stashed changes
    require_once __DIR__ . '/../config.php';
    require_once __DIR__ . '/../includes/db_connect.php';
    require_once __DIR__ . '/../includes/auth.php';

$id = $_GET['id'] ?? 0;
$sale = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT s.*, c.name, c.phone, c.membership 
    FROM sales s
    JOIN clients c ON s.clients_id = c.id
    WHERE s.id = $id
"));

$items = mysqli_query($conn, "
    SELECT si.*, p.product_name 
    FROM sale_items si
    JOIN products p ON si.product_id = p.id
    WHERE si.sale_id = $id
");
<<<<<<< Updated upstream
>>>>>>> Stashed changes
=======
>>>>>>> Stashed changes
?>

<!DOCTYPE html>
<html>
<head>
    <title>Receipt</title>
    <style>
<<<<<<< Updated upstream
<<<<<<< Updated upstream
    body {
        font-family: 'Courier New', monospace;
        width: 300px;
        margin: auto;
        padding: 20px;
        background: #fff;
        color: #000;
    }

    h2, h3 {
        text-align: center;
        margin: 10px 0 5px;
        padding: 0;
    }

    p {
        text-align: center;
        margin: 4px 0;
    }

    .center {
        text-align: center;
    }

    .bold {
        font-weight: bold;
    }

    .line {
        border-top: 1px dashed #000;
        margin: 12px 0;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 12px;
    }

    td, th {
        padding: 6px 4px;
        font-size: 14px;
    }

    .right {
        text-align: right;
    }

    .barcode {
        text-align: center;
        margin-top: 15px;
    }

    button {
        margin-top: 15px;
    }

    @media print {
        button {
            display: none;
        }
    }
</style>

</head>
<body>
 
 

    <h2 class="bold">SMART FOODS LTD</h2>
    <p>P.O BOX 5568</p>
    <p>Makerere, Kampala</p>
    <p>Tel:+256 702 285 608</p>

    <div class="line"></div>
    <h3>CASH RECEIPT</h3>
    <div class="line"></div>

    <p><span class="bold">Client:</span> <?= htmlspecialchars($sale['name']) ?> (<?= htmlspecialchars($sale['phone']) ?>)</p>
    <p><span class="bold">Membership:</span> <?= htmlspecialchars($sale['membership']) ?></p>
    <p><span class="bold">Date:</span> <?= htmlspecialchars($sale['sale_date']) ?></p>

    <div class="line"></div>
=======
=======
>>>>>>> Stashed changes
        body { font-family: Arial; padding: 20px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { padding: 10px; border: 1px solid #ccc; text-align: left; }
    </style>
</head>
<body>
    <h2>Receipt</h2>
    <p><strong>Client:</strong> <?= $sale['name'] ?> (<?= $sale['phone'] ?>)</p>
    <p><strong>Membership:</strong> <?= $sale['membership'] ?></p>
    <p><strong>Sale Date:</strong> <?= $sale['sale_date'] ?></p>
<<<<<<< Updated upstream
>>>>>>> Stashed changes
=======
>>>>>>> Stashed changes

    <table>
        <thead>
            <tr>
<<<<<<< Updated upstream
<<<<<<< Updated upstream
                <th style="text-align:left;">Item</th>
                <th class="right">Qty</th>
                <th class="right">Price</th>
                <th class="right">Total</th>
            </tr>
        </thead>
        <tbody>
            <?php $total = 0; foreach ($items as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['product_name']) ?></td>
                    <td class="right"><?= $row['quantity'] ?></td>
                    <td class="right"><?= number_format($row['price'], 2) ?></td>
                    <td class="right"><?= number_format($row['subtotal'], 2) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="line"></div>

    <table>
        <tr>
            <td><strong>Subtotal</strong></td>
            <td class="right"><?= number_format($sale['total_amount'] + $sale['discount_percent'], 2) ?></td>
        </tr>
        <tr>
            <td><strong>Discount</strong></td>
            <td class="right"><?= number_format($sale['discount_percent'], 2) ?></td>
        </tr>
        <tr>
            <td><strong>Total Paid</strong></td>
            <td class="right bold"><?= number_format($sale['total_amount'], 2) ?></td>
        </tr>
    </table>

    <div class="line"></div>

    <p class="center">Approval Code: #<?= str_pad($sale['id'], 6, '0', STR_PAD_LEFT) ?></p>

    <div class="line"></div>
    <p class="center bold">THANK YOU FOR YOUR PURCHASE!</p>

    <!--
    <div class="barcode">
        <img src="https://barcode.tec-it.com/barcode.ashx?data=<?= $sale['id'] ?>&code=Code128&translate-esc=false" alt="barcode" />
    </div>
            -->

           <div class="center" style="margin-top:10px;">
    <button 
        onclick="window.print()"
        style="
            background-color: #22c55e;
            color: #ffffff;
            border: none;
            padding: 10px 20px;
            font-size: 14px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;"
        onmouseover="this.style.backgroundColor='#16a34a';"
        onmouseout="this.style.backgroundColor='#22c55e';"
    >
        Print Receipt
    </button>
</div>

   
=======
=======
>>>>>>> Stashed changes
                <th>Product</th>
                <th>Qty</th>
                <th>Price</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php $total = 0; while ($row = mysqli_fetch_assoc($items)): ?>
                <tr>
                    <td><?= $row['product_name'] ?></td>
                    <td><?= $row['quantity'] ?></td>
                    <td><?= number_format($row['price'], 2) ?></td>
                    <td><?= number_format($row['subtotal'], 2) ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <p><strong>Total Before Discount:</strong> <?= number_format($sale['total_amount'] + $sale['discount_percent'], 2) ?></p>
    <p><strong>Discount Applied:</strong> <?= number_format($sale['discount_percent'], 2) ?></p>
    <h3>Total Paid: <?= number_format($sale['total_amount'], 2) ?></h3>

    <button onclick="window.print()">Print Receipt</button>
<<<<<<< Updated upstream
>>>>>>> Stashed changes
=======
>>>>>>> Stashed changes
</body>
</html>
