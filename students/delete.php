<?php
// Bắt đầu session để lưu trữ thông tin người dùng
session_start();
// Nạp file chứa các hàm tiện ích
require_once '../utils.php';
// Nạp file chứa class StudentController
require_once '../studentController.php';

// Yêu cầu người dùng phải có quyền xóa sinh viên
requirePermission(PERMISSION_DELETE_STUDENTS);

// Thiết lập header để trả về JSON
header('Content-Type: application/json');

// Kiểm tra xem request có phải là POST không
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Lấy ID sinh viên từ POST, mặc định là 0 nếu không có
    $studentId = $_POST['id'] ?? 0;
    
    // Kiểm tra ID có hợp lệ không
    if (empty($studentId)) {
        // Trả về JSON với thông báo lỗi
        echo json_encode(['success' => false, 'message' => 'ID sinh viên không hợp lệ']);
        // Dừng thực thi script
        exit();
    }
    
    // Tạo đối tượng StudentController
    $studentController = new StudentController();
    // Gọi phương thức deleteStudent để xóa sinh viên
    $result = $studentController->deleteStudent($studentId);
    
    // Trả về kết quả dưới dạng JSON
    echo json_encode($result);
} else {
    // Nếu không phải POST, trả về lỗi
    echo json_encode(['success' => false, 'message' => 'Phương thức không được hỗ trợ']);
}
?>
