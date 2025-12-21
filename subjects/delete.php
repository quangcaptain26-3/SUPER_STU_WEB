<?php
session_start();
require_once '../utils.php';
require_once '../subjectController.php';

header('Content-Type: application/json');

// Kiểm tra quyền - trả về JSON error nếu không có quyền
if (!hasPermission(PERMISSION_DELETE_SUBJECTS)) {
    echo json_encode(['success' => false, 'message' => 'Bạn không có quyền thực hiện hành động này']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'] ?? 0;
    
    if ($id) {
        $subjectController = new SubjectController();
        $result = $subjectController->deleteSubject($id);
        echo json_encode($result);
    } else {
        echo json_encode(['success' => false, 'message' => 'ID không hợp lệ']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Phương thức không hợp lệ']);
}

