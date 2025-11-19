<?php
// Lấy các file cấu hình cơ sở dữ liệu và các hàm tiện ích
require_once 'config/db.php';
require_once 'utils.php';

/**
 * Class ExportController
 * Xử lý xuất dữ liệu sang các định dạng khác nhau như PDF và DOCX
 */
class ExportController
{
    // Biến private lưu kết nối cơ sở dữ liệu
    private $conn;

    /**
     * Hàm khởi tạo ExportController
     * Khởi tạo kết nối cơ sở dữ liệu thông qua lớp Database
     */
    public function __construct()
    {
        // Tạo đối tượng Database mới để kết nối DB
        $database = new Database();
        // Gán kết nối cơ sở dữ liệu vào biến $conn
        $this->conn = $database->getConnection();
    }

    /**
     * Xuất dữ liệu ra file PDF
     * @param string $type Loại dữ liệu cần xuất ('students' hoặc 'scores')
     * @param array $filters Mảng bộ lọc dữ liệu để xuất
     */
    public function exportToPDF($type = 'students', $filters = [])
    {
        // Bao gồm thư viện TCPDF để tạo file PDF
        require_once 'assets/libs/tcpdf/tcpdf.php';

        // Khởi tạo đối tượng PDF mới với các tham số cơ bản
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // Thiết lập thông tin tài liệu PDF
        $pdf->SetCreator('Student Management System');
        $pdf->SetTitle('Báo cáo ' . ($type == 'students' ? 'Danh sách sinh viên' : 'Bảng điểm'));

        // Thiết lập tiêu đề header của file PDF
        $pdf->SetHeaderData('', 0, 'Hệ thống quản lý sinh viên', 'Báo cáo ' . date('d/m/Y H:i'));

        // Cấu hình font chữ cho header và footer
        $pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        // Cấu hình font mặc định cho chữ trong PDF
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // Thiết lập lề trang PDF
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        // Bật chế độ ngắt trang tự động
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // Thiết lập tỉ lệ ảnh trong file PDF
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // Thiết lập font chữ chính cho nội dung
        $pdf->setFont('dejavusans', '', 10);

        // Thêm một trang mới vào PDF
        $pdf->AddPage();

        // Tùy theo $type, tạo nội dung phù hợp
        if ($type == 'students') {
            $this->generateStudentsPDF($pdf, $filters);
        } else {
            $this->generateScoresPDF($pdf, $filters);
        }

        // Tạo tên file gồm loại dữ liệu và timestamp
        $filename = $type . '_' . date('Y-m-d_H-i-s') . '.pdf';

        // Xuất file PDF để tải về (D = Download)
        $pdf->Output($filename, 'D');
    }

    /**
     * Tạo nội dung danh sách sinh viên cho file PDF
     * @param TCPDF $pdf Đối tượng PDF
     * @param array $filters Các bộ lọc để lấy danh sách sinh viên
     */
    private function generateStudentsPDF($pdf, $filters)
    {
        // Tạo đối tượng StudentController để lấy dữ liệu sinh viên
        $studentController = new StudentController();
        $students = $studentController->getAllStudents($filters['search'] ?? '');

        // Tạo HTML bảng danh sách sinh viên để in ra PDF
        $html = '<h2>DANH SÁCH SINH VIÊN</h2>';
        $html .= '<table border="1" cellpadding="5">';
        $html .= '<tr style="background-color:#f0f0f0;">';
        $html .= '<th>STT</th><th>Mã SV</th><th>Họ tên</th><th>Ngày sinh</th><th>Giới tính</th><th>Email</th><th>SĐT</th>';
        $html .= '</tr>';

        // Duyệt từng sinh viên để tạo các dòng dữ liệu
        foreach ($students as $index => $student) {
            $html .= '<tr>';
            $html .= '<td>' . ($index + 1) . '</td>';
            $html .= '<td>' . $student['msv'] . '</td>';
            $html .= '<td>' . $student['fullname'] . '</td>';
            $html .= '<td>' . formatDate($student['dob']) . '</td>';
            // Chuyển giới tính sang chữ phù hợp
            $html .= '<td>' . ($student['gender'] == 'male' ? 'Nam' : ($student['gender'] == 'female' ? 'Nữ' : 'Khác')) . '</td>';
            $html .= '<td>' . $student['email'] . '</td>';
            $html .= '<td>' . $student['phone'] . '</td>';
            $html .= '</tr>';
        }

        $html .= '</table>';

        // Ghi nội dung HTML ra file PDF
        $pdf->writeHTML($html, true, false, true, false, '');
    }

    /**
     * Tạo nội dung bảng điểm cho file PDF
     * @param TCPDF $pdf Đối tượng PDF
     * @param array $filters Các bộ lọc (mã sinh viên, học kỳ) để lấy dữ liệu điểm
     */
    private function generateScoresPDF($pdf, $filters)
    {
        // Tạo đối tượng ScoreController để lấy dữ liệu điểm
        $scoreController = new ScoreController();
        $scores = $scoreController->getAllScores($filters['student_id'] ?? null, $filters['semester'] ?? null);

        // Tạo HTML bảng điểm sinh viên
        $html = '<h2>BẢNG ĐIỂM</h2>';
        $html .= '<table border="1" cellpadding="5">';
        $html .= '<tr style="background-color:#f0f0f0;">';
        $html .= '<th>STT</th><th>Mã SV</th><th>Họ tên</th><th>Môn học</th><th>Điểm</th><th>Học kỳ</th>';
        $html .= '</tr>';

        // Duyệt từng dòng điểm để tạo bảng
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

        // Ghi nội dung HTML ra file PDF
        $pdf->writeHTML($html, true, false, true, false, '');
    }

    /**
     * Xuất dữ liệu ra file DOCX (thực ra là file .txt đơn giản)
     * @param string $type Loại dữ liệu cần xuất ('students' hoặc 'scores')
     * @param array $filters Mảng bộ lọc
     */
    public function exportToDOCX($type = 'students', $filters = [])
    {
        // Lưu ý: Đây là cách xuất file dạng văn bản đơn giản (txt).
        // Trong thực tế nên sử dụng thư viện như PhpOffice\PhpWord để tạo file DOCX chuẩn.

        if ($type == 'students') {
            $this->generateStudentsDOCX($filters);
        } else {
            $this->generateScoresDOCX($filters);
        }
    }

    /**
     * Tạo nội dung danh sách sinh viên cho file DOCX (định dạng text)
     * @param array $filters Bộ lọc tìm kiếm sinh viên
     */
    private function generateStudentsDOCX($filters)
    {
        // Lấy danh sách sinh viên dựa trên bộ lọc
        $studentController = new StudentController();
        $students = $studentController->getAllStudents($filters['search'] ?? '');

        // Tạo nội dung dạng văn bản thô với định dạng cột tương đối
        $content = "DANH SÁCH SINH VIÊN\n";
        $content .= "Ngày xuất: " . date('d/m/Y H:i') . "\n\n";

        // Tiêu đề bảng với căn chỉnh độ rộng cột bằng str_pad
        $content .= str_pad("STT", 5) . str_pad("Mã SV", 15) . str_pad("Họ tên", 30) .
            str_pad("Ngày sinh", 12) . str_pad("Giới tính", 10) . str_pad("Email", 25) . "SĐT\n";
        $content .= str_repeat("-", 100) . "\n";

        // Duyệt từng sinh viên tạo dòng dữ liệu
        foreach ($students as $index => $student) {
            $content .= str_pad($index + 1, 5) .
                str_pad($student['msv'], 15) .
                str_pad($student['fullname'], 30) .
                str_pad(formatDate($student['dob']), 12) .
                str_pad($student['gender'] == 'male' ? 'Nam' : ($student['gender'] == 'female' ? 'Nữ' : 'Khác'), 10) .
                str_pad($student['email'], 25) .
                $student['phone'] . "\n";
        }

        // Gửi header để trình duyệt tải file TXT
        $filename = 'students_' . date('Y-m-d_H-i-s') . '.txt'; // Đổi tên file sử dụng đuôi .txt
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        // Xuất nội dung ra file
        echo $content;

        // Kết thúc script để tránh in thêm dữ liệu không mong muốn
        exit;
    }

    /**
     * Tạo nội dung bảng điểm cho file DOCX (định dạng text)
     * @param array $filters Bộ lọc mã sinh viên và học kỳ
     */
    private function generateScoresDOCX($filters)
    {
        // Lấy dữ liệu điểm dựa trên bộ lọc
        $scoreController = new ScoreController();
        $scores = $scoreController->getAllScores($filters['student_id'] ?? null, $filters['semester'] ?? null);

        // Tạo nội dung bảng điểm dạng văn bản thô
        $content = "BẢNG ĐIỂM\n";
        $content .= "Ngày xuất: " . date('d/m/Y H:i') . "\n\n";

        // Tiêu đề bảng với căn chỉnh cột
        $content .= str_pad("STT", 5) . str_pad("Mã SV", 15) . str_pad("Họ tên", 30) .
            str_pad("Môn học", 25) . str_pad("Điểm", 8) . "Học kỳ\n";
        $content .= str_repeat("-", 100) . "\n";

        // Lặp từng bản ghi điểm sinh viên
        foreach ($scores as $index => $score) {
            $content .= str_pad($index + 1, 5) .
                str_pad($score['msv'], 15) .
                str_pad($score['fullname'], 30) .
                str_pad($score['subject'], 25) .
                str_pad($score['score'], 8) .
                $score['semester'] . "\n";
        }

        // Gửi header xuống client để tải file dạng TXT
        $filename = 'scores_' . date('Y-m-d_H-i-s') . '.txt'; // File text tạm thời cho xuất DOCX
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        // Xuất nội dung bảng điểm
        echo $content;
        exit;
    }
}