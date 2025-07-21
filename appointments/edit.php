<?php
// patients/edit.php - Edit patient details or add a new patient

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db_connect.php'; // This provides $pdo
require_once __DIR__ . '/../includes/auth.php';

// Include header
$pageTitle = "Add New Patient"; // Default to add new patient
include_once __DIR__ . '/../includes/header.php';

$patient_id = null;
$patient_unique_id = '';
$full_name = '';
$date_of_birth = '';
$gender = '';
$height_cm = '';
$address = '';
$email = '';
$phone = '';
$health_conditions = '';
$membership_status = 'No Membership';

// Check if patient_id is provided in the URL for editing
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $patient_id = $_GET['id'];
    $pageTitle = "Edit Patient";

    // Fetch patient data from the database using PDO
    $sql = "SELECT * FROM patients WHERE patient_id = :patient_id";
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':patient_id', $patient_id, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() == 1) {
            $patient = $stmt->fetch(PDO::FETCH_ASSOC);
            $patient_unique_id = $patient['patient_unique_id'];
            $full_name = $patient['full_name'];
            $date_of_birth = $patient['date_of_birth'];
            $gender = $patient['gender'];
            $height_cm = $patient['height_cm'];
            $address = $patient['address'];
            $email = $patient['email'];
            $phone = $patient['phone'];
            $health_conditions = $patient['health_conditions'];
            $membership_status = $patient['membership_status'];
        } else {
            // Patient not found
            $_SESSION['error_message'] = "Patient not found.";
            $patient_id = null; // Reset to prevent update attempt on non-existent patient
            $pageTitle = "Add New Patient"; // Revert to add mode
        }
    } catch (PDOException $e) {
        error_log("ERROR: Could not retrieve patient data in patients/edit.php: " . $e->getMessage());
        $_SESSION['error_message'] = "Error retrieving patient data. Please try again.";
    }
}

// Process form submission (for both add and edit)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $patient_id = isset($_POST['patient_id']) ? intval($_POST['patient_id']) : null;
    $full_name = trim($_POST['full_name']);
    $date_of_birth = trim($_POST['date_of_birth']);
    $gender = trim($_POST['gender']);
    $height_cm = trim($_POST['height_cm']);
    $address = trim($_POST['address']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $health_conditions = trim($_POST['health_conditions']);
    $membership_status = trim($_POST['membership_status']);

    // Validate input
    if (empty($full_name) || empty($email) || empty($phone)) {
        $_SESSION['error_message'] = "Full Name, Email, and Phone are required.";
    } else {
        try {
            if ($patient_id) {
                // Update existing patient using PDO
                $sql = "UPDATE patients SET full_name = :full_name, date_of_birth = :date_of_birth, gender = :gender, height_cm = :height_cm, address = :address, email = :email, phone = :phone, health_conditions = :health_conditions, membership_status = :membership_status, updated_at = CURRENT_TIMESTAMP WHERE patient_id = :patient_id";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':full_name', $full_name);
                $stmt->bindParam(':date_of_birth', $date_of_birth);
                $stmt->bindParam(':gender', $gender);
                $stmt->bindParam(':height_cm', $height_cm);
                $stmt->bindParam(':address', $address);
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':phone', $phone);
                $stmt->bindParam(':health_conditions', $health_conditions);
                $stmt->bindParam(':membership_status', $membership_status);
                $stmt->bindParam(':patient_id', $patient_id, PDO::PARAM_INT);

                if ($stmt->execute()) {
                    $_SESSION['success_message'] = "Patient details updated successfully.";
                    header("location: " . BASE_URL . "/patients/index.php");
                    exit();
                } else {
                    $_SESSION['error_message'] = "Error updating patient.";
                }
            } else {
                // Add new patient using PDO
                $patient_unique_id = 'PAT-' . date('Ymd') . '-' . uniqid(); // Generate unique ID
                $sql = "INSERT INTO patients (patient_unique_id, full_name, date_of_birth, gender, height_cm, address, email, phone, health_conditions, membership_status) VALUES (:patient_unique_id, :full_name, :date_of_birth, :gender, :height_cm, :address, :email, :phone, :health_conditions, :membership_status)";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':patient_unique_id', $patient_unique_id);
                $stmt->bindParam(':full_name', $full_name);
                $stmt->bindParam(':date_of_birth', $date_of_birth);
                $stmt->bindParam(':gender', $gender);
                $stmt->bindParam(':height_cm', $height_cm);
                $stmt->bindParam(':address', $address);
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':phone', $phone);
                $stmt->bindParam(':health_conditions', $health_conditions);
                $stmt->bindParam(':membership_status', $membership_status);

                if ($stmt->execute()) {
                    $_SESSION['success_message'] = "New patient added successfully.";
                    header("location: " . BASE_URL . "/patients/index.php");
                    exit();
                } else {
                    $_SESSION['error_message'] = "Error adding patient.";
                }
            }
        } catch (PDOException $e) {
            error_log("ERROR: Patient form submission error in patients/edit.php: " . $e->getMessage());
            $_SESSION['error_message'] = "An error occurred during save. Please try again.";
        }
    }
}
?>

<div class="bg-white rounded-lg shadow-md p-6">
    <h2 class="text-2xl font-semibold text-gray-800 mb-6"><?php echo $pageTitle; ?></h2>

    <?php
    if (isset($_SESSION['success_message'])) {
        echo '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">' . $_SESSION['success_message'] . '</div>';
        unset($_SESSION['success_message']);
    }
    if (isset($_SESSION['error_message'])) {
        echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">' . $_SESSION['error_message'] . '</div>';
        unset($_SESSION['error_message']);
    }
    ?>

    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" class="space-y-4">
        <?php if ($patient_id): ?>
            <input type="hidden" name="patient_id" value="<?php echo htmlspecialchars($patient_id); ?>">
        <?php endif; ?>

        <div>
            <label for="full_name" class="block text-gray-700 text-sm font-bold mb-2">Full Name:</label>
            <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($full_name); ?>" required
                   class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline focus:border-blue-500">
        </div>

        <div>
            <label for="date_of_birth" class="block text-gray-700 text-sm font-bold mb-2">Date of Birth:</label>
            <input type="date" id="date_of_birth" name="date_of_birth" value="<?php echo htmlspecialchars($date_of_birth); ?>"
                   class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline focus:border-blue-500">
        </div>

        <div>
            <label for="gender" class="block text-gray-700 text-sm font-bold mb-2">Gender:</label>
            <select id="gender" name="gender"
                    class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline focus:border-blue-500">
                <option value="">Select Gender</option>
                <option value="Male" <?php echo ($gender == 'Male') ? 'selected' : ''; ?>>Male</option>
                <option value="Female" <?php echo ($gender == 'Female') ? 'selected' : ''; ?>>Female</option>
                <option value="Other" <?php echo ($gender == 'Other') ? 'selected' : ''; ?>>Other</option>
            </select>
        </div>

        <div>
            <label for="height_cm" class="block text-gray-700 text-sm font-bold mb-2">Height (cm):</label>
            <input type="number" step="0.01" id="height_cm" name="height_cm" value="<?php echo htmlspecialchars($height_cm); ?>"
                   class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline focus:border-blue-500">
        </div>

        <div>
            <label for="address" class="block text-gray-700 text-sm font-bold mb-2">Address:</label>
            <textarea id="address" name="address" rows="3"
                      class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline focus:border-blue-500"><?php echo htmlspecialchars($address); ?></textarea>
        </div>

        <div>
            <label for="email" class="block text-gray-700 text-sm font-bold mb-2">Email:</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required
                   class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline focus:border-blue-500">
        </div>

        <div>
            <label for="phone" class="block text-gray-700 text-sm font-bold mb-2">Phone:</label>
            <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($phone); ?>" required
                   class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline focus:border-blue-500">
        </div>

        <div>
            <label for="health_conditions" class="block text-gray-700 text-sm font-bold mb-2">Health Conditions:</label>
            <textarea id="health_conditions" name="health_conditions" rows="3"
                      class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline focus:border-blue-500"><?php echo htmlspecialchars($health_conditions); ?></textarea>
        </div>

        <div>
            <label for="membership_status" class="block text-gray-700 text-sm font-bold mb-2">Membership Status:</label>
            <select id="membership_status" name="membership_status" required
                    class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline focus:border-blue-500">
                <option value="No Membership" <?php echo ($membership_status == 'No Membership') ? 'selected' : ''; ?>>No Membership</option>
                <option value="Standard" <?php echo ($membership_status == 'Standard') ? 'selected' : ''; ?>>Standard</option>
                <option value="Premium" <?php echo ($membership_status == 'Premium') ? 'selected' : ''; ?>>Premium</option>
            </select>
        </div>

        <div class="flex items-center justify-between">
            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg focus:outline-none focus:shadow-outline transition duration-200">
                <?php echo $patient_id ? 'Update Patient' : 'Add Patient'; ?>
            </button>
            <a href="<?php echo BASE_URL; ?>/patients/index.php" class="inline-block align-baseline font-bold text-blue-500 hover:text-blue-800">
                Cancel
            </a>
        </div>
    </form>
</div>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>
