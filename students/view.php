<?php
// Bắt đầu session để lưu trữ thông tin người dùng
session_start();
// Nạp file chứa các hàm tiện ích
require_once '../utils.php';
// Nạp file chứa class StudentController
require_once '../studentController.php';
// Nạp file chứa class ScoreController
require_once '../scoreController.php';

// Yêu cầu người dùng phải có quyền xem sinh viên
requirePermission(PERMISSION_VIEW_STUDENTS);

// Tạo đối tượng StudentController
$studentController = new StudentController();
// Tạo đối tượng ScoreController
$scoreController = new ScoreController();
// Lấy ID sinh viên từ tham số GET, mặc định là 0 nếu không có
$studentId = $_GET['id'] ?? 0;
// Lấy thông tin sinh viên theo ID
$student = $studentController->getStudentById($studentId);

// Nếu không tìm thấy sinh viên
if (!$student) {
    // Chuyển hướng về trang danh sách với thông báo lỗi
    header('Location: list.php?error=student_not_found');
    // Dừng thực thi script
    exit();
}

// Kiểm tra quyền xem - người dùng có thể xem sinh viên này không
if (!canViewStudent($studentId)) {
    // Lưu thông báo lỗi vào session
    $_SESSION['error'] = 'Bạn không có quyền xem dữ liệu này';
    // Chuyển hướng về trang danh sách với thông báo lỗi
    header('Location: list.php?error=access_denied');
    // Dừng thực thi script
    exit();
}

// Lấy danh sách điểm của sinh viên
$scores = $scoreController->getStudentScores($studentId);
// Lấy điểm trung bình của sinh viên
$averageScore = $scoreController->getStudentAverageScore($studentId);
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết sinh viên - Hệ thống quản lý sinh viên</title>
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
            object-fit: cover;
            border: 4px solid white;
        }

        .info-item {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
        }

        .info-item i {
            width: 20px;
            margin-right: 10px;
        }

        .score-badge {
            font-size: 0.9rem;
            padding: 0.5rem 1rem;
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
                        <h2><i class="fas fa-user me-2"></i>Chi tiết sinh viên</h2>
                        <div>
                            <a href="edit.php?id=<?php echo $student['id']; ?>" class="btn btn-primary me-2">
                                <i class="fas fa-edit me-2"></i>Sửa thông tin
                            </a>
                            <a href="list.php" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Quay lại
                            </a>
                        </div>
                    </div>

                    <!-- Profile Header -->
                    <div class="profile-header">
                        <div class="row align-items-center">
                            <div class="col-md-3 text-center">
                                <?php if ($student['avatar']): ?>
                                    <img src="../uploads/avatars/<?php echo htmlspecialchars($student['avatar']); ?>"
                                        class="avatar-large" alt="Avatar">
                                <?php else: ?>
                                    <div class="avatar-large bg-white d-flex align-items-center justify-content-center mx-auto">
                                        <i class="fas fa-user fa-3x text-primary"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-9">
                                <h3 class="mb-2"><?php echo htmlspecialchars($student['fullname']); ?></h3>
                                <p class="mb-1">
                                    <i class="fas fa-id-card me-2"></i>
                                    Mã sinh viên: <strong><?php echo htmlspecialchars($student['msv']); ?></strong>
                                </p>
                                <p class="mb-1">
                                    <i class="fas fa-envelope me-2"></i>
                                    Email: <?php echo htmlspecialchars($student['email']); ?>
                                </p>
                                <p class="mb-0">
                                    <i class="fas fa-chart-line me-2"></i>
                                    Điểm trung bình:
                                    <span class="badge bg-light text-dark score-badge">
                                        <?php echo $averageScore ?: 'Chưa có điểm'; ?>
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Personal Information -->
                        <div class="col-md-6 mb-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Thông tin cá nhân</h5>
                                </div>
                                <div class="card-body">
                                    <div class="info-item">
                                        <i class="fas fa-calendar text-primary"></i>
                                        <div>
                                            <strong>Ngày sinh:</strong><br>
                                            <?php echo formatDate($student['dob']); ?>
                                        </div>
                                    </div>

                                    <div class="info-item">
                                        <i class="fas fa-venus-mars text-primary"></i>
                                        <div>
                                            <strong>Giới tính:</strong><br>
                                            <?php
                                            // Mảng chuyển đổi giới tính từ tiếng Anh sang tiếng Việt
                                            $genderText = [
                                                'male' => 'Nam',
                                                'female' => 'Nữ',
                                                'other' => 'Khác'
                                            ];
                                            // Hiển thị giới tính, nếu không tìm thấy thì hiển thị 'N/A'
                                            echo $genderText[$student['gender']] ?? 'N/A';
                                            ?>
                                        </div>
                                    </div>

                                    <div class="info-item">
                                        <i class="fas fa-phone text-primary"></i>
                                        <div>
                                            <strong>Số điện thoại:</strong><br>
                                            <?php echo htmlspecialchars($student['phone'] ?: 'Chưa cập nhật'); ?>
                                        </div>
                                    </div>

                                    <div class="info-item">
                                        <i class="fas fa-map-marker-alt text-primary"></i>
                                        <div>
                                            <strong>Địa chỉ:</strong><br>
                                            <?php echo htmlspecialchars($student['address'] ?: 'Chưa cập nhật'); ?>
                                        </div>
                                    </div>

                                    <div class="info-item">
                                        <i class="fas fa-clock text-primary"></i>
                                        <div>
                                            <strong>Ngày tạo:</strong><br>
                                            <?php echo formatDate($student['created_at']); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Academic Information -->
                        <div class="col-md-6 mb-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="fas fa-graduation-cap me-2"></i>Thông tin học tập</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row text-center mb-3">
                                        <div class="col-6">
                                            <div class="border-end">
                                                <h4 class="text-primary mb-1"><?php echo count($scores); ?></h4>
                                                <small class="text-muted">Môn đã học</small>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <h4 class="text-success mb-1"><?php echo $averageScore ?: '0'; ?></h4>
                                            <small class="text-muted">Điểm TB</small>
                                        </div>
                                    </div>

                                    <?php if (!empty($scores)): ?>
                                        <h6 class="mb-3">Điểm theo học kỳ:</h6>
                                        <?php
                                        // Nhóm điểm theo học kỳ
                                        $scoresBySemester = [];
                                        // Duyệt qua từng điểm
                                        foreach ($scores as $score) {
                                            // Thêm điểm vào mảng theo học kỳ
                                            $scoresBySemester[$score['semester']][] = $score;
                                        }
                                        ?>
                                        <?php foreach ($scoresBySemester as $semester => $semesterScores): ?>
                                            <div class="mb-3">
                                                <h6 class="text-muted"><?php echo htmlspecialchars($semester); ?></h6>
                                                <div class="d-flex flex-wrap gap-1">
                                                    <?php foreach ($semesterScores as $score): ?>
                                                        <span class="badge bg-primary">
                                                            <?php echo htmlspecialchars($score['subject']); ?>: <?php echo $score['score']; ?>
                                                        </span>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="text-center text-muted">
                                            <i class="fas fa-chart-line fa-3x mb-3"></i>
                                            <p>Chưa có điểm số</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Scores Table -->
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Bảng điểm chi tiết</h5>
                            <a href="../scores/add.php?student_id=<?php echo $student['id']; ?>" class="btn btn-sm btn-primary">
                                <i class="fas fa-plus me-1"></i>Thêm điểm
                            </a>
                        </div>
                        <div class="card-body p-0">
                            <?php if (empty($scores)): ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-chart-line fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">Chưa có điểm số nào</p>
                                    <a href="../scores/add.php?student_id=<?php echo $student['id']; ?>" class="btn btn-primary">
                                        <i class="fas fa-plus me-2"></i>Thêm điểm đầu tiên
                                    </a>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead>
                                            <tr>
                                                <th>STT</th>
                                                <th>Môn học</th>
                                                <th>Điểm</th>
                                                <th>Học kỳ</th>
                                                <th>Xếp loại</th>
                                                <th>Thao tác</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($scores as $index => $score): ?>
                                                <tr>
                                                    <td><?php echo $index + 1; ?></td>
                                                    <td><?php echo htmlspecialchars($score['subject']); ?></td>
                                                    <td>
                                                        <span class="badge <?php
                                                                            echo $score['score'] >= 8 ? 'bg-success' : ($score['score'] >= 6 ? 'bg-warning' : 'bg-danger');
                                                                            ?>">
                                                            <?php echo $score['score']; ?>
                                                        </span>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($score['semester']); ?></td>
                                                    <td>
                                                        <?php
                                                        // Xác định xếp loại dựa trên điểm số
                                                        // >= 9: A+, >= 8: A, >= 7: B+, >= 6: B, >= 5: C, < 5: D
                                                        $grade = $score['score'] >= 9 ? 'A+' : ($score['score'] >= 8 ? 'A' : ($score['score'] >= 7 ? 'B+' : ($score['score'] >= 6 ? 'B' : ($score['score'] >= 5 ? 'C' : 'D'))));
                                                        // Hiển thị xếp loại
                                                        echo $grade;
                                                        ?>
                                                    </td>
                                                    <td>
                                                        <a href="../scores/edit.php?id=<?php echo $score['id']; ?>"
                                                            class="btn btn-sm btn-outline-primary" title="Sửa">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <button onclick="deleteScore(<?php echo $score['id']; ?>)"
                                                            class="btn btn-sm btn-outline-danger" title="Xóa">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../assets/js/notifications.js"></script>
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
                    fetch('../scores/delete.php', {
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