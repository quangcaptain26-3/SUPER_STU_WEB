<?php
require_once 'config/db.php';
require_once 'utils.php';

class ExportController {
    private $conn;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    public function exportToPDF($type = 'students', $filters = []) {
        require_once 'assets/libs/tcpdf/tcpdf.php';
        
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetCreator('Student Management System');
        $pdf->SetTitle('Báo cáo ' . ($type == 'students' ? 'Danh sách sinh viên' : 'Bảng điểm'));
        $pdf->SetHeaderData('', 0, 'Hệ thống quản lý sinh viên', 'Báo cáo ' . date('d/m/Y H:i'));
        
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
        $pdf->setFont('dejavusans', '', 10);
        
        $pdf->AddPage();
        
        if ($type == 'students') {
            $this->generateStudentsPDF($pdf, $filters);
        } else {
            $this->generateScoresPDF($pdf, $filters);
        }
        
        $filename = $type . '_' . date('Y-m-d_H-i-s') . '.pdf';
        $pdf->Output($filename, 'D');
    }
    
    private function generateStudentsPDF($pdf, $filters) {
        $studentController = new StudentController();
        $students = $studentController->getAllStudents($filters['search'] ?? '');
        
        $html = '<h2>DANH SÁCH SINH VIÊN</h2>';
        $html .= '<table border="1" cellpadding="5">';
        $html .= '<tr style="background-color:#f0f0f0;">';
        $html .= '<th>STT</th><th>Mã SV</th><th>Họ tên</th><th>Ngày sinh</th><th>Giới tính</th><th>Email</th><th>SĐT</th>';
        $html .= '</tr>';
        
        foreach ($students as $index => $student) {
            $html .= '<tr>';
            $html .= '<td>' . ($index + 1) . '</td>';
            $html .= '<td>' . $student['msv'] . '</td>';
            $html .= '<td>' . $student['fullname'] . '</td>';
            $html .= '<td>' . formatDate($student['dob']) . '</td>';
            $html .= '<td>' . ($student['gender'] == 'male' ? 'Nam' : ($student['gender'] == 'female' ? 'Nữ' : 'Khác')) . '</td>';
            $html .= '<td>' . $student['email'] . '</td>';
            $html .= '<td>' . $student['phone'] . '</td>';
            $html .= '</tr>';
        }
        
        $html .= '</table>';
        $pdf->writeHTML($html, true, false, true, false, '');
    }
    
    private function generateScoresPDF($pdf, $filters) {
        $scoreController = new ScoreController();
        $scores = $scoreController->getAllScores($filters['student_id'] ?? null, $filters['semester'] ?? null);
        
        $html = '<h2>BẢNG ĐIỂM</h2>';
        $html .= '<table border="1" cellpadding="5">';
        $html .= '<tr style="background-color:#f0f0f0;">';
        $html .= '<th>STT</th><th>Mã SV</th><th>Họ tên</th><th>Môn học</th><th>Điểm</th><th>Học kỳ</th>';
        $html .= '</tr>';
        
        foreach ($scores as $index => $score) {
            $html .= '<tr>';
            $html .= '<td>' . ($index + 1) . '</td>';
            $html .= '<td>' . $score['msv'] . '</td>';
            $html .= '<td>' . $score['fullname'] . '</td>';
            $html .= '<td>' . $score['subject'] . '</td>';
            $html .= '<td>' . $score['score'] . '</td>';
            $html .= '<td>' . $score['semester'] . '</td>';
            $html .= '</tr>';
        }
        
        $html .= '</table>';
        $pdf->writeHTML($html, true, false, true, false, '');
    }
    
    public function exportToDOCX($type = 'students', $filters = []) {
        // Simple DOCX export using basic HTML to DOCX conversion
        // In production, use PhpOffice\PhpWord library
        
        if ($type == 'students') {
            $this->generateStudentsDOCX($filters);
        } else {
            $this->generateScoresDOCX($filters);
        }
    }
    
    private function generateStudentsDOCX($filters) {
        $studentController = new StudentController();
        $students = $studentController->getAllStudents($filters['search'] ?? '');
        
        $content = "DANH SÁCH SINH VIÊN\n";
        $content .= "Ngày xuất: " . date('d/m/Y H:i') . "\n\n";
        
        $content .= str_pad("STT", 5) . str_pad("Mã SV", 15) . str_pad("Họ tên", 30) . 
                   str_pad("Ngày sinh", 12) . str_pad("Giới tính", 10) . str_pad("Email", 25) . "SĐT\n";
        $content .= str_repeat("-", 100) . "\n";
        
        foreach ($students as $index => $student) {
            $content .= str_pad($index + 1, 5) . 
                       str_pad($student['msv'], 15) . 
                       str_pad($student['fullname'], 30) . 
                       str_pad(formatDate($student['dob']), 12) . 
                       str_pad($student['gender'] == 'male' ? 'Nam' : ($student['gender'] == 'female' ? 'Nữ' : 'Khác'), 10) . 
                       str_pad($student['email'], 25) . 
                       $student['phone'] . "\n";
        }
        
        $filename = 'students_' . date('Y-m-d_H-i-s') . '.txt';
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        echo $content;
        exit;
    }
    
    private function generateScoresDOCX($filters) {
        $scoreController = new ScoreController();
        $scores = $scoreController->getAllScores($filters['student_id'] ?? null, $filters['semester'] ?? null);
        
        $content = "BẢNG ĐIỂM\n";
        $content .= "Ngày xuất: " . date('d/m/Y H:i') . "\n\n";
        
        $content .= str_pad("STT", 5) . str_pad("Mã SV", 15) . str_pad("Họ tên", 30) . 
                   str_pad("Môn học", 25) . str_pad("Điểm", 8) . "Học kỳ\n";
        $content .= str_repeat("-", 100) . "\n";
        
        foreach ($scores as $index => $score) {
            $content .= str_pad($index + 1, 5) . 
                       str_pad($score['msv'], 15) . 
                       str_pad($score['fullname'], 30) . 
                       str_pad($score['subject'], 25) . 
                       str_pad($score['score'], 8) . 
                       $score['semester'] . "\n";
        }
        
        $filename = 'scores_' . date('Y-m-d_H-i-s') . '.txt';
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        echo $content;
        exit;
    }
}
?>
