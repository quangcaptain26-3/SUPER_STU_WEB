<?php
session_start();
require_once '../utils.php';
require_once '../authController.php';

requireLogin();

$authController = new AuthController();
$user = $authController->getUserById($_SESSION['user_id']);

$error = '';
$success = '';

// Xử lý cập nhật thông tin
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'update_profile') {
    $username = sanitize($_POST['username']);
    $email = sanitize($_POST['email']);
    
    if (empty($username) || empty($email)) {
        $error = 'Vui lòng điền đầy đủ thông tin';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email không hợp lệ';
    } else {
        $data = [
            'username' => $username,
            'email' => $email,
            'role' => $user['role'] // Giữ nguyên role
        ];
        
        $result = $authController->updateUser($_SESSION['user_id'], $data);
        if ($result['success']) {
            $success = $result['message'];
            $_SESSION['username'] = $username;
            $_SESSION['email'] = $email;
            $user = $authController->getUserById($_SESSION['user_id']); // Cập nhật lại thông tin
        } else {
            $error = $result['message'];
        }
    }
}

// Xử lý đổi mật khẩu
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'change_password') {
    $currentPassword = $_POST['current_password'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];
    
    if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        $error = 'Vui lòng điền đầy đủ thông tin';
    } elseif ($newPassword !== $confirmPassword) {
        $error = 'Mật khẩu mới và xác nhận không khớp';
    } elseif (strlen($newPassword) < 6) {
        $error = 'Mật khẩu mới phải có ít nhất 6 ký tự';
    } else {
        // Kiểm tra mật khẩu hiện tại
        $loginResult = $authController->login($user['username'], $currentPassword);
        if ($loginResult['success']) {
            $result = $authController->changePassword($_SESSION['user_id'], $newPassword);
            if ($result['success']) {
                $success = $result['message'];
            } else {
                $error = $result['message'];
            }
        } else {
            $error = 'Mật khẩu hiện tại không đúng';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thông tin cá nhân - Hệ thống quản lý sinh viên</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 12px 20px;
            border-radius: 8px;
            margin: 2px 0;
            transition: all 0.3s;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            color: white;
            background: rgba(255,255,255,0.2);
            transform: translateX(5px);
        }
        .main-content {
            background-color: #f8f9fa;
            min-height: 100vh;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
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
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            padding: 12px 30px;
            font-weight: 600;
            transition: transform 0.3s;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
        }
        .profile-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
        }
        .avatar-large {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: rgba(255,255,255,0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            border: 4px solid white;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar p-0">
                <div class="p-3">
                    <h4 class="text-white mb-4">
                        <i class="fas fa-graduation-cap me-2"></i>
                        Student Management
                    </h4>
                    <div class="text-white-50 mb-3">
                        <i class="fas fa-user me-2"></i>
                        <?php echo htmlspecialchars($_SESSION['username']); ?>
                        <span class="badge <?php echo getRoleBadgeClass($_SESSION['role']); ?> ms-2">
                            <?php echo getRoleDisplayName($_SESSION['role']); ?>
                        </span>
                    </div>
                </div>
                
                <nav class="nav flex-column px-3">
                    <a class="nav-link" href="index.php">
                        <i class="fas fa-home me-2"></i>Trang chủ
                    </a>
                    
                    <?php if (canAccess(PERMISSION_VIEW_STUDENTS)): ?>
                    <a class="nav-link" href="../students/list.php">
                        <i class="fas fa-users me-2"></i>
                        <?php echo canAccess(PERMISSION_ADD_STUDENTS) ? 'Quản lý sinh viên' : 'Danh sách sinh viên'; ?>
                    </a>
                    <?php endif; ?>
                    
                    <?php if (canAccess(PERMISSION_VIEW_SCORES)): ?>
                    <a class="nav-link" href="../scores/list.php">
                        <i class="fas fa-chart-line me-2"></i>
                        <?php echo canAccess(PERMISSION_ADD_SCORES) ? 'Quản lý điểm' : 'Xem điểm'; ?>
                    </a>
                    <?php endif; ?>
                    
                    <?php if (canAccess(PERMISSION_VIEW_STATISTICS)): ?>
                    <a class="nav-link" href="../charts/statistics.php">
                        <i class="fas fa-chart-bar me-2"></i>Thống kê
                    </a>
                    <?php endif; ?>
                    
                    <?php if (canAccess(PERMISSION_MANAGE_USERS)): ?>
                    <a class="nav-link" href="users.php">
                        <i class="fas fa-user-cog me-2"></i>Quản lý người dùng
                    </a>
                    <?php endif; ?>
                    
                    <a class="nav-link active" href="profile.php">
                        <i class="fas fa-user me-2"></i>Thông tin cá nhân
                    </a>
                    
                    <a class="nav-link" href="logout.php">
                        <i class="fas fa-sign-out-alt me-2"></i>Đăng xuất
                    </a>
                </nav>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 main-content">
                <div class="p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2><i class="fas fa-user me-2"></i>Thông tin cá nhân</h2>
                    </div>
                    
                    <!-- Profile Header -->
                    <div class="profile-header">
                        <div class="row align-items-center">
                            <div class="col-md-3 text-center">
                                <div class="avatar-large mx-auto">
                                    <i class="fas fa-user"></i>
                                </div>
                            </div>
                            <div class="col-md-9">
                                <h3 class="mb-2"><?php echo htmlspecialchars($user['username']); ?></h3>
                                <p class="mb-1">
                                    <i class="fas fa-envelope me-2"></i>
                                    Email: <?php echo htmlspecialchars($user['email']); ?>
                                </p>
                                <p class="mb-1">
                                    <i class="fas fa-user-tag me-2"></i>
                                    Vai trò: 
                                    <span class="badge bg-light text-dark">
                                        <?php echo getRoleDisplayName($user['role']); ?>
                                    </span>
                                </p>
                                <p class="mb-0">
                                    <i class="fas fa-calendar me-2"></i>
                                    Tham gia: <?php echo formatDate($user['created_at']); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <!-- Update Profile -->
                        <div class="col-md-6 mb-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="fas fa-edit me-2"></i>Cập nhật thông tin</h5>
                                </div>
                                <div class="card-body">
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
                                        <input type="hidden" name="action" value="update_profile">
                                        
                                        <div class="mb-3">
                                            <label for="username" class="form-label">
                                                <i class="fas fa-user me-2"></i>Tên đăng nhập
                                            </label>
                                            <input type="text" class="form-control" id="username" name="username" 
                                                   value="<?php echo htmlspecialchars($user['username']); ?>" required>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="email" class="form-label">
                                                <i class="fas fa-envelope me-2"></i>Email
                                            </label>
                                            <input type="email" class="form-control" id="email" name="email" 
                                                   value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">
                                                <i class="fas fa-user-tag me-2"></i>Vai trò
                                            </label>
                                            <input type="text" class="form-control" 
                                                   value="<?php echo getRoleDisplayName($user['role']); ?>" readonly>
                                        </div>
                                        
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-2"></i>Cập nhật thông tin
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Change Password -->
                        <div class="col-md-6 mb-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="fas fa-lock me-2"></i>Đổi mật khẩu</h5>
                                </div>
                                <div class="card-body">
                                    <form method="POST" id="changePasswordForm">
                                        <input type="hidden" name="action" value="change_password">
                                        
                                        <div class="mb-3">
                                            <label for="current_password" class="form-label">
                                                <i class="fas fa-lock me-2"></i>Mật khẩu hiện tại
                                            </label>
                                            <input type="password" class="form-control" id="current_password" 
                                                   name="current_password" required>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="new_password" class="form-label">
                                                <i class="fas fa-key me-2"></i>Mật khẩu mới
                                            </label>
                                            <input type="password" class="form-control" id="new_password" 
                                                   name="new_password" required>
                                            <div class="form-text">Mật khẩu phải có ít nhất 6 ký tự</div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="confirm_password" class="form-label">
                                                <i class="fas fa-key me-2"></i>Xác nhận mật khẩu mới
                                            </label>
                                            <input type="password" class="form-control" id="confirm_password" 
                                                   name="confirm_password" required>
                                        </div>
                                        
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-2"></i>Đổi mật khẩu
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Permissions Info -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-shield-alt me-2"></i>Quyền hạn của bạn</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <?php 
                                $permissions = getRolePermissions($user['role']);
                                $permissionNames = [
                                    PERMISSION_VIEW_STUDENTS => 'Xem danh sách sinh viên',
                                    PERMISSION_ADD_STUDENTS => 'Thêm sinh viên',
                                    PERMISSION_EDIT_STUDENTS => 'Sửa thông tin sinh viên',
                                    PERMISSION_DELETE_STUDENTS => 'Xóa sinh viên',
                                    PERMISSION_VIEW_SCORES => 'Xem điểm số',
                                    PERMISSION_ADD_SCORES => 'Thêm điểm số',
                                    PERMISSION_EDIT_SCORES => 'Sửa điểm số',
                                    PERMISSION_DELETE_SCORES => 'Xóa điểm số',
                                    PERMISSION_VIEW_STATISTICS => 'Xem thống kê',
                                    PERMISSION_MANAGE_USERS => 'Quản lý người dùng',
                                    PERMISSION_EXPORT_DATA => 'Xuất dữ liệu'
                                ];
                                
                                foreach ($permissions as $permission): ?>
                                <div class="col-md-6 mb-2">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-check-circle text-success me-2"></i>
                                        <span><?php echo $permissionNames[$permission] ?? $permission; ?></span>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form validation
        document.getElementById('changePasswordForm').addEventListener('submit', function(e) {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (newPassword !== confirmPassword) {
                e.preventDefault();
                alert('Mật khẩu mới và xác nhận không khớp');
                return false;
            }
            
            if (newPassword.length < 6) {
                e.preventDefault();
                alert('Mật khẩu mới phải có ít nhất 6 ký tự');
                return false;
            }
        });
    </script>
</body>
</html>
