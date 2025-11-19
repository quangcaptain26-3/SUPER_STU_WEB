<?php
// Bắt đầu session để lưu trữ thông tin người dùng
session_start();
// Nạp file chứa các hàm tiện ích
require_once '../utils.php';
// Nạp file chứa class ScoreController
require_once '../scoreController.php';

// Yêu cầu người dùng phải có quyền xóa điểm
requirePermission(PERMISSION_DELETE_SCORES);

// Thiết lập header để trả về JSON
header('Content-Type: application/json');

// Kiểm tra xem request có phải là POST không
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Lấy ID điểm từ POST, mặc định là 0 nếu không có
    $scoreId = $_POST['id'] ?? 0;
    
    // Kiểm tra ID có hợp lệ không
    if (empty($scoreId)) {
        // Trả về JSON với thông báo lỗi
        echo json_encode(['success' => false, 'message' => 'ID điểm không hợp lệ']);
        // Dừng thực thi script
        exit();
    }
    
    // Tạo đối tượng ScoreController
    $scoreController = new ScoreController();
    // Gọi phương thức deleteScore để xóa điểm
    $result = $scoreController->deleteScore($scoreId);
    
    // Trả về kết quả dưới dạng JSON
    echo json_encode($result);
} else {
    // Nếu không phải POST, trả về lỗi
    echo json_encode(['success' => false, 'message' => 'Phương thức không được hỗ trợ']);
}
?>
