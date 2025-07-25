<?php
// sales/receipt.php

// TEMPORARY: Enable detailed error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/auth.php';

$pageTitle = 'Sale Receipt';
require_once __DIR__ . '/../includes/header.php';

$sale_id = filter_input(INPUT_GET, 'sale_id', FILTER_VALIDATE_INT);

$sale = null;
$sale_items = [];
$patient_info = null;

// Debugging: Output sale_id to console
echo '<script>';
echo 'console.log("receipt.php: Received sale_id = ' . json_encode($sale_id) . '");';
echo '</script>';


if ($sale_id) {
    try {
        // Fetch sale details
        // Updated to select customer_type, customer_name, customer_phone
        $stmt_sale = $pdo->prepare("SELECT s.*, p.full_name, p.patient_unique_id, p.membership_status
                                    FROM sales s
                                    LEFT JOIN patients p ON s.clients_id = p.patient_id
                                    WHERE s.id = ?");
        $stmt_sale->execute([$sale_id]);
        $sale = $stmt_sale->fetch();

        if ($sale) {
            // Fetch sale items
            $stmt_items = $pdo->prepare("SELECT si.*, pr.product_name
                                         FROM sale_items si
                                         JOIN products pr ON si.product_id = pr.id
                                         WHERE si.sale_id = ?");
            $stmt_items->execute([$sale_id]);
            $sale_items = $stmt_items->fetchAll();
        }

    } catch (PDOException $e) {
        error_log("Error fetching sale receipt: " . $e->getMessage());
        // Debugging: Output PDO error to console
        echo '<script>';
        echo 'console.error("receipt.php: PDO Error fetching sale: ' . json_encode($e->getMessage()) . '");';
        echo '</script>';
        $sale = null; // Ensure sale is null on error
    }
}

// Debugging: Output fetched sale and sale_items data to console
echo '<script>';
echo 'console.log("receipt.php: Fetched Sale Data = ", ' . json_encode($sale) . ');';
echo 'console.log("receipt.php: Fetched Sale Items = ", ' . json_encode($sale_items) . ');';
echo '</script>';
?>

<style>
    /* General styles for the receipt area */
    #receipt-area {
        max-width: 300px; /* Typical width for thermal printer */
        margin: 0 auto; /* Center the receipt */
        background-color: #fff;
        padding: 10px; /* Smaller padding for compact look */
        font-family: 'monospace', 'Courier New', Courier, monospace; /* Monospace font for classic POS look */
        color: #000;
        font-size: 12px; /* Base font size for POS */
        line-height: 1.2;
    }

    /* Hide elements not needed for print */
    @media print {
        html, body {
            margin: 0;
            padding: 0;
            width: 100%;
            height: auto; /* Allow content to define height */
            overflow: visible; /* Ensure content is not hidden by overflow */
        }
        body * {
            visibility: hidden;
        }
        #receipt-area, #receipt-area * {
            visibility: visible;
        }
        #receipt-area {
            /* Removed position: absolute; to allow natural flow */
            width: 100%;
            margin: 0;
            padding: 0;
            box-shadow: none;
            border-radius: 0;
            font-size: 10px; /* Even smaller for print */
            line-height: 1;
            min-height: 100px; /* Ensure it takes up some space */
            display: block; /* Ensure it's treated as a block element */
        }
        .no-print {
            display: none;
        }
        /* Ensure no extra margins/padding on print */
        .container, .bg-white, .shadow-lg, .rounded-lg {
            margin: 0 !important;
            padding: 0 !important;
            box-shadow: none !important;
            border-radius: 0 !important;
        }
        table {
            width: 100%; /* Ensure table takes full width */
            border-collapse: collapse;
        }
        th, td {
            padding: 2px 0; /* Compact padding for table cells */
            border: none; /* No borders in print */
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .text-left {
            text-align: left;
        }
        .border-b, .border-t {
            border-bottom: 1px dashed #000 !important; /* Dashed lines for separation */
            border-top: 1px dashed #000 !important;
            padding-top: 5px !important;
            margin-top: 5px !important;
        }
        .pt-2, .mt-2 {
            padding-top: 2px !important;
            margin-top: 2px !important;
        }
        .mb-6 {
            margin-bottom: 5px !important;
        }
        .pb-6 {
            padding-bottom: 5px !important;
        }
        .text-3xl, .text-xl, .text-lg {
            font-size: 1.2em; /* Adjust heading sizes for print */
        }
        .font-bold, .font-extrabold {
            font-weight: bold;
        }
    }

    /* Specific styles for the POS look */
    .receipt-header, .receipt-footer {
        text-align: center;
        margin-bottom: 10px;
    }
    .receipt-info, .receipt-summary {
        margin-bottom: 10px;
    }
    .receipt-item-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 10px;
    }
    .receipt-item-table th, .receipt-item-table td {
        padding: 2px 0;
        text-align: left;
    }
    .receipt-item-table th:nth-child(2),
    .receipt-item-table td:nth-child(2) { /* Qty column */
        text-align: center;
    }
    .receipt-item-table th:nth-child(3),
    .receipt-item-table td:nth-child(3),
    .receipt-item-table th:nth-child(4),
    .receipt-item-table td:nth-child(4) { /* Price and Subtotal columns */
        text-align: right;
    }

    .receipt-summary div {
        display: flex;
        justify-content: space-between;
        margin-bottom: 2px;
    }
    .receipt-summary .total {
        font-size: 1.2em;
        font-weight: bold;
        border-top: 1px dashed #000;
        padding-top: 5px;
        margin-top: 5px;
    }
</style>

<div class="container mx-auto p-4 md:p-8 no-print">
    <div id="receipt-area" class="bg-white rounded-lg shadow-lg p-6 md:p-8">
        <div class="receipt-header">
            <h2 class="text-xl font-bold"><?php echo htmlspecialchars(SITE_NAME); ?></h2>
            <p class="text-xs">SMART FOOD, Kampala, Uganda</p>
            <p class="text-xs">Phone: +123 456 7890 | Email: info@example.com</p>
            <p class="text-md font-semibold mt-2">SALE RECEIPT</p>
            <p class="text-sm">Receipt #<?php echo htmlspecialchars($sale_id); ?></p>
            <p class="text-sm">Date: <?php echo htmlspecialchars(date('Y-m-d H:i:s', strtotime($sale['sale_date'] ?? 'now'))); ?></p>
            <div style="border-bottom: 1px dashed #000; margin: 10px 0;"></div>
        </div>

        <?php if ($sale): ?>
            <div class="receipt-info">
                <?php if ($sale['customer_type'] === 'Patient'): ?>
                    <p>Customer: <?php echo htmlspecialchars($sale['full_name'] ?? 'N/A'); ?> (Patient)</p>
                    <p>Patient ID: <?php echo htmlspecialchars($sale['patient_unique_id'] ?? 'N/A'); ?></p>
                    <p>Membership: <?php echo htmlspecialchars($sale['membership_status'] ?? 'N/A'); ?></p>
                <?php else: ?>
                    <p>Customer: <?php echo htmlspecialchars($sale['customer_name'] ?? 'Visitor'); ?></p>
                    <?php if (!empty($sale['customer_phone'])): ?>
                        <p>Phone: <?php echo htmlspecialchars($sale['customer_phone']); ?></p>
                    <?php endif; ?>
                <?php endif; ?>
                <div style="border-bottom: 1px dashed #000; margin: 10px 0;"></div>
            </div>

            <div class="receipt-items">
                <table class="receipt-item-table">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th class="text-center">Qty</th>
                            <th class="text-right">Price</th>
                            <th class="text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($sale_items)): ?>
                            <?php foreach ($sale_items as $item): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                                    <td class="text-center"><?php echo htmlspecialchars($item['quantity']); ?></td>
                                    <td class="text-right"><?php echo number_format($item['price'], 2); ?></td>
                                    <td class="text-right"><?php echo number_format($item['subtotal'], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center">No items found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                <div style="border-bottom: 1px dashed #000; margin: 10px 0;"></div>
            </div>

            <div class="receipt-summary">
                 <?php
                    // Recalculate totals for accurate display
                    $subtotal_before_item_discounts = array_reduce($sale_items, function($sum, $item) {
                        return $sum + ($item['price'] * $item['quantity']);
                    }, 0);
                    
                    $total_item_discounts = array_reduce($sale_items, function($sum, $item) {
                        // This assumes subtotal = (price * qty) - discount
                        $item_subtotal = $item['price'] * $item['quantity'];
                        $item_discount = $item_subtotal - $item['subtotal'];
                        return $sum + $item_discount;
                    },0);

                    $subtotal_after_item_discounts = $subtotal_before_item_discounts - $total_item_discounts;

                    $member_discount_amount = $subtotal_after_item_discounts * ($sale['discount_percent'] / 100);
                    $subtotal_after_member_discount = $subtotal_after_item_discounts - $member_discount_amount;
                    $tax_amount = $subtotal_after_member_discount * (TAX_RATE_PERCENT / 100);
                ?>
                <div>
                    <span>Subtotal:</span>
                    <span class="font-bold"><?php echo DEFAULT_CURRENCY . ' ' . number_format($subtotal_before_item_discounts, 2); ?></span>
                </div>
                <div>
                    <span>Item Discounts:</span>
                    <span class="font-bold">- <?php echo DEFAULT_CURRENCY . ' ' . number_format($total_item_discounts, 2); ?></span>
                </div>
                <div>
                    <span>Member Discount (<?php echo htmlspecialchars($sale['discount_percent']); ?>%):</span>
                    <span class="font-bold">- <?php echo DEFAULT_CURRENCY . ' ' . number_format($member_discount_amount, 2); ?></span>
                </div>
                <div>
                    <span>Tax (<?php echo htmlspecialchars(TAX_RATE_PERCENT); ?>%):</span>
                    <span class="font-bold">+ <?php echo DEFAULT_CURRENCY . ' ' . number_format($tax_amount, 2); ?></span>
                </div>
                <div class="total">
                    <span>TOTAL:</span>
                    <span class="font-extrabold"><?php echo DEFAULT_CURRENCY . ' ' . number_format($sale['total_amount'], 2); ?></span>
                </div>
                <p class="text-sm text-center mt-2">Payment Method: <?php echo htmlspecialchars($sale['payment_method'] ?? 'N/A'); ?></p>
                <div style="border-bottom: 1px dashed #000; margin: 10px 0;"></div>
            </div>

            <div class="receipt-footer">
                <p class="text-sm">Thank you for your purchase!</p>
                <p class="text-xs">Please come again.</p>
            </div>

        <?php else: ?>
            <div class="text-center py-8">
                <p class="text-gray-600 text-lg">Sale receipt not found or invalid sale ID.</p>
            </div>
        <?php endif; ?>
    </div>

    <div class="flex justify-center mt-6 no-print">
        <?php if ($sale): ?>
            <!-- This button now triggers the POS receipt print -->
            <button onclick="printPosReceipt(<?php echo htmlspecialchars($sale_id); ?>)" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg mr-4">
                <i class="fas fa-print mr-2"></i> Print Receipt
            </button>
        <?php endif; ?>
        <a href="new.php" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg mr-2">New Sale</a>
        <a href="history.php" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-lg">View Sales History</a>
    </div>
</div>

<script>
    function printPosReceipt(saleId) {
        // Open the new POS receipt page in a new window/tab
        const printWindow = window.open(`pos_receipt_print.php?sale_id=${saleId}`, '_blank', 'width=320,height=600,scrollbars=yes,resizable=yes');
        
        // Optional: Focus the new window and ensure it prints
        if (printWindow) {
            printWindow.focus();
            // The window.print() call is now inside pos_receipt_print.php
        } else {
            // Use a custom message box instead of alert
            showMessage('Pop-up Blocked', 'Please allow pop-ups for this site to print the POS receipt.');
        }
    }

    // You might need to define showMessage if it's not globally available from new.php
    // If new.php and receipt.php are completely separate, copy the function here:
    function showMessage(title, message) {
        const messageBox = document.getElementById('messageBox');
        const messageBoxTitle = document.getElementById('messageBoxTitle');
        const messageBoxContent = document.getElementById('messageBoxContent');
        const messageBoxCloseBtn = document.getElementById('messageBoxCloseBtn');

        if (messageBox && messageBoxTitle && messageBoxContent && messageBoxCloseBtn) {
            messageBoxTitle.textContent = title;
            messageBoxContent.textContent = message;
            messageBox.classList.remove('hidden');
            messageBoxCloseBtn.onclick = () => messageBox.classList.add('hidden');
        } else {
            console.error("Message box elements not found. Cannot display message.");
            // Fallback to alert if custom message box isn't available
            alert(`${title}\n\n${message}`);
        }
    }
</script>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>