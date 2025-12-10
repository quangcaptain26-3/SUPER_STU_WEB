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
        /* CSS styles... */
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar p-0">
                <!-- ... -->
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
                                <div class="card-body">
                                    <?php if ($error): ?>
                                    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                                    <?php endif; ?>
                                    
                                    <?php if ($success): ?>
                                    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                                    <?php endif; ?>
                                    
                                    <form method="POST" enctype="multipart/form-data" id="studentForm">
                                        <?php // Tạo và chèn CSRF token vào form để bảo mật. ?>
                                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                        
                                        <!-- Các trường nhập liệu của form -->
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="msv" class="form-label">Mã sinh viên *</label>
                                                <input type="text" class="form-control" id="msv" name="msv" value="<?php echo htmlspecialchars($data['msv'] ?? ''); ?>" required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="fullname" class="form-label">Họ và tên *</label>
                                                <input type="text" class="form-control" id="fullname" name="fullname" value="<?php echo htmlspecialchars($data['fullname'] ?? ''); ?>" required>
                                            </div>
                                        </div>
                                        <!-- ... các trường khác ... -->
                                         <div class="d-flex justify-content-end gap-2">
                                            <a href="list.php" class="btn btn-outline-secondary">
                                                <i class="fas fa-times me-2"></i>Hủy
                                            </a>
                                            <button type="submit" class="btn btn-primary">
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

    <script>
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
                // Tạo một đối tượng FileReader để đọc nội dung file.
                const reader = new FileReader();

                // Định nghĩa một hàm callback sẽ được thực thi khi FileReader đọc xong file.
                reader.onload = function(e) {
                    // `e.target.result` chứa dữ liệu của file ảnh dưới dạng Base64 Data URL.
                    // Gán dữ liệu này vào thuộc tính `src` của thẻ <img> để hiển thị ảnh.
                    document.getElementById('avatarPreview').src = e.target.result;
                }

                // Bắt đầu đọc file được chọn. Kết quả sẽ được cung cấp cho sự kiện `onload`.
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</body>
</html>
