<?php
session_start();
require_once '../utils.php';
require_once '../middleware.php';

requireLogin();

$currentUser = PermissionMiddleware::getCurrentUserRole();
$allRoles = ['student', 'teacher', 'admin', 'superadmin'];
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
        .permission-table {
            font-size: 0.9rem;
        }
        .permission-table th {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            font-weight: 600;
        }
        .permission-table td {
            vertical-align: middle;
            border: 1px solid #dee2e6;
        }
        .permission-check {
            color: #28a745;
            font-size: 1.2rem;
        }
        .permission-cross {
            color: #dc3545;
            font-size: 1.2rem;
        }
        .role-badge {
            font-size: 0.8rem;
            padding: 0.5rem 1rem;
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
            
            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 main-content">
                <div class="p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2><i class="fas fa-shield-alt me-2"></i>Hệ thống phân quyền</h2>
                    </div>
                    
                    <!-- Current User Info -->
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
                                        <?php foreach ($currentUser['permissions'] as $permission): ?>
                                        <span class="badge bg-success me-1 mb-1">
                                            <?php echo $allPermissions[$permission] ?? $permission; ?>
                                        </span>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Permission Matrix -->
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
                                            <?php foreach ($allRoles as $role): ?>
                                            <th class="text-center" style="width: 120px;">
                                                <span class="badge <?php echo getRoleBadgeClass($role); ?> role-badge">
                                                    <?php echo getRoleDisplayName($role); ?>
                                                </span>
                                            </th>
                                            <?php endforeach; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($allPermissions as $permission => $name): ?>
                                        <tr>
                                            <td><strong><?php echo $name; ?></strong></td>
                                            <?php foreach ($allRoles as $role): ?>
                                            <td class="text-center">
                                                <?php 
                                                $rolePermissions = getRolePermissions($role);
                                                if (in_array($permission, $rolePermissions)): ?>
                                                    <i class="fas fa-check permission-check"></i>
                                                <?php else: ?>
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
                    
                    <!-- Role Descriptions -->
                    <div class="row mt-4">
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
                        
                        <div class="col-md-6 mb-3">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">
                                        <i class="fas fa-user-shield me-2"></i>Quản trị viên
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <ul class="list-unstyled mb-0">
                                        <li><i class="fas fa-check text-success me-2"></i>Quản lý sinh viên đầy đủ</li>
                                        <li><i class="fas fa-check text-success me-2"></i>Quản lý điểm số đầy đủ</li>
                                        <li><i class="fas fa-check text-success me-2"></i>Xem thống kê</li>
                                        <li><i class="fas fa-check text-success me-2"></i>Xuất dữ liệu</li>
                                        <li><i class="fas fa-times text-danger me-2"></i>Không thể quản lý người dùng</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
