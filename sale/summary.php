<?php
include('../includes/db_connect.php');
include('../includes/header.php'); // Include header for consistent styling and auth

$pageTitle = 'Sales Summary'; // Set page title

// --- Weekly sales query for the CURRENT week (Monday to Sunday) ---
$startOfWeek = date('Y-m-d', strtotime('monday this week'));
$endOfWeek = date('Y-m-d', strtotime('sunday this week'));

$currentWeeklySalesStmt = $pdo->prepare("
    SELECT
        DATE(sale_date) as sale_day,
        SUM(total_amount) AS daily_revenue
    FROM sales
    WHERE sale_date >= ? AND sale_date <= ?
    GROUP BY sale_day
    ORDER BY sale_day ASC
");
$currentWeeklySalesStmt->execute([$startOfWeek, $endOfWeek]);
$currentWeeklySalesRawData = $currentWeeklySalesStmt->fetchAll(PDO::FETCH_ASSOC);

// Prepare data for the current week, ensuring all days are present
$currentWeeklySalesData = [];
$daysOfWeek = [
    'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'
];
$currentDate = strtotime($startOfWeek);
for ($i = 0; $i < 7; $i++) {
    $dayName = date('l', $currentDate);
    $currentWeeklySalesData[$dayName] = 0; // Initialize revenue for each day to 0
    $currentDate = strtotime('+1 day', $currentDate);
}

// Populate with actual sales data
foreach ($currentWeeklySalesRawData as $row) {
    $dayName = date('l', strtotime($row['sale_day']));
    $currentWeeklySalesData[$dayName] = (float)$row['daily_revenue'];
}


// --- Monthly sales query for the CURRENT year (January to December) ---
$startOfYear = date('Y-01-01');
$endOfYear = date('Y-12-31');

$currentMonthlySalesStmt = $pdo->prepare("
    SELECT
        DATE_FORMAT(sale_date, '%Y-%m') AS sale_month,
        SUM(total_amount) AS monthly_revenue
    FROM sales
    WHERE sale_date >= ? AND sale_date <= ?
    GROUP BY sale_month
    ORDER BY sale_month ASC
");
$currentMonthlySalesStmt->execute([$startOfYear, $endOfYear]);
$currentMonthlySalesRawData = $currentMonthlySalesStmt->fetchAll(PDO::FETCH_ASSOC);

// Prepare data for the current year, ensuring all months are present
$currentMonthlySalesData = [];
$monthsOfYear = [];
for ($m = 1; $m <= 12; $m++) {
    $monthKey = date('Y-m', mktime(0, 0, 0, $m, 1, date('Y')));
    $monthName = date('M', mktime(0, 0, 0, $m, 1, date('Y'))); // Abbreviated month name
    $monthsOfYear[$monthKey] = $monthName;
    $currentMonthlySalesData[$monthKey] = 0; // Initialize revenue for each month to 0
}

// Populate with actual sales data
foreach ($currentMonthlySalesRawData as $row) {
    $currentMonthlySalesData[$row['sale_month']] = (float)$row['monthly_revenue'];
}


// Best sellers query (removed pagination logic)
$topStmt = $pdo->query("
    SELECT p.product_name, SUM(si.quantity) as total_sold
    FROM sale_items si
    JOIN products p ON si.product_id = p.id
    GROUP BY si.product_id
    ORDER BY total_sold DESC
    LIMIT 5
");
?>

<div class="container mx-auto p-4 md:p-8">
    <div class="bg-white rounded-lg shadow-lg p-6 md:p-8">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-semibold text-gray-800 m-0">Sales Summary</h2>
            <a href="export_summary_pdf.php" class="inline-flex items-center space-x-2 px-4 py-2 rounded-lg bg-green-600 text-white font-semibold hover:bg-blue-700 transition-colors duration-200 shadow-md">
                <i class="fas fa-download"></i>
                <span>Download </span>
            </a>
        </div>
        
        <!-- Weekly Sales Graph (Line Chart for Current Week's Daily Sales) -->
        <h3 class="text-xl font-medium text-gray-700 mb-4">Current Week's Daily Sales Revenue</h3>
        <div class="mb-8 bg-gray-50 p-4 rounded-lg shadow-inner">
            <canvas id="weeklySalesChart"></canvas>
        </div>

        <!-- Monthly Sales Graph (Line Chart for Current Year's Monthly Sales) -->
        <h3 class="text-xl font-medium text-gray-700 mb-4">Current Year's Monthly Sales Revenue</h3>
        <div class="mb-8 bg-gray-50 p-4 rounded-lg shadow-inner">
            <canvas id="monthlySalesChart"></canvas>
        </div>

        <!-- Removed Daily Sales Table -->

        <h3 class="text-xl font-medium text-gray-700 mb-4">Top 5 Best-Selling Products</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white rounded-lg overflow-hidden">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="py-3 px-4 text-left text-sm font-semibold text-gray-600">Product</th>
                        <th class="py-3 px-4 text-left text-sm font-semibold text-gray-600">Units Sold</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php while ($row = $topStmt->fetch(PDO::FETCH_ASSOC)): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="py-3 px-4 text-gray-800"><?= htmlspecialchars($row['product_name']) ?></td>
                        <td class="py-3 px-4 text-gray-800"><?= (int)$row['total_sold'] ?></td>
                    </tr>
                    <?php endwhile; ?>
                    <?php if ($topStmt->rowCount() === 0): ?>
                        <tr>
                            <td colspan="2" class="text-center py-4 text-gray-500">No best-selling products data found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Removed Pagination Controls for Top Products -->

    </div>
</div>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // PHP data passed to JavaScript
    const currentWeeklySalesData = <?= json_encode($currentWeeklySalesData) ?>;
    const currentMonthlySalesData = <?= json_encode(array_values($currentMonthlySalesData)) ?>; // Pass only values for chart data
    const monthlyLabels = <?= json_encode(array_values($monthsOfYear)) ?>; // Pass only month names for labels
    const defaultCurrency = '<?php echo DEFAULT_CURRENCY; ?>';

    // --- Weekly Sales Chart (Current Week's Daily Sales) ---
    const weeklyCtx = document.getElementById('weeklySalesChart').getContext('2d');
    new Chart(weeklyCtx, {
        type: 'line',
        data: {
            labels: Object.keys(currentWeeklySalesData).map(dayName => {
                const today = new Date();
                const dayIndex = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'].indexOf(dayName);
                const currentDayIndex = today.getDay();
                let date = new Date(today);
                date.setDate(today.getDate() - currentDayIndex + dayIndex);

                return date.toLocaleDateString('en-US', { weekday: 'long', day: 'numeric', month: 'long' });
            }),
            datasets: [{
                label: 'Daily Revenue (' + defaultCurrency + ')',
                data: Object.values(currentWeeklySalesData),
                backgroundColor: 'rgba(75, 192, 192, 0.6)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 2,
                fill: true,
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Revenue'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Day of Week'
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            label += parseFloat(context.raw).toLocaleString('en-US', {
                                style: 'currency',
                                currency: defaultCurrency,
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            });
                            return label;
                        }
                    }
                }
            }
        }
    });

    // --- Monthly Sales Chart (Current Year's Monthly Sales) ---
    const monthlyCtx = document.getElementById('monthlySalesChart').getContext('2d');
    new Chart(monthlyCtx, {
        type: 'line',
        data: {
            labels: monthlyLabels,
            datasets: [{
                label: 'Monthly Revenue (' + defaultCurrency + ')',
                data: currentMonthlySalesData,
                backgroundColor: 'rgba(153, 102, 255, 0.6)',
                borderColor: 'rgba(153, 102, 255, 1)',
                borderWidth: 2,
                fill: true,
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Revenue'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Month'
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            label += parseFloat(context.raw).toLocaleString('en-US', {
                                style: 'currency',
                                currency: defaultCurrency,
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            });
                            return label;
                        }
                    }
                }
            }
        }
    });
});
</script>

<?php
include('../includes/footer.php'); // Include footer
?>
