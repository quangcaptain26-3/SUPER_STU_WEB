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
            table { width: 100%; border-collapse: collapse; }
            th, td { border: 1px solid #ddd; padding: 8px; }
            th { background-color: #f2f2f2; font-weight: bold; text-align: center; }
            h1 { text-align: center; }
        </style>';

// --- LẤY DỮ LIỆU VÀ TẠO BẢNG TƯƠNG ỨNG VỚI LOẠI BÁO CÁO ---
if ($type == 'students') {
    $studentController = new StudentController();
    // Lấy danh sách sinh viên (tối đa 1000) có áp dụng bộ lọc tìm kiếm.
    $students = $studentController->getAllStudents($search, 1000, 0);
    
    $html .= '<h1>DANH SÁCH SINH VIÊN</h1>';
    $html .= '<p>Ngày xuất: ' . date('d/m/Y H:i') . '</p>';
    $html .= '<table>
                <thead>
                    <tr>
                        <th width="10%">STT</th>
                        <th width="15%">Mã SV</th>
                        <th width="25%">Họ tên</th>
                        <th width="15%">Ngày sinh</th>
                        <th width="10%">Giới tính</th>
                        <th width="25%">Email</th>
                    </tr>
                </thead>
                <tbody>';
    
    foreach ($students as $index => $student) {
        $genderText = ($student['gender'] == 'male') ? 'Nam' : (($student['gender'] == 'female') ? 'Nữ' : 'Khác');
        $html .= '<tr>
                    <td align="center">' . ($index + 1) . '</td>
                    <td>' . htmlspecialchars($student['msv']) . '</td>
                    <td>' . htmlspecialchars($student['fullname']) . '</td>
                    <td align="center">' . formatDate($student['dob']) . '</td>
                    <td align="center">' . $genderText . '</td>
                    <td>' . htmlspecialchars($student['email']) . '</td>
                </tr>';
    }
    
    $html .= '</tbody></table>';
    $html .= '<p><strong>Tổng số sinh viên:</strong> ' . count($students) . '</p>';
    
} else { // Xử lý cho loại báo cáo 'scores'
    $scoreController = new ScoreController();
    // Lấy danh sách điểm có áp dụng bộ lọc sinh viên và học kỳ.
    $scores = $scoreController->getAllScores($studentId, $semester);
    
    $html .= '<h1>BẢNG ĐIỂM</h1>';
    $html .= '<p>Ngày xuất: ' . date('d/m/Y H:i') . '</p>';
    // Hiển thị thông tin bộ lọc đã áp dụng
    if ($studentId) {
        $studentController = new StudentController();
        $student = $studentController->getStudentById($studentId);
        $html .= '<p><strong>Sinh viên:</strong> ' . htmlspecialchars($student['fullname'] ?? 'N/A') . '</p>';
    }
    if ($semester) {
        $html .= '<p><strong>Học kỳ:</strong> ' . htmlspecialchars($semester) . '</p>';
    }

    $html .= '<table>
                <thead>
                    <tr>
                        <th width="8%">STT</th>
                        <th width="15%">Mã SV</th>
                        <th width="22%">Họ tên</th>
                        <th width="20%">Môn học</th>
                        <th width="10%">Điểm</th>
                        <th width="15%">Học kỳ</th>
                        <th width="10%">Xếp loại</th>
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
                    <td align="center">' . ($index + 1) . '</td>
                    <td>' . htmlspecialchars($score['msv']) . '</td>
                    <td>' . htmlspecialchars($score['fullname']) . '</td>
                    <td>' . htmlspecialchars($score['subject']) . '</td>
                    <td align="center">' . $score['score'] . '</td>
                    <td align="center">' . htmlspecialchars($score['semester']) . '</td>
                    <td align="center">' . $grade . '</td>
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
