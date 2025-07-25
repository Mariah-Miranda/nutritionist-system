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
        $stmt_sale = $pdo->prepare("SELECT s.*, p.full_name, p.patient_unique_id, p.membership_status
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
        // Log the error but do not display it on the receipt page
        error_log("Error fetching POS receipt: " . $e->getMessage());
        $sale = null;
    }
}

// Set headers to prevent caching and ensure proper printing
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// --- Adapt variables from your provided receipt.php to match current data ---
$orderno = htmlspecialchars($sale['id'] ?? 'N/A'); // Using sale ID as order number
$remarks = 'Sale Transaction'; // Generic remark
$cashier_name = $_SESSION['username'] ?? ''; // Get username from session, default to empty string
$order_date = date('Y-m-d H:i:s', strtotime($sale['sale_date'] ?? 'now'));
$date_completed = date('Y-m-d H:i:s', strtotime($sale['sale_date'] ?? 'now')); // Using sale_date as completion date

// Recalculate totals for accurate display based on your sales data
$subtotal_before_item_discounts = array_reduce($sale_items, function($sum, $item) {
    return $sum + ($item['price'] * $item['quantity']);
}, 0);

$total_item_discounts = array_reduce($sale_items, function($sum, $item) {
    $item_gross_total = $item['price'] * $item['quantity'];
    $item_discount = $item_gross_total - $item['subtotal'];
    return $sum + $item_discount;
}, 0);

$subtotal_after_item_discounts = $subtotal_before_item_discounts - $total_item_discounts;

$member_discount_percent = (float)($sale['discount_percent'] ?? 0);
$member_discount_amount = $subtotal_after_item_discounts * ($member_discount_percent / 100);
$tax_amount = ($subtotal_after_item_discounts - $member_discount_amount) * (TAX_RATE_PERCENT / 100);

$total_amount_due_db = (float)($sale['total_amount'] ?? 0.00); // This should be the final total after all discounts and tax

// Consistent payment details for all customer types
$previous_paid_amount = 0.00; // Assuming no partial payments for a single POS transaction
$tender_amount_this_transaction = $total_amount_due_db; // Assuming customer paid the exact total
$total_paid_after_this_transaction = $total_amount_due_db; // Assuming total paid equals total due
$balance_due_db = 0.00; // Assuming sale is fully paid
$change_amount_this_transaction = 0.00; // Assuming exact change

$payment_status_db = htmlspecialchars($sale['payment_method'] ?? 'N/A'); // Using payment method as status
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="Smart Food Sale Receipt">
<meta name="author" content="Smart Food">
<title>Smart Food Sale Receipt</title>
 
<style type="text/css">
    /* General print styles */
    @page { size: auto;  margin: 1mm; } /* auto is the initial value; adjust margin as needed */
    body {
        font-family: Arial, sans-serif;
        font-size: 10px; /* Base font size for receipt */
        width: 100%;
        padding: 0px;
        margin: 0 auto; /* Center the body horizontally */
        box-sizing: border-box;
        background-color: #fff; /* Ensure white background for printing */
    }
    #wrapper {
        width: 100%;
        max-width: 280px; /* Typical width for thermal printer receipts */
        margin: 0 auto;
        padding: 5px;
        box-sizing: border-box;
        border: 1px solid #eee; /* Light border for screen view */
        box-shadow: 0 0 5px rgba(0,0,0,0.1); /* Subtle shadow for screen view */
    }
    .company-header {
        text-align: center;
        margin-bottom: 10px;
    }
    .company-header h2 {
        margin: 0;
        font-size: 16px;
        font-weight: bold;
        color: #333; /* Darker color for headings */
    }
    .company-header p {
        margin: 0;
        font-size: 9px;
        color: #555; /* Slightly lighter text for info */
    }
    .company-header .slogan {
        font-style: italic;
        margin-top: 5px;
        color: #666;
    }
    .receipt-info, .receipt-summary {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 5px;
    }
    .receipt-info td, .receipt-summary td, .receipt-summary th {
        padding: 2px 0;
        border: none; /* No borders for clean look */
        color: #444;
    }
    .receipt-items {
        width: 100%;
        border-collapse: collapse;
        margin-top: 5px;
        margin-bottom: 5px;
    }
    .receipt-items th, .receipt-items td {
        padding: 3px 0;
        border-bottom: 1px dashed #ccc; /* Dashed line for item separation */
        text-align: left;
        font-size: 9px;
        color: #333;
        word-break: break-all; /* Ensure long words break */
    }
    .receipt-items th:nth-child(2), .receipt-items td:nth-child(2) { text-align: right; } /* Price */
    .receipt-items th:nth-child(3), .receipt-items td:nth-child(3) { text-align: center; } /* Qty */
    .receipt-items th:nth-child(4), .receipt-items td:nth-child(4) { text-align: right; } /* Discount */
    .receipt-items th:nth-child(5), .receipt-items td:nth-child(5) { text-align: right; } /* Sub-total */
    
    .text-right { text-align: right; }
    .text-center { text-align: center; }
    .footer-section {
        margin-top: 10px;
        text-align: center;
        font-size: 9px;
        color: #555;
    }
    /* Logo styling */
    .company-logo {
        max-width: 100px; /* Adjust as needed */
        height: auto;
        margin-bottom: 5px; /* Space between logo and text */
        display: block; /* Ensure it's a block element for centering */
        margin-left: auto;
        margin-right: auto;
    }

    /* Print-specific adjustments */
    @media print {
        body {
            margin: 0 !important;
            padding: 0 !important;
            box-shadow: none !important;
            border: none !important;
            background-color: #fff !important; /* Force white background for print */
        }
        #wrapper {
            box-shadow: none !important;
            border: none !important;
            padding: 0 !important;
            margin: 0 !important;
        }
        /* Ensure all text is black for print */
        body, #wrapper, .company-header h2, .company-header p, .company-header .slogan,
        .receipt-info td, .receipt-summary td, .receipt-summary th,
        .receipt-items th, .receipt-items td, .footer-section p {
            color: #000 !important;
        }
        /* Hide any elements that should not print */
        .no-print {
            display: none !important;
        }
    }
</style>

</head>
<body onload="window.print();">
 <center>
<div id="wrapper">
    <div class="container">
        <!-- Company Header -->
        <div class="company-header">
            <!-- Company Logo -->
            <img src="https://placehold.co/100x50/10B981/ffffff?text=Smart+Food" alt="Smart Food Logo" class="company-logo" onerror="this.onerror=null;this.src='https://placehold.co/100x50/10B981/ffffff?text=Smart+Food';">
            <h2>SMART FOOD</h2>
            <p>Kampala, Uganda</p>
            <p>Phone: +123 456 7890</p>
            <p>Email: info@smartfood.com</p>
            <p class="slogan">"Healthy Food, Healthy Life!"</p>
            <hr style="border-top: 1px dashed #000; margin: 10px 0;">
        </div>

        <div style="text-align: center; font-size: 12px; font-weight: bold; margin-bottom: 5px;">SALE RECEIPT</div>
        <div style="text-align: center; font-size: 10px; margin-bottom: 10px;"><?php echo $remarks; ?></div>
        

        <table class="receipt-info">
            <tr>
                <td>Receipt #:</td>
                <td class="text-right"><strong><?php echo htmlspecialchars($orderno); ?></strong></td>
            </tr>
            <tr>
                <td>Sale Date:</td>
                <td class="text-right"><?php echo date('d/m/Y H:i:s', strtotime($order_date)); ?></td>
            </tr>
            <?php if ($sale['customer_type'] === 'Patient' && !empty($sale['full_name'])): ?>
            <tr>
                <td>Customer:</td>
                <td class="text-right"><?php echo htmlspecialchars($sale['full_name']); ?> (Patient)</td>
            </tr>
            <?php elseif (!empty($sale['customer_name'])): ?>
            <tr>
                <td>Customer:</td>
                <td class="text-right"><?php echo htmlspecialchars($sale['customer_name']); ?></td>
            </tr>
            <?php endif; ?>
            <?php if (!empty($sale['customer_phone'])): ?>
            <tr>
                <td>Phone:</td>
                <td class="text-right"><?php echo htmlspecialchars($sale['customer_phone']); ?></td>
            </tr>
            <?php endif; ?>
            <?php if (!empty($cashier_name)): // Conditionally display Cashier ?>
            <tr>
                <td>Cashier:</td>
                <td class="text-right"><?php echo $cashier_name; ?></td>
            </tr>
            <?php endif; ?>
            <tr>
                <td>Print Date:</td>
                <td class="text-right"><?php echo date('d/m/Y H:i:s'); ?></td>
            </tr>
            <tr>
                <td colspan="2"><hr style="border-top: 1px dashed #000; margin: 5px 0;"></td>
            </tr>
        </table>
       
        <table class="receipt-items">
            <thead>
                <tr> 
                    <th>Item</th>
                    <th>Price</th>
                    <th>Qty</th>
                    <th>Discount</th>
                    <th>Amount</th> 
                </tr> 
            </thead>
            <tbody>
                <?php 
                if (!empty($sale_items)) {
                    foreach ($sale_items as $item) { 
                        // Calculate item discount for display
                        $item_gross_price = $item['price'] * $item['quantity'];
                        $item_discount_display = $item_gross_price - $item['subtotal'];

                        echo '<tr>'; 
                        echo '<td>'.htmlspecialchars($item['product_name']).'</td>';
                        echo '<td class="text-right">'.number_format($item['price'], 2).'</td>';
                        echo '<td class="text-center">'.number_format($item['quantity'], 0).'</td>';
                        echo '<td class="text-right">'.number_format($item_discount_display, 2).'</td>'; // Display item discount
                        echo '<td class="text-right">'.number_format($item['subtotal'], 2).'</td>'; 
                        echo '</tr>';
                    } 
                } else {
                    echo '<tr><td colspan="5" class="text-center">No items in this sale.</td></tr>'; // Adjusted colspan
                }
                ?>  
            </tbody>
        </table>  
        
        <table class="receipt-summary">
            <tr> 
                <td class="text-right">Subtotal:</td>
                <td class="text-right"><?php echo number_format($subtotal_before_item_discounts, 2); ?> <?php echo DEFAULT_CURRENCY; ?></td>
            </tr>
            <?php if ($total_item_discounts > 0): ?>
            <tr> 
                <td class="text-right">Item Discounts:</td>
                <td class="text-right">- <?php echo number_format($total_item_discounts, 2); ?> <?php echo DEFAULT_CURRENCY; ?></td>
            </tr>
            <?php endif; ?>
            <?php if ($member_discount_amount > 0): // Using calculated member discount amount ?>
            <tr> 
                <td class="text-right">Member Discount (<?php echo htmlspecialchars($member_discount_percent); ?>%):</td>
                <td class="text-right">- <?php echo number_format($member_discount_amount, 2); ?> <?php echo DEFAULT_CURRENCY; ?></td>
            </tr>
            <?php endif; ?>
            <tr>
                <td class="text-right">Tax (<?php echo TAX_RATE_PERCENT; ?>%):</td>
                <td class="text-right">+ <?php echo number_format($tax_amount, 2); ?> <?php echo DEFAULT_CURRENCY; ?></td>
            </tr>
            <tr>
                <td colspan="2"><hr style="border-top: 1px dashed #000; margin: 5px 0;"></td>
            </tr>
            <tr> 
                <td class="text-right">TOTAL:</td>
                <td class="text-right" style="font-weight: bold; font-size: 1.2em;"><?php echo number_format($total_amount_due_db, 2); ?> <?php echo DEFAULT_CURRENCY; ?></td>
            </tr>
            <tr> 
                <td class="text-right">Previous Paid:</td>
                <td class="text-right"><?php echo number_format($previous_paid_amount, 2); ?> <?php echo DEFAULT_CURRENCY; ?></td>
            </tr>
            <tr> 
                <td class="text-right">Amount Tendered (this transaction):</td>
                <td class="text-right" style="font-weight: bold;"><?php echo number_format($tender_amount_this_transaction, 2); ?> <?php echo DEFAULT_CURRENCY; ?></td>
            </tr>
            <tr> 
                <td class="text-right">Total Paid:</td>
                <td class="text-right" style="font-weight: bold;"><?php echo number_format($total_paid_after_this_transaction, 2); ?> <?php echo DEFAULT_CURRENCY; ?></td>
            </tr>
            <tr> 
                <td class="text-right">Balance Due:</td>
                <td class="text-right" style="font-weight: bold;"><?php echo number_format($balance_due_db, 2); ?> <?php echo DEFAULT_CURRENCY; ?></td>
            </tr>
            <tr> 
                <td class="text-right">Payment Status:</td>
                <td class="text-right" style="font-weight: bold;"><?php echo $payment_status_db; ?></td>
            </tr>
            <tr>
                <td class="text-right">Change:</td>
                <td class="text-right" style="font-weight: bold;"><?php echo number_format($change_amount_this_transaction, 2); ?> <?php echo DEFAULT_CURRENCY; ?></td>
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
                // If not matching print media, assume print dialog was closed
                afterPrint();
            }
        });
    }

    // Fallback for older browsers or if mediaQueryList is not triggered
    window.onafterprint = afterPrint;
}());
</script>
 </center>
</body>
</html>
