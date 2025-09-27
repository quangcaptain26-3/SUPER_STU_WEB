<?php
session_start();
require_once '../authController.php';
require_once '../utils.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: index.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = sanitize($_POST['email']);
    
    if (empty($email)) {
        $error = 'Vui lòng nhập email';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email không hợp lệ';
    } else {
        $auth = new AuthController();
        $result = $auth->forgotPassword($email);
        
        if ($result['success']) {
            $success = $result['message'];
        } else {
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
                                Link có hiệu lực trong 1 giờ.
                            </p>
                            <div class="mt-4">
                                <a href="login.php" class="btn btn-outline-primary">
                                    <i class="fas fa-arrow-left me-2"></i>Quay lại đăng nhập
                                </a>
                            </div>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto focus on email field
        document.getElementById('email')?.focus();
        
        // Form validation
        document.querySelector('form')?.addEventListener('submit', function(e) {
            const email = document.getElementById('email').value.trim();
            
            if (!email) {
                e.preventDefault();
                alert('Vui lòng nhập email');
                return false;
            }
            
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                e.preventDefault();
                alert('Email không hợp lệ');
                return false;
            }
        });
    </script>
</body>
</html>
