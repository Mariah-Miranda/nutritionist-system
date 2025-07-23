<?php
// sales/receipt.php

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/auth.php';

$pageTitle = 'Sale Receipt';
require_once __DIR__ . '/../includes/header.php';

$sale_id = filter_input(INPUT_GET, 'sale_id', FILTER_VALIDATE_INT);

$sale = null;
$sale_items = [];
$patient_info = null;

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
        $sale = null; // Ensure sale is null on error
    }
}
?>

<div class="container mx-auto p-4 md:p-8">
    <div class="bg-white rounded-lg shadow-lg p-6 md:p-8">
        <h2 class="text-2xl font-semibold text-gray-800 mb-6">Sale Receipt #<?php echo htmlspecialchars($sale_id); ?></h2>

        <?php if ($sale): ?>
            <div class="mb-6 border border-gray-200 rounded-lg p-4">
                <h3 class="text-xl font-medium text-gray-700 mb-4">Sale Details</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-gray-700">
                    <div><strong>Customer Type:</strong> <?php echo htmlspecialchars($sale['customer_type'] ?? 'N/A'); ?></div>
                    <?php if ($sale['customer_type'] === 'Patient'): ?>
                        <div><strong>Patient Name:</strong> <?php echo htmlspecialchars($sale['full_name'] ?? 'N/A'); ?></div>
                        <div><strong>Patient ID:</strong> <?php echo htmlspecialchars($sale['patient_unique_id'] ?? 'N/A'); ?></div>
                        <div><strong>Membership:</strong> <?php echo htmlspecialchars($sale['membership_status'] ?? 'N/A'); ?></div>
                    <?php else: ?>
                        <div><strong>Customer Name:</strong> <?php echo htmlspecialchars($sale['customer_name'] ?? 'N/A'); ?></div>
                        <div><strong>Customer Phone:</strong> <?php echo htmlspecialchars($sale['customer_phone'] ?? 'N/A'); ?></div>
                    <?php endif; ?>
                    <div><strong>Sale Date:</strong> <?php echo htmlspecialchars(date('Y-m-d H:i:s', strtotime($sale['sale_date']))); ?></div>
                    <div><strong>Member Discount Applied:</strong> <?php echo htmlspecialchars($sale['discount_percent']); ?>%</div>
                    <div><strong>Payment Method:</strong> <?php echo htmlspecialchars($sale['payment_method'] ?? 'N/A'); ?></div>
                </div>
            </div>

            <div class="mb-6 border border-gray-200 rounded-lg p-4 overflow-x-auto">
                <h3 class="text-xl font-medium text-gray-700 mb-4">Items Purchased</h3>
                <table class="min-w-full bg-white rounded-lg overflow-hidden">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="py-3 px-4 text-left text-sm font-semibold text-gray-600">Product</th>
                            <th class="py-3 px-4 text-left text-sm font-semibold text-gray-600">Price (<?php echo DEFAULT_CURRENCY; ?>)</th>
                            <th class="py-3 px-4 text-left text-sm font-semibold text-gray-600">Qty</th>
                            <th class="py-3 px-4 text-left text-sm font-semibold text-gray-600">Subtotal (<?php echo DEFAULT_CURRENCY; ?>)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($sale_items)): ?>
                            <?php foreach ($sale_items as $item): ?>
                                <tr class="border-b border-gray-200 last:border-b-0">
                                    <td class="py-3 px-4 text-gray-800"><?php echo htmlspecialchars($item['product_name']); ?></td>
                                    <td class="py-3 px-4 text-gray-800"><?php echo DEFAULT_CURRENCY . ' ' . number_format($item['price'], 2); ?></td>
                                    <td class="py-3 px-4 text-gray-800"><?php echo htmlspecialchars($item['quantity']); ?></td>
                                    <td class="py-3 px-4 text-gray-800 font-medium"><?php echo DEFAULT_CURRENCY . ' ' . number_format($item['subtotal'], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center py-4 text-gray-500">No items found for this sale.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="mb-6 bg-gray-50 rounded-lg p-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                <?php
                    // Recalculate subtotal before member discount for display
                    $calculated_subtotal_before_discount = array_reduce($sale_items, function($sum, $item) {
                        return $sum + ($item['price'] * $item['quantity']);
                    }, 0);

                    $calculated_member_discount_amount = $calculated_subtotal_before_discount * ($sale['discount_percent'] / 100);
                    $subtotal_after_member_discount = $calculated_subtotal_before_discount - $calculated_member_discount_amount;
                    $tax_amount = $subtotal_after_member_discount * (TAX_RATE_PERCENT / 100);
                ?>
                <div class="flex justify-between items-center text-lg font-medium text-gray-700">
                    <span>Subtotal (before member discount):</span>
                    <span class="font-semibold text-green-800"><?php echo DEFAULT_CURRENCY . ' ' . number_format($calculated_subtotal_before_discount, 2); ?></span>
                </div>
                <div class="flex justify-between items-center text-lg font-medium text-gray-700">
                    <span>Member Discount (<?php echo htmlspecialchars($sale['discount_percent']); ?>%):</span>
                    <span class="font-semibold text-red-600"><?php echo DEFAULT_CURRENCY . ' ' . number_format($calculated_member_discount_amount, 2); ?></span>
                </div>
                <div class="flex justify-between items-center text-lg font-medium text-gray-700">
                    <span>Subtotal (after member discount):</span>
                    <span class="font-semibold text-green-800"><?php echo DEFAULT_CURRENCY . ' ' . number_format($subtotal_after_member_discount, 2); ?></span>
                </div>
                <div class="flex justify-between items-center text-lg font-medium text-gray-700">
                    <span>Tax (<?php echo htmlspecialchars(TAX_RATE_PERCENT); ?>%):</span>
                    <span class="font-semibold text-green-800"><?php echo DEFAULT_CURRENCY . ' ' . number_format($tax_amount, 2); ?></span>
                </div>
                <div class="flex justify-between items-center text-xl font-bold text-gray-800 border-t-2 border-gray-300 pt-2 mt-2">
                    <span>Total Amount:</span>
                    <span class="font-extrabold text-green-900"><?php echo DEFAULT_CURRENCY . ' ' . number_format($sale['total_amount'], 2); ?></span>
                </div>
            </div>

            <div class="flex justify-end mt-6">
                <a href="new.php" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg mr-2">New Sale</a>
                <a href="history.php" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-lg">View Sales History</a>
            </div>

        <?php else: ?>
            <div class="text-center py-8">
                <p class="text-gray-600 text-lg">Sale receipt not found or invalid sale ID.</p>
                <a href="new.php" class="mt-4 inline-block bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg">Go to New Sale</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
require_once __DIR__ . '/../includes/footer.php'; // Assuming you have a footer.php
?>
