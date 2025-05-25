<?php

class DocumentHandler {
    private $encryptionKey = 'a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6'; // Random secure key
    private $uploadsDir = __DIR__ . '/../uploads';

    public function __construct() {
        // Ensure the uploads directory exists
        if (!file_exists($this->uploadsDir)) {
            mkdir($this->uploadsDir, 0777, true);
        }
    }

    public function encryptFile($filePath) {
        $data = file_get_contents($filePath);
        $encryptedData = openssl_encrypt($data, 'AES-256-CBC', $this->encryptionKey, 0, substr(md5($this->encryptionKey), 0, 16));
        file_put_contents($filePath, $encryptedData);
    }

    public function decryptFile($filePath) {
        $encryptedData = file_get_contents($filePath);
        $decryptedData = openssl_decrypt($encryptedData, 'AES-256-CBC', $this->encryptionKey, 0, substr(md5($this->encryptionKey), 0, 16));
        file_put_contents($filePath, $decryptedData);
    }

    public function processDirectory($dir) {
        $files = scandir($dir);
        foreach ($files as $file) {
            if ($file != '.' && $file != '..') {
                $filePath = $dir . '/' . $file;
                if (is_dir($filePath)) {
                    $this->processDirectory($filePath);
                } else {
                    $this->encryptFile($filePath);
                }
            }
        }
    }

    public function encryptUploadsFolder() {
        $this->processDirectory($this->uploadsDir);
    }
}

?> 