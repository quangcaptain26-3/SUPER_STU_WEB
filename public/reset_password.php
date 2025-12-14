<?php
// Bắt đầu session để có thể lưu trữ thông tin tạm thời
session_start();
// Nạp file chứa class AuthController để xử lý logic đặt lại mật khẩu
require_once '../authController.php';
// Nạp file chứa các hàm tiện ích
require_once '../utils.php';

// Kiểm tra xem người dùng đã đăng nhập chưa
// Nếu đã đăng nhập thì chuyển hướng về trang chủ (không cần reset mật khẩu)
if (isLoggedIn()) {
    // Chuyển hướng về trang chủ
    header('Location: ../index.php');
    // Dừng thực thi script
    exit();
}

// Khởi tạo biến lưu thông báo lỗi
$error = '';
// Khởi tạo biến lưu thông báo thành công
$success = '';
// Lấy token từ tham số GET (token được gửi qua email)
$token = $_GET['token'] ?? '';

// Kiểm tra token có tồn tại không
// Token là bắt buộc để xác thực người dùng muốn đặt lại mật khẩu
if (empty($token)) {
    // Nếu không có token, chuyển hướng về trang đăng nhập với thông báo lỗi
    header('Location: login.php?error=invalid_token');
    // Dừng thực thi script
    exit();
}

// Kiểm tra xem request có phải là POST không (khi form đặt lại mật khẩu được submit)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Lấy mật khẩu mới từ form
    $newPassword = $_POST['new_password'];
    // Lấy mật khẩu xác nhận từ form
    $confirmPassword = $_POST['confirm_password'];
    
    // Kiểm tra các trường bắt buộc có được điền đầy đủ không
    if (empty($newPassword) || empty($confirmPassword)) {
        // Nếu thiếu thông tin, gán thông báo lỗi
        $error = 'Vui lòng nhập đầy đủ thông tin';
    } elseif ($newPassword !== $confirmPassword) {
        // Kiểm tra mật khẩu xác nhận có khớp với mật khẩu mới không
        $error = 'Mật khẩu xác nhận không khớp';
    } elseif (strlen($newPassword) < 6) {
        // Kiểm tra độ dài mật khẩu (tối thiểu 6 ký tự)
        $error = 'Mật khẩu phải có ít nhất 6 ký tự';
    } else {
        // Nếu tất cả validation đều pass, tạo đối tượng AuthController
        $auth = new AuthController();
        // Gọi phương thức resetPassword để đặt lại mật khẩu với token
        $result = $auth->resetPassword($token, $newPassword);
        
        // Nếu đặt lại mật khẩu thành công
        if ($result['success']) {
            // Lưu thông báo thành công kèm hướng dẫn đăng nhập
            $success = $result['message'] . ' Bạn có thể đăng nhập ngay bây giờ.';
        } else {
            // Nếu đặt lại mật khẩu thất bại, lưu thông báo lỗi
            $error = $result['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt lại mật khẩu - Hệ thống quản lý sinh viên</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .reset-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .reset-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        .reset-form {
            padding: 2rem;
        }
        .form-control {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            padding: 12px 15px;
            transition: all 0.3s;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-reset {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-weight: 600;
            transition: transform 0.3s;
        }
        .btn-reset:hover {
            transform: translateY(-2px);
        }
        .password-strength {
            height: 4px;
            background: #e9ecef;
            border-radius: 2px;
            margin-top: 5px;
            overflow: hidden;
        }
        .password-strength-bar {
            height: 100%;
            transition: all 0.3s;
            border-radius: 2px;
        }
        .strength-weak { background: #dc3545; width: 25%; }
        .strength-fair { background: #ffc107; width: 50%; }
        .strength-good { background: #17a2b8; width: 75%; }
        .strength-strong { background: #28a745; width: 100%; }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-4">
                <div class="reset-container">
                    <div class="reset-header">
                        <i class="fas fa-lock fa-3x mb-3"></i>
                        <h3>Đặt lại mật khẩu</h3>
                        <p class="mb-0">Nhập mật khẩu mới cho tài khoản của bạn</p>
                    </div>
                    
                    <div class="reset-form">
                        <?php if ($error): ?>
                        <div class="alert alert-danger" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                        <div class="alert alert-success" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            <?php echo htmlspecialchars($success); ?>
                        </div>
                        <div class="text-center mt-4">
                            <a href="login.php" class="btn btn-primary">
                                <i class="fas fa-sign-in-alt me-2"></i>Đăng nhập ngay
                            </a>
                        </div>
                        <?php else: ?>
                        <form method="POST" id="resetForm">
                            <div class="mb-3">
                                <label for="new_password" class="form-label">
                                    <i class="fas fa-lock me-2"></i>Mật khẩu mới
                                </label>
                                <input type="password" class="form-control" id="new_password" name="new_password" required>
                                <div class="password-strength">
                                    <div class="password-strength-bar" id="strengthBar"></div>
                                </div>
                                <small class="text-muted">Mật khẩu phải có ít nhất 6 ký tự</small>
                            </div>
                            
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">
                                    <i class="fas fa-lock me-2"></i>Xác nhận mật khẩu mới
                                </label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>
                            
                            <button type="submit" class="btn btn-primary btn-reset w-100">
                                <i class="fas fa-save me-2"></i>Đặt lại mật khẩu
                            </button>
                        </form>
                        <?php endif; ?>
                        
                        <div class="text-center mt-4">
                            <a href="login.php" class="text-decoration-none">
                                <i class="fas fa-arrow-left me-1"></i>Quay lại đăng nhập
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Nạp Bootstrap JS từ CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Kiểm tra độ mạnh mật khẩu khi người dùng nhập
        // Hiển thị thanh độ mạnh mật khẩu để người dùng biết mật khẩu của họ có an toàn không
        document.getElementById('new_password')?.addEventListener('input', function() {
            // Lấy giá trị mật khẩu
            const password = this.value;
            // Lấy phần tử thanh độ mạnh
            const strengthBar = document.getElementById('strengthBar');
            
            // Tính điểm độ mạnh mật khẩu (0-5 điểm)
            let strength = 0;
            if (password.length >= 6) strength++;           // Độ dài >= 6 ký tự
            if (password.match(/[a-z]/)) strength++;         // Có chữ thường
            if (password.match(/[A-Z]/)) strength++;         // Có chữ hoa
            if (password.match(/[0-9]/)) strength++;         // Có số
            if (password.match(/[^a-zA-Z0-9]/)) strength++;  // Có ký tự đặc biệt
            
            // Reset class của thanh độ mạnh
            strengthBar.className = 'password-strength-bar';
            // Xác định màu sắc và độ rộng dựa trên điểm độ mạnh
            if (strength <= 1) {
                // Yếu: màu đỏ, 25% độ rộng
                strengthBar.classList.add('strength-weak');
            } else if (strength <= 2) {
                // Trung bình: màu vàng, 50% độ rộng
                strengthBar.classList.add('strength-fair');
            } else if (strength <= 3) {
                // Khá: màu xanh dương, 75% độ rộng
                strengthBar.classList.add('strength-good');
            } else {
                // Mạnh: màu xanh lá, 100% độ rộng
                strengthBar.classList.add('strength-strong');
            }
        });
        
        // Validation form trước khi submit
        // Kiểm tra dữ liệu ở phía client để tránh submit form không hợp lệ
        document.getElementById('resetForm')?.addEventListener('submit', function(e) {
            // Lấy giá trị các trường
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            // Kiểm tra mật khẩu xác nhận có khớp không
            if (newPassword !== confirmPassword) {
                // Ngăn form submit
                e.preventDefault();
                // Hiển thị cảnh báo
                alert('Mật khẩu xác nhận không khớp');
                return false;
            }
            
            // Kiểm tra độ dài mật khẩu
            if (newPassword.length < 6) {
                // Ngăn form submit
                e.preventDefault();
                // Hiển thị cảnh báo
                alert('Mật khẩu phải có ít nhất 6 ký tự');
                return false;
            }
        });
        
        // Tự động focus vào trường mật khẩu mới khi trang load (nếu có)
        document.getElementById('new_password')?.focus();
    </script>
</body>
</html>
