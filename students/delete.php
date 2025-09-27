<?php
session_start();
require_once '../utils.php';
require_once '../studentController.php';

requirePermission(PERMISSION_DELETE_STUDENTS);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $studentId = $_POST['id'] ?? 0;
    
    if (empty($studentId)) {
        echo json_encode(['success' => false, 'message' => 'ID sinh viên không hợp lệ']);
        exit();
    }
    
    $studentController = new StudentController();
    $result = $studentController->deleteStudent($studentId);
    
    echo json_encode($result);
} else {
    echo json_encode(['success' => false, 'message' => 'Phương thức không được hỗ trợ']);
}
?>
