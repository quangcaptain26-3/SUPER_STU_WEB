<?php
// Bắt đầu session để lưu trữ thông tin người dùng
session_start();
// Nạp file chứa các hàm tiện ích chung (như isLoggedIn, getRoleDisplayName, getRoleBadgeClass)
require_once '../utils.php';
// Nạp file chứa lớp PermissionMiddleware để xử lý phân quyền
require_once '../middleware.php';

// Yêu cầu người dùng phải đăng nhập để có thể truy cập trang này
requireLogin();

// Lấy thông tin chi tiết về vai trò và quyền hạn của người dùng hiện tại thông qua Middleware
// Mảng $currentUser sẽ chứa: 'role', 'display_name', 'badge_class', 'permissions'
$currentUser = PermissionMiddleware::getCurrentUserRole();

// Định nghĩa danh sách tất cả các vai trò có trong hệ thống
$allRoles = ['student', 'teacher', 'admin', 'superadmin'];

// Ánh xạ các hằng số quyền hạn sang tên hiển thị bằng tiếng Việt để dễ đọc và hiểu trên giao diện
$allPermissions = [
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
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hệ thống phân quyền - Hệ thống quản lý sinh viên</title>
    <!-- Nạp Bootstrap CSS từ CDN để tạo giao diện responsive và hiện đại -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Nạp Font Awesome từ CDN để sử dụng các biểu tượng (icons) -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* CSS cho Sidebar (thanh điều hướng bên trái) */
        .sidebar {
            min-height: 100vh; /* Đảm bảo sidebar có chiều cao tối thiểu bằng chiều cao của viewport */
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); /* Nền gradient màu tím */
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8); /* Màu chữ mặc định cho các liên kết */
            padding: 12px 20px; /* Khoảng đệm bên trong liên kết */
            border-radius: 8px; /* Bo tròn các góc */
            margin: 2px 0; /* Khoảng cách giữa các liên kết */
            transition: all 0.3s; /* Hiệu ứng chuyển động mượt mà khi hover */
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            color: white; /* Màu chữ trắng khi di chuột qua hoặc liên kết đang hoạt động */
            background: rgba(255,255,255,0.2); /* Nền hơi trong suốt khi di chuột qua hoặc liên kết đang hoạt động */
            transform: translateX(5px); /* Dịch chuyển liên kết sang phải 5px khi hover */
        }
        /* CSS cho Main Content (khu vực nội dung chính) */
        .main-content {
            background-color: #f8f9fa; /* Màu nền xám nhạt */
            min-height: 100vh; /* Đảm bảo nội dung chính có chiều cao tối thiểu bằng chiều cao của viewport */
        }
        /* CSS cho các Card (thẻ) */
        .card {
            border: none; /* Bỏ đường viền mặc định */
            border-radius: 15px; /* Bo tròn các góc của card */
            box-shadow: 0 4px 6px rgba(0,0,0,0.1); /* Tạo đổ bóng nhẹ */
        }
        /* CSS tùy chỉnh cho bảng phân quyền */
        .permission-table {
            font-size: 0.9rem; /* Kích thước font nhỏ hơn cho bảng */
        }
        .permission-table th {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); /* Nền gradient cho tiêu đề cột */
            color: white; /* Màu chữ trắng cho tiêu đề */
            border: none; /* Bỏ đường viền mặc định */
            font-weight: 600; /* Chữ đậm hơn */
        }
        .permission-table td {
            vertical-align: middle; /* Căn giữa theo chiều dọc cho nội dung ô */
            border: 1px solid #dee2e6; /* Đường viền nhẹ giữa các ô */
        }
        .permission-check {
            color: #28a745; /* Màu xanh lá cho biểu tượng check (có quyền) */
            font-size: 1.2rem; /* Kích thước lớn hơn cho biểu tượng */
        }
        .permission-cross {
            color: #dc3545; /* Màu đỏ cho biểu tượng cross (không có quyền) */
            font-size: 1.2rem; /* Kích thước lớn hơn cho biểu tượng */
        }
        /* CSS cho badge hiển thị vai trò */
        .role-badge {
            font-size: 0.8rem; /* Kích thước font nhỏ hơn */
            padding: 0.5rem 1rem; /* Khoảng đệm bên trong badge */
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar (Thanh điều hướng bên trái) -->
            <div class="col-md-3 col-lg-2 sidebar p-0">
                <div class="p-3">
                    <h4 class="text-white mb-4">
                        <i class="fas fa-graduation-cap me-2"></i>
                        Student Management
                    </h4>
                    <!-- Hiển thị tên người dùng và vai trò hiện tại -->
                    <div class="text-white-50 mb-3">
                        <i class="fas fa-user me-2"></i>
                        <?php echo htmlspecialchars($_SESSION['username']); ?>
                        <span class="badge <?php echo getRoleBadgeClass($_SESSION['role']); ?> ms-2">
                            <?php echo getRoleDisplayName($_SESSION['role']); ?>
                        </span>
                    </div>
                </div>
                
                <!-- Menu điều hướng chính -->
                <nav class="nav flex-column px-3">
                    <a class="nav-link" href="../index.php">
                        <i class="fas fa-home me-2"></i>Trang chủ
                    </a>
                    <a class="nav-link" href="../students/list.php">
                        <i class="fas fa-users me-2"></i>Quản lý sinh viên
                    </a>
                    <a class="nav-link" href="../scores/list.php">
                        <i class="fas fa-chart-line me-2"></i>Quản lý điểm
                    </a>
                    <a class="nav-link" href="../charts/statistics.php">
                        <i class="fas fa-chart-bar me-2"></i>Thống kê
                    </a>
                    
                    <?php 
                    // Chỉ hiển thị liên kết "Quản lý người dùng" nếu người dùng hiện tại có quyền PERMISSION_MANAGE_USERS
                    if (canAccess(PERMISSION_MANAGE_USERS)): ?>
                    <a class="nav-link" href="users.php">
                        <i class="fas fa-user-cog me-2"></i>Quản lý người dùng
                    </a>
                    <?php endif; ?>
                    
                    <a class="nav-link" href="profile.php">
                        <i class="fas fa-user me-2"></i>Thông tin cá nhân
                    </a>
                    
                    <a class="nav-link active" href="permissions.php">
                        <i class="fas fa-shield-alt me-2"></i>Hệ thống phân quyền
                    </a>
                    
                    <a class="nav-link" href="logout.php">
                        <i class="fas fa-sign-out-alt me-2"></i>Đăng xuất
                    </a>
                </nav>
            </div>
            
            <!-- Main Content (Khu vực nội dung chính) -->
            <div class="col-md-9 col-lg-10 main-content">
                <div class="p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2><i class="fas fa-shield-alt me-2"></i>Hệ thống phân quyền</h2>
                    </div>
                    
                    <!-- Card hiển thị thông tin tài khoản hiện tại -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-user me-2"></i>Thông tin tài khoản hiện tại</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Vai trò:</strong> 
                                        <span class="badge <?php echo $currentUser['badge_class']; ?> role-badge">
                                            <?php echo $currentUser['display_name']; ?>
                                        </span>
                                    </p>
                                    <p><strong>Số quyền hạn:</strong> <?php echo count($currentUser['permissions']); ?></p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Quyền hạn của bạn:</strong></p>
                                    <div class="d-flex flex-wrap">
                                        <?php 
                                        // Hiển thị tất cả các quyền hạn mà người dùng hiện tại có
                                        foreach ($currentUser['permissions'] as $permission): ?>
                                        <span class="badge bg-success me-1 mb-1">
                                            <?php echo $allPermissions[$permission] ?? $permission; // Hiển thị tên tiếng Việt hoặc chuỗi quyền gốc ?>
                                        </span>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Card hiển thị Ma trận phân quyền (bảng so sánh quyền giữa các vai trò) -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-table me-2"></i>Ma trận phân quyền</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-bordered permission-table mb-0">
                                    <thead>
                                        <tr>
                                            <th style="width: 200px;">Quyền hạn</th>
                                            <?php 
                                            // Duyệt qua tất cả các vai trò để tạo cột tiêu đề cho bảng
                                            foreach ($allRoles as $role): ?>
                                            <th class="text-center" style="width: 120px;">
                                                <span class="badge <?php echo getRoleBadgeClass($role); ?> role-badge">
                                                    <?php echo getRoleDisplayName($role); // Hiển thị tên vai trò bằng tiếng Việt ?>
                                                </span>
                                            </th>
                                            <?php endforeach; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        // Duyệt qua từng quyền hạn đã định nghĩa để tạo các dòng trong bảng
                                        foreach ($allPermissions as $permission => $name): ?>
                                        <tr>
                                            <!-- Cột đầu tiên: Hiển thị tên tiếng Việt của quyền hạn -->
                                            <td><strong><?php echo $name; ?></strong></td>
                                            <?php 
                                            // Duyệt qua từng vai trò để kiểm tra xem vai trò đó có quyền hạn hiện tại hay không
                                            foreach ($allRoles as $role): ?>
                                            <td class="text-center">
                                                <?php 
                                                // Lấy danh sách quyền hạn cụ thể cho vai trò đang xét
                                                $rolePermissions = getRolePermissions($role);
                                                // Kiểm tra xem quyền hiện tại có trong danh sách quyền của vai trò đó không
                                                if (in_array($permission, $rolePermissions)): ?>
                                                    <!-- Nếu vai trò CÓ quyền, hiển thị icon check màu xanh -->
                                                    <i class="fas fa-check permission-check"></i>
                                                <?php else: ?>
                                                    <!-- Nếu vai trò KHÔNG CÓ quyền, hiển thị icon X màu đỏ -->
                                                    <i class="fas fa-times permission-cross"></i>
                                                <?php endif; ?>
                                            </td>
                                            <?php endforeach; ?>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Mô tả chi tiết về các vai trò -->
                    <div class="row mt-4">
                        <!-- Card mô tả vai trò Sinh viên -->
                        <div class="col-md-6 mb-3">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">
                                        <i class="fas fa-graduation-cap me-2"></i>Sinh viên
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <ul class="list-unstyled mb-0">
                                        <li><i class="fas fa-check text-success me-2"></i>Xem danh sách sinh viên</li>
                                        <li><i class="fas fa-check text-success me-2"></i>Xem điểm số</li>
                                        <li><i class="fas fa-times text-danger me-2"></i>Không thể thêm/sửa/xóa dữ liệu</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Card mô tả vai trò Giảng viên -->
                        <div class="col-md-6 mb-3">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">
                                        <i class="fas fa-chalkboard-teacher me-2"></i>Giảng viên
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <ul class="list-unstyled mb-0">
                                        <li><i class="fas fa-check text-success me-2"></i>Xem danh sách sinh viên</li>
                                        <li><i class="fas fa-check text-success me-2"></i>Thêm sinh viên</li>
                                        <li><i class="fas fa-check text-success me-2"></i>Sửa thông tin sinh viên</li>
                                        <li><i class="fas fa-check text-success me-2"></i>Quản lý điểm số</li>
                                        <li><i class="fas fa-check text-success me-2"></i>Xem thống kê</li>
                                        <li><i class="fas fa-check text-success me-2"></i>Xuất báo cáo</li>
                                        <li><i class="fas fa-times text-danger me-2"></i>Không thể xóa sinh viên</li>
                                        <li><i class="fas fa-times text-danger me-2"></i>Không thể quản lý người dùng</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Card mô tả vai trò Quản trị viên -->
                        <div class="col-md-6 mb-3">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">
                                        <i class="fas fa-user-shield me-2"></i>Quản trị viên
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <ul class="list-unstyled mb-0">
                                        <li><i class="fas fa-check text-success me-2"></i>Quản lý sinh viên đầy đủ (CRUD)</li>
                                        <li><i class="fas fa-check text-success me-2"></i>Quản lý điểm số đầy đủ (CRUD)</li>
                                        <li><i class="fas fa-check text-success me-2"></i>Xem thống kê</li>
                                        <li><i class="fas fa-check text-success me-2"></i>Xuất dữ liệu</li>
                                        <li><i class="fas fa-times text-danger me-2"></i>Không thể quản lý người dùng</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Card mô tả vai trò Siêu quản trị -->
                        <div class="col-md-6 mb-3">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">
                                        <i class="fas fa-crown me-2"></i>Siêu quản trị
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <ul class="list-unstyled mb-0">
                                        <li><i class="fas fa-check text-success me-2"></i>Tất cả quyền hạn</li>
                                        <li><i class="fas fa-check text-success me-2"></i>Quản lý người dùng</li>
                                        <li><i class="fas fa-check text-success me-2"></i>Quản lý hệ thống</li>
                                        <li><i class="fas fa-check text-success me-2"></i>Toàn quyền truy cập</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Nạp Bootstrap JS từ CDN để bật các tính năng tương tác của Bootstrap (ví dụ: dropdown, modal) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
