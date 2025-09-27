<?php
session_start();
require_once '../utils.php';
require_once '../studentController.php';
require_once '../scoreController.php';
require_once '../assets/libs/phpword/src/PhpWord/Autoloader.php';
\PhpOffice\PhpWord\Autoloader::register();

requirePermission(PERMISSION_EXPORT_DATA);

$type = $_GET['type'] ?? 'students';
$search = $_GET['search'] ?? '';
$studentId = $_GET['student_id'] ?? null;
$semester = $_GET['semester'] ?? null;

// Create new document
$phpWord = new \PhpOffice\PhpWord\PhpWord();
$phpWord->setDefaultFontName('Times New Roman');
$phpWord->setDefaultFontSize(12);

$phpWord->getDocInfo()->setTitle($type == 'students' ? 'Danh sách sinh viên' : 'Bảng điểm');
$phpWord->getDocInfo()->setCreator('Student Management System');

// Add section
$section = $phpWord->addSection();

// Define styles
$headerStyle = array('bold' => true, 'size' => 14);
$tableStyle = array('borderSize' => 6, 'borderColor' => '999999', 'cellMargin' => 80, 'width' => 100 * 50, 'unit' => 'pct');
$cellStyle = array('valign' => 'center');
$headerCellStyle = array('bold' => true, 'bgColor' => 'f2f2f2');

if ($type == 'students') {
    $studentController = new StudentController();
    $students = $studentController->getAllStudents($search, 1000, 0);
    
    // Title
    $section->addText('DANH SÁCH SINH VIÊN', $headerStyle, array('alignment' => 'center'));
    $section->addTextBreak(1);
    
    // Table
    $table = $section->addTable($tableStyle);
    $table->addRow(null, array('tblHeader' => true));
    $headers = array('STT', 'Mã SV', 'Họ tên', 'Ngày sinh', 'Giới tính', 'Email', 'SĐT');
    foreach ($headers as $header) {
        $table->addCell(null, $headerCellStyle)->addText($header, array('bold' => true), array('alignment' => 'center'));
    }
    
    foreach ($students as $index => $student) {
        $table->addRow();
        $genderText = $student['gender'] == 'male' ? 'Nam' : ($student['gender'] == 'female' ? 'Nữ' : 'Khác');
        $rowData = [
            $index + 1,
            $student['msv'],
            $student['fullname'],
            formatDate($student['dob']),
            $genderText,
            $student['email'],
            $student['phone']
        ];
        foreach ($rowData as $cellData) {
            $table->addCell(null, $cellStyle)->addText($cellData);
        }
    }
    
    // Statistics
    $section->addTextBreak(1);
    $section->addText('Thống kê:', array('bold' => true));
    $section->addText('Tổng số sinh viên: ' . count($students));
    $section->addText('Ngày xuất báo cáo: ' . date('d/m/Y H:i'));
    
} else {
    $scoreController = new ScoreController();
    $scores = $scoreController->getAllScores($studentId, $semester);
    
    // Title
    $section->addText('BẢNG ĐIỂM', $headerStyle, array('alignment' => 'center'));
    $section->addTextBreak(1);
    
    // Filter info
    if ($studentId || $semester) {
        $section->addText('Bộ lọc áp dụng:', array('bold' => true));
        if ($studentId) {
            $studentController = new StudentController();
            $student = $studentController->getStudentById($studentId);
            $section->addText('Sinh viên: ' . ($student['fullname'] ?? 'N/A'));
        }
        if ($semester) {
            $section->addText('Học kỳ: ' . $semester);
        }
        $section->addTextBreak(1);
    }
    
    // Table
    $table = $section->addTable($tableStyle);
    $table->addRow(null, array('tblHeader' => true));
    $headers = array('STT', 'Mã SV', 'Họ tên', 'Môn học', 'Điểm', 'Học kỳ', 'Xếp loại');
    foreach ($headers as $header) {
        $table->addCell(null, $headerCellStyle)->addText($header, array('bold' => true), array('alignment' => 'center'));
    }
    
    foreach ($scores as $index => $score) {
        $table->addRow();
        $grade = $score['score'] >= 9 ? 'A+' : 
                ($score['score'] >= 8 ? 'A' : 
                ($score['score'] >= 7 ? 'B+' : 
                ($score['score'] >= 6 ? 'B' : 
                ($score['score'] >= 5 ? 'C' : 'D'))));
        
        $rowData = [
            $index + 1,
            $score['msv'],
            $score['fullname'],
            $score['subject'],
            $score['score'],
            $score['semester'],
            $grade
        ];
        foreach ($rowData as $cellData) {
            $table->addCell(null, $cellStyle)->addText($cellData);
        }
    }
    
    // Statistics
    $totalScores = count($scores);
    $avgScore = $totalScores > 0 ? array_sum(array_column($scores, 'score')) / $totalScores : 0;
    
    $section->addTextBreak(1);
    $section->addText('Thống kê:', array('bold' => true));
    $section->addText('Tổng số điểm: ' . $totalScores);
    $section->addText('Điểm trung bình: ' . number_format($avgScore, 2));
    $section->addText('Ngày xuất báo cáo: ' . date('d/m/Y H:i'));
}

// Save file
$filename = $type . '_' . date('Y-m-d_H-i-s') . '.docx';
header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$writer = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
$writer->save('php://output');
exit;
?>
