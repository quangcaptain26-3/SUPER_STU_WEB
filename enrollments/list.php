<?php
session_start();
require_once '../utils.php';
require_once '../enrollmentController.php';
require_once '../studentController.php';
require_once '../subjectController.php';

// Kiểm tra quyền - nếu không có quyền thì hiển thị thông báo thay vì redirect
$hasPermission = hasPermission(PERMISSION_VIEW_ENROLLMENTS);

$enrollmentController = new EnrollmentController();
$studentController = new StudentController();
$subjectController = new SubjectController();

$studentId = $_GET['student_id'] ?? null;
$subjectId = $_GET['subject_id'] ?? null;
$semester = $_GET['semester'] ?? '';

$enrollments = $hasPermission ? $enrollmentController->getAllEnrollments($studentId, $subjectId, $semester) : [];
$students = $hasPermission ? $studentController->getAllStudents('', 1000, 0) : [];
$subjects = $hasPermission ? $subjectController->getAllSubjects('active') : [];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký môn học - Hệ thống quản lý sinh viên</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .sidebar, .offcanvas-sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .sidebar .nav-link, .offcanvas-sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 12px 20px;
            border-radius: 8px;
            margin: 2px 0;
            transition: all 0.3s;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active,
        .offcanvas-sidebar .nav-link:hover, .offcanvas-sidebar .nav-link.active {
            color: white;
            background: rgba(255, 255, 255, 0.2);
            transform: translateX(5px);
        }
        .main-content {
            background-color: #f8f9fa;
            min-height: 100vh;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .table thead th {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
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
        .offcanvas-sidebar { z-index: 1050; }
        .offcanvas-backdrop { z-index: 1040; }
        .offcanvas-sidebar .nav-link {
            pointer-events: auto !important;
            touch-action: manipulation;
            -webkit-tap-highlight-color: rgba(255,255,255,0.3);
            position: relative;
            z-index: 1;
        }
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
            <div class="col-md-3 col-lg-2 sidebar p-0 d-none d-md-block">
                <div class="p-3">
                    <h4 class="text-white mb-4">
                        <i class="fas fa-graduation-cap me-2"></i>Student Management
                    </h4>
                    <div class="text-white-50 mb-3">
                        <i class="fas fa-user me-2"></i>
                        <?php echo htmlspecialchars($_SESSION['username']); ?>
                        <span class="badge bg-light text-dark ms-2"><?php echo ucfirst($_SESSION['role']); ?></span>
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
                    <a class="nav-link active" href="list.php">
                        <i class="fas fa-clipboard-list me-2"></i>Đăng ký môn học
                    </a>
                    <a class="nav-link" href="../scores/list.php">
                        <i class="fas fa-chart-line me-2"></i>Quản lý điểm
                    </a>
                    <a class="nav-link" href="../charts/statistics.php">
                        <i class="fas fa-chart-bar me-2"></i>Thống kê
                    </a>
                    <?php if (canAccess(PERMISSION_MANAGE_USERS)): ?>
                    <a class="nav-link" href="../public/users.php">
                        <i class="fas fa-user-cog me-2"></i>Quản lý người dùng
                    </a>
                    <?php endif; ?>
                    <a class="nav-link" href="../public/profile.php">
                        <i class="fas fa-user me-2"></i>Thông tin cá nhân
                    </a>
                    <a class="nav-link" href="../public/logout.php">
                        <i class="fas fa-sign-out-alt me-2"></i>Đăng xuất
                    </a>
                </nav>
            </div>

            <div class="col-md-9 col-lg-10 main-content">
                <div class="p-4">
                    <?php if (!$hasPermission): ?>
                        <!-- Thông báo không có quyền -->
                        <div class="card">
                            <div class="card-body text-center py-5">
                                <i class="fas fa-lock fa-4x text-warning mb-3 d-block"></i>
                                <h4 class="text-muted mb-3">Bạn không có quyền truy cập trang này</h4>
                                <p class="text-muted">Vui lòng liên hệ quản trị viên để được cấp quyền.</p>
                                <a href="../index.php" class="btn btn-primary mt-3">
                                    <i class="fas fa-arrow-left me-2"></i>Quay về trang chủ
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="d-flex align-items-center gap-3">
                            <button class="btn btn-primary d-md-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebar">
                                <i class="fas fa-bars"></i>
                            </button>
                            <h2 class="mb-0"><i class="fas fa-clipboard-list me-2"></i>Đăng ký môn học</h2>
                        </div>
                        <?php if (hasPermission(PERMISSION_ADD_ENROLLMENTS)): ?>
                            <a href="add.php" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>Đăng ký môn học
                            </a>
                        <?php endif; ?>
                    </div>

                    <!-- Filter -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <form method="GET" class="row g-3">
                                <div class="col-md-4">
                                    <label for="student_id" class="form-label">Sinh viên</label>
                                    <select class="form-control" id="student_id" name="student_id">
                                        <option value="">Tất cả sinh viên</option>
                                        <?php foreach ($students as $student): ?>
                                            <option value="<?php echo $student['id']; ?>"
                                                <?php echo ($studentId == $student['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($student['msv'] . ' - ' . $student['fullname']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="semester" class="form-label">Học kỳ</label>
                                    <select class="form-control" id="semester" name="semester">
                                        <option value="">Tất cả học kỳ</option>
                                        <option value="HK1-2024" <?php echo ($semester == 'HK1-2024') ? 'selected' : ''; ?>>HK1-2024</option>
                                        <option value="HK2-2024" <?php echo ($semester == 'HK2-2024') ? 'selected' : ''; ?>>HK2-2024</option>
                                        <option value="HK1-2023" <?php echo ($semester == 'HK1-2023') ? 'selected' : ''; ?>>HK1-2023</option>
                                        <option value="HK2-2023" <?php echo ($semester == 'HK2-2023') ? 'selected' : ''; ?>>HK2-2023</option>
                                    </select>
                                </div>
                                <div class="col-md-4 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary me-2">Lọc</button>
                                    <a href="list.php" class="btn btn-outline-secondary">Làm mới</a>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Table -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-list me-2"></i>Danh sách đăng ký
                                <span class="badge bg-primary ms-2"><?php echo count($enrollments); ?></span>
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>STT</th>
                                            <th>Mã SV</th>
                                            <th>Họ tên</th>
                                            <th>Môn học</th>
                                            <th>Học kỳ</th>
                                            <th>Trạng thái</th>
                                            <th>Thao tác</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($enrollments)): ?>
                                            <tr>
                                                <td colspan="7" class="text-center py-5">
                                                    <i class="fas fa-clipboard-list fa-4x text-muted mb-3 d-block"></i>
                                                    <h5 class="text-muted">Chưa có đăng ký nào</h5>
                                                    <?php if (hasPermission(PERMISSION_ADD_ENROLLMENTS)): ?>
                                                        <a href="add.php" class="btn btn-primary mt-3">
                                                            <i class="fas fa-plus me-2"></i>Đăng ký môn học đầu tiên
                                                        </a>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($enrollments as $index => $enrollment): ?>
                                                <tr>
                                                    <td><?php echo $index + 1; ?></td>
                                                    <td><strong><?php echo htmlspecialchars($enrollment['msv']); ?></strong></td>
                                                    <td><?php echo htmlspecialchars($enrollment['student_name']); ?></td>
                                                    <td>
                                                        <?php 
                                                        $subjectDisplay = ($enrollment['subject_code'] ? $enrollment['subject_code'] . ' - ' : '') . $enrollment['subject_name'];
                                                        echo htmlspecialchars($subjectDisplay);
                                                        ?>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($enrollment['semester']); ?></td>
                                                    <td>
                                                        <span class="badge bg-<?php echo $enrollment['status'] == 'enrolled' ? 'success' : ($enrollment['status'] == 'completed' ? 'info' : 'secondary'); ?>">
                                                            <?php 
                                                            $statusText = ['enrolled' => 'Đã đăng ký', 'completed' => 'Hoàn thành', 'dropped' => 'Hủy'];
                                                            echo $statusText[$enrollment['status']] ?? $enrollment['status'];
                                                            ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <?php if (hasPermission(PERMISSION_DELETE_ENROLLMENTS)): ?>
                                                            <button onclick="deleteEnrollment(<?php echo $enrollment['id']; ?>)" class="btn btn-sm btn-outline-danger" title="Xóa">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Mobile Sidebar -->
    <div class="offcanvas offcanvas-start offcanvas-sidebar" tabindex="-1" id="mobileSidebar">
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
                    <span class="badge bg-light text-dark ms-2"><?php echo ucfirst($_SESSION['role']); ?></span>
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
                <a class="nav-link active" href="list.php" data-bs-dismiss="offcanvas">
                    <i class="fas fa-clipboard-list me-2"></i>Đăng ký môn học
                </a>
                <a class="nav-link" href="../scores/list.php" data-bs-dismiss="offcanvas">
                    <i class="fas fa-chart-line me-2"></i>Quản lý điểm
                </a>
                <a class="nav-link" href="../charts/statistics.php" data-bs-dismiss="offcanvas">
                    <i class="fas fa-chart-bar me-2"></i>Thống kê
                </a>
                
                <?php if (canAccess(PERMISSION_MANAGE_USERS)): ?>
                <a class="nav-link" href="../public/users.php" data-bs-dismiss="offcanvas">
                    <i class="fas fa-user-cog me-2"></i>Quản lý người dùng
                </a>
                <?php endif; ?>
                
                <a class="nav-link" href="../public/profile.php" data-bs-dismiss="offcanvas">
                    <i class="fas fa-user me-2"></i>Thông tin cá nhân
                </a>
                
                <a class="nav-link" href="../public/logout.php" data-bs-dismiss="offcanvas">
                    <i class="fas fa-sign-out-alt me-2"></i>Đăng xuất
                </a>
            </nav>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Đóng offcanvas trước khi điều hướng (mobile)
        document.addEventListener('DOMContentLoaded', () => {
            try {
                const offcanvasEl = document.getElementById('mobileSidebar');
                if (offcanvasEl) {
                    const links = offcanvasEl.querySelectorAll('.nav-link[href]');
                    links.forEach(link => {
                        link.addEventListener('click', (event) => {
                            try {
                                const target = link.getAttribute('href');
                                if (!target || target === '#' || target.startsWith('javascript:')) return;
                                event.preventDefault();
                                event.stopPropagation();
                                const bsOffcanvas = bootstrap.Offcanvas.getOrCreateInstance(offcanvasEl);
                                bsOffcanvas.hide();
                                setTimeout(() => { window.location.href = target; }, 150);
                            } catch (e) {
                                console.error('Error handling link click:', e);
                            }
                        });
                    });
                }
            } catch (e) {
                console.error('Error initializing mobile sidebar:', e);
            }
        });

        function deleteEnrollment(id) {
            Swal.fire({
                title: 'Xác nhận xóa',
                text: 'Bạn có chắc chắn muốn xóa đăng ký này?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Xóa',
                cancelButtonText: 'Hủy'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('delete.php', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                        body: 'id=' + id
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire('Thành công!', data.message, 'success').then(() => location.reload());
                        } else {
                            Swal.fire('Lỗi!', data.message, 'error');
                        }
                    });
                }
            });
        }
    </script>
</body>
</html>

