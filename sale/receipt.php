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
        max-width: 800px; /* Standard page width */
        margin: 20px auto; /* Center the receipt with margins */
        background-color: #fff;
        padding: 20px; /* Standard padding */
        font-family: 'Inter', sans-serif; /* Use Inter font */
        color: #333;
        font-size: 14px; /* Standard font size */
        line-height: 1.5;
        border-radius: 0.75rem; /* rounded-lg */
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05); /* shadow-lg */
    }

    /* Hide elements not needed for print */
    @media print {
        body * {
            visibility: hidden;
        }
        #receipt-area, #receipt-area * {
            visibility: visible;
        }
        #receipt-area {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            margin: 0;
            padding: 0;
            box-shadow: none;
            border-radius: 0;
            font-size: 12px; /* Slightly smaller for print */
            line-height: 1.3;
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
            padding: 4px 0; /* Compact padding for table cells */
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

    /* Standard page styles */
    .receipt-header, .receipt-footer {
        text-align: center;
        margin-bottom: 20px;
    }
    .receipt-header h2 {
        font-size: 2.25rem; /* text-3xl */
        font-weight: 700; /* font-bold */
        color: #1f2937; /* gray-800 */
        margin-bottom: 1.5rem; /* mb-6 */
    }
    .receipt-header p {
        font-size: 0.875rem; /* text-sm */
        color: #6b7280; /* gray-500 */
    }
    .receipt-info, .receipt-summary {
        margin-bottom: 20px;
    }
    .receipt-info p, .receipt-summary div {
        display: flex;
        justify-content: space-between;
        margin-bottom: 8px; /* mb-2 */
        color: #4b5563; /* gray-700 */
    }
    .receipt-info p span, .receipt-summary div span {
        font-weight: 600; /* font-semibold */
        color: #1f2937; /* gray-800 */
    }
    .receipt-item-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
    }
    .receipt-item-table th, .receipt-item-table td {
        padding: 0.75rem; /* p-3 */
        text-align: left;
        border-bottom: 1px solid #e5e7eb; /* border-b border-gray-200 */
        color: #374151; /* gray-700 */
    }
    .receipt-item-table th {
        background-color: #f9fafb; /* bg-gray-50 */
        font-weight: 600; /* font-semibold */
        text-transform: uppercase;
        font-size: 0.75rem; /* text-xs */
        color: #6b7280; /* gray-600 */
    }
    .receipt-item-table td {
        font-size: 0.875rem; /* text-sm */
    }
    .receipt-item-table td:nth-child(2),
    .receipt-item-table td:nth-child(3),
    .receipt-item-table td:nth-child(4) {
        text-align: right;
    }

    .receipt-summary .total {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 1.25rem; /* text-xl */
        font-weight: 700; /* font-bold */
        color: #111827; /* gray-900 */
        border-top: 1px solid #d1d5db; /* border-t border-gray-300 */
        padding-top: 0.5rem; /* pt-2 */
        margin-top: 0.5rem; /* mt-2 */
    }
    .receipt-summary .total span:last-child {
        font-weight: 800; /* font-extrabold */
    }
</style>

<div class="container mx-auto p-4 lg:p-8">
    <div id="receipt-area" class="bg-white rounded-2xl shadow-xl p-4 sm:p-6 lg:p-8">
        <div class="receipt-header">
            <h2 class="text-3xl font-bold text-gray-800 mb-6">Sale Receipt</h2>
            <p class="text-sm text-gray-500">SMART FOOD, Kampala, Uganda</p>
            <p class="text-sm text-gray-500">Phone: +123 456 7890 | Email: info@smartfood.com</p>
            <p class="text-sm text-gray-500">Receipt #<?php echo htmlspecialchars($sale_id); ?></p>
            <p class="text-sm text-gray-500">Date: <?php echo htmlspecialchars(date('Y-m-d H:i:s', strtotime($sale['sale_date'] ?? 'now'))); ?></p>
            <hr class="my-4 border-gray-200">
        </div>

        <?php if ($sale): ?>
            <div class="mb-6 border border-gray-200 rounded-lg p-4">
                <h3 class="text-xl font-semibold text-gray-700 mb-4">Customer Details</h3>
                <div class="receipt-info">
                    <?php if ($sale['customer_type'] === 'Patient'): ?>
                        <p>Customer Type: <span>Patient</span></p>
                        <p>Patient Name: <span><?php echo htmlspecialchars($sale['full_name'] ?? 'N/A'); ?></span></p>
                        <p>Patient ID: <span><?php
                            // Display only the last 6 characters of the patient_unique_id
                            $display_patient_id = $sale['patient_unique_id'] ?? 'N/A';
                            echo htmlspecialchars(substr($display_patient_id, -6));
                        ?></span></p>
                        <p>Membership Status: <span><?php echo htmlspecialchars($sale['membership_status'] ?? 'N/A'); ?></span></p>
                    <?php else: ?>
                        <p>Customer Type: <span><?php echo htmlspecialchars($sale['customer_type']); ?></span></p>
                        <p>Customer Name: <span><?php echo htmlspecialchars($sale['customer_name'] ?? 'N/A'); ?></span></p>
                        <?php if (!empty($sale['customer_phone'])): ?>
                            <p>Customer Phone: <span><?php echo htmlspecialchars($sale['customer_phone']); ?></span></p>
                        <?php endif; ?>
                    <?php endif; ?>
                    <?php if (!empty($_SESSION['username'])): ?>
                    <p>Cashier: <span><?php echo htmlspecialchars($_SESSION['username']); ?></span></p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="mb-6 border border-gray-200 rounded-lg p-4">
                <h3 class="text-xl font-semibold text-gray-700 mb-4">Sale Items</h3>
                <table class="receipt-item-table w-full">
                    <thead>
                        <tr>
                            <th class="py-2 px-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Product</th>
                            <th class="py-2 px-4 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Quantity</th>
                            <th class="py-2 px-4 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Price</th>
                            <th class="py-2 px-4 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($sale_items)): ?>
                            <?php foreach ($sale_items as $item): ?>
                                <tr class="bg-white">
                                    <td class="py-3 px-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo htmlspecialchars($item['product_name']); ?></td>
                                    <td class="py-3 px-4 whitespace-nowrap text-sm text-gray-700 text-right"><?php echo DEFAULT_CURRENCY . ' ' . number_format($item['price'], 2); ?></td>
                                    <td class="py-3 px-4 whitespace-nowrap text-sm text-gray-700 text-center"><?php echo htmlspecialchars($item['quantity']); ?></td>
                                    <td class="py-3 px-4 whitespace-nowrap text-sm text-gray-700 text-right"><?php echo DEFAULT_CURRENCY . ' ' . number_format($item['subtotal'], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr class="bg-white">
                                <td colspan="4" class="py-3 px-4 text-center text-sm text-gray-600">No items found for this sale.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="mb-6 border border-gray-200 rounded-lg p-4">
                <h3 class="text-xl font-semibold text-gray-700 mb-4">Summary</h3>
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

                        $member_discount_amount = $subtotal_after_item_discounts * ((float)($sale['discount_percent'] ?? 0) / 100);
                        $subtotal_after_member_discount = $subtotal_after_item_discounts - $member_discount_amount;
                        $tax_amount = $subtotal_after_member_discount * (TAX_RATE_PERCENT / 100);
                    ?>
                    <div>
                        <span>Subtotal:</span>
                        <span class="font-semibold text-gray-800"><?php echo DEFAULT_CURRENCY . ' ' . number_format($subtotal_before_item_discounts, 2); ?></span>
                    </div>
                    <?php if ($total_item_discounts > 0): ?>
                    <div>
                        <span>Item Discounts:</span>
                        <span class="font-semibold text-gray-800">- <?php echo DEFAULT_CURRENCY . ' ' . number_format($total_item_discounts, 2); ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if ($member_discount_amount > 0): ?>
                    <div>
                        <span>Member Discount (<?php echo htmlspecialchars($sale['discount_percent']); ?>%):</span>
                        <span class="font-semibold text-gray-800">- <?php echo DEFAULT_CURRENCY . ' ' . number_format($member_discount_amount, 2); ?></span>
                    </div>
                    <?php endif; ?>
                    <div>
                        <span>Tax (<?php echo htmlspecialchars(TAX_RATE_PERCENT); ?>%):</span>
                        <span class="font-semibold text-gray-800">+ <?php echo DEFAULT_CURRENCY . ' ' . number_format($tax_amount, 2); ?></span>
                    </div>
                    <div class="total">
                        <span>Total Amount:</span>
                        <span class="font-extrabold"><?php echo DEFAULT_CURRENCY . ' ' . number_format($sale['total_amount'], 2); ?></span>
                    </div>
                    <p class="text-sm text-gray-700 mt-2">Payment Method: <span class="font-semibold"><?php echo htmlspecialchars($sale['payment_method'] ?? 'N/A'); ?></span></p>
                </div>
            </div>

            <div class="text-center py-4">
                <p class="text-gray-600 text-sm">Thank you for your purchase!</p>
                <p class="text-gray-600 text-xs mt-1">Please come again.</p>
            </div>

        <?php else: ?>
            <div class="text-center py-8">
                <p class="text-gray-600 text-lg">Sale receipt not found or invalid sale ID.</p>
            </div>
        <?php endif; ?>
    </div>

    <div class="flex justify-center mt-6 no-print">
        <?php if ($sale): ?>
            <button onclick="printPosReceipt(<?php echo htmlspecialchars($sale_id); ?>)" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg mr-4">
                <i class="fas fa-print mr-2"></i> Print POS Receipt
            </button>
        <?php endif; ?>
        <a href="new.php" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg mr-2">New Sale</a>
        <a href="history.php" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-lg">View Sales History</a>
    </div>
</div>

<script>
    function printPosReceipt(saleId) {
        const printWindow = window.open(`pos_receipt_print.php?sale_id=${saleId}`, '_blank', 'width=320,height=600,scrollbars=yes,resizable=yes');
        if (printWindow) {
            printWindow.focus();
        } else {
            showMessage('Pop-up Blocked', 'Please allow pop-ups for this site to print the POS receipt.');
        }
    }

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
            alert(`${title}\n\n${message}`);
        }
    }
</script>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
