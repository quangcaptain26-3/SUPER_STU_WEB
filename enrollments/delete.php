<?php
session_start();
require_once '../utils.php';
require_once '../enrollmentController.php';

header('Content-Type: application/json');

// Kiểm tra quyền - trả về JSON error nếu không có quyền
if (!hasPermission(PERMISSION_DELETE_ENROLLMENTS)) {
    echo json_encode(['success' => false, 'message' => 'Bạn không có quyền thực hiện hành động này']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'] ?? 0;
    
    if ($id) {
        $enrollmentController = new EnrollmentController();
        $result = $enrollmentController->deleteEnrollment($id);
        echo json_encode($result);
    } else {
        echo json_encode(['success' => false, 'message' => 'ID không hợp lệ']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Phương thức không hợp lệ']);
}

