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
    $username = sanitize($_POST['username']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        $error = 'Vui lòng nhập đầy đủ thông tin';
    } else {
        $auth = new AuthController();
        $result = $auth->login($username, $password);
        
        if ($result['success']) {
            header('Location: index.php');
            exit();
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
    <title>Đăng nhập - Hệ thống quản lý sinh viên</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .login-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .login-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        .login-form {
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
        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-weight: 600;
            transition: transform 0.3s;
        }
        .btn-login:hover {
            transform: translateY(-2px);
        }
        .social-login {
            text-align: center;
            margin-top: 1rem;
        }
        .social-btn {
            display: inline-block;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            line-height: 50px;
            text-align: center;
            color: white;
            text-decoration: none;
            margin: 0 5px;
            transition: transform 0.3s;
        }
        .social-btn:hover {
            transform: scale(1.1);
            color: white;
        }
        .facebook { background: #3b5998; }
        .google { background: #dd4b39; }
        .twitter { background: #1da1f2; }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-4">
                <div class="login-container">
                    <div class="login-header">
                        <i class="fas fa-graduation-cap fa-3x mb-3"></i>
                        <h3>Đăng nhập</h3>
                        <p class="mb-0">Hệ thống quản lý sinh viên</p>
                    </div>
                    
                    <div class="login-form">
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
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label for="username" class="form-label">
                                    <i class="fas fa-user me-2"></i>Tên đăng nhập
                                </label>
                                <input type="text" class="form-control" id="username" name="username" 
                                       value="<?php echo htmlspecialchars($username ?? ''); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">
                                    <i class="fas fa-lock me-2"></i>Mật khẩu
                                </label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="remember">
                                <label class="form-check-label" for="remember">
                                    Ghi nhớ đăng nhập
                                </label>
                            </div>
                            
                            <button type="submit" class="btn btn-primary btn-login w-100">
                                <i class="fas fa-sign-in-alt me-2"></i>Đăng nhập
                            </button>
                        </form>
                        
                        <div class="text-center mt-3">
                            <a href="forgot_password.php" class="text-decoration-none">
                                <i class="fas fa-key me-1"></i>Quên mật khẩu?
                            </a>
                        </div>
                        
                        <hr class="my-4">
                        
                        <div class="text-center">
                            <p class="text-muted mb-3">Hoặc đăng nhập bằng</p>
                            <div class="social-login">
                                <a href="#" class="social-btn facebook">
                                    <i class="fab fa-facebook-f"></i>
                                </a>
                                <a href="#" class="social-btn google">
                                    <i class="fab fa-google"></i>
                                </a>
                                <a href="#" class="social-btn twitter">
                                    <i class="fab fa-twitter"></i>
                                </a>
                            </div>
                        </div>
                        
                        <div class="text-center mt-4">
                            <p class="text-muted">
                                Chưa có tài khoản? 
                                <a href="register.php" class="text-decoration-none fw-bold">
                                    Đăng ký ngay
                                </a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto focus on username field
        document.getElementById('username').focus();
        
        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value;
            
            if (!username || !password) {
                e.preventDefault();
                alert('Vui lòng nhập đầy đủ thông tin');
                return false;
            }
        });
    </script>
</body>
</html>
