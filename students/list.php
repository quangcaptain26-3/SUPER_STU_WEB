<?php
session_start();
require_once '../utils.php';
require_once '../studentController.php';

requirePermission(PERMISSION_VIEW_STUDENTS);

$studentController = new StudentController();
$search = $_GET['search'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 10;
$offset = ($page - 1) * $limit;

$students = $studentController->getAllStudents($search, $limit, $offset);
$totalStudents = $studentController->getTotalStudents($search);
$totalPages = ceil($totalStudents / $limit);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý sinh viên - Hệ thống quản lý sinh viên</title>
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
        .table {
            border-radius: 10px;
            overflow: hidden;
        }
        .table thead th {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            font-weight: 600;
        }
        .avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }
        .btn-action {
            padding: 5px 10px;
            margin: 2px;
            border-radius: 5px;
        }
        .search-box {
            border-radius: 25px;
            border: 2px solid #e9ecef;
            padding: 10px 20px;
            transition: all 0.3s;
        }
        .search-box:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
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
                        <span class="badge bg-light text-dark ms-2"><?php echo ucfirst($_SESSION['role']); ?></span>
                    </div>
                </div>
                
                <nav class="nav flex-column px-3">
                    <a class="nav-link" href="../public/index.php">
                        <i class="fas fa-home me-2"></i>Trang chủ
                    </a>
                    <a class="nav-link active" href="list.php">
                        <i class="fas fa-users me-2"></i>Quản lý sinh viên
                    </a>
                    <a class="nav-link" href="../scores/list.php">
                        <i class="fas fa-chart-line me-2"></i>Quản lý điểm
                    </a>
                    <a class="nav-link" href="../charts/statistics.php">
                        <i class="fas fa-chart-bar me-2"></i>Thống kê
                    </a>
                    <a class="nav-link" href="../public/logout.php">
                        <i class="fas fa-sign-out-alt me-2"></i>Đăng xuất
                    </a>
                </nav>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 main-content">
                <div class="p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2><i class="fas fa-users me-2"></i>Quản lý sinh viên</h2>
                        <a href="add.php" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Thêm sinh viên
                        </a>
                    </div>
                    
                    <!-- Search and Filter -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <form method="GET" class="row g-3">
                                <div class="col-md-8">
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fas fa-search"></i>
                                        </span>
                                        <input type="text" class="form-control search-box" name="search" 
                                               value="<?php echo htmlspecialchars($search); ?>" 
                                               placeholder="Tìm kiếm theo tên, mã SV, email...">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <button type="submit" class="btn btn-outline-primary me-2">
                                        <i class="fas fa-search me-1"></i>Tìm kiếm
                                    </button>
                                    <a href="list.php" class="btn btn-outline-secondary">
                                        <i class="fas fa-refresh me-1"></i>Làm mới
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Students Table -->
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="fas fa-list me-2"></i>Danh sách sinh viên
                                <span class="badge bg-primary ms-2"><?php echo $totalStudents; ?></span>
                            </h5>
                            <div>
                                <a href="../exports/export_pdf.php?type=students" class="btn btn-outline-danger btn-sm me-2">
                                    <i class="fas fa-file-pdf me-1"></i>Xuất PDF
                                </a>
                                <a href="../exports/export_docx.php?type=students" class="btn btn-outline-success btn-sm">
                                    <i class="fas fa-file-word me-1"></i>Xuất DOCX
                                </a>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>STT</th>
                                            <th>Ảnh</th>
                                            <th>Mã SV</th>
                                            <th>Họ tên</th>
                                            <th>Ngày sinh</th>
                                            <th>Giới tính</th>
                                            <th>Email</th>
                                            <th>SĐT</th>
                                            <th>Thao tác</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($students)): ?>
                                        <tr>
                                            <td colspan="9" class="text-center py-4">
                                                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                                <p class="text-muted">Không có sinh viên nào</p>
                                            </td>
                                        </tr>
                                        <?php else: ?>
                                        <?php foreach ($students as $index => $student): ?>
                                        <tr>
                                            <td><?php echo $offset + $index + 1; ?></td>
                                            <td>
                                                <?php if ($student['avatar']): ?>
                                                <img src="../uploads/avatars/<?php echo htmlspecialchars($student['avatar']); ?>" 
                                                     class="avatar" alt="Avatar">
                                                <?php else: ?>
                                                <div class="avatar bg-secondary d-flex align-items-center justify-content-center">
                                                    <i class="fas fa-user text-white"></i>
                                                </div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($student['msv']); ?></strong>
                                            </td>
                                            <td><?php echo htmlspecialchars($student['fullname']); ?></td>
                                            <td><?php echo formatDate($student['dob']); ?></td>
                                            <td>
                                                <?php
                                                $genderText = [
                                                    'male' => 'Nam',
                                                    'female' => 'Nữ',
                                                    'other' => 'Khác'
                                                ];
                                                echo $genderText[$student['gender']] ?? 'N/A';
                                                ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($student['email']); ?></td>
                                            <td><?php echo htmlspecialchars($student['phone']); ?></td>
                                            <td>
                                                <a href="edit.php?id=<?php echo $student['id']; ?>" 
                                                   class="btn btn-sm btn-outline-primary btn-action" title="Sửa">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="view.php?id=<?php echo $student['id']; ?>" 
                                                   class="btn btn-sm btn-outline-info btn-action" title="Xem">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <button onclick="deleteStudent(<?php echo $student['id']; ?>)" 
                                                        class="btn btn-sm btn-outline-danger btn-action" title="Xóa">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        <!-- Pagination -->
                        <?php if ($totalPages > 1): ?>
                        <div class="card-footer">
                            <nav aria-label="Page navigation">
                                <ul class="pagination justify-content-center mb-0">
                                    <?php if ($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>">
                                            <i class="fas fa-chevron-left"></i>
                                        </a>
                                    </li>
                                    <?php endif; ?>
                                    
                                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    </li>
                                    <?php endfor; ?>
                                    
                                    <?php if ($page < $totalPages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>">
                                            <i class="fas fa-chevron-right"></i>
                                        </a>
                                    </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../assets/js/notifications.js"></script>
    <script src="../assets/js/realtime.js"></script>
    <script>
        function deleteStudent(id) {
            confirmDelete(
                'Xác nhận xóa sinh viên',
                'Bạn có chắc chắn muốn xóa sinh viên này? Hành động này không thể hoàn tác.',
                function() {
                fetch('delete.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'id=' + id
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        notification.success('Xóa sinh viên thành công');
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        notification.error('Lỗi: ' + data.message);
                    }
                })
                .catch(error => {
                    notification.error('Lỗi: ' + error);
                });
                }
        }
    </script>
</body>
</html>
