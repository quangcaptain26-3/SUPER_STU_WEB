<?php
session_start();
require_once '../utils.php';
require_once '../scoreController.php';

requirePermission(PERMISSION_DELETE_SCORES);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $scoreId = $_POST['id'] ?? 0;
    
    if (empty($scoreId)) {
        echo json_encode(['success' => false, 'message' => 'ID điểm không hợp lệ']);
        exit();
    }
    
    $scoreController = new ScoreController();
    $result = $scoreController->deleteScore($scoreId);
    
    echo json_encode($result);
} else {
    echo json_encode(['success' => false, 'message' => 'Phương thức không được hỗ trợ']);
}
?>
