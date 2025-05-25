<?php
require_once '../config/database.php';
require_once '../includes/auth.php';

$encryption_key = 'my_super_secret_key_123'; // تأكد من تطابق المفتاح مع مفتاح التشفير المستخدم

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'patient') {
    header('Location: ../login.php');
    exit;
}

$patient_id = $_SESSION['user']['id'];
$record_id = isset($_GET['record_id']) ? (int)$_GET['record_id'] : 0;

if (!$record_id) {
    $_SESSION['error_message'] = 'Invalid record ID.';
    header('Location: patient_records.php');
    exit;
}

// Fetch the medical record to ensure it belongs to the logged-in patient
$stmt = $pdo->prepare('SELECT diagnosis, prescription FROM medical_records WHERE id = ? AND patient_id = ?');
$stmt->execute([$record_id, $patient_id]);
$record = $stmt->fetch();

if (!$record) {
    $_SESSION['error_message'] = 'Record not found or does not belong to you.';
    header('Location: patient_records.php');
    exit;
}

// Decrypt the prescription
$decrypted_prescription = openssl_decrypt($record['prescription'], 'AES-128-ECB', $encryption_key);

if ($decrypted_prescription === false) {
    $_SESSION['error_message'] = 'Error decrypting prescription.';
    header('Location: patient_records.php');
    exit;
}

// Prepare the prescription content for download
$file_content = "Prescription:\n\n" . $decrypted_prescription;
$file_name = "prescription_record_" . $record_id . ".txt";

// Set headers for download
header('Content-Type: text/plain');
header('Content-Disposition: attachment; filename="' . $file_name . '"');
header('Content-Length: ' . strlen($file_content));

// Output the file content
echo $file_content;
exit;
?> 