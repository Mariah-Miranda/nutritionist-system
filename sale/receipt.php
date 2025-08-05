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
$customer_info = null;

if ($sale_id) {
    try {
        // Fetch sale details
        $stmt_sale = $pdo->prepare("SELECT s.*, p.full_name, p.patient_unique_id
                                    FROM sales s
                                    LEFT JOIN patients p ON s.clients_id = p.patient_id
                                    WHERE s.id = ?");
        $stmt_sale->execute([$sale_id]);
        $sale = $stmt_sale->fetch(PDO::FETCH_ASSOC);

        if ($sale) {
            // Fetch sale items
            $stmt_items = $pdo->prepare("SELECT si.*, pr.product_name
                                         FROM sale_items si
                                         JOIN products pr ON si.product_id = pr.id
                                         WHERE si.sale_id = ?");
            $stmt_items->execute([$sale_id]);
            $sale_items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);

            // Determine customer info for display
            if ($sale['customer_type'] === 'Patient') {
                $customer_info = htmlspecialchars($sale['full_name']) . ' (' . htmlspecialchars($sale['patient_unique_id']) . ')';
            } else {
                $customer_info = htmlspecialchars($sale['customer_name']);
            }
        }

    } catch (PDOException $e) {
        error_log("Receipt page database error: " . $e->getMessage());
        $sale = null;
    }
}

// Redirect if sale not found or no ID provided
if (!$sale) {
    echo '<div class="container mx-auto p-8"><p class="text-center text-red-500">Sale not found.</p></div>';
    require_once __DIR__ . '/../includes/footer.php';
    exit();
}

?>
<div class="container mx-auto p-4 md:p-8">
    <div class="bg-white rounded-2xl shadow-xl p-6 sm:p-8 lg:p-12">
        <div class="border-b-2 border-gray-200 pb-4 mb-6 text-center">
            <h2 class="text-3xl font-bold text-gray-800">Sale Receipt</h2>
            <p class="text-lg text-gray-600">Sale ID: #<?= htmlspecialchars($sale['id']) ?></p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
            <div>
                <h3 class="text-xl font-semibold text-gray-700">Customer Details</h3>
                <p class="text-gray-600"><strong>Type:</strong> <?= htmlspecialchars($sale['customer_type']) ?></p>
                <p class="text-gray-600"><strong>Name:</strong> <?= $customer_info ?></p>
                <p class="text-gray-600"><strong>Date:</strong> <?= htmlspecialchars($sale['sale_date']) ?></p>
            </div>
        </div>

        <h3 class="text-xl font-semibold text-gray-700 mb-4 border-b pb-2">Items Purchased</h3>
        <div class="overflow-x-auto mb-8 rounded-lg shadow-sm border border-gray-200">
            <table class="min-w-full bg-white">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="py-3 px-4 text-left text-sm font-semibold text-gray-600">Product</th>
                        <th class="py-3 px-4 text-left text-sm font-semibold text-gray-600">Quantity</th>
                        <th class="py-3 px-4 text-left text-sm font-semibold text-gray-600">Unit Price</th>
                        <th class="py-3 px-4 text-left text-sm font-semibold text-gray-600">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php
                    $subtotal = 0;
                    foreach ($sale_items as $item):
                        $item_total = $item['quantity'] * $item['price'];
                        $subtotal += $item_total;
                        ?>
                    <tr class="hover:bg-gray-50">
                        <td class="py-3 px-4 text-gray-800"><?= htmlspecialchars($item['product_name']) ?></td>
                        <td class="py-3 px-4 text-gray-800"><?= (int)$item['quantity'] ?></td>
                        <td class="py-3 px-4 text-gray-800"><?= DEFAULT_CURRENCY . ' ' . number_format($item['price'], 2) ?></td>
                        <td class="py-3 px-4 text-gray-800"><?= DEFAULT_CURRENCY . ' ' . number_format($item_total, 2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <div class="flex justify-end mb-8">
            <div class="w-full sm:w-1/2 md:w-1/3 space-y-2">
                <div class="flex justify-between text-lg text-gray-700">
                    <span>Subtotal:</span>
                    <span><?= DEFAULT_CURRENCY . ' ' . number_format($subtotal, 2) ?></span>
                </div>
                <div class="flex justify-between text-lg text-gray-700">
                    <span>Tax:</span>
                    <span><?= DEFAULT_CURRENCY . ' ' . number_format($sale['total_amount'] - $subtotal, 2) ?></span>
                </div>
                <div class="flex justify-between text-2xl font-bold text-gray-800 border-t-2 border-gray-300 pt-2 mt-2">
                    <span>Total:</span>
                    <span><?= DEFAULT_CURRENCY . ' ' . number_format($sale['total_amount'], 2) ?></span>
                </div>
            </div>
        </div>

        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <button onclick="printPosReceipt(<?= $sale_id ?>)" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg shadow-md transition duration-200 ease-in-out transform hover:scale-105">
                Print POS Receipt
            </button>
            <a href="new.php" class="text-center bg-gray-600 hover:bg-gray-700 text-white font-bold py-3 px-6 rounded-lg shadow-md transition duration-200 ease-in-out transform hover:scale-105">
                New Sale
            </a>
            <a href="history.php" class="text-center bg-gray-600 hover:bg-gray-700 text-white font-bold py-3 px-6 rounded-lg shadow-md transition duration-200 ease-in-out transform hover:scale-105">
                View Sales History
            </a>
        </div>
    </div>
</div>

<script>
    function printPosReceipt(saleId) {
        const printWindow = window.open(`pos_receipt_print.php?sale_id=${saleId}`, '_blank', 'width=320,height=600,scrollbars=yes,resizable=yes');
        if (printWindow) {
            printWindow.focus();
        } else {
            // Using a custom message box instead of alert
            const messageBox = document.getElementById('messageBox');
            const messageBoxTitle = document.getElementById('messageBoxTitle');
            const messageBoxContent = document.getElementById('messageBoxContent');
            messageBoxTitle.textContent = 'Pop-up Blocked';
            messageBoxContent.textContent = 'Please allow pop-ups for this site to print the POS receipt.';
            messageBox.classList.remove('hidden');
        }
    }
</script>
<!-- Custom Message Box for pop-up blocked warning -->
<div id="messageBox" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <h3 id="messageBoxTitle" class="text-lg font-bold">Pop-up Blocked</h3>
        <div class="mt-2 px-7 py-3">
            <p id="messageBoxContent" class="text-sm text-gray-600">Please allow pop-ups for this site to print the POS receipt.</p>
        </div>
        <div class="mt-4 text-center">
            <button onclick="document.getElementById('messageBox').classList.add('hidden')" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Close
            </button>
        </div>
    </div>
</div>

<?php
include('../includes/footer.php'); // Include footer
?>
