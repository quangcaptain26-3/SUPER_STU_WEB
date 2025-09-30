<?php
session_start();
require_once '../utils.php';
require_once '../studentController.php';

requirePermission(PERMISSION_EDIT_STUDENTS);

$studentController = new StudentController();
$studentId = $_GET['id'] ?? 0;
$student = $studentController->getStudentById($studentId);

if (!$student) {
    header('Location: list.php?error=student_not_found');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = [
        'msv' => sanitize($_POST['msv']),
        'fullname' => sanitize($_POST['fullname']),
        'dob' => $_POST['dob'],
        'gender' => $_POST['gender'],
        'address' => sanitize($_POST['address']),
        'phone' => sanitize($_POST['phone']),
        'email' => sanitize($_POST['email']),
        'avatar' => $student['avatar'] // Keep existing avatar
    ];
    
    // Handle file upload
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == 0) {
        $uploadResult = uploadFile($_FILES['avatar']);
        if ($uploadResult['success']) {
            // Delete old avatar
            if ($student['avatar']) {
                deleteFile('uploads/avatars/' . $student['avatar']);
            }
            $data['avatar'] = $uploadResult['filename'];
        } else {
            $error = $uploadResult['message'];
        }
    }
    
    if (empty($error)) {
        $result = $studentController->updateStudent($studentId, $data);
        
        if ($result['success']) {
            $success = $result['message'];
            // Update student data for display
            $student = array_merge($student, $data);
        } else {
            $error = $result['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sửa sinh viên - Hệ thống quản lý sinh viên</title>
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
        .avatar-preview {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #e9ecef;
        }
        .avatar-upload {
            position: relative;
            display: inline-block;
        }
        .avatar-upload input[type="file"] {
            position: absolute;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
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
                        <h2><i class="fas fa-user-edit me-2"></i>Sửa thông tin sinh viên</h2>
                        <a href="list.php" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Quay lại
                        </a>
                    </div>
                    
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="fas fa-info-circle me-2"></i>Thông tin sinh viên
                                        <span class="badge bg-primary ms-2"><?php echo htmlspecialchars($student['msv']); ?></span>
                                    </h5>
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
                                    
                                    <form method="POST" enctype="multipart/form-data" id="studentForm">
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="msv" class="form-label">
                                                    <i class="fas fa-id-card me-2"></i>Mã sinh viên *
                                                </label>
                                                <input type="text" class="form-control" id="msv" name="msv" 
                                                       value="<?php echo htmlspecialchars($student['msv']); ?>" required>
                                            </div>
                                            
                                            <div class="col-md-6 mb-3">
                                                <label for="fullname" class="form-label">
                                                    <i class="fas fa-user me-2"></i>Họ và tên *
                                                </label>
                                                <input type="text" class="form-control" id="fullname" name="fullname" 
                                                       value="<?php echo htmlspecialchars($student['fullname']); ?>" required>
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="dob" class="form-label">
                                                    <i class="fas fa-calendar me-2"></i>Ngày sinh *
                                                </label>
                                                <input type="date" class="form-control" id="dob" name="dob" 
                                                       value="<?php echo $student['dob']; ?>" required>
                                            </div>
                                            
                                            <div class="col-md-6 mb-3">
                                                <label for="gender" class="form-label">
                                                    <i class="fas fa-venus-mars me-2"></i>Giới tính *
                                                </label>
                                                <select class="form-control" id="gender" name="gender" required>
                                                    <option value="">Chọn giới tính</option>
                                                    <option value="male" <?php echo ($student['gender'] == 'male') ? 'selected' : ''; ?>>Nam</option>
                                                    <option value="female" <?php echo ($student['gender'] == 'female') ? 'selected' : ''; ?>>Nữ</option>
                                                    <option value="other" <?php echo ($student['gender'] == 'other') ? 'selected' : ''; ?>>Khác</option>
                                                </select>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="email" class="form-label">
                                                <i class="fas fa-envelope me-2"></i>Email *
                                            </label>
                                            <input type="email" class="form-control" id="email" name="email" 
                                                   value="<?php echo htmlspecialchars($student['email']); ?>" required>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="phone" class="form-label">
                                                <i class="fas fa-phone me-2"></i>Số điện thoại
                                            </label>
                                            <input type="tel" class="form-control" id="phone" name="phone" 
                                                   value="<?php echo htmlspecialchars($student['phone']); ?>">
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="address" class="form-label">
                                                <i class="fas fa-map-marker-alt me-2"></i>Địa chỉ
                                            </label>
                                            <textarea class="form-control" id="address" name="address" rows="3"><?php echo htmlspecialchars($student['address']); ?></textarea>
                                        </div>
                                        
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
                        
                        <div class="col-lg-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="fas fa-image me-2"></i>Ảnh đại diện</h5>
                                </div>
                                <div class="card-body text-center">
                                    <div class="avatar-upload mb-3">
                                        <img id="avatarPreview" 
                                             src="<?php echo $student['avatar'] ? '../uploads/avatars/' . htmlspecialchars($student['avatar']) : 'https://via.placeholder.com/150x150?text=No+Image'; ?>" 
                                             class="avatar-preview" alt="Avatar Preview">
                                        <input type="file" id="avatar" name="avatar" accept="image/*" onchange="previewImage(this)">
                                    </div>
                                    <p class="text-muted small">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Click vào ảnh để thay đổi<br>
                                        Hỗ trợ: JPG, PNG, GIF (tối đa 5MB)
                                    </p>
                                    <?php if ($student['avatar']): ?>
                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeAvatar()">
                                        <i class="fas fa-trash me-1"></i>Xóa ảnh
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="card mt-3">
                                <div class="card-header">
                                    <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Thông tin bổ sung</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row text-center">
                                        <div class="col-6">
                                            <div class="border-end">
                                                <h6 class="text-muted">Ngày tạo</h6>
                                                <p class="mb-0"><?php echo formatDate($student['created_at']); ?></p>
                                            </div>
                                        </div>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../assets/js/notifications.js"></script>
    <script>
        function previewImage(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('avatarPreview').src = e.target.result;
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
        
        function removeAvatar() {
            confirmDelete(
                'Xác nhận xóa ảnh đại diện',
                'Bạn có chắc chắn muốn xóa ảnh đại diện?',
                function() {
                // Create a hidden input to indicate avatar removal
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'remove_avatar';
                hiddenInput.value = '1';
                document.getElementById('studentForm').appendChild(hiddenInput);
                
                // Update preview
                document.getElementById('avatarPreview').src = 'https://via.placeholder.com/150x150?text=No+Image';
                document.getElementById('avatar').value = '';
                notification.success('Đã xóa ảnh đại diện');
                }
        }
        
        // Form validation
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
    </script>
</body>
</html>
