<?php
// Bắt đầu hoặc tiếp tục phiên làm việc để sử dụng session.
session_start();
// Nạp các file tiện ích và controller.
require_once '../utils.php';
require_once '../studentController.php';

// Yêu cầu quyền `PERMISSION_ADD_STUDENTS`. Nếu không có quyền, script sẽ dừng và chuyển hướng.
requirePermission(PERMISSION_ADD_STUDENTS);

// Khởi tạo các biến để lưu trữ thông báo và dữ liệu form.
$error = '';
$success = '';
$data = []; // Mảng để giữ lại dữ liệu người dùng đã nhập nếu có lỗi.

// Kiểm tra nếu form được gửi đi bằng phương thức POST.
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 1. Xác thực CSRF Token.
    // Kiểm tra xem token có được gửi từ form không và có khớp với token trong session không.
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $error = 'Lỗi xác thực (CSRF token không hợp lệ). Vui lòng thử lại.';
    } else {
        // 2. Xử lý dữ liệu từ form.
        // Gán dữ liệu từ `$_POST` vào mảng `$data`, đồng thời làm sạch để chống XSS.
        $data = [
            'msv'      => sanitize($_POST['msv'] ?? ''),
            'fullname' => sanitize($_POST['fullname'] ?? ''),
            'dob'      => $_POST['dob'] ?? '',
            'gender'   => $_POST['gender'] ?? '',
            'address'  => sanitize($_POST['address'] ?? ''),
            'phone'    => sanitize($_POST['phone'] ?? ''),
            'email'    => sanitize($_POST['email'] ?? ''),
            'avatar'   => '' // Khởi tạo tên file avatar là rỗng.
        ];

        // 3. Xử lý upload file ảnh đại diện.
        // Kiểm tra xem có file được tải lên với tên 'avatar' và không có lỗi nào xảy ra.
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            // Gọi hàm `uploadFile` từ `utils.php` để xử lý file.
            $uploadResult = uploadFile($_FILES['avatar'], '../uploads/avatars/');
            
            if ($uploadResult['success']) {
                // Nếu upload thành công, lưu tên file đã được tạo duy nhất vào mảng data.
                $data['avatar'] = $uploadResult['filename'];
            } else {
                // Nếu upload thất bại, gán thông báo lỗi.
                $error = $uploadResult['message'];
            }
        }

        // 4. Thêm sinh viên vào CSDL (chỉ khi không có lỗi upload).
        if (empty($error)) {
            // Khởi tạo đối tượng StudentController.
            $studentController = new StudentController();
            // Gọi phương thức `addStudent` để chèn dữ liệu vào CSDL.
            $result = $studentController->addStudent($data);

            if ($result['success']) {
                // Nếu thêm thành công, gán thông báo thành công.
                $success = $result['message'];
                // Xóa dữ liệu đã nhập khỏi mảng `$data` để form được reset cho lần nhập tiếp theo.
                $data = array_fill_keys(array_keys($data), ''); 
            } else {
                // Nếu thêm thất bại (ví dụ: trùng MSV), gán thông báo lỗi.
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
    <title>Thêm sinh viên - Hệ thống quản lý sinh viên</title>
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
                        <h2><i class="fas fa-user-plus me-2"></i>Thêm sinh viên mới</h2>
                        <a href="list.php" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Quay lại danh sách
                        </a>
                    </div>
                    
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Thông tin sinh viên</h5>
                                </div>
                                <div class="card-body position-relative">
                                    <?php if ($error): ?>
                                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                        <i class="fas fa-exclamation-circle me-2"></i>
                                        <?php echo htmlspecialchars($error); ?>
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($success): ?>
                                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                                        <i class="fas fa-check-circle me-2"></i>
                                        <?php echo htmlspecialchars($success); ?>
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <form method="POST" enctype="multipart/form-data" id="studentForm">
                                        <?php // Tạo và chèn CSRF token vào form để bảo mật. ?>
                                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                        
                                        <!-- Loading overlay khi submit -->
                                        <div id="formLoading" class="d-none position-absolute top-0 start-0 w-100 h-100 bg-white bg-opacity-75 d-flex align-items-center justify-content-center" style="z-index: 1000; border-radius: 15px;">
                                            <div class="text-center">
                                                <div class="spinner-border text-primary mb-2" role="status">
                                                    <span class="visually-hidden">Đang xử lý...</span>
                                                </div>
                                                <p class="text-muted mb-0">Đang lưu dữ liệu...</p>
                                            </div>
                                        </div>
                                        
                                        <!-- Các trường nhập liệu của form -->
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="msv" class="form-label">
                                                    <i class="fas fa-id-card me-2"></i>Mã sinh viên *
                                                </label>
                                                <input type="text" class="form-control" id="msv" name="msv" value="<?php echo htmlspecialchars($data['msv'] ?? ''); ?>" required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="fullname" class="form-label">
                                                    <i class="fas fa-user me-2"></i>Họ và tên *
                                                </label>
                                                <input type="text" class="form-control" id="fullname" name="fullname" value="<?php echo htmlspecialchars($data['fullname'] ?? ''); ?>" required>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="dob" class="form-label">
                                                    <i class="fas fa-calendar me-2"></i>Ngày sinh
                                                </label>
                                                <input type="date" class="form-control" id="dob" name="dob" value="<?php echo htmlspecialchars($data['dob'] ?? ''); ?>">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="gender" class="form-label">
                                                    <i class="fas fa-venus-mars me-2"></i>Giới tính
                                                </label>
                                                <select class="form-select" id="gender" name="gender">
                                                    <option value="">-- Chọn giới tính --</option>
                                                    <option value="male" <?php echo (isset($data['gender']) && $data['gender'] == 'male') ? 'selected' : ''; ?>>Nam</option>
                                                    <option value="female" <?php echo (isset($data['gender']) && $data['gender'] == 'female') ? 'selected' : ''; ?>>Nữ</option>
                                                    <option value="other" <?php echo (isset($data['gender']) && $data['gender'] == 'other') ? 'selected' : ''; ?>>Khác</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="phone" class="form-label">
                                                    <i class="fas fa-phone me-2"></i>Số điện thoại
                                                </label>
                                                <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($data['phone'] ?? ''); ?>" placeholder="0123456789">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="email" class="form-label">
                                                    <i class="fas fa-envelope me-2"></i>Email
                                                </label>
                                                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($data['email'] ?? ''); ?>" placeholder="example@email.com">
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-12 mb-3">
                                                <label for="address" class="form-label">
                                                    <i class="fas fa-map-marker-alt me-2"></i>Địa chỉ
                                                </label>
                                                <textarea class="form-control" id="address" name="address" rows="3" placeholder="Nhập địa chỉ đầy đủ"><?php echo htmlspecialchars($data['address'] ?? ''); ?></textarea>
                                            </div>
                                        </div>

                                        <div class="d-flex justify-content-end gap-2">
                                            <a href="list.php" class="btn btn-outline-secondary" id="cancelBtn">
                                                <i class="fas fa-times me-2"></i>Hủy
                                            </a>
                                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                                <i class="fas fa-save me-2"></i>Lưu sinh viên
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="fas fa-image me-2"></i>Ảnh đại diện</h5>
                                </div>
                                <div class="card-body text-center">
                                    <div class="avatar-upload mb-3">
                                        <img id="avatarPreview" src="https://via.placeholder.com/150x150?text=No+Image" class="avatar-preview" alt="Avatar Preview" onclick="selectAvatar()">
                                        <?php // Input file này bị ẩn, chỉ được kích hoạt bằng JS. ?>
                                        <input type="file" id="avatar" name="avatar" accept="image/*" onchange="previewImage(this)" style="display: none;">
                                    </div>
                                    <button type="button" class="btn btn-outline-primary btn-sm mb-2" onclick="selectAvatar()">
                                        <i class="fas fa-upload me-1"></i>Chọn ảnh
                                    </button>
                                    <p class="text-muted small mb-0">
                                        <i class="fas fa-info-circle me-1"></i>
                                        JPG, PNG, GIF. Tối đa 5MB.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Nạp SweetAlert2 và Notification System -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../assets/js/notifications.js"></script>
    <script>
        // Khởi tạo notification system
        const notifications = new NotificationSystem();
        
        // Xử lý form submit với loading state
        document.getElementById('studentForm').addEventListener('submit', function(e) {
            const form = this;
            const submitBtn = document.getElementById('submitBtn');
            const cancelBtn = document.getElementById('cancelBtn');
            const loadingOverlay = document.getElementById('formLoading');
            
            // Validate form
            const msv = document.getElementById('msv').value.trim();
            const fullname = document.getElementById('fullname').value.trim();
            
            if (!msv || !fullname) {
                e.preventDefault();
                notifications.error('Vui lòng điền đầy đủ các trường bắt buộc (Mã SV và Họ tên)', 'Thiếu thông tin');
                return false;
            }
            
            // Hiển thị loading state
            loadingOverlay.classList.remove('d-none');
            submitBtn.disabled = true;
            cancelBtn.style.pointerEvents = 'none';
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Đang lưu...';
        });
        
        /**
         * Kích hoạt sự kiện click trên input file ẩn khi người dùng nhấn vào ảnh hoặc nút.
         */
        function selectAvatar() {
            document.getElementById('avatar').click();
        }

        /**
         * Đọc và hiển thị ảnh xem trước (preview) khi người dùng chọn một file.
         * @param {HTMLInputElement} input - Phần tử input[type="file"] đã thay đổi.
         */
        function previewImage(input) {
            // Đảm bảo người dùng đã chọn một file.
            if (input.files && input.files[0]) {
                // Kiểm tra kích thước file (tối đa 5MB)
                if (input.files[0].size > 5 * 1024 * 1024) {
                    notifications.warning('Kích thước file không được vượt quá 5MB', 'File quá lớn');
                    input.value = '';
                    return;
                }
                
                // Kiểm tra loại file
                const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                if (!allowedTypes.includes(input.files[0].type)) {
                    notifications.warning('Chỉ chấp nhận file ảnh (JPG, PNG, GIF, WEBP)', 'Định dạng không hợp lệ');
                    input.value = '';
                    return;
                }
                
                // Tạo một đối tượng FileReader để đọc nội dung file.
                const reader = new FileReader();

                // Định nghĩa một hàm callback sẽ được thực thi khi FileReader đọc xong file.
                reader.onload = function(e) {
                    // `e.target.result` chứa dữ liệu của file ảnh dưới dạng Base64 Data URL.
                    // Gán dữ liệu này vào thuộc tính `src` của thẻ <img> để hiển thị ảnh.
                    document.getElementById('avatarPreview').src = e.target.result;
                    notifications.success('Ảnh đã được chọn thành công', 'Upload ảnh');
                }

                // Bắt đầu đọc file được chọn. Kết quả sẽ được cung cấp cho sự kiện `onload`.
                reader.readAsDataURL(input.files[0]);
            }
        }
        
        // Thêm real-time validation feedback
        document.getElementById('email').addEventListener('blur', function() {
            const email = this.value.trim();
            if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                this.classList.add('is-invalid');
                this.classList.remove('is-valid');
            } else if (email) {
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
            }
        });
        
        document.getElementById('msv').addEventListener('input', function() {
            if (this.value.trim()) {
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
            }
        });
        
        document.getElementById('fullname').addEventListener('input', function() {
            if (this.value.trim()) {
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
            }
        });
    </script>
    <style>
        .form-control.is-valid {
            border-color: #28a745;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 8 8'%3e%3cpath fill='%2328a745' d='m2.3 6.73.98-.98-.98-.98-.98.98.98.98zm5.6-5.6.98-.98-.98-.98-.98.98.98.98z'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right calc(0.375em + 0.1875rem) center;
            background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
        }
        .form-control.is-invalid {
            border-color: #dc3545;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath d='m5.8 3.6.4.4.4-.4m0 4.8-.4-.4-.4.4'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right calc(0.375em + 0.1875rem) center;
            background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
        }
        #studentForm {
            position: relative;
        }
    </style>
</body>
</html>
