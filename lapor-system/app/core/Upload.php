<?php
// File: C:\xampp\htdocs\lapor-system\app\core\Upload.php

class Upload {
    private $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
    private $maxSize = 5 * 1024 * 1024; // 5MB
    private $uploadPath;
    
    public function __construct($uploadPath = '../uploads/') {
        $this->uploadPath = $uploadPath;
        
        // Buat folder uploads jika belum ada
        if (!file_exists($this->uploadPath)) {
            mkdir($this->uploadPath, 0777, true);
        }
    }
    
    public function process($file, $prefix = 'report_') {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'message' => 'Error uploading file'];
        }
        
        // Validasi size
        if ($file['size'] > $this->maxSize) {
            return ['success' => false, 'message' => 'File terlalu besar. Maksimal 5MB'];
        }
        
        // Validasi extension
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $this->allowedExtensions)) {
            return ['success' => false, 'message' => 'Format file tidak didukung. Gunakan JPG, PNG, atau GIF'];
        }
        
        // Generate unique filename
        $filename = $prefix . time() . '_' . uniqid() . '.' . $extension;
        $destination = $this->uploadPath . $filename;
        
        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $destination)) {
            return [
                'success' => true,
                'filename' => $filename,
                'path' => $destination,
                'url' => 'uploads/' . $filename
            ];
        }
        
        return ['success' => false, 'message' => 'Gagal menyimpan file'];
    }
    
    public function delete($filename) {
        $filepath = $this->uploadPath . $filename;
        if (file_exists($filepath)) {
            return unlink($filepath);
        }
        return false;
    }
}
?>