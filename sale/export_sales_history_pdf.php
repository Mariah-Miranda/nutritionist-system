<?php
ob_start(); // Prevent accidental output

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../vendor/autoload.php'; // Composer autoload for TCPDF

$pdf = new TCPDF();
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Your App Name');
$pdf->SetTitle('Sales History');
$pdf->SetMargins(10, 10, 10);
$pdf->AddPage();

$html = '<h2 style="text-align:center;">Sales History</h2><br><table border="1" cellpadding="4">
<tr style="background-color:#f2f2f2;">
    <th><strong>Sale ID</strong></th>
    <th><strong>Client Name</strong></th>
    <th><strong>Products</strong></th>
    <th><strong>Quantities</strong></th>
    <th><strong>Total</strong></th>
    <th><strong>Date</strong></th>
</tr>';

try {
    $stmt = $pdo->query("
        SELECT 
            s.id AS sale_id,
            s.customer_name,
            s.customer_type,
            s.total_amount,
            s.sale_date,
            p.full_name AS patient_name,
            pr.product_name,
            si.quantity
        FROM sales s
        LEFT JOIN patients p ON s.clients_id = p.patient_id
        LEFT JOIN sale_items si ON s.id = si.sale_id
        LEFT JOIN products pr ON si.product_id = pr.id
        ORDER BY s.sale_date DESC, s.id, pr.product_name
    ");

    $sales = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $id = $row['sale_id'];
        if (!isset($sales[$id])) {
            $sales[$id] = [
                'sale_id' => $id,
                'client_name' => $row['customer_type'] === 'Patient'
                    ? ($row['patient_name'] ?? 'N/A')
                    : ($row['customer_name'] ?? 'N/A'),
                'total_amount' => $row['total_amount'],
                'sale_date' => $row['sale_date'],
                'products' => [],
                'quantities' => []
            ];
        }
        if ($row['product_name']) {
            $sales[$id]['products'][] = $row['product_name'];
            $sales[$id]['quantities'][] = $row['quantity'];
        }
    }

    foreach ($sales as $sale) {
        $productList = implode('<br>', array_map('htmlspecialchars', $sale['products']));
        $quantityList = implode('<br>', array_map('htmlspecialchars', $sale['quantities']));
        $html .= '<tr>
            <td>' . htmlspecialchars($sale['sale_id']) . '</td>
            <td>' . htmlspecialchars($sale['client_name']) . '</td>
            <td>' . $productList . '</td>
            <td>' . $quantityList . '</td>
            <td>' . DEFAULT_CURRENCY . ' ' . number_format($sale['total_amount'], 2) . '</td>
            <td>' . htmlspecialchars($sale['sale_date']) . '</td>
        </tr>';
    }

    $html .= '</table>';
    $pdf->writeHTML($html, true, false, true, false, '');

    $pdf->Output('sales_history_' . '.pdf', 'D');

    ob_end_flush();
    exit;

} catch (PDOException $e) {
    echo "Error generating PDF: " . $e->getMessage();
}
