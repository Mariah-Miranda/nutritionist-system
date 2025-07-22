<?php
// admin/reports/export_sales.php - Exports sales data to CSV

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/db_connect.php';
require_once __DIR__ . '/../../includes/auth.php';

requireLogin();
// Optional: Add role-based access control if only certain roles can export reports
// if (!hasRole('Admin') || !hasRole('Sales')) {
//     $_SESSION['error_message'] = "Access denied. You do not have permission to export sales reports.";
//     header('Location: ' . BASE_URL . 'admin/reports/report.php');
//     exit();
// }

try {
    $sql_sales = "SELECT s.id AS sale_id, s.sale_date, s.total_amount, s.discount_percent, c.name AS client_name
                  FROM sales s
                  LEFT JOIN clients c ON s.clients_id = c.id
                  ORDER BY s.sale_date DESC";
    $stmt_sales = $pdo->query($sql_sales);
    $sales = $stmt_sales->fetchAll(PDO::FETCH_ASSOC);

    // Set headers for CSV download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="sales_report_' . date('Y-m-d') . '.csv"');

    // Create a file pointer connected to the output stream
    $output = fopen('php://output', 'w');

    // Output the column headings
    fputcsv($output, ['Sale ID', 'Sale Date', 'Total Amount', 'Discount Percent', 'Client Name']);

    // Loop through the sales and output each row
    if (!empty($sales)) {
        foreach ($sales as $sale) {
            // Format sale_date
            $sale['sale_date'] = $sale['sale_date'] ? date('Y-m-d H:i:s', strtotime($sale['sale_date'])) : '';
            fputcsv($output, $sale);
        }
    }
    
    fclose($output);
    exit();

} catch (PDOException $e) {
    error_log("ERROR: Could not export sales report: " . $e->getMessage());
    $_SESSION['error_message'] = "Error exporting sales report. Please try again later.";
    header('Location: ' . BASE_URL . 'admin/reports/report.php');
    exit();
}
?>
