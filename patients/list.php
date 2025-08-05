<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireLogin();
$pageTitle = "All Clients";

// Handle filters
$search = $_GET['search'] ?? '';
$gender = $_GET['gender'] ?? '';
$showOnlyContacts = $_GET['only_contacts'] ?? '';

$params = [];
$where = [];

if ($search) {
    $where[] = 'full_name LIKE ?';
    $params[] = "%$search%";
}

if ($gender) {
    $where[] = 'gender = ?';
    $params[] = $gender;
}

if ($showOnlyContacts === 'yes') {
    // Only filter clients with phone not empty
    $where[] = "(phone IS NOT NULL AND phone != '')";
}

$sql = "SELECT patient_id, patient_unique_id, full_name, date_of_birth, email, phone, gender, membership_status FROM patients";
if (!empty($where)) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}

if ($showOnlyContacts === 'yes') {
    // Order by phone only when showing only contacts (optional)
    $sql .= " ORDER BY phone ASC";
} else {
    $sql .= " ORDER BY full_name ASC";
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$patients = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container mx-auto p-6 bg-white rounded-lg shadow-md">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Client List</h2>
        <div class="flex space-x-2">
            <a href="<?php echo BASE_URL; ?>add.php" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 shadow">
                <i class="fas fa-plus mr-2"></i>Add New Client
            </a>
            <a href="export_patients_pdf.php?<?php echo http_build_query($_GET); ?>" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-blue-700 shadow">
                <i class="fas fa-download mr-2"></i>Download
            </a>
        </div>
    </div>

    <form method="get" class="mb-4 flex flex-wrap items-center gap-4">
        <?php if ($showOnlyContacts !== 'yes'): ?>
        <input type="text" name="search" placeholder="Search name..." value="<?= htmlspecialchars($search) ?>"
            class="px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring w-full md:w-1/4">
        <?php endif; ?>

        <?php if ($showOnlyContacts !== 'yes'): ?>
        <select name="gender" class="px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring w-full md:w-1/5">
            <option value="">All Genders</option>
            <option value="Male" <?= $gender === 'Male' ? 'selected' : '' ?>>Male</option>
            <option value="Female" <?= $gender === 'Female' ? 'selected' : '' ?>>Female</option>
        </select>
        <?php endif; ?>

        <select name="only_contacts" class="px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring w-full md:w-1/5">
            <option value="">All Clients</option>
            <option value="yes" <?= $showOnlyContacts === 'yes' ? 'selected' : '' ?>>Show Only Contacts</option>
        </select>

        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md shadow hover:bg-blue-700">
            Filter
        </button>
    </form>

    <?php if (empty($patients)): ?>
        <div class="text-blue-700 bg-blue-100 p-4 rounded">No clients match your filter.</div>
    <?php else: ?>
        <div class="overflow-x-auto border border-gray-200 rounded">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <?php if ($showOnlyContacts === 'yes'): ?>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500">Phone</th>
                        <?php else: ?>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500">Client ID</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500">Full Name</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500">Phone</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500">Gender</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500">Age</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500">Membership</th>
                            <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500">Actions</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    <?php foreach ($patients as $patient): ?>
                        <tr class="hover:bg-gray-50">
                            <?php if ($showOnlyContacts === 'yes'): ?>
                                <td class="px-6 py-4"><?= htmlspecialchars($patient['phone']) ?></td>
                            <?php else: ?>
                                <td class="px-6 py-4"><?= htmlspecialchars($patient['patient_unique_id']) ?></td>
                                <td class="px-6 py-4"><?= htmlspecialchars($patient['full_name']) ?></td>
                                <td class="px-6 py-4"><?= htmlspecialchars($patient['email'] ?? 'N/A') ?></td>
                                <td class="px-6 py-4"><?= htmlspecialchars($patient['phone'] ?? 'N/A') ?></td>
                                <td class="px-6 py-4"><?= htmlspecialchars($patient['gender']) ?></td>
                                <td class="px-6 py-4"><?= calculateAgeFromDob($patient['date_of_birth']) ?></td>
                                <td class="px-6 py-4">
                                    <span class="text-xs font-semibold px-2 py-1 rounded-full 
                                        <?= $patient['membership_status'] === 'Premium' ? 'bg-green-100 text-green-800' : ($patient['membership_status'] === 'Standard' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800') ?>">
                                        <?= htmlspecialchars($patient['membership_status']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex justify-center space-x-2">
                                        <a href="<?= BASE_URL ?>view.php?id=<?= $patient['patient_id'] ?>" class="text-blue-600 hover:text-blue-800"><i class="fas fa-eye"></i></a>
                                        <a href="<?= BASE_URL ?>edit.php?id=<?= $patient['patient_id'] ?>" class="text-yellow-600 hover:text-yellow-800"><i class="fas fa-edit"></i></a>
                                        <a href="#" onclick="showCustomConfirm('Are you sure you want to delete <?= htmlspecialchars($patient['full_name']) ?>?', function(c) { if(c) location.href='<?= BASE_URL ?>delete.php?id=<?= $patient['patient_id'] ?>'; });" class="text-red-600 hover:text-red-800"><i class="fas fa-trash-alt"></i></a>
                                    </div>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
