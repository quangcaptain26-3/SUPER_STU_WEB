<?php
session_start(); // Bắt đầu phiên làm việc
require_once '../utils.php'; // Nạp tệp utils.php chứa các hàm tiện ích
require_once '../studentController.php'; // Nạp tệp studentController.php để quản lý sinh viên
require_once '../scoreController.php'; // Nạp tệp scoreController.php để quản lý điểm
require_once '../assets/libs/phpword/src/PhpWord/Autoloader.php'; // Nạp tệp tự động tải lớp của PHPWord
\PhpOffice\PhpWord\Autoloader::register(); // Đăng ký bộ tự động tải lớp của PHPWord

requirePermission(PERMISSION_EXPORT_DATA); // Yêu cầu quyền xuất dữ liệu

$type = $_GET['type'] ?? 'students'; // Lấy loại xuất dữ liệu từ URL, mặc định là 'students'
$search = $_GET['search'] ?? ''; // Lấy từ khóa tìm kiếm từ URL, mặc định là rỗng
$studentId = $_GET['student_id'] ?? null; // Lấy ID sinh viên từ URL, mặc định là null
$semester = $_GET['semester'] ?? null; // Lấy học kỳ từ URL, mặc định là null

// Create new document
$phpWord = new \PhpOffice\PhpWord\PhpWord(); // Tạo một đối tượng PHPWord mới
$phpWord->setDefaultFontName('Times New Roman'); // Đặt font chữ mặc định là 'Times New Roman'
$phpWord->setDefaultFontSize(12); // Đặt cỡ chữ mặc định là 12

$phpWord->getDocInfo()->setTitle($type == 'students' ? 'Danh sách sinh viên' : 'Bảng điểm'); // Đặt tiêu đề cho tài liệu
$phpWord->getDocInfo()->setCreator('Student Management System'); // Đặt người tạo tài liệu

// Add section
$section = $phpWord->addSection(); // Thêm một section mới vào tài liệu

// Define styles
$headerStyle = array('bold' => true, 'size' => 14); // Định nghĩa kiểu cho tiêu đề chính
$tableStyle = array('borderSize' => 6, 'borderColor' => '999999', 'cellMargin' => 80, 'width' => 100 * 50, 'unit' => 'pct'); // Định nghĩa kiểu cho bảng
$cellStyle = array('valign' => 'center'); // Định nghĩa kiểu cho ô trong bảng
$headerCellStyle = array('bold' => true, 'bgColor' => 'f2f2f2'); // Định nghĩa kiểu cho ô tiêu đề của bảng

if ($type == 'students') { // Nếu loại xuất là 'students'
    $studentController = new StudentController(); // Tạo một đối tượng StudentController
    $students = $studentController->getAllStudents($search, 1000, 0); // Lấy danh sách tất cả sinh viên
    
    // Title
    $section->addText('DANH SÁCH SINH VIÊN', $headerStyle, array('alignment' => 'center')); // Thêm tiêu đề chính 'DANH SÁCH SINH VIÊN'
    $section->addTextBreak(1); // Thêm một dòng ngắt
    
    // Table
    $table = $section->addTable($tableStyle); // Thêm một bảng với kiểu đã định nghĩa
    $table->addRow(null, array('tblHeader' => true)); // Thêm một dòng tiêu đề cho bảng
    $headers = array('STT', 'Mã SV', 'Họ tên', 'Ngày sinh', 'Giới tính', 'Email', 'SĐT'); // Mảng chứa các tiêu đề cột
    foreach ($headers as $header) { // Lặp qua các tiêu đề
        $table->addCell(null, $headerCellStyle)->addText($header, array('bold' => true), array('alignment' => 'center')); // Thêm ô và văn bản tiêu đề vào bảng
    }
    
    foreach ($students as $index => $student) { // Lặp qua danh sách sinh viên
        $table->addRow(); // Thêm một dòng mới vào bảng
        $genderText = $student['gender'] == 'male' ? 'Nam' : ($student['gender'] == 'female' ? 'Nữ' : 'Khác'); // Chuyển đổi giới tính sang tiếng Việt
        $rowData = [ // Mảng chứa dữ liệu của một dòng
            $index + 1,
            $student['msv'],
            $student['fullname'],
            formatDate($student['dob']),
            $genderText,
            $student['email'],
            $student['phone']
        ];
        foreach ($rowData as $cellData) { // Lặp qua dữ liệu của dòng
            $table->addCell(null, $cellStyle)->addText($cellData); // Thêm ô và dữ liệu vào bảng
        }
    }
    
    // Statistics
    $section->addTextBreak(1); // Thêm một dòng ngắt
    $section->addText('Thống kê:', array('bold' => true)); // Thêm văn bản 'Thống kê:'
    $section->addText('Tổng số sinh viên: ' . count($students)); // Thêm tổng số sinh viên
    $section->addText('Ngày xuất báo cáo: ' . date('d/m/Y H:i')); // Thêm ngày xuất báo cáo
    
} else { // Nếu loại xuất không phải là 'students' (là 'scores')
    $scoreController = new ScoreController(); // Tạo một đối tượng ScoreController
    $scores = $scoreController->getAllScores($studentId, $semester); // Lấy danh sách điểm
    
    // Title
    $section->addText('BẢNG ĐIỂM', $headerStyle, array('alignment' => 'center')); // Thêm tiêu đề chính 'BẢNG ĐIỂM'
    $section->addTextBreak(1); // Thêm một dòng ngắt
    
    // Filter info
    if ($studentId || $semester) { // Nếu có ID sinh viên hoặc học kỳ
        $section->addText('Bộ lọc áp dụng:', array('bold' => true)); // Thêm văn bản 'Bộ lọc áp dụng:'
        if ($studentId) { // Nếu có ID sinh viên
            $studentController = new StudentController(); // Tạo đối tượng StudentController
            $student = $studentController->getStudentById($studentId); // Lấy thông tin sinh viên bằng ID
            $section->addText('Sinh viên: ' . ($student['fullname'] ?? 'N/A')); // Thêm tên sinh viên
        }
        if ($semester) { // Nếu có học kỳ
            $section->addText('Học kỳ: ' . $semester); // Thêm thông tin học kỳ
        }
        $section->addTextBreak(1); // Thêm một dòng ngắt
    }
    
    // Table
    $table = $section->addTable($tableStyle); // Thêm một bảng với kiểu đã định nghĩa
    $table->addRow(null, array('tblHeader' => true)); // Thêm một dòng tiêu đề cho bảng
    $headers = array('STT', 'Mã SV', 'Họ tên', 'Môn học', 'Điểm', 'Học kỳ', 'Xếp loại'); // Mảng chứa các tiêu đề cột
    foreach ($headers as $header) { // Lặp qua các tiêu đề
        $table->addCell(null, $headerCellStyle)->addText($header, array('bold' => true), array('alignment' => 'center')); // Thêm ô và văn bản tiêu đề vào bảng
    }
    
    foreach ($scores as $index => $score) { // Lặp qua danh sách điểm
        $table->addRow(); // Thêm một dòng mới
        $grade = $score['score'] >= 9 ? 'A+' : // Xếp loại học lực dựa trên điểm
                ($score['score'] >= 8 ? 'A' : 
                ($score['score'] >= 7 ? 'B+' : 
                ($score['score'] >= 6 ? 'B' : 
                ($score['score'] >= 5 ? 'C' : 'D'))));
        
        $rowData = [ // Mảng chứa dữ liệu của một dòng
            $index + 1,
            $score['msv'],
            $score['fullname'],
            $score['subject'],
            $score['score'],
            $score['semester'],
            $grade
        ];
        foreach ($rowData as $cellData) { // Lặp qua dữ liệu của dòng
            $table->addCell(null, $cellStyle)->addText($cellData); // Thêm ô và dữ liệu vào bảng
        }
    }
    
    // Statistics
    $totalScores = count($scores); // Tính tổng số điểm
    $avgScore = $totalScores > 0 ? array_sum(array_column($scores, 'score')) / $totalScores : 0; // Tính điểm trung bình
    
    $section->addTextBreak(1); // Thêm một dòng ngắt
    $section->addText('Thống kê:', array('bold' => true)); // Thêm văn bản 'Thống kê:'
    $section->addText('Tổng số điểm: ' . $totalScores); // Thêm tổng số điểm
    $section->addText('Điểm trung bình: ' . number_format($avgScore, 2)); // Thêm điểm trung bình
    $section->addText('Ngày xuất báo cáo: ' . date('d/m/Y H:i')); // Thêm ngày xuất báo cáo
}

// Save file
$filename = $type . '_' . date('Y-m-d_H-i-s') . '.docx'; // Tạo tên tệp tin
header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document'); // Thiết lập header Content-Type cho tệp DOCX
header('Content-Disposition: attachment;filename="' . $filename . '"'); // Thiết lập header để tải tệp xuống
header('Cache-Control: max-age=0'); // Thiết lập header Cache-Control

$writer = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007'); // Tạo đối tượng writer cho Word2007
$writer->save('php://output'); // Lưu tệp vào output stream
exit; // Kết thúc kịch bản
?>
