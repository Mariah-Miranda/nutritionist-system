<?php
ob_start();

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
requireLogin();

require_once __DIR__ . '/../vendor/autoload.php'; // TCPDF via Composer

$pdf = new TCPDF();
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Your App Name');
$pdf->SetTitle('Client List');
$pdf->SetMargins(10, 10, 10);
$pdf->AddPage();

// Filters from query string
$search       = $_GET['search'] ?? '';
$gender       = $_GET['gender'] ?? '';
$onlyContacts = $_GET['only_contacts'] ?? '';

$where  = [];
$params = [];

if ($search && $onlyContacts !== 'yes') {
    // Only apply search if not onlyContacts mode, since we won't show names then
    $where[] = 'full_name LIKE ?';
    $params[] = "%$search%";
}

if ($gender && $onlyContacts !== 'yes') {
    // Only apply gender filter if not onlyContacts mode
    $where[] = 'gender = ?';
    $params[] = $gender;
}

if ($onlyContacts === 'yes') {
    $where[] = "(phone IS NOT NULL AND phone != '')";
}

$sql = "SELECT patient_unique_id, full_name, date_of_birth, email, phone, gender, membership_status FROM patients";
if (!empty($where)) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

if ($onlyContacts === 'yes') {
    $sql .= " ORDER BY phone ASC";
} else {
    $sql .= " ORDER BY full_name ASC";
}

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    // Build HTML table
    $html = '<h2 style="text-align:center;">Client List</h2><br><table border="1" cellpadding="4"><tr style="background-color:#f2f2f2;">';

    if ($onlyContacts === 'yes') {
        // Only one column: Phone
        $html .= '<th><strong>Phone</strong></th>';
    } else {
        // Full table headers
        $html .= '
            <th><strong>Client ID</strong></th>
            <th><strong>Full Name</strong></th>
            <th><strong>Email</strong></th>
            <th><strong>Phone</strong></th>
            <th><strong>Gender</strong></th>
            <th><strong>Age</strong></th>
            <th><strong>Membership</strong></th>';
    }

    $html .= '</tr>';

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $html .= '<tr>';

        if ($onlyContacts === 'yes') {
            // Only phone
            $html .= '<td>' . htmlspecialchars($row['phone']) . '</td>';
        } else {
            $html .= '
                <td>' . htmlspecialchars($row['patient_unique_id']) . '</td>
                <td>' . htmlspecialchars($row['full_name']) . '</td>
                <td>' . htmlspecialchars($row['email'] ?? 'N/A') . '</td>
                <td>' . htmlspecialchars($row['phone'] ?? 'N/A') . '</td>
                <td>' . htmlspecialchars($row['gender'] ?? 'N/A') . '</td>
                <td>' . htmlspecialchars(calculateAgeFromDob($row['date_of_birth'])) . '</td>
                <td>' . htmlspecialchars($row['membership_status'] ?? 'N/A') . '</td>';
        }

        $html .= '</tr>';
    }

    $html .= '</table>';

    $pdf->writeHTML($html, true, false, true, false, '');
    $pdf->Output('Client_list_filtered.pdf', 'D');

    ob_end_flush();
    exit;

} catch (PDOException $e) {
    echo "Error generating PDF: " . $e->getMessage();
}
