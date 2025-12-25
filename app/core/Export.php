<?php
// File: C:\xampp\htdocs\lapor-system\app\core\Export.php

require_once __DIR__ . '/../../vendor/fpdf/fpdf.php';

class Export {
    private $db;
    
    public function __construct($dbConnection = null) {
        if ($dbConnection) {
            $this->db = $dbConnection;
        } else {
            require_once __DIR__ . '/Database.php';
            $database = new Database();
            $this->db = $database->connect();
        }
    }
    
    public function exportReportsToPDF($filters = []) {
        // Get reports with filters
        require_once __DIR__ . '/../models/Report.php';
        $reportModel = new Report($this->db);
        $reports = $reportModel->getPaginatedReports(1, 1000, $filters); // Get all reports
        
        // Create PDF
        $pdf = new FPDF('P', 'mm', 'A4');
        $pdf->AddPage();
        
        // Header
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->Cell(0, 10, 'Laporan Pengaduan Masyarakat', 0, 1, 'C');
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(0, 10, 'Tanggal Export: ' . date('d/m/Y H:i'), 0, 1, 'C');
        $pdf->Ln(10);
        
        // Filter info
        if (!empty($filters)) {
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(0, 10, 'Filter yang digunakan:', 0, 1);
            $pdf->SetFont('Arial', '', 10);
            
            $filterText = [];
            if (!empty($filters['status'])) {
                $statusText = [
                    'pending' => 'Menunggu',
                    'diproses' => 'Diproses',
                    'selesai' => 'Selesai'
                ];
                $filterText[] = 'Status: ' . $statusText[$filters['status']];
            }
            if (!empty($filters['search'])) {
                $filterText[] = 'Pencarian: ' . $filters['search'];
            }
            
            $pdf->Cell(0, 10, implode(' | ', $filterText), 0, 1);
            $pdf->Ln(5);
        }
        
        // Table header
        $pdf->SetFillColor(200, 220, 255);
        $pdf->SetFont('Arial', 'B', 10);
        
        $headers = ['No', 'ID', 'Judul', 'Pelapor', 'Kategori', 'Tanggal', 'Status'];
        $widths = [10, 15, 60, 40, 30, 25, 20];
        
        for($i = 0; $i < count($headers); $i++) {
            $pdf->Cell($widths[$i], 10, $headers[$i], 1, 0, 'C', true);
        }
        $pdf->Ln();
        
        // Table rows
        $pdf->SetFont('Arial', '', 9);
        $fill = false;
        $no = 1;
        
        foreach($reports as $report) {
            $pdf->Cell($widths[0], 8, $no, 'LR', 0, 'C', $fill);
            $pdf->Cell($widths[1], 8, '#' . $report['id'], 'LR', 0, 'C');
            $pdf->Cell($widths[2], 8, substr($report['judul'], 0, 40), 'LR', 0, 'L');
            $pdf->Cell($widths[3], 8, $report['nama_lengkap'] ?: $report['username'], 'LR', 0, 'L');
            $pdf->Cell($widths[4], 8, $report['nama_kategori'], 'LR', 0, 'L');
            $pdf->Cell($widths[5], 8, date('d/m/Y', strtotime($report['created_at'])), 'LR', 0, 'C');
            
            $statusText = [
                'pending' => 'Menunggu',
                'diproses' => 'Proses',
                'selesai' => 'Selesai'
            ];
            $pdf->Cell($widths[6], 8, $statusText[$report['status']], 'LR', 0, 'C');
            $pdf->Ln();
            
            $fill = !$fill;
            $no++;
            
            // Break page jika terlalu banyak data
            if($no % 25 == 0) {
                $pdf->AddPage();
                // Tambah header table lagi
                $pdf->SetFont('Arial', 'B', 10);
                for($i = 0; $i < count($headers); $i++) {
                    $pdf->Cell($widths[$i], 10, $headers[$i], 1, 0, 'C', true);
                }
                $pdf->Ln();
                $pdf->SetFont('Arial', '', 9);
            }
        }
        
        // Close table
        $pdf->Cell(array_sum($widths), 0, '', 'T');
        
        // Footer
        $pdf->Ln(10);
        $pdf->SetFont('Arial', 'I', 10);
        $pdf->Cell(0, 10, 'Total Laporan: ' . count($reports), 0, 1);
        $pdf->Cell(0, 10, 'Dicetak oleh: ' . ($_SESSION['nama_lengkap'] ?? $_SESSION['username']), 0, 1);
        
        // Output
        $filename = 'laporan-pengaduan-' . date('Y-m-d-H-i') . '.pdf';
        $pdf->Output('I', $filename);
    }
    
    public function exportReportsToExcel($filters = []) {
        // Get reports with filters
        require_once __DIR__ . '/../models/Report.php';
        $reportModel = new Report($this->db);
        $reports = $reportModel->getPaginatedReports(1, 1000, $filters);
        
        // Set headers for Excel
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="laporan-pengaduan-' . date('Y-m-d-H-i') . '.xls"');
        header('Cache-Control: max-age=0');
        
        // Start output
        echo "<table border='1'>";
        
        // Header
        echo "<tr>";
        echo "<th colspan='7' style='background-color: #4CAF50; color: white; font-size: 16px; padding: 10px;'>Laporan Pengaduan Masyarakat</th>";
        echo "</tr>";
        echo "<tr>";
        echo "<td colspan='7'>Tanggal Export: " . date('d/m/Y H:i') . "</td>";
        echo "</tr>";
        
        if (!empty($filters)) {
            echo "<tr>";
            echo "<td colspan='7'><strong>Filter:</strong> ";
            $filterText = [];
            if (!empty($filters['status'])) {
                $statusText = [
                    'pending' => 'Menunggu',
                    'diproses' => 'Diproses',
                    'selesai' => 'Selesai'
                ];
                $filterText[] = 'Status: ' . $statusText[$filters['status']];
            }
            if (!empty($filters['search'])) {
                $filterText[] = 'Pencarian: ' . $filters['search'];
            }
            echo implode(' | ', $filterText) . "</td>";
            echo "</tr>";
        }
        
        echo "<tr>";
        echo "<th>No</th>";
        echo "<th>ID</th>";
        echo "<th>Judul</th>";
        echo "<th>Pelapor</th>";
        echo "<th>Kategori</th>";
        echo "<th>Tanggal</th>";
        echo "<th>Status</th>";
        echo "</tr>";
        
        // Data rows
        $no = 1;
        foreach($reports as $report) {
            $statusText = [
                'pending' => 'Menunggu',
                'diproses' => 'Diproses',
                'selesai' => 'Selesai'
            ];
            
            echo "<tr>";
            echo "<td>" . $no . "</td>";
            echo "<td>#" . $report['id'] . "</td>";
            echo "<td>" . htmlspecialchars($report['judul']) . "</td>";
            echo "<td>" . htmlspecialchars($report['nama_lengkap'] ?: $report['username']) . "</td>";
            echo "<td>" . htmlspecialchars($report['nama_kategori']) . "</td>";
            echo "<td>" . date('d/m/Y H:i', strtotime($report['created_at'])) . "</td>";
            echo "<td>" . $statusText[$report['status']] . "</td>";
            echo "</tr>";
            
            $no++;
        }
        
        // Footer
        echo "<tr>";
        echo "<td colspan='7'><strong>Total Laporan:</strong> " . count($reports) . "</td>";
        echo "</tr>";
        echo "<tr>";
        echo "<td colspan='7'><strong>Dicetak oleh:</strong> " . ($_SESSION['nama_lengkap'] ?? $_SESSION['username']) . "</td>";
        echo "</tr>";
        
        echo "</table>";
        exit();
    }
    
    public function exportUserReportsToPDF($userId, $filters = []) {
        $filters['user_id'] = $userId;
        return $this->exportReportsToPDF($filters);
    }
}
?>