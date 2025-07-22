<?php
// view/analytics.php - Displays patient health analytics

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/db_connect.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php'; // For calculateAgeFromDob if needed, and sanitizeInput

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
    header('Location: ' . BASE_URL . 'list.php'); // Redirect to patient list
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
        header('Location: ' . BASE_URL . 'list.php'); // Redirect to patient list
        exit();
    }

    $pageTitle = htmlspecialchars($patient['full_name']) . " - Health Analytics";

    // Fetch all health metrics for the patient, ordered by date
    $stmt_all_metrics = $pdo->prepare("SELECT record_date, weight_kg, bmi, systolic_bp, diastolic_bp, blood_sugar_level_mg_dL, blood_sugar_fasting_status FROM patient_health_metrics WHERE patient_id = :patient_id ORDER BY record_date ASC");
    $stmt_all_metrics->bindParam(':patient_id', $patient_id, PDO::PARAM_INT);
    $stmt_all_metrics->execute();
    $allHealthMetrics = $stmt_all_metrics->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("ERROR: Could not fetch patient analytics data: " . $e->getMessage());
    $message = "Error loading patient analytics. Please try again later.";
    $_SESSION['error_message'] = $message;
    header('Location: ' . BASE_URL . 'patients/list.php' . $patient_id); // Redirect back to patient view
    exit();
}

// Include the header
require_once __DIR__ . '/../../includes/header.php';
?>

<!-- Include Chart.js from CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.0/dist/chart.min.js"></script>

<div class="container mx-auto p-6 bg-white rounded-lg shadow-md">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800"><?php echo htmlspecialchars($patient['full_name']); ?>'s Health Analytics</h2>
        <a href="<?php echo BASE_URL; ?>../view.php?id=<?php echo $patient_id; ?>" class="inline-flex items-center space-x-2 px-4 py-2 rounded-lg bg-gray-300 text-gray-800 font-semibold hover:bg-gray-400 transition-colors">
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
            <p>There is no historical health data available for this patient yet. Please add metrics via the "Edit Patient" page.</p>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mt-6">
            <!-- Weight & BMI Chart -->
            <div class="bg-white p-6 rounded-lg shadow-md border border-gray-200">
                <h3 class="text-xl font-semibold text-gray-800 mb-4 border-b border-gray-200 pb-2">Weight & BMI Trends</h3>
                <canvas id="weightBmiChart"></canvas>
            </div>

            <!-- Blood Pressure Chart -->
            <div class="bg-white p-6 rounded-lg shadow-md border border-gray-200">
                <h3 class="text-xl font-semibold text-gray-800 mb-4 border-b border-gray-200 pb-2">Blood Pressure Trends</h3>
                <canvas id="bpChart"></canvas>
            </div>

            <!-- Blood Sugar Chart -->
            <div class="bg-white p-6 rounded-lg shadow-md border border-gray-200 lg:col-span-2">
                <h3 class="text-xl font-semibold text-gray-800 mb-4 border-b border-gray-200 pb-2">Blood Sugar Trends</h3>
                <canvas id="bloodSugarChart"></canvas>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const healthMetricsData = <?php echo json_encode($allHealthMetrics); ?>;

    if (healthMetricsData.length === 0) {
        return; // No data to chart
    }

    // Prepare data for charts
    const labels = healthMetricsData.map(metric => new Date(metric.record_date).toLocaleDateString());
    const weights = healthMetricsData.map(metric => parseFloat(metric.weight_kg));
    const bmis = healthMetricsData.map(metric => parseFloat(metric.bmi));
    const systolicBps = healthMetricsData.map(metric => parseFloat(metric.systolic_bp));
    const diastolicBps = healthMetricsData.map(metric => parseFloat(metric.diastolic_bp));
    const bloodSugars = healthMetricsData.map(metric => parseFloat(metric.blood_sugar_level_mg_dL));

    // --- Weight & BMI Chart ---
    const weightBmiCtx = document.getElementById('weightBmiChart').getContext('2d');
    new Chart(weightBmiCtx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Weight (kg)',
                    data: weights,
                    borderColor: 'rgb(75, 192, 192)',
                    tension: 0.1,
                    fill: false
                },
                {
                    label: 'BMI',
                    data: bmis,
                    borderColor: 'rgb(153, 102, 255)',
                    tension: 0.1,
                    fill: false
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'Weight and BMI Over Time'
                }
            },
            scales: {
                y: {
                    beginAtZero: false
                }
            }
        }
    });

    // --- Blood Pressure Chart ---
    const bpCtx = document.getElementById('bpChart').getContext('2d');
    new Chart(bpCtx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Systolic BP (mmHg)',
                    data: systolicBps,
                    borderColor: 'rgb(255, 99, 132)',
                    tension: 0.1,
                    fill: false
                },
                {
                    label: 'Diastolic BP (mmHg)',
                    data: diastolicBps,
                    borderColor: 'rgb(54, 162, 235)',
                    tension: 0.1,
                    fill: false
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'Blood Pressure Over Time'
                }
            },
            scales: {
                y: {
                    beginAtZero: false
                }
            }
        }
    });

    // --- Blood Sugar Chart ---
    const bloodSugarCtx = document.getElementById('bloodSugarChart').getContext('2d');
    new Chart(bloodSugarCtx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Blood Sugar (mg/dL)',
                    data: bloodSugars,
                    borderColor: 'rgb(255, 205, 86)',
                    tension: 0.1,
                    fill: false
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'Blood Sugar Over Time'
                }
            },
            scales: {
                y: {
                    beginAtZero: false
                }
            }
        }
    });
});
</script>

<?php include_once __DIR__ . '/../../includes/footer.php'; ?>
