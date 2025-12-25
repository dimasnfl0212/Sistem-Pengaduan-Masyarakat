<?php
// File: C:\xampp\htdocs\lapor-system\app\controllers\ReportController.php

class ReportController {
    private $report;
    private $category;
    private $session;
    private $upload;
    
    public function __construct() {
        require_once __DIR__ . '/../core/Database.php';
        require_once __DIR__ . '/../core/Session.php';
        require_once __DIR__ . '/../core/Upload.php';
        require_once __DIR__ . '/../models/Report.php';
        require_once __DIR__ . '/../models/Category.php';
        
        $this->session = new Session();
        
        $database = new Database();
        $db = $database->connect();
        
        $this->report = new Report($db);
        $this->category = new Category($db);
        $this->upload = new Upload();
    }
    
    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validasi
            if (empty($_POST['judul']) || empty($_POST['deskripsi']) || empty($_POST['lokasi']) || empty($_POST['kategori'])) {
                $this->session->setFlash('Semua field wajib diisi!', 'danger');
                return false;
            }
            
            $userId = $this->session->get('user_id');
            $data = [
                'user_id' => $userId,
                'kategori_id' => $_POST['kategori'],
                'judul' => trim($_POST['judul']),
                'deskripsi' => trim($_POST['deskripsi']),
                'lokasi' => trim($_POST['lokasi']),
                'foto' => null
            ];
            
            // Handle file upload
            if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
                $uploadResult = $this->upload->process($_FILES['foto']);
                
                if ($uploadResult['success']) {
                    $data['foto'] = $uploadResult['filename'];
                } else {
                    $this->session->setFlash($uploadResult['message'], 'warning');
                }
            }
            
            // Create report
            if ($this->report->create($data)) {
                $this->session->setFlash('Laporan berhasil dibuat!', 'success');
                return true;
            } else {
                $this->session->setFlash('Gagal membuat laporan', 'danger');
                return false;
            }
        }
    }
    
    // Tambahkan method ini di class ReportController

    public function getPaginatedData($page = 1, $perPage = 10, $filters = []) {
    $total = $this->report->countReports($filters);
    $reports = $this->report->getPaginatedReports($page, $perPage, $filters);
    
    return [
        'reports' => $reports,
        'total' => $total,
        'page' => $page,
        'perPage' => $perPage,
        'totalPages' => ceil($total / $perPage)
    ];
}

    public function getPagination($totalItems, $itemsPerPage, $currentPage, $urlPattern = '?page={page}') {
    require_once __DIR__ . '/../core/Pagination.php';
    return new Pagination($totalItems, $itemsPerPage, $currentPage, $urlPattern);
}

    public function getUserReports() {
        $userId = $this->session->get('user_id');
        return $this->report->getUserReports($userId);
    }
    
    public function getAllReports($page = 1, $perPage = 10) {
        $total = $this->report->countAll();
        $offset = ($page - 1) * $perPage;
        
        return [
            'reports' => $this->report->getAllReports($perPage, $offset),
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => ceil($total / $perPage)
        ];
    }
    
    public function getReport($id) {
        return $this->report->getById($id);
    }
    
    public function updateStatus($id, $status) {
        if (!$this->session->isAdmin()) {
            return false;
        }
        
        return $this->report->updateStatus($id, $status);
    }
    
    public function delete($id) {
        if (!$this->session->isAdmin()) {
            return false;
        }
        
        $report = $this->report->getById($id);
        
        // Delete photo if exists
        if ($report && $report['foto']) {
            $this->upload->delete($report['foto']);
        }
        
        return $this->report->delete($id);
    }
    
    public function getCategories() {
        return $this->category->getAll();
    }
    
    public function update($data) {
    // Hanya update jika laporan masih pending atau user adalah admin
    $report = $this->report->getById($data['id']);
    
    if (!$report) {
        return false;
    }
    
    $userId = $this->session->get('user_id');
    $isAdmin = $this->session->isAdmin();
    
    // Check permission
    if (!$isAdmin && $report['user_id'] != $userId) {
        return false;
    }
    
    // Non-admin can only edit pending reports
    if (!$isAdmin && $report['status'] != 'pending') {
        return false;
    }
    
    // Prepare update data
    $updateData = [
        'id' => $data['id'],
        'judul' => $data['judul'],
        'kategori_id' => $data['kategori_id'],
        'deskripsi' => $data['deskripsi'],
        'lokasi' => $data['lokasi']
    ];
    
    // Add photo if provided
    if (isset($data['foto'])) {
        $updateData['foto'] = $data['foto'];
    }
    
    // Handle photo deletion
    if (isset($_POST['delete_photo']) && $_POST['delete_photo'] == '1') {
        $updateData['foto'] = null;
        
        // Delete physical file
        if ($report['foto']) {
            require_once __DIR__ . '/../core/Upload.php';
            $upload = new Upload();
            $upload->delete($report['foto']);
        }
    }
    
    // Update report
    return $this->report->update($updateData);
}

    public function search($keyword, $category = null, $status = null) {
        return $this->report->search($keyword, $category, $status);
    }
}
?>