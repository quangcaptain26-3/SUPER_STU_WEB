<?php
// Bắt đầu phiên làm việc để truy cập thông tin người dùng và quyền hạn.
session_start();

// Nạp các file cần thiết
require_once '../utils.php'; // Chứa các hàm tiện ích và kiểm tra quyền.
require_once '../studentController.php'; // Chứa logic nghiệp vụ liên quan đến sinh viên.
require_once '../scoreController.php';   // Chứa logic nghiệp vụ liên quan đến điểm.
// Nạp thư viện TCPDF để tạo file PDF.
require_once '../assets/libs/tcpdf/TCPDF-main/tcpdf.php';

// --- BẢO VỆ & LẤY THAM SỐ ---
// Yêu cầu người dùng phải có quyền `PERMISSION_EXPORT_DATA` để có thể sử dụng chức năng này.
requirePermission(PERMISSION_EXPORT_DATA);

// Lấy các tham số từ URL để xác định nội dung và bộ lọc cho file PDF.
$type = $_GET['type'] ?? 'students'; // Loại báo cáo: 'students' hoặc 'scores'. Mặc định là 'students'.
$search = $_GET['search'] ?? ''; // Từ khóa tìm kiếm.
$studentId = $_GET['student_id'] ?? null; // Lọc theo ID sinh viên (cho báo cáo điểm).
$semester = $_GET['semester'] ?? null;   // Lọc theo học kỳ (cho báo cáo điểm).

// --- TÙY CHỈNH LỚP PDF ---
// Tạo một lớp `MYPDF` kế thừa từ lớp `TCPDF` gốc để tùy chỉnh Header và Footer của tài liệu.
class MYPDF extends TCPDF {
    // Ghi đè phương thức `Header()` để tạo header tùy chỉnh.
    public function Header() {
        // Đặt font cho header. 'dejavusans' hỗ trợ Unicode (tiếng Việt).
        $this->SetFont('dejavusans', 'B', 12);
        // Vẽ một ô (Cell) chứa tiêu đề, căn giữa.
        $this->Cell(0, 10, 'Hệ thống Quản lý Sinh viên', 0, false, 'C', 0, '', 0, false, 'M', 'M');
    }

    // Ghi đè phương thức `Footer()` để tạo footer tùy chỉnh.
    public function Footer() {
        // Đặt vị trí con trỏ Y ở -15mm từ cuối trang.
        $this->SetY(-15);
        // Đặt font cho footer.
        $this->SetFont('dejavusans', 'I', 8);
        // Vẽ một ô chứa số trang hiện tại và tổng số trang.
        $this->Cell(0, 10, 'Trang '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}

// --- KHỞI TẠO VÀ CẤU HÌNH TÀI LIỆU PDF ---
// Tạo một đối tượng PDF mới từ lớp tùy chỉnh `MYPDF`.
// P: khổ dọc, mm: đơn vị, A4: khổ giấy, true: bật Unicode, UTF-8: bảng mã.
$pdf = new MYPDF('P', 'mm', 'A4', true, 'UTF-8', false);

// Đặt thông tin metadata cho tài liệu.
$pdf->SetCreator('Student Management System');
$pdf->SetAuthor('Super-Stu');
$pdf->SetTitle($type == 'students' ? 'Danh sách sinh viên' : 'Bảng điểm');
$pdf->SetSubject('Báo cáo được tạo từ Hệ thống Quản lý Sinh viên');

// Cấu hình lề trang.
$pdf->SetMargins(15, 15, 15);
$pdf->SetHeaderMargin(5);
$pdf->SetFooterMargin(10);

// Bật chế độ tự động ngắt trang khi nội dung đầy.
$pdf->SetAutoPageBreak(TRUE, 15);

// Đặt font chữ mặc định cho nội dung tài liệu.
$pdf->SetFont('dejavusans', '', 10);

// Thêm một trang mới vào tài liệu.
$pdf->AddPage();

// --- XÂY DỰNG NỘI DUNG HTML ---
// Bắt đầu xây dựng một chuỗi HTML sẽ được render thành PDF.
$html = '<style>
            table { width: 100%; border-collapse: collapse; margin-top: 10px; }
            th, td { border: 1px solid #333; padding: 6px; }
            th { background-color: #e0e0e0; font-weight: bold; }
            td { font-size: 9pt; }
            h1 { text-align: center; margin-bottom: 10px; font-size: 16pt; }
            p { margin: 5px 0; font-size: 10pt; }
        </style>';

// --- LẤY DỮ LIỆU VÀ TẠO BẢNG TƯƠNG ỨNG VỚI LOẠI BÁO CÁO ---
if ($type == 'students') {
    $studentController = new StudentController();
    // Lấy danh sách sinh viên (tối đa 1000) có áp dụng bộ lọc tìm kiếm.
    $students = $studentController->getAllStudents($search, 1000, 0);
    
    $html .= '<h1>DANH SÁCH SINH VIÊN</h1>';
    $html .= '<p style="margin-bottom: 10px;"><strong>Ngày xuất:</strong> ' . date('d/m/Y') . ' lúc ' . date('H:i:s') . '</p>';
    $html .= '<table cellpadding="5" cellspacing="0">
                <thead>
                    <tr>
                        <th width="8%" style="text-align: center;">STT</th>
                        <th width="12%" style="text-align: center;">Mã SV</th>
                        <th width="28%" style="text-align: left;">Họ tên</th>
                        <th width="15%" style="text-align: center;">Ngày sinh</th>
                        <th width="10%" style="text-align: center;">Giới tính</th>
                        <th width="27%" style="text-align: left;">Email</th>
                    </tr>
                </thead>
                <tbody>';
    
    foreach ($students as $index => $student) {
        $genderText = ($student['gender'] == 'male') ? 'Nam' : (($student['gender'] == 'female') ? 'Nữ' : 'Khác');
        $html .= '<tr>
                    <td style="text-align: center;">' . ($index + 1) . '</td>
                    <td style="text-align: center;">' . htmlspecialchars($student['msv']) . '</td>
                    <td style="text-align: left;">' . htmlspecialchars($student['fullname']) . '</td>
                    <td style="text-align: center;">' . formatDate($student['dob']) . '</td>
                    <td style="text-align: center;">' . $genderText . '</td>
                    <td style="text-align: left;">' . htmlspecialchars($student['email']) . '</td>
                </tr>';
    }
    
    $html .= '</tbody></table>';
    $html .= '<p><strong>Tổng số sinh viên:</strong> ' . count($students) . '</p>';
    
} else { // Xử lý cho loại báo cáo 'scores'
    $scoreController = new ScoreController();
    // Lấy danh sách điểm có áp dụng bộ lọc sinh viên và học kỳ.
    $scores = $scoreController->getAllScores($studentId, $semester);
    
    $html .= '<h1>BẢNG ĐIỂM</h1>';
    $html .= '<p style="margin-bottom: 10px;"><strong>Ngày xuất:</strong> ' . date('d/m/Y') . ' lúc ' . date('H:i:s') . '</p>';
    // Hiển thị thông tin bộ lọc đã áp dụng
    if ($studentId) {
        $studentController = new StudentController();
        $student = $studentController->getStudentById($studentId);
        $html .= '<p style="margin-bottom: 5px;"><strong>Sinh viên:</strong> ' . htmlspecialchars($student['fullname'] ?? 'N/A') . '</p>';
    }
    if ($semester) {
        $html .= '<p style="margin-bottom: 10px;"><strong>Học kỳ:</strong> ' . htmlspecialchars($semester) . '</p>';
    }

    $html .= '<table cellpadding="5" cellspacing="0">
                <thead>
                    <tr>
                        <th width="7%" style="text-align: center;">STT</th>
                        <th width="12%" style="text-align: center;">Mã SV</th>
                        <th width="25%" style="text-align: left;">Họ tên</th>
                        <th width="20%" style="text-align: left;">Môn học</th>
                        <th width="10%" style="text-align: center;">Điểm</th>
                        <th width="13%" style="text-align: center;">Học kỳ</th>
                        <th width="13%" style="text-align: center;">Xếp loại</th>
                    </tr>
                </thead>
                <tbody>';

    foreach ($scores as $index => $score) {
        // Logic xếp loại
        $grade = 'N/A';
        if ($score['score'] >= 9) $grade = 'A+';
        elseif ($score['score'] >= 8) $grade = 'A';
        elseif ($score['score'] >= 7) $grade = 'B+';
        elseif ($score['score'] >= 6) $grade = 'B';
        elseif ($score['score'] >= 5) $grade = 'C';
        else $grade = 'D';
        
        $html .= '<tr>
                    <td style="text-align: center;">' . ($index + 1) . '</td>
                    <td style="text-align: center;">' . htmlspecialchars($score['msv']) . '</td>
                    <td style="text-align: left;">' . htmlspecialchars($score['fullname']) . '</td>
                    <td style="text-align: left;">' . htmlspecialchars($score['subject']) . '</td>
                    <td style="text-align: center;">' . $score['score'] . '</td>
                    <td style="text-align: center;">' . htmlspecialchars($score['semester']) . '</td>
                    <td style="text-align: center;">' . $grade . '</td>
                </tr>';
    }

    $html .= '</tbody></table>';
    $totalScores = count($scores);
    $avgScore = $totalScores > 0 ? array_sum(array_column($scores, 'score')) / $totalScores : 0;
    $html .= '<p><strong>Tổng số môn:</strong> ' . $totalScores . '</p>';
    $html .= '<p><strong>Điểm trung bình:</strong> ' . number_format($avgScore, 2) . '</p>';
}

// --- GHI VÀ XUẤT PDF ---
// Ghi nội dung HTML đã tạo vào tài liệu PDF.
$pdf->writeHTML($html, true, false, true, false, '');

// Tạo tên file động.
$filename = 'bao_cao_' . $type . '_' . date('Ymd_His') . '.pdf';

// `Output()`: Gửi tài liệu PDF đến trình duyệt.
// 'I' (Inline): Hiển thị PDF trong trình duyệt.
// 'D' (Download): Bắt buộc tải file về.
$pdf->Output($filename, 'I');
?>
