<?php
// view.php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php'; // For sanitizeInput and calculateAgeFromDob

// Set the page title for the header (will be dynamic)
$pageTitle = "Patient Profile";

// Require login for this page
requireLogin();
// Uncomment and refine roles as needed
// if (!hasAnyRole(['Admin', 'Nutritionist', 'Staff', 'Sales'])) {
//     header('Location: ' . BASE_URL . 'admin/index.php?message=Access denied. You do not have permission to view patient profiles.');
//     exit();
// }

$patient = null;
$latestMetrics = [];
$message = '';
$aiRecommendations = []; // Initialize AI recommendations array

// Get patient ID from URL
$patient_id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);

if (!$patient_id) {
    $message = "Invalid patient ID provided.";
    // Redirect back to patient list if no valid ID
    header('Location: ' . BASE_URL . 'list.php?message=' . urlencode($message));
    exit();
}

try {
    // Fetch patient details
    $stmt = $pdo->prepare("SELECT * FROM patients WHERE patient_id = :patient_id");
    $stmt->bindParam(':patient_id', $patient_id, PDO::PARAM_INT);
    $stmt->execute();
    $patient = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$patient) {
        $message = "Patient not found.";
        header('Location: ' . BASE_URL . 'list.php?message=' . urlencode($message));
        exit();
    }

    // Set dynamic page title
    $pageTitle = htmlspecialchars($patient['full_name']) . " - Profile";

    // Fetch latest health metrics for the patient
    $stmt_metrics = $pdo->prepare("SELECT * FROM patient_health_metrics WHERE patient_id = :patient_id ORDER BY record_date DESC LIMIT 1");
    $stmt_metrics->bindParam(':patient_id', $patient_id, PDO::PARAM_INT);
    $stmt_metrics->execute();
    $latestMetrics = $stmt_metrics->fetch(PDO::FETCH_ASSOC);

    // Fetch all health metrics for analytics (for chart data later)
    $stmt_all_metrics = $pdo->prepare("SELECT record_date, weight_kg, bmi, systolic_bp, diastolic_bp, blood_sugar_level_mg_dL, blood_sugar_fasting_status FROM patient_health_metrics WHERE patient_id = :patient_id ORDER BY record_date ASC");
    $stmt_all_metrics->bindParam(':patient_id', $patient_id, PDO::PARAM_INT);
    $stmt_all_metrics->execute();
    $allHealthMetrics = $stmt_all_metrics->fetchAll(PDO::FETCH_ASSOC);

    // Check for AI recommendations in session
    if (isset($_SESSION['ai_recommendations']) && !empty($_SESSION['ai_recommendations'])) {
        $aiRecommendations = $_SESSION['ai_recommendations'];
        unset($_SESSION['ai_recommendations']); // Clear recommendations from session after displaying
    }

} catch (PDOException $e) {
    error_log("Error fetching patient details: " . $e->getMessage());
    $message = "Error loading patient profile. Please try again later.";
    header('Location: ' . BASE_URL . 'list.php?message=' . urlencode($message));
    exit();
}

// Include the header
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container mx-auto p-6 bg-white rounded-lg shadow-md">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800"><?php echo htmlspecialchars($patient['full_name']); ?>'s Profile</h2>
        <div class="flex space-x-3">
            <a href="<?php echo BASE_URL; ?>edit.php?id=<?php echo $patient['patient_id']; ?>" class="inline-flex items-center space-x-2 px-4 py-2 rounded-lg bg-blue-600 text-white font-semibold hover:bg-blue-700 transition-colors duration-200 shadow-md">
                <i class="fas fa-edit"></i>
                <span>Edit Patient</span>
            </a>
            <a href="<?php echo BASE_URL; ?>add.php" class="inline-flex items-center space-x-2 px-4 py-2 rounded-lg bg-green-600 text-white font-semibold hover:bg-green-700 transition-colors duration-200 shadow-md">
                <i class="fas fa-plus"></i>
                <span>Add New Patient</span>
            </a>
        </div>
    </div>

    <?php if ($message): ?>
        <div class="bg-<?php echo strpos($message, 'successfully') !== false ? 'green' : 'red'; ?>-100 border-l-4 border-<?php echo strpos($message, 'successfully') !== false ? 'green' : 'red'; ?>-500 text-<?php echo strpos($message, 'successfully') !== false ? 'green' : 'red'; ?>-700 p-4 mb-6 rounded" role="alert">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <!-- AI Recommendations Section -->
    <?php if (!empty($aiRecommendations)): ?>
        <div class="bg-blue-50 border-l-4 border-blue-500 text-blue-700 p-4 rounded-md mb-6" role="alert">
            <p class="font-bold mb-2">AI Recommendations:</p>
            <ul class="list-disc list-inside space-y-1">
                <?php foreach ($aiRecommendations as $rec): ?>
                    <li><?php echo htmlspecialchars($rec); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Patient Details Card -->
        <div class="md:col-span-2 bg-white p-6 rounded-lg shadow-md border border-gray-200">
            <h3 class="text-xl font-semibold text-gray-800 mb-4 border-b border-gray-200 pb-2">Personal Information</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-3 gap-x-6 text-gray-700">
                <div><strong>Patient ID:</strong> <span class="text-gray-900"><?php echo htmlspecialchars($patient['patient_unique_id']); ?></span></div>
                <div><strong>Date of Birth:</strong> <span class="text-gray-900"><?php echo htmlspecialchars($patient['date_of_birth'] ?? 'N/A'); ?></span></div>
                <div><strong>Age:</strong> <span class="text-gray-900"><?php echo htmlspecialchars(calculateAgeFromDob($patient['date_of_birth']) ?? 'N/A'); ?></span></div>
                <div><strong>Gender:</strong> <span class="text-gray-900"><?php echo htmlspecialchars($patient['gender'] ?? 'N/A'); ?></span></div>
                <div><strong>Email:</strong> <span class="text-gray-900"><?php echo htmlspecialchars($patient['email'] ?? 'N/A'); ?></span></div>
                <div><strong>Phone:</strong> <span class="text-gray-900"><?php echo htmlspecialchars($patient['phone'] ?? 'N/A'); ?></span></div>
                <div class="col-span-full"><strong>Address:</strong> <span class="text-gray-900"><?php echo nl2br(htmlspecialchars($patient['address'] ?? 'N/A')); ?></span></div>
                <div class="col-span-full"><strong>Health Conditions:</strong> <span class="text-gray-900"><?php echo nl2br(htmlspecialchars($patient['health_conditions'] ?? 'N/A')); ?></span></div>
                <div><strong>Membership:</strong>
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                        <?php
                            if ($patient['membership_status'] === 'Premium') echo 'bg-green-100 text-green-800';
                            else if ($patient['membership_status'] === 'Standard') echo 'bg-blue-100 text-blue-800';
                            else echo 'bg-gray-100 text-gray-800';
                        ?>">
                        <?php echo htmlspecialchars($patient['membership_status']); ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Latest Health Metrics Card -->
        <div class="bg-white p-6 rounded-lg shadow-md border border-gray-200">
            <h3 class="text-xl font-semibold text-gray-800 mb-4 border-b border-gray-200 pb-2">Latest Health Metrics</h3>
            <?php if ($latestMetrics): ?>
                <div class="space-y-3 text-gray-700">
                    <p><strong>Recorded On:</strong> <span class="text-gray-900"><?php echo htmlspecialchars(date('Y-m-d H:i', strtotime($latestMetrics['record_date']))); ?></span></p>
                    <p><strong>Height:</strong> <span class="text-gray-900"><?php echo htmlspecialchars($latestMetrics['height_cm'] ?? 'N/A'); ?> cm</span></p>
                    <p><strong>Weight:</strong> <span class="text-gray-900"><?php echo htmlspecialchars($latestMetrics['weight_kg'] ?? 'N/A'); ?> kg</span></p>
                    <p><strong>BMI:</strong> <span class="text-gray-900"><?php echo htmlspecialchars($latestMetrics['bmi'] ?? 'N/A'); ?></span>
                        <?php
                        // Display BMI status
                        $bmi = $latestMetrics['bmi'];
                        if ($bmi !== null) {
                            $bmiStatusText = '';
                            $bmiStatusClass = '';
                            if ($bmi < 18.5) { $bmiStatusText = ' (Underweight)'; $bmiStatusClass = 'text-yellow-600'; }
                            else if ($bmi >= 18.5 && $bmi < 24.9) { $bmiStatusText = ' (Normal weight)'; $bmiStatusClass = 'text-green-600'; }
                            else if ($bmi >= 25 && $bmi < 29.9) { $bmiStatusText = ' (Overweight)'; $bmiStatusClass = 'text-orange-600'; }
                            else if ($bmi >= 30) { $bmiStatusText = ' (Obese)'; $bmiStatusClass = 'text-red-600'; }
                            echo '<span class="' . $bmiStatusClass . '">' . $bmiStatusText . '</span>';
                        }
                        ?>
                    </p>
                    <p><strong>Blood Pressure:</strong> <span class="text-gray-900"><?php echo htmlspecialchars($latestMetrics['systolic_bp'] ?? 'N/A'); ?>/<?php echo htmlspecialchars($latestMetrics['diastolic_bp'] ?? 'N/A'); ?> mmHg</span>
                        <?php
                        // Display BP status
                        $systolic = $latestMetrics['systolic_bp'];
                        $diastolic = $latestMetrics['diastolic_bp'];
                        if ($systolic !== null && $diastolic !== null) {
                            $bpStatusText = '';
                            $bpStatusClass = '';
                            if ($systolic >= 140 || $diastolic >= 90) { $bpStatusText = ' (High)'; $bpStatusClass = 'text-red-600'; }
                            else if ($systolic <= 90 || $diastolic <= 60) { $bpStatusText = ' (Low)'; $bpStatusClass = 'text-yellow-600'; }
                            else if (($systolic >= 120 && $systolic <= 129) && $diastolic < 80) { $bpStatusText = ' (Elevated)'; $bpStatusClass = 'text-orange-600'; }
                            echo '<span class="' . $bpStatusClass . '">' . $bpStatusText . '</span>';
                        }
                        ?>
                    </p>
                    <p><strong>Blood Sugar:</strong> <span class="text-gray-900"><?php echo htmlspecialchars($latestMetrics['blood_sugar_level_mg_dL'] ?? 'N/A'); ?> mg/dL</span>
                        <?php
                        // Display BS status
                        $bloodSugar = $latestMetrics['blood_sugar_level_mg_dL'];
                        $fastingStatus = $latestMetrics['blood_sugar_fasting_status'];
                        if ($bloodSugar !== null) {
                            $bsStatusText = '';
                            $bsStatusClass = '';
                            if ($fastingStatus === 'Fasting (8+ hours)') {
                                if ($bloodSugar < 70) { $bsStatusText = ' (Low - Hypoglycemia)'; $bsStatusClass = 'text-yellow-600'; }
                                else if ($bloodSugar >= 70 && $bloodSugar <= 100) { $bsStatusText = ' (Normal Fasting)'; $bsStatusClass = 'text-green-600'; }
                                else if ($bloodSugar > 100 && $bloodSugar <= 125) { $bsStatusText = ' (Pre-diabetes)'; $bsStatusClass = 'text-orange-600'; }
                                else if ($bloodSugar > 125) { $bsStatusText = ' (High - Diabetes)'; $bsStatusClass = 'text-red-600'; }
                            } else { // Non-fasting/Random
                                if ($bloodSugar < 70) { $bsStatusText = ' (Low - Hypoglycemia)'; $bsStatusClass = 'text-yellow-600'; }
                                else if ($bloodSugar < 140) { $bsStatusText = ' (Normal Non-Fasting)'; $bsStatusClass = 'text-green-600'; }
                                else if ($bloodSugar >= 140 && $bloodSugar <= 199) { $bsStatusText = ' (Pre-diabetes)'; $bsStatusClass = 'text-orange-600'; }
                                else if ($bloodSugar >= 200) { $bsStatusText = ' (High - Diabetes)'; $bsStatusClass = 'text-red-600'; }
                            }
                            echo '<span class="' . $bsStatusClass . '">' . $bsStatusText . '</span>';
                        }
                        ?>
                    </p>
                    <p><strong>Fasting Status:</strong> <span class="text-gray-900"><?php echo htmlspecialchars($latestMetrics['blood_sugar_fasting_status'] ?? 'N/A'); ?></span></p>
                </div>
            <?php else: ?>
                <p class="text-gray-600">No health metrics recorded for this patient yet.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Patient Analytics Section -->
    <div class="mt-8 bg-white p-6 rounded-lg shadow-md border border-gray-200">
        <h3 class="text-xl font-semibold text-gray-800 mb-4 border-b border-gray-200 pb-2">Patient Progress & Analytics</h3>
        <p class="text-gray-600">
            This section will display charts and detailed analytics for the patient's health progress over time.
            <a href="<?php echo BASE_URL; ?>view/analytics.php?id=<?php echo $patient['patient_id']; ?>" class="text-blue-600 hover:underline">View detailed analytics</a>.
        </p>
        <!-- Data for JS charts will be passed here or fetched via AJAX -->
        <script>
            // Pass PHP data to JavaScript for charts in analytics.php
            // This data can be used by assets/js/patient-analytics.js (to be created)
            window.patientHealthMetricsData = <?php echo json_encode($allHealthMetrics); ?>;
            window.patientId = <?php echo json_encode($patient_id); ?>;
            window.patientFullName = <?php echo json_encode($patient['full_name']); ?>;
        </script>
    </div>

    <!-- Back to List Button -->
    <div class="mt-8 text-center">
        <a href="<?php echo BASE_URL; ?>list.php" class="inline-flex items-center space-x-2 px-6 py-2 rounded-lg bg-gray-300 text-gray-800 font-semibold hover:bg-gray-400 transition-colors">
            <i class="fas fa-arrow-left"></i> Back to Patient List
        </a>
    </div>

</div>

<?php
// Include the footer
require_once __DIR__ . '/../includes/footer.php';
?>
