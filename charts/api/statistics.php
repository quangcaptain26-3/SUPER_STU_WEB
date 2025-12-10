<?php
// --- SETUP & SECURITY ---

// Bắt đầu hoặc tiếp tục phiên làm việc để truy cập quyền của người dùng.
session_start();

// Thiết lập header của response để trình duyệt biết rằng nội dung trả về là định dạng JSON.
header('Content-Type: application/json');

// Nạp các file cần thiết
require_once '../../utils.php';             // Chứa hàm kiểm tra quyền `requirePermission`.
require_once '../../studentController.php'; // Chứa logic lấy thống kê sinh viên.
require_once '../../scoreController.php';   // Chứa logic lấy thống kê điểm.

// Yêu cầu người dùng phải có quyền `PERMISSION_VIEW_STATISTICS` để truy cập API này.
// Nếu không có quyền, hàm `requirePermission` sẽ dừng script và chuyển hướng.
// Điều này ngăn chặn việc truy cập dữ liệu thống kê trái phép.
requirePermission(PERMISSION_VIEW_STATISTICS);


// --- DATA FETCHING & PROCESSING ---

// Khởi tạo các đối tượng controller.
$studentController = new StudentController();
$scoreController = new ScoreController();

// Gọi các phương thức `getStatistics()` từ cả hai controller để lấy dữ liệu thống kê thô từ CSDL.
$studentStats = $studentController->getStatistics(); // Lấy tổng SV, SV theo giới tính, SV theo tháng.
$scoreStats = $scoreController->getScoreStatistics();   // Lấy điểm TB theo môn, phân bố xếp loại.


// --- DATA TRANSFORMATION & AGGREGATION ---

// Khởi tạo cấu trúc dữ liệu JSON cuối cùng sẽ được trả về cho client.
$data = [
    'total_students'  => (int)($studentStats['total_students'] ?? 0), // Lấy tổng số sinh viên.
    'male_students'   => 0, // Khởi tạo số lượng sinh viên nam.
    'female_students' => 0, // Khởi tạo số lượng sinh viên nữ.
    'other_students'  => 0, // Khởi tạo số lượng sinh viên giới tính khác.
    'avg_score'       => 0.0, // Khởi tạo điểm trung bình chung.
    'monthly_labels'  => [],  // Mảng chứa nhãn các tháng cho biểu đồ (trục X).
    'monthly_data'    => []   // Mảng chứa số lượng sinh viên mới theo tháng (trục Y).
];

// Xử lý dữ liệu thống kê theo giới tính từ `$studentStats`.
if (!empty($studentStats['by_gender'])) {
    foreach ($studentStats['by_gender'] as $gender) {
        switch ($gender['gender']) {
            case 'male':
                $data['male_students'] = (int)$gender['count'];
                break;
            case 'female':
                $data['female_students'] = (int)$gender['count'];
                break;
            default:
                $data['other_students'] += (int)$gender['count']; // Cộng dồn cho các giới tính 'other'.
                break;
        }
    }
}

// Xử lý dữ liệu thống kê sinh viên mới theo tháng từ `$studentStats`.
if (!empty($studentStats['by_month'])) {
    foreach ($studentStats['by_month'] as $month) {
        // Thêm tháng (ví dụ: '2025-12') vào mảng nhãn.
        $data['monthly_labels'][] = $month['month'];
        // Thêm số lượng sinh viên của tháng đó vào mảng dữ liệu.
        $data['monthly_data'][] = (int)$month['count'];
    }
}

// Tính toán điểm trung bình chung có trọng số từ `$scoreStats`.
$totalScore = 0;
$scoreCount = 0;
if (!empty($scoreStats['by_subject'])) {
    foreach ($scoreStats['by_subject'] as $subject) {
        // Tổng điểm = tổng của (điểm trung bình của môn * số lượng điểm của môn đó).
        $totalScore += $subject['avg_score'] * $subject['count'];
        // Cộng dồn tổng số lượng điểm.
        $scoreCount += $subject['count'];
    }
}

// Tính điểm trung bình chung và làm tròn đến 1 chữ số thập phân.
// Tránh chia cho 0 nếu không có điểm nào trong hệ thống.
$data['avg_score'] = $scoreCount > 0 ? round($totalScore / $scoreCount, 1) : 0.0;


// --- OUTPUT ---

// Mã hóa mảng `$data` thành chuỗi JSON và gửi về cho client.
echo json_encode($data);
?>
