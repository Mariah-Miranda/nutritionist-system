<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

$pageTitle = "Patient Analytics";
requireLogin();

$patient = null;
$allHealthMetrics = [];
$message = '';

$patient_id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);

if (!$patient_id) {
    $_SESSION['error_message'] = "Invalid patient ID provided for analytics.";
    header('Location: ' . BASE_URL . 'list.php'); // ✅ updated path
    exit();
}

try {
    $stmt = $pdo->prepare("SELECT full_name FROM patients WHERE patient_id = :patient_id");
    $stmt->bindParam(':patient_id', $patient_id, PDO::PARAM_INT);
    $stmt->execute();
    $patient = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$patient) {
        $_SESSION['error_message'] = "Patient not found.";
        header('Location: ' . BASE_URL . 'list.php'); // ✅ updated path
        exit();
    }

    $pageTitle = htmlspecialchars($patient['full_name']) . " - Health Analytics";

    $days_back = 30;
    $stmt_all_metrics = $pdo->prepare("SELECT record_date, weight_kg, bmi, systolic_bp, diastolic_bp, blood_sugar_level_mg_dL, blood_sugar_fasting_status FROM patient_health_metrics WHERE patient_id = :patient_id AND record_date >= DATE_SUB(CURDATE(), INTERVAL :days_back DAY) ORDER BY record_date ASC");
    $stmt_all_metrics->bindParam(':patient_id', $patient_id, PDO::PARAM_INT);
    $stmt_all_metrics->bindParam(':days_back', $days_back, PDO::PARAM_INT);
    $stmt_all_metrics->execute();
    $allHealthMetrics = $stmt_all_metrics->fetchAll(PDO::FETCH_ASSOC);

    $initialWeight = null;
    $latestWeight = null;
    $initialBMI = null;
    $latestBMI = null;

    if (!empty($allHealthMetrics)) {
        $initialMetric = $allHealthMetrics[0];
        $latestMetric = $allHealthMetrics[count($allHealthMetrics) - 1];
        $initialWeight = $initialMetric['weight_kg'];
        $latestWeight = $latestMetric['weight_kg'];
        $initialBMI = $initialMetric['bmi'];
        $latestBMI = $latestMetric['bmi'];
    }

    $averageWeightLoss = 0;
    $weightLossPercentage = 0;
    if ($initialWeight !== null && $latestWeight !== null && $initialWeight > 0) {
        $averageWeightLoss = $initialWeight - $latestWeight;
        $weightLossPercentage = ($averageWeightLoss / $initialWeight) * 100;
    }

    $bmiImprovement = 0;
    $bmiReductionPercentage = 0;
    if ($initialBMI !== null && $latestBMI !== null && $initialBMI > 0) {
        $bmiImprovement = $initialBMI - $latestBMI;
        $bmiReductionPercentage = ($bmiImprovement / $initialBMI) * 100;
    }

    // BMI Chart Data
    $bmiDates = [];
    $bmiValues = [];
    foreach ($allHealthMetrics as $entry) {
        $bmiDates[] = $entry['record_date'];
        $bmiValues[] = $entry['bmi'];
    }
    $bmiDatesJson = json_encode($bmiDates);
    $bmiValuesJson = json_encode($bmiValues);

} catch (PDOException $e) {
    error_log("ERROR: " . $e->getMessage());
    $_SESSION['error_message'] = "Error loading patient analytics.";
    header('Location: view.php?id=' . $patient_id);
    exit();
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container mx-auto p-6 bg-white rounded-lg shadow-md">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800"><?php echo htmlspecialchars($patient['full_name']); ?>'s Health Analytics</h2>
        <a href="<?php echo BASE_URL; ?>view.php?id=<?php echo $patient_id; ?>" class="inline-flex items-center space-x-2 px-4 py-2 rounded-lg bg-gray-300 text-gray-800 font-semibold hover:bg-gray-400 transition-colors">
            <i class="fas fa-arrow-left"></i>
            <span>Back to Profile</span>
        </a>
    </div>

    <?php if ($message): ?>
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <?php if (empty($allHealthMetrics)): ?>
        <div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 rounded">
            <p class="font-bold">No Health Metrics Recorded</p>
            <p>Please add metrics via the "Edit client" page.</p>
        </div>
    <?php else: ?>
        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Weight Loss -->
            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 flex items-center space-x-4">
                <div class="p-3 bg-green-100 rounded-full text-green-600">
                    <i class="fas fa-weight-hanging text-2xl"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Average Weight Loss</p>
                    <p class="text-2xl font-bold text-green-700"><?= number_format($averageWeightLoss, 1); ?> kg</p>
                    <p class="text-xs text-gray-500 mt-1">
                        <?= ($weightLossPercentage > 0) ? "<span class='text-green-600'>↓ " . number_format($weightLossPercentage, 1) . "%</span> from initial" : "No significant change"; ?>
                    </p>
                </div>
            </div>

            <!-- BMI -->
            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 flex items-center space-x-4">
                <div class="p-3 bg-blue-100 rounded-full text-blue-600">
                    <i class="fas fa-chart-line text-2xl"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-500">BMI Improvement</p>
                    <p class="text-2xl font-bold text-blue-700"><?= number_format($bmiImprovement, 1); ?> pts</p>
                    <p class="text-xs text-gray-500 mt-1">
                        <?= ($bmiReductionPercentage > 0) ? "<span class='text-blue-600'>↓ " . number_format($bmiReductionPercentage, 1) . "%</span> average reduction" : "No significant change"; ?>
                    </p>
                </div>
            </div>
        </div>

        <!-- Metrics Table -->
        <div class="bg-white p-6 rounded-lg shadow-md border border-gray-200 mb-10">
            <h3 class="text-xl font-semibold text-gray-800 mb-4 border-b border-gray-200 pb-2">Metrics History</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Weight (kg)</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">BMI</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Systolic BP</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Diastolic BP</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Blood Sugar</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Fasting?</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($allHealthMetrics as $metric): ?>
                            <tr>
                                <td class="px-6 py-4 text-sm text-gray-900"><?= htmlspecialchars($metric['record_date']) ?></td>
                                <td class="px-6 py-4 text-sm text-gray-900"><?= number_format($metric['weight_kg'], 1) ?></td>
                                <td class="px-6 py-4 text-sm text-gray-900"><?= number_format($metric['bmi'], 1) ?></td>
                                <td class="px-6 py-4 text-sm text-gray-900"><?= $metric['systolic_bp'] ?? 'N/A' ?></td>
                                <td class="px-6 py-4 text-sm text-gray-900"><?= $metric['diastolic_bp'] ?? 'N/A' ?></td>
                                <td class="px-6 py-4 text-sm text-gray-900"><?= $metric['blood_sugar_level_mg_dL'] ?? 'N/A' ?></td>
                                <td class="px-6 py-4 text-sm text-gray-900"><?= $metric['blood_sugar_fasting_status'] ?? 'N/A' ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- BMI Graph -->
        <div class="mt-10 bg-white p-6 rounded-lg shadow-md border border-gray-200">
            <h3 class="text-xl font-semibold text-gray-800 mb-4 border-b border-gray-200 pb-2">BMI Progress Chart</h3>
            <canvas id="bmiChart" height="120"></canvas>
        </div>

        <!-- Chart.js -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            const bmiLabels = <?= $bmiDatesJson ?>;
            const bmiData = <?= $bmiValuesJson ?>;
            const config = {
                type: 'line',
                data: {
                    labels: bmiLabels,
                    datasets: [{
                        label: 'BMI',
                        data: bmiData,
                        fill: false,
                        borderColor: 'rgb(54, 162, 235)',
                        backgroundColor: 'rgba(54, 162, 235, 0.2)',
                        tension: 0.3,
                        pointRadius: 4,
                        pointHoverRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { position: 'top' },
                        title: { display: true, text: 'BMI Trend Over Time' }
                    },
                    scales: {
                        y: {
                            title: { display: true, text: 'BMI' },
                            beginAtZero: false
                        },
                        x: {
                            title: { display: true, text: 'Date' }
                        }
                    }
                }
            };
            new Chart(document.getElementById('bmiChart'), config);
        </script>
    <?php endif; ?>
</div>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>
