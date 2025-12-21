<?php
session_start();
require_once '../utils.php';
require_once '../enrollmentController.php';
require_once '../studentController.php';
require_once '../subjectController.php';

// Kiểm tra quyền - nếu không có quyền thì hiển thị thông báo thay vì redirect
$hasPermission = hasPermission(PERMISSION_ADD_ENROLLMENTS);

$enrollmentController = new EnrollmentController();
$studentController = new StudentController();
$subjectController = new SubjectController();

$students = $hasPermission ? $studentController->getAllStudents('', 1000, 0) : [];
$subjects = $hasPermission ? $subjectController->getAllSubjects('active') : [];

$error = '';
$success = '';
$data = [];

if ($hasPermission && $_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $error = 'Lỗi xác thực. Vui lòng thử lại.';
    } else {
        $data = [
            'student_id' => $_POST['student_id'] ?? null,
            'subject_id' => $_POST['subject_id'] ?? null,
            'semester' => sanitize($_POST['semester'] ?? ''),
            'status' => $_POST['status'] ?? 'enrolled'
        ];

        if (empty($data['student_id']) || empty($data['subject_id']) || empty($data['semester'])) {
            $error = 'Vui lòng điền đầy đủ thông tin';
        } else {
            $result = $enrollmentController->addEnrollment($data);
            if ($result['success']) {
                $success = $result['message'];
                $data = [];
            } else {
                $error = $result['message'];
            }
        }
    }
}
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
        .form-control {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            padding: 10px 15px;
            transition: all 0.3s;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .menu-toggle {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            padding: 8px 12px;
            border-radius: 5px;
            font-size: 1.2rem;
        }
        .offcanvas-sidebar { z-index: 1050; }
        .offcanvas-backdrop { z-index: 1040; }
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
                            <button class="btn menu-toggle d-md-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebar">
                                <i class="fas fa-bars"></i>
                            </button>
                            <h2 class="mb-0"><i class="fas fa-plus me-2"></i>Đăng ký môn học</h2>
                        </div>
                        <a href="list.php" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Quay lại
                        </a>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <?php if ($error): ?>
                                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                            <?php endif; ?>
                            <?php if ($success): ?>
                                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                            <?php endif; ?>

                            <form method="POST">
                                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                
                                <div class="mb-3">
                                    <label for="student_id" class="form-label">Sinh viên *</label>
                                    <select class="form-control" id="student_id" name="student_id" required>
                                        <option value="">Chọn sinh viên</option>
                                        <?php foreach ($students as $student): ?>
                                            <option value="<?php echo $student['id']; ?>"
                                                <?php echo (($data['student_id'] ?? '') == $student['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($student['msv'] . ' - ' . $student['fullname']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="subject_id" class="form-label">Môn học *</label>
                                    <select class="form-control" id="subject_id" name="subject_id" required>
                                        <option value="">Chọn môn học</option>
                                        <?php foreach ($subjects as $subject): ?>
                                            <option value="<?php echo $subject['id']; ?>"
                                                <?php echo (($data['subject_id'] ?? '') == $subject['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars(($subject['code'] ? $subject['code'] . ' - ' : '') . $subject['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="semester" class="form-label">Học kỳ *</label>
                                    <select class="form-control" id="semester" name="semester" required>
                                        <option value="">Chọn học kỳ</option>
                                        <option value="HK1-2024" <?php echo (($data['semester'] ?? '') == 'HK1-2024') ? 'selected' : ''; ?>>HK1-2024</option>
                                        <option value="HK2-2024" <?php echo (($data['semester'] ?? '') == 'HK2-2024') ? 'selected' : ''; ?>>HK2-2024</option>
                                        <option value="HK1-2023" <?php echo (($data['semester'] ?? '') == 'HK1-2023') ? 'selected' : ''; ?>>HK1-2023</option>
                                        <option value="HK2-2023" <?php echo (($data['semester'] ?? '') == 'HK2-2023') ? 'selected' : ''; ?>>HK2-2023</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="status" class="form-label">Trạng thái</label>
                                    <select class="form-control" id="status" name="status">
                                        <option value="enrolled" <?php echo (($data['status'] ?? 'enrolled') == 'enrolled') ? 'selected' : ''; ?>>Đã đăng ký</option>
                                        <option value="completed" <?php echo (($data['status'] ?? '') == 'completed') ? 'selected' : ''; ?>>Hoàn thành</option>
                                    </select>
                                </div>

                                <div class="d-flex justify-content-end gap-2">
                                    <a href="list.php" class="btn btn-outline-secondary">Hủy</a>
                                    <button type="submit" class="btn btn-primary">Đăng ký</button>
                                </div>
                            </form>
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
    </script>
</body>
</html>

