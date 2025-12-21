<?php
// --- SETUP & SECURITY ---

// Tắt hiển thị lỗi để đảm bảo chỉ output JSON
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Bắt đầu hoặc tiếp tục phiên làm việc để truy cập quyền của người dùng.
session_start();

// Thiết lập header của response để trình duyệt biết rằng nội dung trả về là định dạng JSON.
header('Content-Type: application/json; charset=utf-8');

// Hàm để trả về JSON error và dừng script
function returnJsonError($message, $code = 500) {
    http_response_code($code);
    echo json_encode(['error' => $message], JSON_UNESCAPED_UNICODE);
    exit();
}

// Bọc toàn bộ code trong try-catch để bắt mọi lỗi
try {
    // Nạp các file cần thiết
    require_once '../../utils.php';             // Chứa hàm kiểm tra quyền `requirePermission`.
    require_once '../../studentController.php'; // Chứa logic lấy thống kê sinh viên.
    require_once '../../scoreController.php';   // Chứa logic lấy thống kê điểm.

    // Yêu cầu người dùng phải có quyền `PERMISSION_VIEW_STATISTICS` để truy cập API này.
    // Kiểm tra quyền và trả về JSON error thay vì chuyển hướng (vì đây là API)
    if (!function_exists('requireLogin') || !function_exists('hasPermission')) {
        returnJsonError('Hàm kiểm tra quyền không tồn tại', 500);
    }
    
    requireLogin();
    if (!hasPermission(PERMISSION_VIEW_STATISTICS)) {
        returnJsonError('Bạn không có quyền xem thống kê', 403);
    }

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
        'monthly_data'    => [],  // Mảng chứa số lượng sinh viên mới theo tháng (trục Y).
        'subject_labels'  => [],  // Mảng chứa tên các môn học (top 5).
        'subject_scores'  => []   // Mảng chứa điểm trung bình của các môn học.
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
            $totalScore += (float)$subject['avg_score'] * (int)$subject['count'];
            // Cộng dồn tổng số lượng điểm.
            $scoreCount += (int)$subject['count'];
        }
    }

    // Tính điểm trung bình chung và làm tròn đến 1 chữ số thập phân.
    // Tránh chia cho 0 nếu không có điểm nào trong hệ thống.
    $data['avg_score'] = $scoreCount > 0 ? round($totalScore / $scoreCount, 1) : 0.0;

    // Xử lý dữ liệu điểm trung bình theo môn học (top 5).
    if (!empty($scoreStats['by_subject'])) {
        $topSubjects = array_slice($scoreStats['by_subject'], 0, 5); // Lấy 5 môn đầu tiên
        foreach ($topSubjects as $subject) {
            // Sử dụng subject_name nếu có, nếu không thì dùng subject
            $subjectName = $subject['subject_name'] ?? $subject['subject'] ?? 'N/A';
            $data['subject_labels'][] = $subjectName;
            $data['subject_scores'][] = round((float)($subject['avg_score'] ?? 0), 1);
        }
    }

    // --- OUTPUT ---

    // Mã hóa mảng `$data` thành chuỗi JSON và gửi về cho client.
    // Sử dụng JSON_UNESCAPED_UNICODE để hiển thị tiếng Việt đúng
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    // Bắt mọi lỗi còn lại và trả về JSON error
    returnJsonError('Lỗi khi xử lý dữ liệu: ' . $e->getMessage(), 500);
} catch (Error $e) {
    // Bắt lỗi fatal error (PHP 7+)
    returnJsonError('Lỗi nghiêm trọng: ' . $e->getMessage(), 500);
}
?>
