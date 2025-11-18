<?php
session_start();
require_once '../utils.php';
require_once '../scoreController.php';
require_once '../studentController.php';

requirePermission(PERMISSION_ADD_SCORES);

$scoreController = new ScoreController();
$studentController = new StudentController();
$students = $studentController->getAllStudents('', 1000, 0);

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Kiểm tra CSRF token
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $error = 'Lỗi xác thực. Vui lòng thử lại.';
    } else {
        $data = [
            'student_id' => $_POST['student_id'],
            'subject' => sanitize($_POST['subject']),
            'score' => floatval($_POST['score']),
            'semester' => sanitize($_POST['semester'])
        ];

        if (empty($data['student_id']) || empty($data['subject']) || empty($data['semester'])) {
            $error = 'Vui lòng điền đầy đủ thông tin';
        } elseif ($data['score'] < 0 || $data['score'] > 10) {
            $error = 'Điểm phải từ 0 đến 10';
        } else {
            $result = $scoreController->addScore($data);

            if ($result['success']) {
                $success = $result['message'];
                // Clear form data
                $data = array_fill_keys(array_keys($data), '');
            } else {
                $error = $result['message'];
            }
        }
    }
}

// Pre-select student if coming from student view
$selectedStudentId = $_GET['student_id'] ?? '';
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm điểm - Hệ thống quản lý sinh viên</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 12px 20px;
            border-radius: 8px;
            margin: 2px 0;
            transition: all 0.3s;
        }

        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
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

        .score-preview {
            font-size: 2rem;
            font-weight: bold;
            text-align: center;
            padding: 1rem;
            border-radius: 10px;
            margin-top: 1rem;
        }

        .score-excellent {
            background: #d4edda;
            color: #155724;
        }

        .score-good {
            background: #fff3cd;
            color: #856404;
        }

        .score-poor {
            background: #f8d7da;
            color: #721c24;
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
                    <a class="nav-link" href="../students/list.php">
                        <i class="fas fa-users me-2"></i>Quản lý sinh viên
                    </a>
                    <a class="nav-link active" href="list.php">
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
                        <h2><i class="fas fa-plus me-2"></i>Thêm điểm mới</h2>
                        <a href="list.php" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Quay lại
                        </a>
                    </div>

                    <div class="row">
                        <div class="col-lg-8">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Thông tin điểm</h5>
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

                                    <form method="POST" id="scoreForm">
                                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                        <div class="mb-3">
                                            <label for="student_id" class="form-label">
                                                <i class="fas fa-user me-2"></i>Sinh viên *
                                            </label>
                                            <select class="form-control" id="student_id" name="student_id" required>
                                                <option value="">Chọn sinh viên</option>
                                                <?php foreach ($students as $student): ?>
                                                    <option value="<?php echo $student['id']; ?>"
                                                        <?php echo ($selectedStudentId == $student['id'] || ($data['student_id'] ?? '') == $student['id']) ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($student['msv'] . ' - ' . $student['fullname']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="subject" class="form-label">
                                                    <i class="fas fa-book me-2"></i>Môn học *
                                                </label>
                                                <input type="text" class="form-control" id="subject" name="subject"
                                                    value="<?php echo htmlspecialchars($data['subject'] ?? ''); ?>"
                                                    placeholder="Ví dụ: Toán cao cấp, Lập trình web..." required>
                                            </div>

                                            <div class="col-md-6 mb-3">
                                                <label for="score" class="form-label">
                                                    <i class="fas fa-star me-2"></i>Điểm số *
                                                </label>
                                                <input type="number" class="form-control" id="score" name="score"
                                                    value="<?php echo $data['score'] ?? ''; ?>"
                                                    min="0" max="10" step="0.1" required>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="semester" class="form-label">
                                                <i class="fas fa-calendar me-2"></i>Học kỳ *
                                            </label>
                                            <select class="form-control" id="semester" name="semester" required>
                                                <option value="">Chọn học kỳ</option>
                                                <option value="HK1-2024" <?php echo (($data['semester'] ?? '') == 'HK1-2024') ? 'selected' : ''; ?>>HK1-2024</option>
                                                <option value="HK2-2024" <?php echo (($data['semester'] ?? '') == 'HK2-2024') ? 'selected' : ''; ?>>HK2-2024</option>
                                                <option value="HK1-2023" <?php echo (($data['semester'] ?? '') == 'HK1-2023') ? 'selected' : ''; ?>>HK1-2023</option>
                                                <option value="HK2-2023" <?php echo (($data['semester'] ?? '') == 'HK2-2023') ? 'selected' : ''; ?>>HK2-2023</option>
                                            </select>
                                        </div>

                                        <div class="d-flex justify-content-end gap-2">
                                            <a href="list.php" class="btn btn-outline-secondary">
                                                <i class="fas fa-times me-2"></i>Hủy
                                            </a>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-save me-2"></i>Lưu điểm
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Xem trước điểm</h5>
                                </div>
                                <div class="card-body text-center">
                                    <div id="scorePreview" class="score-preview score-poor">
                                        Chưa có điểm
                                    </div>
                                    <div id="gradePreview" class="mt-2">
                                        <small class="text-muted">Nhập điểm để xem xếp loại</small>
                                    </div>
                                </div>
                            </div>

                            <div class="card mt-3">
                                <div class="card-header">
                                    <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Thang điểm</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row text-center">
                                        <div class="col-6 mb-2">
                                            <div class="score-preview score-excellent" style="font-size: 1rem; padding: 0.5rem;">
                                                9.0 - 10.0
                                            </div>
                                            <small class="text-muted">A+</small>
                                        </div>
                                        <div class="col-6 mb-2">
                                            <div class="score-preview score-good" style="font-size: 1rem; padding: 0.5rem;">
                                                8.0 - 8.9
                                            </div>
                                            <small class="text-muted">A</small>
                                        </div>
                                        <div class="col-6 mb-2">
                                            <div class="score-preview score-good" style="font-size: 1rem; padding: 0.5rem;">
                                                7.0 - 7.9
                                            </div>
                                            <small class="text-muted">B+</small>
                                        </div>
                                        <div class="col-6 mb-2">
                                            <div class="score-preview score-good" style="font-size: 1rem; padding: 0.5rem;">
                                                6.0 - 6.9
                                            </div>
                                            <small class="text-muted">B</small>
                                        </div>
                                        <div class="col-6 mb-2">
                                            <div class="score-preview score-poor" style="font-size: 1rem; padding: 0.5rem;">
                                                5.0 - 5.9
                                            </div>
                                            <small class="text-muted">C</small>
                                        </div>
                                        <div class="col-6 mb-2">
                                            <div class="score-preview score-poor" style="font-size: 1rem; padding: 0.5rem;">
                                                0.0 - 4.9
                                            </div>
                                            <small class="text-muted">D</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function updateScorePreview() {
            const score = parseFloat(document.getElementById('score').value) || 0;
            const preview = document.getElementById('scorePreview');
            const gradePreview = document.getElementById('gradePreview');

            if (score === 0) {
                preview.textContent = 'Chưa có điểm';
                preview.className = 'score-preview score-poor';
                gradePreview.innerHTML = '<small class="text-muted">Nhập điểm để xem xếp loại</small>';
                return;
            }

            preview.textContent = score.toFixed(1);

            let grade, className;
            if (score >= 9) {
                grade = 'A+';
                className = 'score-excellent';
            } else if (score >= 8) {
                grade = 'A';
                className = 'score-good';
            } else if (score >= 7) {
                grade = 'B+';
                className = 'score-good';
            } else if (score >= 6) {
                grade = 'B';
                className = 'score-good';
            } else if (score >= 5) {
                grade = 'C';
                className = 'score-poor';
            } else {
                grade = 'D';
                className = 'score-poor';
            }

            preview.className = 'score-preview ' + className;
            gradePreview.innerHTML = '<strong>Xếp loại: ' + grade + '</strong>';
        }

        // Update preview when score changes
        document.getElementById('score').addEventListener('input', updateScorePreview);

        // Form validation
        document.getElementById('scoreForm').addEventListener('submit', function(e) {
            const studentId = document.getElementById('student_id').value;
            const subject = document.getElementById('subject').value.trim();
            const score = parseFloat(document.getElementById('score').value);
            const semester = document.getElementById('semester').value;

            if (!studentId || !subject || !semester) {
                e.preventDefault();
                alert('Vui lòng điền đầy đủ thông tin');
                return false;
            }

            if (isNaN(score) || score < 0 || score > 10) {
                e.preventDefault();
                alert('Điểm phải từ 0 đến 10');
                return false;
            }
        });

        // Auto focus on first field
        document.getElementById('student_id').focus();

        // Initial preview update
        updateScorePreview();
    </script>
</body>

</html>