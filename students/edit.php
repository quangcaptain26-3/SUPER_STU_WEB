<?php
// Bắt đầu session để lưu trữ thông tin người dùng
session_start();
// Nạp file chứa các hàm tiện ích
require_once '../utils.php';
// Nạp file chứa class StudentController
require_once '../studentController.php';

// Yêu cầu người dùng phải có quyền chỉnh sửa sinh viên
requirePermission(PERMISSION_EDIT_STUDENTS);

// Tạo đối tượng StudentController
$studentController = new StudentController();
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

// Khởi tạo biến lưu thông báo lỗi
$error = '';
// Khởi tạo biến lưu thông báo thành công
$success = '';

// Kiểm tra xem request có phải là POST không (khi form được submit)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Kiểm tra CSRF token để bảo vệ khỏi tấn công CSRF
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        // Nếu token không hợp lệ, gán thông báo lỗi
        $error = 'Lỗi xác thực. Vui lòng thử lại.';
    } else {
        // Nếu token hợp lệ, bắt đầu xử lý dữ liệu form
        // Tạo mảng chứa dữ liệu sinh viên đã được làm sạch
        $data = [
            'msv' => sanitize($_POST['msv']),              // Mã sinh viên đã được làm sạch
            'fullname' => sanitize($_POST['fullname']),     // Họ và tên đã được làm sạch
            'dob' => $_POST['dob'],                         // Ngày sinh
            'gender' => $_POST['gender'],                    // Giới tính
            'address' => sanitize($_POST['address']),       // Địa chỉ đã được làm sạch
            'phone' => sanitize($_POST['phone']),           // Số điện thoại đã được làm sạch
            'email' => sanitize($_POST['email']),           // Email đã được làm sạch
            'avatar' => $student['avatar']                   // Giữ nguyên avatar hiện tại
        ];

        // Xử lý upload file ảnh đại diện mới
        // Kiểm tra xem có file được upload và không có lỗi không
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == 0) {
            // Gọi hàm uploadFile để xử lý upload
            $uploadResult = uploadFile($_FILES['avatar']);
            // Nếu upload thành công
            if ($uploadResult['success']) {
                // Xóa ảnh đại diện cũ nếu có
                if ($student['avatar']) {
                    deleteFile('uploads/avatars/' . $student['avatar']);
                }
                // Lưu tên file mới vào mảng data
                $data['avatar'] = $uploadResult['filename'];
            } else {
                // Nếu upload thất bại, lưu thông báo lỗi
                $error = $uploadResult['message'];
            }
        }

        // Nếu không có lỗi nào
        if (empty($error)) {
            // Gọi phương thức updateStudent để cập nhật thông tin sinh viên
            $result = $studentController->updateStudent($studentId, $data);

            // Nếu cập nhật thành công
            if ($result['success']) {
                // Lưu thông báo thành công
                $success = $result['message'];
                // Cập nhật dữ liệu sinh viên để hiển thị
                $student = array_merge($student, $data);
            } else {
                // Nếu cập nhật thất bại, lưu thông báo lỗi
                $error = $result['message'];
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <!-- Khai báo bảng mã ký tự UTF-8 -->
    <meta charset="UTF-8">
    <!-- Thiết lập viewport để responsive trên mobile -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Tiêu đề trang -->
    <title>Sửa sinh viên - Hệ thống quản lý sinh viên</title>
    <!-- Nạp Bootstrap CSS từ CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Nạp Font Awesome icons từ CDN -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Style cho sidebar */
        .sidebar {
            min-height: 100vh; /* Chiều cao tối thiểu bằng chiều cao viewport */
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); /* Gradient màu tím */
        }

        /* Style cho các link trong sidebar */
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8); /* Màu trắng với độ trong suốt 80% */
            padding: 12px 20px; /* Padding */
            border-radius: 8px; /* Bo góc */
            margin: 2px 0; /* Margin */
            transition: all 0.3s; /* Hiệu ứng chuyển đổi */
        }

        /* Style khi hover hoặc active link */
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: white; /* Màu trắng */
            background: rgba(255, 255, 255, 0.2); /* Nền trắng trong suốt */
            transform: translateX(5px); /* Dịch chuyển sang phải */
        }

        /* Style cho phần nội dung chính */
        .main-content {
            background-color: #f8f9fa; /* Màu nền xám nhạt */
            min-height: 100vh; /* Chiều cao tối thiểu */
        }

        /* Style cho card */
        .card {
            border: none; /* Không có viền */
            border-radius: 15px; /* Bo góc */
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); /* Đổ bóng */
        }

        /* Style cho các input form */
        .form-control {
            border-radius: 10px; /* Bo góc */
            border: 2px solid #e9ecef; /* Viền */
            padding: 12px 15px; /* Padding */
            transition: all 0.3s; /* Hiệu ứng */
        }

        /* Style khi focus vào input */
        .form-control:focus {
            border-color: #667eea; /* Viền màu tím */
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25); /* Đổ bóng */
        }

        /* Style cho nút primary */
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); /* Gradient */
            border: none; /* Không viền */
            border-radius: 10px; /* Bo góc */
            padding: 12px 30px; /* Padding */
            font-weight: 600; /* Độ đậm chữ */
            transition: transform 0.3s; /* Hiệu ứng */
        }

        /* Style khi hover nút */
        .btn-primary:hover {
            transform: translateY(-2px); /* Dịch chuyển lên */
        }

        /* Style cho ảnh preview avatar */
        .avatar-preview {
            width: 150px; /* Chiều rộng */
            height: 150px; /* Chiều cao */
            border-radius: 50%; /* Bo tròn */
            object-fit: cover; /* Cắt ảnh */
            border: 3px solid #e9ecef; /* Viền */
        }

        /* Style cho container upload avatar */
        .avatar-upload {
            position: relative; /* Vị trí tương đối */
            display: inline-block; /* Hiển thị inline-block */
        }

        /* Ẩn input file */
        .avatar-upload input[type="file"] {
            position: absolute; /* Vị trí tuyệt đối */
            opacity: 0; /* Ẩn */
            width: 100%; /* Chiều rộng */
            height: 100%; /* Chiều cao */
            cursor: pointer; /* Con trỏ pointer */
        }
    </style>
</head>

<body>
    <!-- Container fluid -->
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar p-0">
                <div class="p-3">
                    <!-- Tiêu đề sidebar -->
                    <h4 class="text-white mb-4">
                        <i class="fas fa-graduation-cap me-2"></i>
                        Student Management
                    </h4>
                    <!-- Hiển thị thông tin người dùng -->
                    <div class="text-white-50 mb-3">
                        <i class="fas fa-user me-2"></i>
                        <?php echo htmlspecialchars($_SESSION['username']); ?>
                        <!-- Badge vai trò -->
                        <span class="badge bg-light text-dark ms-2"><?php echo ucfirst($_SESSION['role']); ?></span>
                    </div>
                </div>

                <!-- Menu điều hướng -->
                <nav class="nav flex-column px-3">
                    <a class="nav-link" href="../public/index.php">
                        <i class="fas fa-home me-2"></i>Trang chủ
                    </a>
                    <!-- Link active -->
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
                    <!-- Header -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2><i class="fas fa-user-edit me-2"></i>Sửa thông tin sinh viên</h2>
                        <a href="list.php" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Quay lại
                        </a>
                    </div>

                    <div class="row">
                        <!-- Cột form -->
                        <div class="col-lg-8">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="fas fa-info-circle me-2"></i>Thông tin sinh viên
                                        <!-- Badge hiển thị mã sinh viên -->
                                        <span class="badge bg-primary ms-2"><?php echo htmlspecialchars($student['msv']); ?></span>
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <!-- Hiển thị thông báo lỗi -->
                                    <?php if ($error): ?>
                                        <div class="alert alert-danger" role="alert">
                                            <i class="fas fa-exclamation-triangle me-2"></i>
                                            <?php echo htmlspecialchars($error); ?>
                                        </div>
                                    <?php endif; ?>

                                    <!-- Hiển thị thông báo thành công -->
                                    <?php if ($success): ?>
                                        <div class="alert alert-success" role="alert">
                                            <i class="fas fa-check-circle me-2"></i>
                                            <?php echo htmlspecialchars($success); ?>
                                        </div>
                                    <?php endif; ?>

                                    <!-- Form sửa thông tin -->
                                    <form method="POST" enctype="multipart/form-data" id="studentForm">
                                        <!-- Hidden input chứa CSRF token -->
                                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                        <div class="row">
                                            <!-- Cột mã sinh viên -->
                                            <div class="col-md-6 mb-3">
                                                <label for="msv" class="form-label">
                                                    <i class="fas fa-id-card me-2"></i>Mã sinh viên *
                                                </label>
                                                <input type="text" class="form-control" id="msv" name="msv"
                                                    value="<?php echo htmlspecialchars($student['msv']); ?>" required>
                                            </div>

                                            <!-- Cột họ và tên -->
                                            <div class="col-md-6 mb-3">
                                                <label for="fullname" class="form-label">
                                                    <i class="fas fa-user me-2"></i>Họ và tên *
                                                </label>
                                                <input type="text" class="form-control" id="fullname" name="fullname"
                                                    value="<?php echo htmlspecialchars($student['fullname']); ?>" required>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <!-- Cột ngày sinh -->
                                            <div class="col-md-6 mb-3">
                                                <label for="dob" class="form-label">
                                                    <i class="fas fa-calendar me-2"></i>Ngày sinh *
                                                </label>
                                                <input type="date" class="form-control" id="dob" name="dob"
                                                    value="<?php echo $student['dob']; ?>" required>
                                            </div>

                                            <!-- Cột giới tính -->
                                            <div class="col-md-6 mb-3">
                                                <label for="gender" class="form-label">
                                                    <i class="fas fa-venus-mars me-2"></i>Giới tính *
                                                </label>
                                                <select class="form-control" id="gender" name="gender" required>
                                                    <option value="">Chọn giới tính</option>
                                                    <!-- Option Nam -->
                                                    <option value="male" <?php echo ($student['gender'] == 'male') ? 'selected' : ''; ?>>Nam</option>
                                                    <!-- Option Nữ -->
                                                    <option value="female" <?php echo ($student['gender'] == 'female') ? 'selected' : ''; ?>>Nữ</option>
                                                    <!-- Option Khác -->
                                                    <option value="other" <?php echo ($student['gender'] == 'other') ? 'selected' : ''; ?>>Khác</option>
                                                </select>
                                            </div>
                                        </div>

                                        <!-- Trường email -->
                                        <div class="mb-3">
                                            <label for="email" class="form-label">
                                                <i class="fas fa-envelope me-2"></i>Email *
                                            </label>
                                            <input type="email" class="form-control" id="email" name="email"
                                                value="<?php echo htmlspecialchars($student['email']); ?>" required>
                                        </div>

                                        <!-- Trường số điện thoại -->
                                        <div class="mb-3">
                                            <label for="phone" class="form-label">
                                                <i class="fas fa-phone me-2"></i>Số điện thoại
                                            </label>
                                            <input type="tel" class="form-control" id="phone" name="phone"
                                                value="<?php echo htmlspecialchars($student['phone']); ?>">
                                        </div>

                                        <!-- Trường địa chỉ -->
                                        <div class="mb-3">
                                            <label for="address" class="form-label">
                                                <i class="fas fa-map-marker-alt me-2"></i>Địa chỉ
                                            </label>
                                            <textarea class="form-control" id="address" name="address" rows="3"><?php echo htmlspecialchars($student['address']); ?></textarea>
                                        </div>

                                        <!-- Các nút hành động -->
                                        <div class="d-flex justify-content-end gap-2">
                                            <a href="list.php" class="btn btn-outline-secondary">
                                                <i class="fas fa-times me-2"></i>Hủy
                                            </a>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-save me-2"></i>Cập nhật
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Cột bên phải -->
                        <div class="col-lg-4">
                            <!-- Card upload ảnh đại diện -->
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="fas fa-image me-2"></i>Ảnh đại diện</h5>
                                </div>
                                <div class="card-body text-center">
                                    <div class="avatar-upload mb-3">
                                        <!-- Ảnh preview, hiển thị ảnh hiện tại hoặc placeholder -->
                                        <img id="avatarPreview"
                                            src="<?php echo $student['avatar'] ? '../uploads/avatars/' . htmlspecialchars($student['avatar']) : 'https://via.placeholder.com/150x150?text=No+Image'; ?>"
                                            class="avatar-preview" alt="Avatar Preview">
                                        <!-- Input file ẩn -->
                                        <input type="file" id="avatar" name="avatar" accept="image/*" onchange="previewImage(this)">
                                    </div>
                                    <p class="text-muted small">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Click vào ảnh để thay đổi<br>
                                        Hỗ trợ: JPG, PNG, GIF (tối đa 5MB)
                                    </p>
                                    <!-- Nút xóa ảnh nếu có ảnh -->
                                    <?php if ($student['avatar']): ?>
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeAvatar()">
                                            <i class="fas fa-trash me-1"></i>Xóa ảnh
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Card thông tin bổ sung -->
                            <div class="card mt-3">
                                <div class="card-header">
                                    <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Thông tin bổ sung</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row text-center">
                                        <!-- Cột ngày tạo -->
                                        <div class="col-6">
                                            <div class="border-end">
                                                <h6 class="text-muted">Ngày tạo</h6>
                                                <p class="mb-0"><?php echo formatDate($student['created_at']); ?></p>
                                            </div>
                                        </div>
                                        <!-- Cột ID -->
                                        <div class="col-6">
                                            <h6 class="text-muted">ID</h6>
                                            <p class="mb-0">#<?php echo $student['id']; ?></p>
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

    <!-- Nạp Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Nạp SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Nạp file notifications.js -->
    <script src="../assets/js/notifications.js"></script>
    <script>
        // Hàm preview ảnh khi chọn file
        function previewImage(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('avatarPreview').src = e.target.result;
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        // Hàm xóa ảnh đại diện
        function removeAvatar() {
            confirmDelete(
                'Xác nhận xóa ảnh đại diện',
                'Bạn có chắc chắn muốn xóa ảnh đại diện?',
                function() {
                    // Tạo hidden input để đánh dấu xóa avatar
                    const hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = 'remove_avatar';
                    hiddenInput.value = '1';
                    document.getElementById('studentForm').appendChild(hiddenInput);

                    // Cập nhật preview
                    document.getElementById('avatarPreview').src = 'https://via.placeholder.com/150x150?text=No+Image';
                    document.getElementById('avatar').value = '';
                    notification.success('Đã xóa ảnh đại diện');
                }
            );

            // Validation form
            document.getElementById('studentForm').addEventListener('submit', function(e) {
                const msv = document.getElementById('msv').value.trim();
                const fullname = document.getElementById('fullname').value.trim();
                const email = document.getElementById('email').value.trim();
                const dob = document.getElementById('dob').value;
                const gender = document.getElementById('gender').value;

                if (!msv || !fullname || !email || !dob || !gender) {
                    e.preventDefault();
                    alert('Vui lòng điền đầy đủ thông tin bắt buộc');
                    return false;
                }

                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(email)) {
                    e.preventDefault();
                    alert('Email không hợp lệ');
                    return false;
                }
            });
        }
    </script>
</body>

</html>
