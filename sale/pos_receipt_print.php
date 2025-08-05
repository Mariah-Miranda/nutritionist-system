<?php
// sales/pos_receipt_print.php

// Ensure configuration and database connection are loaded
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db_connect.php';
// No auth needed for a print-only page, assuming it's accessed securely from receipt.php

$sale_id = filter_input(INPUT_GET, 'sale_id', FILTER_VALIDATE_INT);

$sale = null;
$sale_items = [];

if ($sale_id) {
    try {
        // Fetch sale details from 'sales' table
        $stmt_sale = $pdo->prepare("SELECT s.*, p.full_name AS client_name, p.patient_unique_id AS client_unique_id, p.membership_status
                                    FROM sales s
                                    LEFT JOIN patients p ON s.clients_id = p.patient_id
                                    WHERE s.id = ?");
        $stmt_sale->execute([$sale_id]);
        $sale = $stmt_sale->fetch();

        if ($sale) {
            // Fetch sale items from 'sale_items' table
            $stmt_items = $pdo->prepare("SELECT si.*, pr.product_name
                                         FROM sale_items si
                                         JOIN products pr ON si.product_id = pr.id
                                         WHERE si.sale_id = ?");
            $stmt_items->execute([$sale_id]);
            $sale_items = $stmt_items->fetchAll();
        }

    } catch (PDOException $e) {
        // Log the error for debugging
        error_log("Database Error: " . $e->getMessage());
        $sale = null;
    }
}

// Redirect or show error if sale not found
if (!$sale) {
    die("Sale record not found.");
}

// Extract data for display
$sale_date = $sale['sale_date'];
$date_completed = $sale_date; // Using sale_date as the completed date
$client_name = htmlspecialchars($sale['client_name'] ?? $sale['customer_name'] ?? 'N/A');
$client_id = htmlspecialchars($sale['client_unique_id'] ?? 'N/A');
$subtotal_db = 0;
foreach ($sale_items as $item) {
    $subtotal_db += $item['subtotal'];
}
$tax_amount_db = $sale['total_amount'] - $subtotal_db;
$total_amount_due_db = $sale['total_amount'];

?>
<!DOCTYPE html>
<html>
<head>
    <title>Receipt #<?php echo htmlspecialchars($sale_id); ?></title>
    <style>
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            color: #000;
            margin: 0;
            padding: 0;
            background: #fff;
        }
        #wrapper {
            max-width: 300px;
            margin: 0 auto;
            padding: 10px;
            border: 1px solid #000;
        }
        .header-section, .client-section, .footer-section {
            text-align: center;
            margin-bottom: 10px;
        }
        .text-left { text-align: left; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 5px 0;
        }
        .item-row td {
            border-bottom: 1px dashed #ddd;
        }
        .logo {
            font-weight: bold;
            font-size: 1.5em;
        }
        @media print {
            body {
                background: none;
            }
            #wrapper {
                border: none;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div id="wrapper">
        <div class="header-section">
            <h1 class="logo">Nutrition Shop</h1>
            <p>123 Nutrition St, Kampala</p>
            <p>Tel: +256 771 234567</p>
            <p>Email: info@nutrition.com</p>
            <hr style="border-top: 1px dashed #000; margin: 10px 0;">
        </div>
        
        <div class="client-section">
            <p><strong>Receipt ID:</strong> #<?php echo htmlspecialchars($sale_id); ?></p>
            <p><strong>Client Name:</strong> <?php echo $client_name; ?></p>
            <p><strong>Client ID:</strong> <?php echo $client_id; ?></p>
            <hr style="border-top: 1px dashed #000; margin: 10px 0;">
        </div>

        <table>
            <thead>
                <tr>
                    <th class="text-left">Item</th>
                    <th class="text-right">Qty</th>
                    <th class="text-right">Price</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($sale_items as $item): ?>
                <tr class="item-row">
                    <td class="text-left"><?php echo htmlspecialchars($item['product_name']); ?></td>
                    <td class="text-right"><?php echo htmlspecialchars($item['quantity']); ?></td>
                    <td class="text-right"><?php echo number_format($item['unit_price'], 2); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <table style="margin-top: 10px;">
            <tr>
                <td class="text-right">Subtotal:</td>
                <td class="text-right"><?php echo number_format($subtotal_db, 2); ?> <?php echo DEFAULT_CURRENCY; ?></td>
            </tr>
            <tr>
                <td class="text-right">Tax (<?php echo TAX_RATE_PERCENT; ?>%):</td>
                <td class="text-right"><?php echo number_format($tax_amount_db, 2); ?> <?php echo DEFAULT_CURRENCY; ?></td>
            </tr>
            <tr>
                <td colspan="2"><hr style="border-top: 1px dashed #000; margin: 5px 0;"></td>
            </tr>
            <tr> 
                <td class="text-right">TOTAL:</td>
                <td class="text-right" style="font-weight: bold; font-size: 1.2em;"><?php echo number_format($total_amount_due_db, 2); ?> <?php echo DEFAULT_CURRENCY; ?></td>
            </tr>
        </table>
        
        <div class="footer-section">
            <hr style="border-top: 1px dashed #000; margin: 10px 0;">
            <p>Thank you for your purchase!</p>
            <p style="margin-top: 10px;">Please keep this receipt for your records.</p>
            <p style="margin-top: 10px;">Date & Time: <?php echo date('d/m/Y H:i:s', strtotime($date_completed)); ?></p>
        </div>
            
    </div>
</div>
    <!-- /#wrapper -->

<script type="text/javascript">
    (function() {
    var afterPrint = function() {
        // Optional: Close the window after printing
        // window.close();
    };

    if (window.matchMedia) {
        var mediaQueryList = window.matchMedia('print');
        mediaQueryList.addListener(function(mql) {
            if (!mql.matches) {
                // Not a print event, do nothing
                afterPrint();
            }
        });
    }

    // Call print function automatically when the page loads
    window.print();
    
    // Note: The print dialog can block the main thread, so window.close() might not fire reliably.
    // An alternative is to use window.onafterprint, but browser support varies.
    // For this reason, a simple console log is often used to confirm the event.
    // window.onafterprint = afterPrint;
    })();
</script>
</body>
</html>
