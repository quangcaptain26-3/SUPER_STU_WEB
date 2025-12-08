<?php
// Bắt đầu session để có thể lưu trữ thông tin tạm thời
session_start();
// Nạp file chứa class AuthController để xử lý logic quên mật khẩu
require_once '../authController.php';
// Nạp file chứa các hàm tiện ích
require_once '../utils.php';

// Kiểm tra xem người dùng đã đăng nhập chưa
// Nếu đã đăng nhập thì chuyển hướng về trang chủ (không cần quên mật khẩu)
if (isLoggedIn()) {
    // Chuyển hướng về trang chủ
    header('Location: index.php');
    // Dừng thực thi script
    exit();
}

// Khởi tạo biến lưu thông báo lỗi
$error = '';
// Khởi tạo biến lưu thông báo thành công
$success = '';

// Kiểm tra xem request có phải là POST không (khi form quên mật khẩu được submit)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Lấy email từ form và làm sạch để tránh XSS
    $email = sanitize($_POST['email']);
    
    // Kiểm tra email có được nhập không
    if (empty($email)) {
        // Nếu chưa nhập email, gán thông báo lỗi
        $error = 'Vui lòng nhập email';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // Kiểm tra định dạng email có hợp lệ không
        $error = 'Email không hợp lệ';
    } else {
        // Nếu email hợp lệ, tạo đối tượng AuthController
        $auth = new AuthController();
        // Gọi phương thức forgotPassword để gửi email reset mật khẩu
        $result = $auth->forgotPassword($email);
        
        // Nếu gửi email thành công
        if ($result['success']) {
            // Lưu thông báo thành công
            $success = $result['message'];
            // Nếu là development mode, lưu link reset để hiển thị
            if (isset($result['reset_link'])) {
                $resetLink = $result['reset_link'];
                $resetToken = $result['token'] ?? '';
            }
        } else {
            // Nếu gửi email thất bại, lưu thông báo lỗi
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
    <title>Quên mật khẩu - Hệ thống quản lý sinh viên</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .forgot-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .forgot-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        .forgot-form {
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
        .btn-forgot {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-weight: 600;
            transition: transform 0.3s;
        }
        .btn-forgot:hover {
            transform: translateY(-2px);
        }
        .back-to-login {
            text-align: center;
            margin-top: 1rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-4">
                <div class="forgot-container">
                    <div class="forgot-header">
                        <i class="fas fa-key fa-3x mb-3"></i>
                        <h3>Quên mật khẩu</h3>
                        <p class="mb-0">Nhập email để nhận link đặt lại mật khẩu</p>
                    </div>
                    
                    <div class="forgot-form">
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
                        
                        <?php if (isset($resetLink)): ?>
                        <!-- Development Mode: Hiển thị link reset trực tiếp -->
                        <div class="alert alert-info" role="alert">
                            <h6><i class="fas fa-code me-2"></i>Development Mode - Link Reset:</h6>
                            <div class="mb-2">
                                <strong>Link đặt lại mật khẩu:</strong><br>
                                <a href="<?php echo htmlspecialchars($resetLink); ?>" target="_blank" class="text-break">
                                    <?php echo htmlspecialchars($resetLink); ?>
                                </a>
                            </div>
                            <?php if (!empty($resetToken)): ?>
                            <div class="mb-2">
                                <strong>Token:</strong><br>
                                <code class="text-break"><?php echo htmlspecialchars($resetToken); ?></code>
                            </div>
                            <?php endif; ?>
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                Trong môi trường development, link được hiển thị trực tiếp thay vì gửi email.
                            </small>
                        </div>
                        <?php endif; ?>
                        <?php endif; ?>
                        
                        <?php if (!$success): ?>
                        <form method="POST">
                            <div class="mb-3">
                                <label for="email" class="form-label">
                                    <i class="fas fa-envelope me-2"></i>Email đăng ký
                                </label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($email ?? ''); ?>" 
                                       placeholder="Nhập email của bạn" required>
                                <div class="form-text">
                                    Chúng tôi sẽ gửi link đặt lại mật khẩu đến email này
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary btn-forgot w-100">
                                <i class="fas fa-paper-plane me-2"></i>Gửi link đặt lại
                            </button>
                        </form>
                        <?php else: ?>
                        <div class="text-center">
                            <i class="fas fa-envelope-open fa-4x text-success mb-3"></i>
                            <h5>Email đã được gửi!</h5>
                            <p class="text-muted">
                                Vui lòng kiểm tra hộp thư của bạn và click vào link để đặt lại mật khẩu.
                                Link có hiệu lực trong <?php echo isDevelopmentMode() ? '24 giờ' : '12 giờ'; ?>.
                            </p>
                        </div>
                        <?php endif; ?>
                        
                        <div class="back-to-login">
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
        // Tự động focus vào trường email khi trang load (nếu có)
        // Giúp người dùng có thể bắt đầu nhập ngay
        document.getElementById('email')?.focus();
        
        // Validation form trước khi submit
        // Kiểm tra dữ liệu ở phía client để tránh submit form không hợp lệ
        document.querySelector('form')?.addEventListener('submit', function(e) {
            // Lấy giá trị email và loại bỏ khoảng trắng đầu cuối
            const email = document.getElementById('email').value.trim();
            
            // Kiểm tra email có được nhập không
            if (!email) {
                // Ngăn form submit
                e.preventDefault();
                // Hiển thị cảnh báo
                alert('Vui lòng nhập email');
                return false;
            }
            
            // Regex để kiểm tra định dạng email
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            // Kiểm tra email có đúng định dạng không
            if (!emailRegex.test(email)) {
                // Ngăn form submit
                e.preventDefault();
                // Hiển thị cảnh báo
                alert('Email không hợp lệ');
                return false;
            }
        });
    </script>
</body>
</html>
