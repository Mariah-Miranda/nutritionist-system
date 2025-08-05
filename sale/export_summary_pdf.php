<?php
ob_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../vendor/autoload.php'; // TCPDF

$pdf = new TCPDF();
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Your App Name');
$pdf->SetTitle('Sales Summary');
$pdf->SetMargins(10, 10, 10);
$pdf->AddPage();

// Fetch daily summary
$dailyStmt = $pdo->query("
    SELECT DATE(sale_date) as day, COUNT(*) as total_sales, SUM(total_amount) as revenue
    FROM sales
    GROUP BY day
    ORDER BY day DESC
");

// Fetch top 5 products
$topStmt = $pdo->query("
    SELECT p.product_name, SUM(si.quantity) as total_sold
    FROM sale_items si
    JOIN products p ON si.product_id = p.id
    GROUP BY si.product_id
    ORDER BY total_sold DESC
    LIMIT 5
");

// Build Daily Sales HTML
$html = '<h2 style="text-align:center;">Sales Summary</h2><br>';

$html .= '<h3>Daily Sales</h3>';
$html .= '<table border="1" cellpadding="5" cellspacing="0" style="width:100%; border-collapse: collapse;">
<thead style="background-color:#f2f2f2;">
<tr>
<th>Date</th>
<th>Total Sales</th>
<th>Revenue (' . htmlspecialchars(DEFAULT_CURRENCY) . ')</th>
</tr>
</thead><tbody>';

if ($dailyStmt->rowCount() > 0) {
    while ($row = $dailyStmt->fetch(PDO::FETCH_ASSOC)) {
        $html .= '<tr>
            <td>' . htmlspecialchars($row['day']) . '</td>
            <td>' . (int)$row['total_sales'] . '</td>
            <td>' . number_format((float)$row['revenue'], 2) . '</td>
        </tr>';
    }
} else {
    $html .= '<tr><td colspan="3" style="text-align:center;">No daily sales data found.</td></tr>';
}
$html .= '</tbody></table><br><br>';

// Build Top 5 Products HTML
$html .= '<h3>Top 5 Best-Selling Products</h3>';
$html .= '<table border="1" cellpadding="5" cellspacing="0" style="width:50%; border-collapse: collapse;">
<thead style="background-color:#f2f2f2;">
<tr>
<th>Product</th>
<th>Units Sold</th>
</tr>
</thead><tbody>';

if ($topStmt->rowCount() > 0) {
    while ($row = $topStmt->fetch(PDO::FETCH_ASSOC)) {
        $html .= '<tr>
            <td>' . htmlspecialchars($row['product_name']) . '</td>
            <td>' . (int)$row['total_sold'] . '</td>
        </tr>';
    }
} else {
    $html .= '<tr><td colspan="2" style="text-align:center;">No best-selling products data found.</td></tr>';
}

$html .= '</tbody></table>';

$pdf->writeHTML($html, true, false, true, false, '');
$pdf->Output('sales_summary_' . '.pdf', 'D');

ob_end_flush();
exit;
