<?php
// Bắt đầu session để lưu trữ thông tin người dùng
session_start();
// Nạp file chứa các hàm tiện ích
require_once '../utils.php';
// Nạp file chứa class AuthController
require_once '../authController.php';

// Yêu cầu người dùng phải đăng nhập để truy cập trang này
requireLogin();

// Tạo đối tượng AuthController
$authController = new AuthController();
// Lấy thông tin người dùng hiện tại từ database
$user = $authController->getUserById($_SESSION['user_id']);

// Khởi tạo biến lưu thông báo lỗi
$error = '';
// Khởi tạo biến lưu thông báo thành công
$success = '';

// Xử lý cập nhật thông tin cá nhân
// Kiểm tra xem request có phải là POST và action là 'update_profile' không
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'update_profile') {
    // Lấy và làm sạch dữ liệu từ form
    $username = sanitize($_POST['username']);  // Tên đăng nhập đã được làm sạch
    $email = sanitize($_POST['email']);        // Email đã được làm sạch
    
    // Kiểm tra các trường bắt buộc có được điền đầy đủ không
    if (empty($username) || empty($email)) {
        // Nếu thiếu thông tin, gán thông báo lỗi
        $error = 'Vui lòng điền đầy đủ thông tin';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // Kiểm tra định dạng email có hợp lệ không
        $error = 'Email không hợp lệ';
    } else {
        // Nếu tất cả validation đều pass, tạo mảng dữ liệu để cập nhật
        $data = [
            'username' => $username,
            'email' => $email,
            'role' => $user['role'] // Giữ nguyên role (không cho phép tự thay đổi role)
        ];
        
        // Gọi phương thức updateUser để cập nhật thông tin người dùng
        $result = $authController->updateUser($_SESSION['user_id'], $data);
        // Nếu cập nhật thành công
        if ($result['success']) {
            // Lưu thông báo thành công
            $success = $result['message'];
            // Cập nhật thông tin trong session để đồng bộ
            $_SESSION['username'] = $username;
            $_SESSION['email'] = $email;
            // Lấy lại thông tin người dùng từ database để hiển thị
            $user = $authController->getUserById($_SESSION['user_id']);
        } else {
            // Nếu cập nhật thất bại, lưu thông báo lỗi
            $error = $result['message'];
        }
    }
}

// Xử lý đổi mật khẩu
// Kiểm tra xem request có phải là POST và action là 'change_password' không
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'change_password') {
    // Lấy mật khẩu từ form
    $currentPassword = $_POST['current_password'];  // Mật khẩu hiện tại
    $newPassword = $_POST['new_password'];          // Mật khẩu mới
    $confirmPassword = $_POST['confirm_password'];  // Mật khẩu xác nhận
    
    // Kiểm tra các trường bắt buộc có được điền đầy đủ không
    if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        // Nếu thiếu thông tin, gán thông báo lỗi
        $error = 'Vui lòng điền đầy đủ thông tin';
    } elseif ($newPassword !== $confirmPassword) {
        // Kiểm tra mật khẩu xác nhận có khớp với mật khẩu mới không
        $error = 'Mật khẩu mới và xác nhận không khớp';
    } elseif (strlen($newPassword) < 6) {
        // Kiểm tra độ dài mật khẩu mới (tối thiểu 6 ký tự)
        $error = 'Mật khẩu mới phải có ít nhất 6 ký tự';
    } else {
        // Kiểm tra mật khẩu hiện tại có đúng không
        // Sử dụng phương thức login để xác thực mật khẩu hiện tại
        $loginResult = $authController->login($user['username'], $currentPassword);
        // Nếu mật khẩu hiện tại đúng
        if ($loginResult['success']) {
            // Gọi phương thức changePassword để đổi mật khẩu
            $result = $authController->changePassword($_SESSION['user_id'], $newPassword);
            // Nếu đổi mật khẩu thành công
            if ($result['success']) {
                // Lưu thông báo thành công
                $success = $result['message'];
            } else {
                // Nếu đổi mật khẩu thất bại, lưu thông báo lỗi
                $error = $result['message'];
            }
        } else {
            // Nếu mật khẩu hiện tại không đúng, gán thông báo lỗi
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
        .sidebar, .offcanvas-sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .sidebar .nav-link, .offcanvas-sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 12px 20px;
            border-radius: 8px;
            margin: 2px 0;
            transition: all 0.3s;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active,
        .offcanvas-sidebar .nav-link:hover, .offcanvas-sidebar .nav-link.active {
            color: white;
            background: rgba(255,255,255,0.2);
            transform: translateX(5px);
        }
        .menu-toggle {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            padding: 8px 12px;
            border-radius: 5px;
            font-size: 1.2rem;
        }
        .menu-toggle:hover {
            background: linear-gradient(135deg, #5568d3 0%, #653a8f 100%);
            color: white;
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
        /* Fix click offcanvas mobile */
        .offcanvas-sidebar { z-index: 1050; }
        .offcanvas-backdrop { z-index: 1040; }
        .offcanvas-sidebar .nav-link {
            pointer-events: auto !important;
            touch-action: manipulation;
            -webkit-tap-highlight-color: rgba(255,255,255,0.3);
            position: relative;
            z-index: 1;
        }
        .offcanvas-sidebar { z-index: 1050; }
        .offcanvas-backdrop { z-index: 1040; }
        @media (max-width: 767.98px) {
            .offcanvas-sidebar .nav-link {
                min-height: 44px;
                display: flex;
                align-items: center;
            }
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar Desktop (ẩn trên mobile) -->
            <div class="col-md-3 col-lg-2 sidebar p-0 d-none d-md-block">
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
                    <a class="nav-link" href="../index.php">
                        <i class="fas fa-home me-2"></i>Trang chủ
                    </a>
                    <a class="nav-link" href="../students/list.php">
                        <i class="fas fa-users me-2"></i>Quản lý sinh viên
                    </a>
                    <a class="nav-link" href="../subjects/list.php">
                        <i class="fas fa-book me-2"></i>Quản lý môn học
                    </a>
                    <a class="nav-link" href="../enrollments/list.php">
                        <i class="fas fa-clipboard-list me-2"></i>Đăng ký môn học
                    </a>
                    <a class="nav-link" href="../scores/list.php">
                        <i class="fas fa-chart-line me-2"></i>Quản lý điểm
                    </a>
                    <a class="nav-link" href="../charts/statistics.php">
                        <i class="fas fa-chart-bar me-2"></i>Thống kê
                    </a>
                    
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
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
                        <div class="d-flex align-items-center gap-3">
                            <button class="btn menu-toggle d-md-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebar" aria-controls="mobileSidebar">
                                <i class="fas fa-bars"></i>
                            </button>
                            <h2 class="mb-0"><i class="fas fa-user me-2"></i>Thông tin cá nhân</h2>
                        </div>
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
                                // Lấy danh sách quyền hạn của vai trò hiện tại
                                $permissions = getRolePermissions($user['role']);
                                // Mảng chuyển đổi tên quyền từ constant sang tiếng Việt
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
                                
                                // Duyệt qua từng quyền và hiển thị
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

    <!-- Offcanvas Sidebar cho Mobile -->
    <div class="offcanvas offcanvas-start offcanvas-sidebar" tabindex="-1" id="mobileSidebar" aria-labelledby="mobileSidebarLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title text-white" id="mobileSidebarLabel">
                <i class="fas fa-graduation-cap me-2"></i>
                Student Management
            </h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body p-0">
            <div class="p-3">
                <div class="text-white-50 mb-3">
                    <i class="fas fa-user me-2"></i>
                    <?php echo htmlspecialchars($_SESSION['username']); ?>
                    <span class="badge <?php echo getRoleBadgeClass($_SESSION['role']); ?> ms-2">
                        <?php echo getRoleDisplayName($_SESSION['role']); ?>
                    </span>
                </div>
            </div>
            
            <nav class="nav flex-column px-3">
                <a class="nav-link" href="../index.php" data-bs-dismiss="offcanvas">
                    <i class="fas fa-home me-2"></i>Trang chủ
                </a>
                <a class="nav-link" href="../students/list.php" data-bs-dismiss="offcanvas">
                    <i class="fas fa-users me-2"></i>Quản lý sinh viên
                </a>
                <a class="nav-link" href="../subjects/list.php" data-bs-dismiss="offcanvas">
                    <i class="fas fa-book me-2"></i>Quản lý môn học
                </a>
                <a class="nav-link" href="../enrollments/list.php" data-bs-dismiss="offcanvas">
                    <i class="fas fa-clipboard-list me-2"></i>Đăng ký môn học
                </a>
                <a class="nav-link" href="../scores/list.php" data-bs-dismiss="offcanvas">
                    <i class="fas fa-chart-line me-2"></i>Quản lý điểm
                </a>
                <a class="nav-link" href="../charts/statistics.php" data-bs-dismiss="offcanvas">
                    <i class="fas fa-chart-bar me-2"></i>Thống kê
                </a>
                
                <?php if (canAccess(PERMISSION_MANAGE_USERS)): ?>
                <a class="nav-link" href="users.php" data-bs-dismiss="offcanvas">
                    <i class="fas fa-user-cog me-2"></i>Quản lý người dùng
                </a>
                <?php endif; ?>
                
                <a class="nav-link active" href="profile.php" data-bs-dismiss="offcanvas">
                    <i class="fas fa-user me-2"></i>Thông tin cá nhân
                </a>
                
                <a class="nav-link" href="logout.php" data-bs-dismiss="offcanvas">
                    <i class="fas fa-sign-out-alt me-2"></i>Đăng xuất
                </a>
            </nav>
        </div>
    </div>

    <!-- Nạp Bootstrap JS từ CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Đóng offcanvas trước khi điều hướng (mobile)
        document.addEventListener('DOMContentLoaded', () => {
            const offcanvasEl = document.getElementById('mobileSidebar');
            if (!offcanvasEl) return;

            const links = offcanvasEl.querySelectorAll('.nav-link[href]');
            links.forEach(link => {
                link.addEventListener('click', (event) => {
                    const target = link.getAttribute('href');
                    if (!target) return;
                    event.preventDefault();

                    const bsOffcanvas = bootstrap.Offcanvas.getOrCreateInstance(offcanvasEl);
                    bsOffcanvas.hide();

                    setTimeout(() => { window.location.href = target; }, 150);
                });
            });
        });

        // Validation form đổi mật khẩu trước khi submit
        // Kiểm tra dữ liệu ở phía client để tránh submit form không hợp lệ
        document.getElementById('changePasswordForm').addEventListener('submit', function(e) {
            // Lấy giá trị các trường mật khẩu
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            // Kiểm tra mật khẩu xác nhận có khớp với mật khẩu mới không
            if (newPassword !== confirmPassword) {
                // Ngăn form submit
                e.preventDefault();
                // Hiển thị cảnh báo
                alert('Mật khẩu mới và xác nhận không khớp');
                return false;
            }
            
            // Kiểm tra độ dài mật khẩu mới
            if (newPassword.length < 6) {
                // Ngăn form submit
                e.preventDefault();
                // Hiển thị cảnh báo
                alert('Mật khẩu mới phải có ít nhất 6 ký tự');
                return false;
            }
        });
    </script>
</body>
</html>
