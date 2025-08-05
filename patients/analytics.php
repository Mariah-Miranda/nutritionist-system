<?php
// ../view/analytics.php - Displays patient health analytics

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php'; // For calculateAgeFromDob if needed, and sanitizeInput

// Set the page title
$pageTitle = "Patient Analytics";

// Require login for this page
requireLogin();

$patient = null;
$allHealthMetrics = [];
$message = '';

// Get patient ID from URL
$patient_id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);

if (!$patient_id) {
    $message = "Invalid patient ID provided for analytics.";
    $_SESSION['error_message'] = $message;
    header('Location: ' . BASE_URL . '../patients/list.php'); // Redirect to patient list
    exit();
}

try {
    // Fetch patient details
    $stmt = $pdo->prepare("SELECT full_name FROM patients WHERE patient_id = :patient_id");
    $stmt->bindParam(':patient_id', $patient_id, PDO::PARAM_INT);
    $stmt->execute();
    $patient = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$patient) {
        $message = "Patient not found.";
        $_SESSION['error_message'] = $message;
        header('Location: ' . BASE_URL . '../patients/list.php'); // Redirect to patient list
        exit();
    }

    $pageTitle = htmlspecialchars($patient['full_name']) . " - Health Analytics";

    // Define the number of days back to fetch data
    $days_back = 30; // Fetch data for the last 30 days (approx. 1 month)

    // Fetch all health metrics for the patient, ordered by date
    $stmt_all_metrics = $pdo->prepare("SELECT record_date, weight_kg, bmi, systolic_bp, diastolic_bp, blood_sugar_level_mg_dL, blood_sugar_fasting_status FROM patient_health_metrics WHERE patient_id = :patient_id AND record_date >= DATE_SUB(CURDATE(), INTERVAL :days_back DAY) ORDER BY record_date ASC");
    $stmt_all_metrics->bindParam(':patient_id', $patient_id, PDO::PARAM_INT);
    $stmt_all_metrics->bindParam(':days_back', $days_back, PDO::PARAM_INT);
    $stmt_all_metrics->execute();
    $allHealthMetrics = $stmt_all_metrics->fetchAll(PDO::FETCH_ASSOC);

    // --- Calculate Summary Metrics ---
    $initialWeight = null;
    $latestWeight = null;
    $initialBMI = null;
    $latestBMI = null;

    if (!empty($allHealthMetrics)) {
        // Get initial and latest metrics
        $initialMetric = $allHealthMetrics[0];
        $latestMetric = $allHealthMetrics[count($allHealthMetrics) - 1];

        $initialWeight = $initialMetric['weight_kg'];
        $latestWeight = $latestMetric['weight_kg'];
        $initialBMI = $initialMetric['bmi'];
        $latestBMI = $latestMetric['bmi'];
    }

    // Calculate Average Weight Loss
    $averageWeightLoss = 0;
    $weightLossPercentage = 0;
    if ($initialWeight !== null && $latestWeight !== null && $initialWeight > 0) {
        $averageWeightLoss = $initialWeight - $latestWeight;
        $weightLossPercentage = ($averageWeightLoss / $initialWeight) * 100;
    }

    // Calculate BMI Improvement
    $bmiImprovement = 0;
    $bmiReductionPercentage = 0;
    if ($initialBMI !== null && $latestBMI !== null && $initialBMI > 0) {
        $bmiImprovement = $initialBMI - $latestBMI; // Assuming improvement means reduction
        $bmiReductionPercentage = ($bmiImprovement / $initialBMI) * 100;
    }

} catch (PDOException $e) {
    error_log("ERROR: Could not fetch patient analytics data: " . $e->getMessage());
    $message = "Error loading patient analytics. Please try again later.";
    $_SESSION['error_message'] = $message;
    header('Location: ' . BASE_URL . 'view.php?id=' . $patient_id); // Redirect back to patient view
    exit();
}

// Include the header
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
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded" role="alert">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <?php if (empty($allHealthMetrics)): ?>
        <div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 rounded-md" role="alert">
            <p class="font-bold">No Health Metrics Recorded</p>
            <p>There is no historical health data available for this client yet. Please add metrics via the "Edit client" page.</p>
        </div>
    <?php else: ?>
        <!-- Top Metric Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Average Weight Loss Card -->
            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 flex items-center space-x-4">
                <div class="p-3 bg-green-100 rounded-full text-green-600">
                    <i class="fas fa-weight-hanging text-2xl"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Average Weight Loss</p>
                    <p class="text-2xl font-bold text-green-700"><?php echo number_format($averageWeightLoss, 1); ?> kg</p>
                    <p class="text-xs text-gray-500 mt-1">
                        <?php if ($weightLossPercentage > 0): ?>
                            <span class="text-green-600">↓ <?php echo number_format($weightLossPercentage, 1); ?>%</span> from initial
                        <?php else: ?>
                            No significant change
                        <?php endif; ?>
                    </p>
                </div>
            </div>

            <!-- BMI Improvement Card -->
            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 flex items-center space-x-4">
                <div class="p-3 bg-blue-100 rounded-full text-blue-600">
                    <i class="fas fa-chart-line text-2xl"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-500">BMI Improvement</p>
                    <p class="text-2xl font-bold text-blue-700"><?php echo number_format($bmiImprovement, 1); ?> pts</p>
                    <p class="text-xs text-gray-500 mt-1">
                        <?php if ($bmiReductionPercentage > 0): ?>
                            <span class="text-blue-600">↓ <?php echo number_format($bmiReductionPercentage, 1); ?>%</span> average reduction
                        <?php else: ?>
                            No significant change
                        <?php endif; ?>
                    </p>
                </div>
            </div>

            <!-- Placeholder for Goal Achievement -->
        

            <!-- Placeholder for Patient Satisfaction -->
            
        </div>

        <!-- Individual Patient Progress: BMI Chart (PHP/HTML/CSS based) -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mt-6">
            <div class="lg:col-span-2 bg-white p-6 rounded-lg shadow-md border border-gray-200">
                <h3 class="text-xl font-semibold text-gray-800 mb-4 border-b border-gray-200 pb-2">Individual Patient Progress - BMI</h3>
                <canvas id="bmiChart" class="h-48"></canvas> <!-- Added h-48 for fixed small height -->
            </div>

            <!-- Health Outcomes Progress Bars -->
            <div class="bg-white p-6 rounded-lg shadow-md border border-gray-200">
                <h3 class="text-xl font-semibold text-gray-800 mb-4 border-b border-gray-200 pb-2">Health Outcomes</h3>
                <div class="space-y-4">
                    <div>
                        <div class="flex justify-between text-sm text-gray-700 mb-1">
                            <span>Weight Loss Success:</span>
                            <span class="font-semibold text-green-700"><?php echo number_format($weightLossPercentage, 0); ?>%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2.5">
                            <div class="bg-green-600 h-2.5 rounded-full" style="width: <?php echo min(100, max(0, $weightLossPercentage)); ?>%;"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between text-sm text-gray-700 mb-1">
                            <span>BMI Improvement:</span>
                            <span class="font-semibold text-blue-700"><?php echo number_format($bmiReductionPercentage, 0); ?>%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2.5">
                            <div class="bg-blue-600 h-2.5 rounded-full" style="width: <?php echo min(100, max(0, $bmiReductionPercentage)); ?>%;"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between text-sm text-gray-700 mb-1">
                            <span>Health Goals Met:</span>
                            <span class="font-semibold text-purple-700">78%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2.5">
                            <div class="bg-purple-600 h-2.5 rounded-full" style="width: 78%;"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between text-sm text-gray-700 mb-1">
                            <span>Appointment Adherence:</span>
                            <span class="font-semibold text-orange-700">91%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2.5">
                            <div class="bg-orange-600 h-2.5 rounded-full" style="width: 91%;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
// Declare chart instances in a broader scope to allow destruction
let bmiChartInstance = null;

document.addEventListener('DOMContentLoaded', function() {
    const healthMetricsData = <?php echo json_encode($allHealthMetrics); ?>;

    // Destroy existing charts if they exist before processing new data
    if (bmiChartInstance) bmiChartInstance.destroy();

    if (healthMetricsData.length === 0) {
        return; // No data to chart, and existing charts are destroyed
    }

    // Prepare data for charts
    const labels = healthMetricsData.map(metric => new Date(metric.record_date).toLocaleDateString());
    const bmis = healthMetricsData.map(metric => parseFloat(metric.bmi));

    // --- BMI Chart (Main Progress Chart) ---
    const bmiCtx = document.getElementById('bmiChart').getContext('2d');
    bmiChartInstance = new Chart(bmiCtx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'BMI',
                    data: bmis,
                    borderColor: 'rgb(54, 162, 235)', // Blue color from image
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    tension: 0.3, // Smoother line
                    fill: false,
                    pointBackgroundColor: 'rgb(54, 162, 235)',
                    pointBorderColor: '#fff',
                    pointHoverBackgroundColor: '#fff',
                    pointHoverBorderColor: 'rgb(54, 162, 235)'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false, // Allow chart to fill container
            animation: false, // Disable animations to make it static
            plugins: {
                title: {
                    display: false // Title moved to H3
                },
                legend: {
                    display: true,
                    position: 'top',
                    align: 'start'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.parsed.y !== null) {
                                label += context.parsed.y.toFixed(2);
                            }
                            return label;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: false, // Let Chart.js determine the optimal start based on data
                    // No 'max' or 'min' set, allowing auto-scaling for better data visibility
                    title: {
                        display: true,
                        text: 'BMI',
                        color: '#555'
                    },
                    grid: {
                        color: 'rgba(0,0,0,0.05)'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Record Date',
                        color: '#555'
                    },
                    grid: {
                        color: 'rgba(0,0,0,0.05)'
                    }
                }
            }
        }
    });
});
</script>

<?php include_once __DIR__ . '/../../includes/footer.php'; ?>
