<?php
// Bắt đầu session để có thể truy cập thông tin người dùng đang đăng nhập
session_start();
// Nạp file chứa class AuthController để xử lý logic đăng xuất
require_once '../authController.php';

// Tạo đối tượng AuthController
$auth = new AuthController();
// Gọi phương thức logout để xóa session và đăng xuất người dùng
$auth->logout();

// Chuyển hướng người dùng về trang đăng nhập với thông báo đăng xuất thành công
header('Location: login.php?message=logout_success');
// Dừng thực thi script để đảm bảo không có code nào chạy sau khi chuyển hướng
exit();
?>
