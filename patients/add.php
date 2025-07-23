<?php
// patients/add.php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/auth.php';

// Set the page title for the header
$pageTitle = "Add New Patient";

// Require login for this page (e.g., Nutritionist or Admin)
requireLogin();

// Initialize message variable
$message = '';

// Include the header
require_once __DIR__ . '/../includes/header.php';
?>

<div class="max-w-3xl mx-auto bg-white p-8 rounded-xl shadow-lg border border-gray-200">
    <h2 class="text-2xl font-bold text-gray-800 mb-6 border-b-2 border-green-600 pb-2">Add New Patient</h2>

    <?php if ($message): ?>
        <div class="bg-<?php echo strpos($message, 'successful') !== false ? 'green' : 'red'; ?>-100 border-l-4 border-<?php echo strpos($message, 'successful') !== false ? 'green' : 'red'; ?>-500 text-<?php echo strpos($message, 'successful') !== false ? 'green' : 'red'; ?>-700 p-4 mb-6 rounded" role="alert">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <form action="<?php echo BASE_URL; ?>process_patient.php" method="POST" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Full Name -->
            <div>
                <label for="full_name" class="block text-gray-700 font-semibold mb-2">Full Name</label>
                <input type="text" id="full_name" name="full_name" required placeholder="John Doe" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- Patient ID (will be auto-generated, display only or hidden) -->
            <div>
                <label for="patient_unique_id" class="block text-gray-700 font-semibold mb-2">Patient ID</label>
                <input type="text" id="patient_unique_id" name="patient_unique_id" placeholder="Auto-generated" disabled class="w-full px-4 py-2 border rounded-lg bg-gray-100 text-gray-500 cursor-not-allowed">
            </div>

            <!-- Date of Birth -->
            <div>
                <label for="date_of_birth" class="block text-gray-700 font-semibold mb-2">Date of Birth</label>
                <input type="date" id="date_of_birth" name="date_of_birth" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- Gender -->
            <div>
                <label for="gender" class="block text-gray-700 font-semibold mb-2">Gender</label>
                <select id="gender" name="gender" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Select Gender</option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                    <option value="Other">Other</option>
                </select>
            </div>

            <!-- Height (cm) -->
            <div>
                <label for="height_cm" class="block text-gray-700 font-semibold mb-2">Height (cm)</label>
                <input type="number" id="height_cm" name="height_cm" step="0.01" placeholder="e.g., 175.5" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- Weight (kg) -->
            <div>
                <label for="weight_kg" class="block text-gray-700 font-semibold mb-2">Weight (kg)</label>
                <input type="number" id="weight_kg" name="weight_kg" step="0.01" placeholder="e.g., 70.2" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- BMI (Calculated, display only) -->
            <div>
                <label for="bmi" class="block text-gray-700 font-semibold mb-2">BMI</label>
                <input type="text" id="bmi" name="bmi" placeholder="Calculated automatically" disabled class="w-full px-4 py-2 border rounded-lg bg-gray-100 text-gray-500 cursor-not-allowed">
                <p id="bmi_status" class="text-sm mt-1 text-gray-500"></p>
            </div>

            <!-- Blood Pressure -->
            <div>
                <label for="systolic_bp" class="block text-gray-700 font-semibold mb-2">Blood Pressure (mmHg)</label>
                <div class="flex gap-2">
                    <input type="number" id="systolic_bp" name="systolic_bp" placeholder="Systolic" class="w-1/2 px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <input type="number" id="diastolic_bp" name="diastolic_bp" placeholder="Diastolic" class="w-1/2 px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <p id="bp_status" class="text-sm text-gray-500 mt-1">Normal: 120/80 mmHg</p>
            </div>

            <!-- Blood Sugar Level -->
            <div>
                <label for="blood_sugar_level_mg_dL" class="block text-gray-700 font-semibold mb-2">Blood Sugar Level (mg/dL)</label>
                <input type="number" id="blood_sugar_level_mg_dL" name="blood_sugar_level_mg_dL" step="0.01" placeholder="e.g., 90" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                <p id="bs_status" class="text-sm text-gray-500 mt-1">Normal fasting: 70-100 mg/dL</p>
            </div>

            <!-- Blood Sugar Fasting Status -->
            <div>
                <label for="blood_sugar_fasting_status" class="block text-gray-700 font-semibold mb-2">Blood Sugar Status</label>
                <select id="blood_sugar_fasting_status" name="blood_sugar_fasting_status" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Select Status</option>
                    <option value="Fasting (8+ hours)">Fasting (8+ hours)</option>
                    <option value="Non-Fasting">Non-Fasting</option>
                    <option value="Random">Random</option>
                </select>
            </div>
        </div>

        <!-- Address -->
        <div>
            <label for="address" class="block text-gray-700 font-semibold mb-2">Address</label>
            <textarea id="address" name="address" rows="3" placeholder="Patient's full address" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
        </div>

        <!-- Email -->
        <div>
            <label for="email" class="block text-gray-700 font-semibold mb-2">Email</label>
            <input type="email" id="email" name="email" placeholder="patient.email@example.com" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <!-- Phone -->
        <div>
            <label for="phone" class="block text-gray-700 font-semibold mb-2">Phone</label>
            <input type="text" id="phone" name="phone" placeholder="+256 7XX XXX XXX" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <!-- Health Conditions -->
        <div>
            <label for="health_conditions" class="block text-gray-700 font-semibold mb-2">Health Conditions</label>
            <textarea id="health_conditions" name="health_conditions" rows="4" placeholder="Enter any health conditions, allergies, or medical history..." class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
        </div>

        <!-- Membership -->
        <div>
            <label for="membership_status" class="block text-gray-700 font-semibold mb-2">Membership</label>
            <select id="membership_status" name="membership_status" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="No Membership">No Membership</option>
                <option value="Standard">Standard</option>
                <option value="Premium">Premium</option>
            </select>
        </div>

        <div class="flex justify-end space-x-4 mt-6">
            <a href="<?php echo BASE_URL; ?>patients/list.php" class="px-6 py-2 rounded-lg bg-gray-300 text-gray-800 font-semibold hover:bg-gray-400 transition-colors">Cancel</a>
            <button type="submit" class="px-6 py-2 rounded-lg bg-green-600 text-white font-semibold hover:bg-green-700 transition-colors">Save Patient</button>
        </div>
    </form>
</div>

<?php
// Include the footer
require_once __DIR__ . '/../includes/footer.php';
?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const heightCmInput = document.getElementById('height_cm');
    const weightKgInput = document.getElementById('weight_kg');
    const bmiInput = document.getElementById('bmi');
    const bmiStatus = document.getElementById('bmi_status');

    const systolicBpInput = document.getElementById('systolic_bp');
    const diastolicBpInput = document.getElementById('diastolic_bp');
    const bpStatus = document.getElementById('bp_status');

    const bloodSugarInput = document.getElementById('blood_sugar_level_mg_dL');
    const bsStatus = document.getElementById('bs_status');
    const bsFastingStatus = document.getElementById('blood_sugar_fasting_status');

    // Function to calculate BMI
    function calculateBMI() {
        const heightCm = parseFloat(heightCmInput.value);
        const weightKg = parseFloat(weightKgInput.value);

        if (heightCm > 0 && weightKg > 0) {
            const heightM = heightCm / 100; // Convert cm to meters
            const bmi = weightKg / (heightM * heightM);
            bmiInput.value = bmi.toFixed(2); // Display BMI rounded to 2 decimal places

            // Provide BMI status feedback
            if (bmi < 18.5) {
                bmiStatus.textContent = 'Underweight';
                bmiStatus.className = 'text-sm mt-1 text-yellow-600';
            } else if (bmi >= 18.5 && bmi < 24.9) {
                bmiStatus.textContent = 'Normal weight';
                bmiStatus.className = 'text-sm mt-1 text-green-600';
            } else if (bmi >= 25 && bmi < 29.9) {
                bmiStatus.textContent = 'Overweight';
                bmiStatus.className = 'text-sm mt-1 text-orange-600';
            } else if (bmi >= 30) {
                bmiStatus.textContent = 'Obese';
                bmiStatus.className = 'text-sm mt-1 text-red-600';
            }
        } else {
            bmiInput.value = '';
            bmiStatus.textContent = '';
            bmiStatus.className = 'text-sm mt-1 text-gray-500'; // Reset to default gray
        }
    }

    // Function to check Blood Pressure status
    function checkBloodPressure() {
        const systolic = parseInt(systolicBpInput.value);
        const diastolic = parseInt(diastolicBpInput.value);

        if (!isNaN(systolic) && !isNaN(diastolic)) {
            let statusText = 'Normal: 120/80 mmHg';
            let statusClass = 'text-gray-500';

            if (systolic >= 140 || diastolic >= 90) {
                statusText = 'High Blood Pressure (Hypertension)';
                statusClass = 'text-red-600';
            } else if (systolic <= 90 || diastolic <= 60) {
                statusText = 'Low Blood Pressure (Hypotension)';
                statusClass = 'text-yellow-600';
            } else if ((systolic >= 120 && systolic <= 129) && diastolic < 80) {
                statusText = 'Elevated Blood Pressure';
                statusClass = 'text-orange-600';
            }

            bpStatus.textContent = statusText;
            bpStatus.className = `text-sm mt-1 ${statusClass}`;
        } else {
            bpStatus.textContent = 'Normal: 120/80 mmHg';
            bpStatus.className = 'text-sm mt-1 text-gray-500';
        }
    }

    // Function to check Blood Sugar status
    function checkBloodSugar() {
        const bloodSugar = parseFloat(bloodSugarInput.value);
        const fastingStatus = bsFastingStatus.value;

        if (!isNaN(bloodSugar)) {
            let statusText = 'Normal fasting: 70-100 mg/dL';
            let statusClass = 'text-gray-500';

            if (fastingStatus === 'Fasting (8+ hours)') {
                if (bloodSugar < 70) {
                    statusText = 'Low Blood Sugar (Hypoglycemia)';
                    statusClass = 'text-yellow-600';
                } else if (bloodSugar >= 70 && bloodSugar <= 100) {
                    statusText = 'Normal Fasting Blood Sugar';
                    statusClass = 'text-green-600';
                } else if (bloodSugar > 100 && bloodSugar <= 125) {
                    statusText = 'Pre-diabetes (Impaired Fasting Glucose)';
                    statusClass = 'text-orange-600';
                } else if (bloodSugar > 125) {
                    statusText = 'High Blood Sugar (Diabetes)';
                    statusClass = 'text-red-600';
                }
            } else {
                // Non-fasting or random blood sugar guidelines (simplified)
                if (bloodSugar < 70) {
                    statusText = 'Low Blood Sugar (Hypoglycemia)';
                    statusClass = 'text-yellow-600';
                } else if (bloodSugar < 140) {
                    statusText = 'Normal Non-Fasting Blood Sugar';
                    statusClass = 'text-green-600';
                } else if (bloodSugar >= 140 && bloodSugar <= 199) {
                    statusText = 'Pre-diabetes (Impaired Glucose Tolerance)';
                    statusClass = 'text-orange-600';
                } else if (bloodSugar >= 200) {
                    statusText = 'High Blood Sugar (Diabetes)';
                    statusClass = 'text-red-600';
                }
            }

            bsStatus.textContent = statusText;
            bsStatus.className = `text-sm mt-1 ${statusClass}`;
        } else {
            bsStatus.textContent = 'Normal fasting: 70-100 mg/dL';
            bsStatus.className = 'text-sm mt-1 text-gray-500';
        }
    }


    // Event listeners for BMI calculation
    heightCmInput.addEventListener('input', calculateBMI);
    weightKgInput.addEventListener('input', calculateBMI);

    // Event listeners for Blood Pressure check
    systolicBpInput.addEventListener('input', checkBloodPressure);
    diastolicBpInput.addEventListener('input', checkBloodPressure);

    // Event listeners for Blood Sugar check
    bloodSugarInput.addEventListener('input', checkBloodSugar);
    bsFastingStatus.addEventListener('change', checkBloodSugar); // Also check on fasting status change

    // Initial calculations if values are pre-filled (e.g., on edit page later)
    calculateBMI();
    checkBloodPressure();
    checkBloodSugar();
});
</script>
