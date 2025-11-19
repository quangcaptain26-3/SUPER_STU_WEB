<?php
session_start(); // Bắt đầu phiên làm việc
require_once '../utils.php'; // Nạp tệp utils.php chứa các hàm tiện ích
require_once '../studentController.php'; // Nạp tệp studentController.php để quản lý sinh viên
require_once '../scoreController.php'; // Nạp tệp scoreController.php để quản lý điểm
require_once '../assets/libs/tcpdf/TCPDF-main/tcpdf.php'; // Nạp thư viện TCPDF

requirePermission(PERMISSION_EXPORT_DATA); // Yêu cầu quyền xuất dữ liệu

$type = $_GET['type'] ?? 'students'; // Lấy loại xuất dữ liệu từ URL, mặc định là 'students'
$search = $_GET['search'] ?? ''; // Lấy từ khóa tìm kiếm từ URL, mặc định là rỗng
$studentId = $_GET['student_id'] ?? null; // Lấy ID sinh viên từ URL, mặc định là null
$semester = $_GET['semester'] ?? null; // Lấy học kỳ từ URL, mặc định là null

// Custom TCPDF class for header and footer
class MYPDF extends TCPDF { // Định nghĩa lớp MYPDF kế thừa từ TCPDF để tùy chỉnh header và footer
    public function Header() { // Ghi đè phương thức Header
        $this->SetFont('dejavusans', 'B', 12); // Đặt font chữ cho header
        $this->Cell(0, 10, 'Hệ thống Quản lý Sinh viên', 0, false, 'C', 0, '', 0, false, 'M', 'M'); // Thêm nội dung header
    }

    public function Footer() { // Ghi đè phương thức Footer
        $this->SetY(-15); // Đặt vị trí con trỏ Y
        $this->SetFont('dejavusans', 'I', 8); // Đặt font chữ cho footer
        $this->Cell(0, 10, 'Trang '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M'); // Thêm số trang
    }
}

// Create new PDF document
$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false); // Tạo một đối tượng MYPDF mới

// Set document information
$pdf->SetCreator('Student Management System'); // Đặt người tạo tài liệu
$pdf->SetAuthor('Student Management System'); // Đặt tác giả tài liệu
$pdf->SetTitle($type == 'students' ? 'Danh sách sinh viên' : 'Bảng điểm'); // Đặt tiêu đề tài liệu
$pdf->SetSubject('Báo cáo từ Hệ thống Quản lý Sinh viên'); // Đặt chủ đề tài liệu

// Set default header data
$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING); // Đặt dữ liệu header mặc định

// Set header and footer fonts
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN)); // Đặt font chữ cho header
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA)); // Đặt font chữ cho footer

// Set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED); // Đặt font chữ monospaced mặc định

// Set margins
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT); // Đặt lề trang
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER); // Đặt lề header
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER); // Đặt lề footer

// Set auto page breaks
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM); // Bật chế độ tự động ngắt trang

// Set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO); // Đặt tỷ lệ co giãn hình ảnh

// Set font
$pdf->SetFont('dejavusans', '', 10); // Đặt font chữ cho nội dung

// Add a page
$pdf->AddPage(); // Thêm một trang mới

// Build HTML content
$html = '<style>
            body { font-family: "dejavusans", sans-serif; }
            h1 { font-size: 16pt; text-align: center; }
            table { width: 100%; border-collapse: collapse; }
            th, td { border: 1px solid #ccc; padding: 5px; }
            th { background-color: #f2f2f2; font-weight: bold; }
            .text-center { text-align: center; }
        </style>'; // Chuỗi HTML chứa CSS để định dạng

if ($type == 'students') { // Nếu loại xuất là 'students'
    $studentController = new StudentController(); // Tạo một đối tượng StudentController
    $students = $studentController->getAllStudents($search, 1000, 0); // Lấy danh sách tất cả sinh viên
    
    $html .= '<h1>DANH SÁCH SINH VIÊN</h1>'; // Thêm tiêu đề chính vào HTML
    $html .= '<p>Ngày xuất: ' . date('d/m/Y H:i') . '</p>'; // Thêm ngày xuất vào HTML
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
                <tbody>'; // Thêm cấu trúc bảng và tiêu đề cột vào HTML
    
    foreach ($students as $index => $student) { // Lặp qua danh sách sinh viên
        $genderText = $student['gender'] == 'male' ? 'Nam' : ($student['gender'] == 'female' ? 'Nữ' : 'Khác'); // Chuyển đổi giới tính sang tiếng Việt
        $html .= '<tr>
                    <td class="text-center">' . ($index + 1) . '</td>
                    <td>' . htmlspecialchars($student['msv']) . '</td>
                    <td>' . htmlspecialchars($student['fullname']) . '</td>
                    <td>' . formatDate($student['dob']) . '</td>
                    <td class="text-center">' . $genderText . '</td>
                    <td>' . htmlspecialchars($student['email']) . '</td>
                    <td>' . htmlspecialchars($student['phone']) . '</td>
                </tr>'; // Thêm một dòng dữ liệu sinh viên vào HTML
    }
    
    $html .= '</tbody></table>'; // Đóng bảng
    $html .= '<p><strong>Tổng số sinh viên:</strong> ' . count($students) . '</p>'; // Thêm tổng số sinh viên
    
} else { // Nếu loại xuất không phải là 'students' (là 'scores')
    $scoreController = new ScoreController(); // Tạo một đối tượng ScoreController
    $scores = $scoreController->getAllScores($studentId, $semester); // Lấy danh sách điểm
    
    $html .= '<h1>BẢNG ĐIỂM</h1>'; // Thêm tiêu đề chính vào HTML
    $html .= '<p>Ngày xuất: ' . date('d/m/Y H:i') . '</p>'; // Thêm ngày xuất vào HTML

    if ($studentId || $semester) { // Nếu có ID sinh viên hoặc học kỳ
        $html .= '<div><strong>Bộ lọc áp dụng:</strong><br>'; // Thêm thông tin bộ lọc
        if ($studentId) { // Nếu có ID sinh viên
            $studentController = new StudentController(); // Tạo đối tượng StudentController
            $student = $studentController->getStudentById($studentId); // Lấy thông tin sinh viên
            $html .= 'Sinh viên: ' . htmlspecialchars($student['fullname'] ?? 'N/A') . '<br>'; // Thêm tên sinh viên vào HTML
        }
        if ($semester) { // Nếu có học kỳ
            $html .= 'Học kỳ: ' . htmlspecialchars($semester); // Thêm học kỳ vào HTML
        }
        $html .= '</div><br>'; // Đóng div bộ lọc
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
                <tbody>'; // Thêm cấu trúc bảng và tiêu đề cột vào HTML

    foreach ($scores as $index => $score) { // Lặp qua danh sách điểm
        $grade = $score['score'] >= 9 ? 'A+' : // Xếp loại học lực dựa trên điểm
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
                </tr>'; // Thêm một dòng dữ liệu điểm vào HTML
    }

    $html .= '</tbody></table>'; // Đóng bảng
    $totalScores = count($scores); // Tính tổng số điểm
    $avgScore = $totalScores > 0 ? array_sum(array_column($scores, 'score')) / $totalScores : 0; // Tính điểm trung bình
    $html .= '<p><strong>Tổng số điểm:</strong> ' . $totalScores . '</p>'; // Thêm tổng số điểm
    $html .= '<p><strong>Điểm trung bình:</strong> ' . number_format($avgScore, 2) . '</p>'; // Thêm điểm trung bình
}

// Write HTML content
$pdf->writeHTML($html, true, false, true, false, ''); // Ghi nội dung HTML vào PDF

// Close and output PDF document
$filename = $type . '_' . date('Y-m-d_H-i-s') . '.pdf'; // Tạo tên tệp tin
$pdf->Output($filename, 'I'); // Xuất tệp PDF ra trình duyệt
?>
