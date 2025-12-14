<?php
// Bắt đầu session để lưu trữ thông tin người dùng
session_start();
// Nạp file chứa các hàm tiện ích
require_once '../utils.php';
// Nạp file chứa class ScoreController
require_once '../scoreController.php';
// Nạp file chứa class StudentController
require_once '../studentController.php';

// Yêu cầu người dùng phải có quyền xem điểm
requirePermission(PERMISSION_VIEW_SCORES);

// Tạo đối tượng ScoreController
$scoreController = new ScoreController();
// Tạo đối tượng StudentController
$studentController = new StudentController();

// Lấy ID sinh viên từ tham số GET để lọc (nếu có)
$studentId = $_GET['student_id'] ?? null;
// Lấy học kỳ từ tham số GET để lọc (nếu có)
$semester = $_GET['semester'] ?? '';
// Lấy số trang từ tham số GET, đảm bảo >= 1
$page = max(1, intval($_GET['page'] ?? 1));
// Số lượng điểm hiển thị trên mỗi trang
$limit = 10;
// Tính offset để phân trang (số bản ghi bỏ qua)
$offset = ($page - 1) * $limit;

// Lấy danh sách điểm với bộ lọc sinh viên và học kỳ
$scores = $scoreController->getAllScores($studentId, $semester);
// Lấy danh sách tất cả sinh viên để hiển thị trong bộ lọc
$students = $studentController->getAllStudents('', 1000, 0);
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý điểm - Hệ thống quản lý sinh viên</title>
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

        .btn-action {
            padding: 5px 10px;
            margin: 2px;
            border-radius: 5px;
        }

        .score-badge {
            font-size: 0.9rem;
            padding: 0.5rem 1rem;
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
                    <a class="nav-link" href="../index.php">
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

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 main-content">
                <div class="p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2><i class="fas fa-chart-line me-2"></i>Quản lý điểm số</h2>
                        <?php if (hasPermission(PERMISSION_ADD_SCORES)): ?>
                            <a href="add.php" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>Thêm điểm
                            </a>
                        <?php endif; ?>
                    </div>

                    <!-- Filter -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Bộ lọc</h5>
                        </div>
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
                                    <button type="submit" class="btn btn-primary me-2">
                                        <i class="fas fa-search me-1"></i>Lọc
                                    </button>
                                    <a href="list.php" class="btn btn-outline-secondary">
                                        <i class="fas fa-refresh me-1"></i>Làm mới
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Scores Table -->
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="fas fa-list me-2"></i>Danh sách điểm
                                <span class="badge bg-primary ms-2"><?php echo count($scores); ?></span>
                            </h5>
                            <div>
                                <?php if (hasPermission(PERMISSION_EXPORT_DATA)): ?>
                                    <a href="../exports/export_pdf.php?type=scores" class="btn btn-outline-danger btn-sm me-2">
                                        <i class="fas fa-file-pdf me-1"></i>Xuất PDF
                                    </a>
                                    <a href="../exports/export_docx.php?type=scores" class="btn btn-outline-success btn-sm">
                                        <i class="fas fa-file-word me-1"></i>Xuất DOCX
                                    </a>
                                <?php endif; ?>
                            </div>
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
                                            <th>Điểm</th>
                                            <th>Học kỳ</th>
                                            <th>Xếp loại</th>
                                            <th>Thao tác</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($scores)): ?>
                                            <tr>
                                                <td colspan="8" class="text-center py-5">
                                                    <div class="empty-state">
                                                        <i class="fas fa-chart-line fa-4x text-muted mb-3 d-block"></i>
                                                        <h5 class="text-muted mb-2">Chưa có điểm số nào</h5>
                                                        <p class="text-muted mb-3">Hãy thêm điểm cho sinh viên để bắt đầu quản lý!</p>
                                                        <?php if (hasPermission(PERMISSION_ADD_SCORES)): ?>
                                                            <a href="add.php" class="btn btn-primary">
                                                                <i class="fas fa-plus me-2"></i>Thêm điểm đầu tiên
                                                            </a>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($scores as $index => $score): ?>
                                                <tr>
                                                    <td><?php echo $index + 1; ?></td>
                                                    <td>
                                                        <strong><?php echo htmlspecialchars($score['msv']); ?></strong>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($score['fullname']); ?></td>
                                                    <td><?php echo htmlspecialchars($score['subject']); ?></td>
                                                    <td>
                                                        <span class="badge <?php
                                                                            echo $score['score'] >= 8 ? 'bg-success' : ($score['score'] >= 6 ? 'bg-warning' : 'bg-danger');
                                                                            ?> score-badge">
                                                            <?php echo $score['score']; ?>
                                                        </span>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($score['semester']); ?></td>
                                                    <td>
                                                        <?php
                                                        // Xác định xếp loại dựa trên điểm số
                                                        // >= 9: A+, >= 8: A, >= 7: B+, >= 6: B, >= 5: C, < 5: D
                                                        $grade = $score['score'] >= 9 ? 'A+' : ($score['score'] >= 8 ? 'A' : ($score['score'] >= 7 ? 'B+' : ($score['score'] >= 6 ? 'B' : ($score['score'] >= 5 ? 'C' : 'D'))));
                                                        // Xác định màu sắc cho xếp loại
                                                        // >= 8: màu xanh (tốt), >= 6: màu vàng (khá), < 6: màu đỏ (kém)
                                                        $gradeClass = $score['score'] >= 8 ? 'text-success' : ($score['score'] >= 6 ? 'text-warning' : 'text-danger');
                                                        ?>
                                                        <span class="<?php echo $gradeClass; ?> fw-bold"><?php echo $grade; ?></span>
                                                    </td>
                                                    <td>
                                                        <?php if (hasPermission(PERMISSION_EDIT_SCORES)): ?>
                                                            <a href="edit.php?id=<?php echo $score['id']; ?>"
                                                                class="btn btn-sm btn-outline-primary btn-action" title="Sửa">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                        <?php endif; ?>
                                                        <?php if (hasPermission(PERMISSION_DELETE_SCORES)): ?>
                                                            <button onclick="deleteScore(<?php echo $score['id']; ?>)"
                                                                class="btn btn-sm btn-outline-danger btn-action" title="Xóa">
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
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../assets/js/notifications.js"></script>
    <!-- <script src="../assets/js/realtime.js"></script> -->
    <script>
        // Hàm xóa điểm
        function deleteScore(id) {
            // Hiển thị hộp thoại xác nhận xóa
            Swal.fire({
                title: 'Xác nhận xóa điểm',
                text: 'Bạn có chắc chắn muốn xóa điểm này? Hành động này không thể hoàn tác.',
                icon: 'warning',
                showCancelButton: true, // Hiển thị nút hủy
                confirmButtonColor: '#dc3545', // Màu đỏ cho nút xác nhận
                cancelButtonColor: '#6c757d', // Màu xám cho nút hủy
                confirmButtonText: 'Xóa',
                cancelButtonText: 'Hủy'
            }).then((result) => {
                // Nếu người dùng xác nhận xóa
                if (result.isConfirmed) {
                    // Hiển thị loading
                    Swal.fire({
                        title: 'Đang xử lý...',
                        text: 'Vui lòng chờ trong giây lát',
                        icon: 'info',
                        allowOutsideClick: false, // Không cho click bên ngoài
                        allowEscapeKey: false, // Không cho phím ESC
                        showConfirmButton: false, // Không hiển thị nút xác nhận
                        didOpen: () => {
                            Swal.showLoading(); // Hiển thị spinner loading
                        }
                    });

                    // Gửi request xóa đến server
                    fetch('delete.php', {
                            method: 'POST', // Phương thức POST
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded', // Header
                            },
                            body: 'id=' + id // Body chứa ID điểm cần xóa
                        })
                        .then(response => response.json()) // Chuyển response sang JSON
                        .then(data => {
                            Swal.close(); // Đóng loading
                            // Nếu xóa thành công
                            if (data.success) {
                                Swal.fire({
                                    title: 'Thành công!',
                                    text: 'Điểm đã được xóa thành công',
                                    icon: 'success',
                                    confirmButtonText: 'OK'
                                }).then(() => {
                                    location.reload(); // Tải lại trang
                                });
                            } else {
                                // Nếu xóa thất bại, hiển thị lỗi
                                Swal.fire({
                                    title: 'Lỗi!',
                                    text: data.message,
                                    icon: 'error',
                                    confirmButtonText: 'OK'
                                });
                            }
                        })
                        .catch(error => {
                            Swal.close(); // Đóng loading
                            // Nếu có lỗi xảy ra
                            Swal.fire({
                                title: 'Lỗi!',
                                text: 'Có lỗi xảy ra: ' + error,
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        });
                }
            });
        }
    </script>
</body>

</html>