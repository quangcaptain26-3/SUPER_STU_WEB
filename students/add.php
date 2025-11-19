<?php
// Bắt đầu session để lưu trữ thông tin người dùng
session_start();
// Nạp file chứa các hàm tiện ích (sanitize, requirePermission, v.v.)
require_once '../utils.php';
// Nạp file chứa class StudentController để xử lý logic liên quan đến sinh viên
require_once '../studentController.php';

// Yêu cầu người dùng phải có quyền thêm sinh viên, nếu không sẽ chuyển hướng
requirePermission(PERMISSION_ADD_STUDENTS);

// Khởi tạo biến để lưu thông báo lỗi
$error = '';
// Khởi tạo biến để lưu thông báo thành công
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
            'dob' => $_POST['dob'],                         // Ngày sinh (không cần sanitize vì là date)
            'gender' => $_POST['gender'],                    // Giới tính (đã được validate từ select)
            'address' => sanitize($_POST['address']),       // Địa chỉ đã được làm sạch
            'phone' => sanitize($_POST['phone']),           // Số điện thoại đã được làm sạch
            'email' => sanitize($_POST['email']),           // Email đã được làm sạch
            'avatar' => ''                                  // Khởi tạo avatar rỗng
        ];
    
        // Xử lý upload file ảnh đại diện
        // Kiểm tra xem có file được upload và không có lỗi không
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == 0) {
            // Gọi hàm uploadFile để xử lý upload
            $uploadResult = uploadFile($_FILES['avatar']);
            // Nếu upload thành công
            if ($uploadResult['success']) {
                // Lưu tên file vào mảng data
                $data['avatar'] = $uploadResult['filename'];
            } else {
                // Nếu upload thất bại, lưu thông báo lỗi
                $error = $uploadResult['message'];
            }
        }
    
        // Nếu không có lỗi nào
        if (empty($error)) {
            // Tạo đối tượng StudentController
            $studentController = new StudentController();
            // Gọi phương thức addStudent để thêm sinh viên vào database
            $result = $studentController->addStudent($data);
        
            // Nếu thêm thành công
            if ($result['success']) {
                // Lưu thông báo thành công
                $success = $result['message'];
                // Xóa dữ liệu form để người dùng có thể nhập tiếp
                $data = array_fill_keys(array_keys($data), '');
            } else {
                // Nếu thêm thất bại, lưu thông báo lỗi
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
    <title>Thêm sinh viên - Hệ thống quản lý sinh viên</title>
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
            color: rgba(255,255,255,0.8); /* Màu trắng với độ trong suốt 80% */
            padding: 12px 20px; /* Padding trên dưới 12px, trái phải 20px */
            border-radius: 8px; /* Bo góc 8px */
            margin: 2px 0; /* Margin trên dưới 2px */
            transition: all 0.3s; /* Hiệu ứng chuyển đổi 0.3 giây */
        }
        /* Style khi hover hoặc active link trong sidebar */
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            color: white; /* Màu trắng đầy đủ */
            background: rgba(255,255,255,0.2); /* Nền trắng với độ trong suốt 20% */
            transform: translateX(5px); /* Dịch chuyển sang phải 5px */
        }
        /* Style cho phần nội dung chính */
        .main-content {
            background-color: #f8f9fa; /* Màu nền xám nhạt */
            min-height: 100vh; /* Chiều cao tối thiểu bằng chiều cao viewport */
        }
        /* Style cho card */
        .card {
            border: none; /* Không có viền */
            border-radius: 15px; /* Bo góc 15px */
            box-shadow: 0 4px 6px rgba(0,0,0,0.1); /* Đổ bóng */
        }
        /* Style cho các input form */
        .form-control {
            border-radius: 10px; /* Bo góc 10px */
            border: 2px solid #e9ecef; /* Viền màu xám nhạt */
            padding: 12px 15px; /* Padding */
            transition: all 0.3s; /* Hiệu ứng chuyển đổi */
        }
        /* Style khi focus vào input */
        .form-control:focus {
            border-color: #667eea; /* Viền màu tím */
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25); /* Đổ bóng màu tím */
        }
        /* Style cho nút primary */
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); /* Gradient màu tím */
            border: none; /* Không có viền */
            border-radius: 10px; /* Bo góc 10px */
            padding: 12px 30px; /* Padding */
            font-weight: 600; /* Độ đậm chữ */
            transition: transform 0.3s; /* Hiệu ứng chuyển đổi */
        }
        /* Style khi hover nút primary */
        .btn-primary:hover {
            transform: translateY(-2px); /* Dịch chuyển lên trên 2px */
        }
        /* Style cho ảnh preview avatar */
        .avatar-preview {
            width: 150px; /* Chiều rộng 150px */
            height: 150px; /* Chiều cao 150px */
            border-radius: 50%; /* Bo tròn */
            object-fit: cover; /* Cắt ảnh để vừa khung */
            border: 3px solid #e9ecef; /* Viền màu xám nhạt */
        }
        /* Style cho container upload avatar */
        .avatar-upload {
            position: relative; /* Vị trí tương đối */
            display: inline-block; /* Hiển thị inline-block */
        }
        /* Ẩn input file nhưng vẫn có thể click */
        .avatar-upload input[type="file"] {
            position: absolute; /* Vị trí tuyệt đối */
            opacity: 0; /* Ẩn hoàn toàn */
            width: 100%; /* Chiều rộng 100% */
            height: 100%; /* Chiều cao 100% */
            cursor: pointer; /* Con trỏ dạng pointer */
        }
    </style>
</head>
<body>
    <!-- Container fluid để chiếm toàn bộ chiều rộng -->
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
                    <!-- Hiển thị thông tin người dùng đang đăng nhập -->
                    <div class="text-white-50 mb-3">
                        <i class="fas fa-user me-2"></i>
                        <?php echo htmlspecialchars($_SESSION['username']); ?>
                        <!-- Badge hiển thị vai trò của người dùng -->
                        <span class="badge bg-light text-dark ms-2"><?php echo ucfirst($_SESSION['role']); ?></span>
                    </div>
                </div>
                
                <!-- Menu điều hướng -->
                <nav class="nav flex-column px-3">
                    <!-- Link đến trang chủ -->
                    <a class="nav-link" href="../public/index.php">
                        <i class="fas fa-home me-2"></i>Trang chủ
                    </a>
                    <!-- Link đến trang quản lý sinh viên (active) -->
                    <a class="nav-link active" href="list.php">
                        <i class="fas fa-users me-2"></i>Quản lý sinh viên
                    </a>
                    <!-- Link đến trang quản lý điểm -->
                    <a class="nav-link" href="../scores/list.php">
                        <i class="fas fa-chart-line me-2"></i>Quản lý điểm
                    </a>
                    <!-- Link đến trang thống kê -->
                    <a class="nav-link" href="../charts/statistics.php">
                        <i class="fas fa-chart-bar me-2"></i>Thống kê
                    </a>
                    <!-- Link đăng xuất -->
                    <a class="nav-link" href="../public/logout.php">
                        <i class="fas fa-sign-out-alt me-2"></i>Đăng xuất
                    </a>
                </nav>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 main-content">
                <div class="p-4">
                    <!-- Header với tiêu đề và nút quay lại -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2><i class="fas fa-user-plus me-2"></i>Thêm sinh viên mới</h2>
                        <a href="list.php" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Quay lại
                        </a>
                    </div>
                    
                    <div class="row">
                        <!-- Cột chứa form (chiếm 8/12 cột) -->
                        <div class="col-lg-8">
                            <div class="card">
                                <!-- Header của card -->
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Thông tin sinh viên</h5>
                                </div>
                                <div class="card-body">
                                    <!-- Hiển thị thông báo lỗi nếu có -->
                                    <?php if ($error): ?>
                                    <div class="alert alert-danger" role="alert">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        <?php echo htmlspecialchars($error); ?>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <!-- Hiển thị thông báo thành công nếu có -->
                                    <?php if ($success): ?>
                                    <div class="alert alert-success" role="alert">
                                        <i class="fas fa-check-circle me-2"></i>
                                        <?php echo htmlspecialchars($success); ?>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <!-- Form thêm sinh viên -->
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
                                                       value="<?php echo htmlspecialchars($data['msv'] ?? ''); ?>" required>
                                            </div>
                                            
                                            <!-- Cột họ và tên -->
                                            <div class="col-md-6 mb-3">
                                                <label for="fullname" class="form-label">
                                                    <i class="fas fa-user me-2"></i>Họ và tên *
                                                </label>
                                                <input type="text" class="form-control" id="fullname" name="fullname" 
                                                       value="<?php echo htmlspecialchars($data['fullname'] ?? ''); ?>" required>
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <!-- Cột ngày sinh -->
                                            <div class="col-md-6 mb-3">
                                                <label for="dob" class="form-label">
                                                    <i class="fas fa-calendar me-2"></i>Ngày sinh *
                                                </label>
                                                <input type="date" class="form-control" id="dob" name="dob" 
                                                       value="<?php echo $data['dob'] ?? ''; ?>" required>
                                            </div>
                                            
                                            <!-- Cột giới tính -->
                                            <div class="col-md-6 mb-3">
                                                <label for="gender" class="form-label">
                                                    <i class="fas fa-venus-mars me-2"></i>Giới tính *
                                                </label>
                                                <select class="form-control" id="gender" name="gender" required>
                                                    <option value="">Chọn giới tính</option>
                                                    <!-- Option Nam, được selected nếu giá trị là 'male' -->
                                                    <option value="male" <?php echo (($data['gender'] ?? '') == 'male') ? 'selected' : ''; ?>>Nam</option>
                                                    <!-- Option Nữ, được selected nếu giá trị là 'female' -->
                                                    <option value="female" <?php echo (($data['gender'] ?? '') == 'female') ? 'selected' : ''; ?>>Nữ</option>
                                                    <!-- Option Khác, được selected nếu giá trị là 'other' -->
                                                    <option value="other" <?php echo (($data['gender'] ?? '') == 'other') ? 'selected' : ''; ?>>Khác</option>
                                                </select>
                                            </div>
                                        </div>
                                        
                                        <!-- Trường email -->
                                        <div class="mb-3">
                                            <label for="email" class="form-label">
                                                <i class="fas fa-envelope me-2"></i>Email *
                                            </label>
                                            <input type="email" class="form-control" id="email" name="email" 
                                                   value="<?php echo htmlspecialchars($data['email'] ?? ''); ?>" required>
                                        </div>
                                        
                                        <!-- Trường số điện thoại -->
                                        <div class="mb-3">
                                            <label for="phone" class="form-label">
                                                <i class="fas fa-phone me-2"></i>Số điện thoại
                                            </label>
                                            <input type="tel" class="form-control" id="phone" name="phone" 
                                                   value="<?php echo htmlspecialchars($data['phone'] ?? ''); ?>">
                                        </div>
                                        
                                        <!-- Trường địa chỉ -->
                                        <div class="mb-3">
                                            <label for="address" class="form-label">
                                                <i class="fas fa-map-marker-alt me-2"></i>Địa chỉ
                                            </label>
                                            <textarea class="form-control" id="address" name="address" rows="3"><?php echo htmlspecialchars($data['address'] ?? ''); ?></textarea>
                                        </div>
                                        
                                        <!-- Các nút hành động -->
                                        <div class="d-flex justify-content-end gap-2">
                                            <!-- Nút hủy, quay lại danh sách -->
                                            <a href="list.php" class="btn btn-outline-secondary">
                                                <i class="fas fa-times me-2"></i>Hủy
                                            </a>
                                            <!-- Nút submit form -->
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-save me-2"></i>Lưu sinh viên
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Cột bên phải (chiếm 4/12 cột) -->
                        <div class="col-lg-4">
                            <!-- Card upload ảnh đại diện -->
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="fas fa-image me-2"></i>Ảnh đại diện</h5>
                                </div>
                                <div class="card-body text-center">
                                    <!-- Container upload avatar -->
                                    <div class="avatar-upload mb-3">
                                        <!-- Ảnh preview, mặc định hiển thị placeholder -->
                                        <img id="avatarPreview" src="https://via.placeholder.com/150x150?text=No+Image" 
                                             class="avatar-preview" alt="Avatar Preview">
                                        <!-- Input file ẩn, được kích hoạt khi click vào ảnh -->
                                        <input type="file" id="avatar" name="avatar" accept="image/*" onchange="previewImage(this)">
                                    </div>
                                    <!-- Hướng dẫn upload -->
                                    <p class="text-muted small">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Click vào ảnh để chọn file<br>
                                        Hỗ trợ: JPG, PNG, GIF (tối đa 5MB)
                                    </p>
                                </div>
                            </div>
                            
                            <!-- Card gợi ý -->
                            <div class="card mt-3">
                                <div class="card-header">
                                    <h6 class="mb-0"><i class="fas fa-lightbulb me-2"></i>Gợi ý</h6>
                                </div>
                                <div class="card-body">
                                    <ul class="list-unstyled mb-0 small">
                                        <li><i class="fas fa-check text-success me-2"></i>Mã sinh viên phải duy nhất</li>
                                        <li><i class="fas fa-check text-success me-2"></i>Email phải hợp lệ</li>
                                        <li><i class="fas fa-check text-success me-2"></i>Ảnh đại diện là tùy chọn</li>
                                        <li><i class="fas fa-check text-success me-2"></i>Tất cả trường có * là bắt buộc</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Nạp Bootstrap JS từ CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Hàm preview ảnh khi người dùng chọn file
        function previewImage(input) {
            // Kiểm tra xem có file được chọn không
            if (input.files && input.files[0]) {
                // Tạo đối tượng FileReader để đọc file
                const reader = new FileReader();
                // Khi file được đọc xong
                reader.onload = function(e) {
                    // Cập nhật src của ảnh preview
                    document.getElementById('avatarPreview').src = e.target.result;
                }
                // Đọc file dưới dạng Data URL
                reader.readAsDataURL(input.files[0]);
            }
        }
        
        // Validation form trước khi submit
        document.getElementById('studentForm').addEventListener('submit', function(e) {
            // Lấy giá trị các trường bắt buộc và loại bỏ khoảng trắng
            const msv = document.getElementById('msv').value.trim();
            const fullname = document.getElementById('fullname').value.trim();
            const email = document.getElementById('email').value.trim();
            const dob = document.getElementById('dob').value;
            const gender = document.getElementById('gender').value;
            
            // Kiểm tra các trường bắt buộc có được điền đầy đủ không
            if (!msv || !fullname || !email || !dob || !gender) {
                // Ngăn form submit
                e.preventDefault();
                // Hiển thị cảnh báo
                alert('Vui lòng điền đầy đủ thông tin bắt buộc');
                return false;
            }
            
            // Regex để kiểm tra định dạng email
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            // Kiểm tra email có đúng định dạng không
            if (!emailRegex.test(email)) {
                // Ngăn form submit
                e.preventDefault();
                // Hiển thị cảnh báo
                alert('Email không hợp lệ');
                return false;
            }
        });
        
        // Tự động focus vào trường đầu tiên khi trang load
        document.getElementById('msv').focus();
    </script>
</body>
</html>
