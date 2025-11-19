<?php
// Bắt đầu phiên làm việc
session_start();
// Nạp các tệp cần thiết
require_once '../../utils.php'; // Chứa các hàm tiện ích và hằng số
require_once '../../studentController.php'; // Chứa logic xử lý sinh viên
require_once '../../scoreController.php'; // Chứa logic xử lý điểm

// Yêu cầu quyền xem thống kê, nếu không có quyền sẽ dừng thực thi
requirePermission(PERMISSION_VIEW_STATISTICS);

// Thiết lập header cho response là JSON
header('Content-Type: application/json');

// Khởi tạo các controller
$studentController = new StudentController();
$scoreController = new ScoreController();

// Lấy dữ liệu thống kê từ các controller
$studentStats = $studentController->getStatistics();
$scoreStats = $scoreController->getScoreStatistics();

// Chuẩn bị mảng dữ liệu để trả về cho client
$data = [
    'total_students' => $studentStats['total_students'], // Tổng số sinh viên
    'male_students' => 0, // Số sinh viên nam, mặc định là 0
    'female_students' => 0, // Số sinh viên nữ, mặc định là 0
    'other_students' => 0, // Số sinh viên giới tính khác, mặc định là 0
    'avg_score' => 0, // Điểm trung bình, mặc định là 0
    'monthly_labels' => [], // Nhãn cho biểu đồ (các tháng)
    'monthly_data' => [] // Dữ liệu cho biểu đồ (số lượng sinh viên theo tháng)
];

// Xử lý dữ liệu thống kê theo giới tính
foreach ($studentStats['by_gender'] as $gender) {
    switch ($gender['gender']) {
        case 'male':
            $data['male_students'] = $gender['count']; // Gán số lượng sinh viên nam
            break;
        case 'female':
            $data['female_students'] = $gender['count']; // Gán số lượng sinh viên nữ
            break;
        default:
            $data['other_students'] = $gender['count']; // Gán số lượng sinh viên giới tính khác
            break;
    }
}

// Xử lý dữ liệu thống kê sinh viên mới theo tháng
foreach ($studentStats['by_month'] as $month) {
    $data['monthly_labels'][] = $month['month']; // Thêm nhãn tháng
    $data['monthly_data'][] = $month['count']; // Thêm dữ liệu số lượng
}

// Tính toán điểm trung bình chung
$totalScore = 0; // Tổng điểm
$scoreCount = 0; // Tổng số lượng điểm
foreach ($scoreStats['by_subject'] as $subject) {
    // Tổng điểm = tổng của (điểm trung bình môn * số lượng điểm của môn đó)
    $totalScore += $subject['avg_score'] * $subject['count'];
    $scoreCount += $subject['count']; // Cộng dồn số lượng điểm
}
// Điểm trung bình chung, làm tròn đến 1 chữ số thập phân
$data['avg_score'] = $scoreCount > 0 ? round($totalScore / $scoreCount, 1) : 0;

// Trả về dữ liệu dưới dạng JSON
echo json_encode($data);
?>
