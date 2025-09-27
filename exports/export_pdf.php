<?php
session_start();
require_once '../utils.php';
require_once '../studentController.php';
require_once '../scoreController.php';
require_once '../assets/libs/tcpdf/TCPDF-main/tcpdf.php';

requirePermission(PERMISSION_EXPORT_DATA);

$type = $_GET['type'] ?? 'students';
$search = $_GET['search'] ?? '';
$studentId = $_GET['student_id'] ?? null;
$semester = $_GET['semester'] ?? null;

// Custom TCPDF class for header and footer
class MYPDF extends TCPDF {
    public function Header() {
        $this->SetFont('dejavusans', 'B', 12);
        $this->Cell(0, 10, 'Hệ thống Quản lý Sinh viên', 0, false, 'C', 0, '', 0, false, 'M', 'M');
    }

    public function Footer() {
        $this->SetY(-15);
        $this->SetFont('dejavusans', 'I', 8);
        $this->Cell(0, 10, 'Trang '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}

// Create new PDF document
$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Set document information
$pdf->SetCreator('Student Management System');
$pdf->SetAuthor('Student Management System');
$pdf->SetTitle($type == 'students' ? 'Danh sách sinh viên' : 'Bảng điểm');
$pdf->SetSubject('Báo cáo từ Hệ thống Quản lý Sinh viên');

// Set default header data
$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);

// Set header and footer fonts
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

// Set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// Set margins
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

// Set auto page breaks
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// Set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// Set font
$pdf->SetFont('dejavusans', '', 10);

// Add a page
$pdf->AddPage();

// Build HTML content
$html = '<style>
            body { font-family: "dejavusans", sans-serif; }
            h1 { font-size: 16pt; text-align: center; }
            table { width: 100%; border-collapse: collapse; }
            th, td { border: 1px solid #ccc; padding: 5px; }
            th { background-color: #f2f2f2; font-weight: bold; }
            .text-center { text-align: center; }
        </style>';

if ($type == 'students') {
    $studentController = new StudentController();
    $students = $studentController->getAllStudents($search, 1000, 0);
    
    $html .= '<h1>DANH SÁCH SINH VIÊN</h1>';
    $html .= '<p>Ngày xuất: ' . date('d/m/Y H:i') . '</p>';
    $html .= '<table>
                <thead>
                    <tr>
                        <th width="14%">STT</th>
                        <th width="14%">Mã SV</th>
                        <th width="15%">Họ tên</th>
                        <th width="14%">Ngày sinh</th>
                        <th width="14%">Giới tính</th>
                        <th width="15%">Email</th>
                        <th width="14%">SĐT</th>
                    </tr>
                </thead>
                <tbody>';
    
    foreach ($students as $index => $student) {
        $genderText = $student['gender'] == 'male' ? 'Nam' : ($student['gender'] == 'female' ? 'Nữ' : 'Khác');
        $html .= '<tr>
                    <td class="text-center">' . ($index + 1) . '</td>
                    <td>' . htmlspecialchars($student['msv']) . '</td>
                    <td>' . htmlspecialchars($student['fullname']) . '</td>
                    <td>' . formatDate($student['dob']) . '</td>
                    <td class="text-center">' . $genderText . '</td>
                    <td>' . htmlspecialchars($student['email']) . '</td>
                    <td>' . htmlspecialchars($student['phone']) . '</td>
                </tr>';
    }
    
    $html .= '</tbody></table>';
    $html .= '<p><strong>Tổng số sinh viên:</strong> ' . count($students) . '</p>';
    
} else {
    $scoreController = new ScoreController();
    $scores = $scoreController->getAllScores($studentId, $semester);
    
    $html .= '<h1>BẢNG ĐIỂM</h1>';
    $html .= '<p>Ngày xuất: ' . date('d/m/Y H:i') . '</p>';

    if ($studentId || $semester) {
        $html .= '<div><strong>Bộ lọc áp dụng:</strong><br>';
        if ($studentId) {
            $studentController = new StudentController();
            $student = $studentController->getStudentById($studentId);
            $html .= 'Sinh viên: ' . htmlspecialchars($student['fullname'] ?? 'N/A') . '<br>';
        }
        if ($semester) {
            $html .= 'Học kỳ: ' . htmlspecialchars($semester);
        }
        $html .= '</div><br>';
    }

    $html .= '<table>
                <thead>
                    <tr>
                        <th width="14%">STT</th>
                        <th width="14%">Mã SV</th>
                        <th width="15%">Họ tên</th>
                        <th width="15%">Môn học</th>
                        <th width="14%">Điểm</th>
                        <th width="14%">Học kỳ</th>
                        <th width="14%">Xếp loại</th>
                    </tr>
                </thead>
                <tbody>';

    foreach ($scores as $index => $score) {
        $grade = $score['score'] >= 9 ? 'A+' : 
                ($score['score'] >= 8 ? 'A' : 
                ($score['score'] >= 7 ? 'B+' : 
                ($score['score'] >= 6 ? 'B' : 
                ($score['score'] >= 5 ? 'C' : 'D'))));
        
        $html .= '<tr>
                    <td class="text-center">' . ($index + 1) . '</td>
                    <td>' . htmlspecialchars($score['msv']) . '</td>
                    <td>' . htmlspecialchars($score['fullname']) . '</td>
                    <td>' . htmlspecialchars($score['subject']) . '</td>
                    <td class="text-center">' . $score['score'] . '</td>
                    <td class="text-center">' . htmlspecialchars($score['semester']) . '</td>
                    <td class="text-center">' . $grade . '</td>
                </tr>';
    }

    $html .= '</tbody></table>';
    $totalScores = count($scores);
    $avgScore = $totalScores > 0 ? array_sum(array_column($scores, 'score')) / $totalScores : 0;
    $html .= '<p><strong>Tổng số điểm:</strong> ' . $totalScores . '</p>';
    $html .= '<p><strong>Điểm trung bình:</strong> ' . number_format($avgScore, 2) . '</p>';
}

// Write HTML content
$pdf->writeHTML($html, true, false, true, false, '');

// Close and output PDF document
$filename = $type . '_' . date('Y-m-d_H-i-s') . '.pdf';
$pdf->Output($filename, 'I');
?>
