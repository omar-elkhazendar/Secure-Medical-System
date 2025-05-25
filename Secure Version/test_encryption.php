<?php

require_once 'includes/DocumentHandler.php';

// Create an instance of DocumentHandler
$handler = new DocumentHandler();

// Encrypt all files in the uploads folder
$handler->encryptUploadsFolder();

echo "Files in the uploads folder have been encrypted successfully!";
?> 