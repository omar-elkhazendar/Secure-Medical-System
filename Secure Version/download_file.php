<?php
// download_file.php
require_once __DIR__ . '/includes/DocumentHandler.php';

if (!isset($_GET['file'])) {
    die('No file specified.');
}

$file = basename($_GET['file']); // Prevent directory traversal
$fullPath = __DIR__ . '/uploads/' . $_GET['file'];

if (!file_exists($fullPath)) {
    die('File not found.');
}

$handler = new DocumentHandler();
$encryptionKey = 'a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6'; // Use the same key as DocumentHandler
$encryptedData = file_get_contents($fullPath);
$decryptedData = openssl_decrypt($encryptedData, 'AES-256-CBC', $encryptionKey, 0, substr(md5($encryptionKey), 0, 16));

if ($decryptedData === false) {
    die('Failed to decrypt file.');
}

// Guess content type
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$contentType = finfo_file($finfo, $fullPath);
finfo_close($finfo);

header('Content-Type: ' . $contentType);
header('Content-Disposition: inline; filename="' . $file . '"');
header('Content-Length: ' . strlen($decryptedData));
echo $decryptedData;
exit; 