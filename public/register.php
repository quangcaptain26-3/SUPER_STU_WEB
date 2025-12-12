<?php
// Bắt đầu session để lưu trữ thông tin người dùng sau khi đăng ký
session_start();
// Nạp file chứa class AuthController để xử lý logic đăng ký
require_once '../authController.php';
// Nạp file chứa các hàm tiện ích
require_once '../utils.php';

// Kiểm tra xem người dùng đã đăng nhập chưa
// Nếu đã đăng nhập thì chuyển hướng về trang chủ để tránh đăng ký lại
if (isLoggedIn()) {
    // Chuyển hướng về trang chủ
    header('Location: index.php');
    // Dừng thực thi script
    exit();
}

// Khởi tạo biến lưu thông báo lỗi
$error = '';
// Khởi tạo biến lưu thông báo thành công
$success = '';

// Kiểm tra xem request có phải là POST không (khi form đăng ký được submit)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Lấy và làm sạch dữ liệu từ form
    $username = sanitize($_POST['username']);              // Tên đăng nhập đã được làm sạch
    $password = $_POST['password'];                         // Mật khẩu (không sanitize)
    $confirmPassword = $_POST['confirm_password'];          // Mật khẩu xác nhận
    $email = sanitize($_POST['email']);                     // Email đã được làm sạch
    $role = sanitize($_POST['role']);                      // Vai trò đã được làm sạch
    
    // Kiểm tra các trường bắt buộc có được điền đầy đủ không
    if (empty($username) || empty($password) || empty($email)) {
        // Nếu thiếu thông tin, gán thông báo lỗi
        $error = 'Vui lòng nhập đầy đủ thông tin';
    } elseif ($password !== $confirmPassword) {
        // Kiểm tra mật khẩu xác nhận có khớp với mật khẩu không
        $error = 'Mật khẩu xác nhận không khớp';
    } elseif (strlen($password) < 6) {
        // Kiểm tra độ dài mật khẩu (tối thiểu 6 ký tự)
        $error = 'Mật khẩu phải có ít nhất 6 ký tự';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // Kiểm tra định dạng email có hợp lệ không
        $error = 'Email không hợp lệ';
    } else {
        // Nếu tất cả validation đều pass, tạo đối tượng AuthController
        $auth = new AuthController();
        // Gọi phương thức register để tạo tài khoản mới
        $result = $auth->register($username, $password, $email, $role);
        
        // Nếu đăng ký thành công
        if ($result['success']) {
            // Lưu thông báo thành công kèm hướng dẫn đăng nhập
            $success = $result['message'] . ' Bạn có thể đăng nhập ngay bây giờ.';
        } else {
            // Nếu đăng ký thất bại, lưu thông báo lỗi
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
    <title>Đăng ký - Hệ thống quản lý sinh viên</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 2rem 0;
        }
        .register-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .register-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        .register-form {
            padding: 2rem;
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
        .btn-register {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-weight: 600;
            transition: transform 0.3s;
        }
        .btn-register:hover {
            transform: translateY(-2px);
        }
        .password-strength {
            height: 4px;
            background: #e9ecef;
            border-radius: 2px;
            margin-top: 5px;
            overflow: hidden;
        }
        .password-strength-bar {
            height: 100%;
            transition: all 0.3s;
            border-radius: 2px;
        }
        .strength-weak { background: #dc3545; width: 25%; }
        .strength-fair { background: #ffc107; width: 50%; }
        .strength-good { background: #17a2b8; width: 75%; }
        .strength-strong { background: #28a745; width: 100%; }
        .modal-body .ratio {
            --bs-aspect-ratio: 56.25%; /* 16:9 */
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="register-container">
                    <div class="register-header">
                        <i class="fas fa-user-plus fa-3x mb-3"></i>
                        <h3>Đăng ký tài khoản</h3>
                        <p class="mb-0">Tạo tài khoản mới để sử dụng hệ thống</p>
                    </div>
                    
                    <div class="register-form">
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
                        
                        <form method="POST" id="registerForm">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="username" class="form-label">
                                        <i class="fas fa-user me-2"></i>Tên đăng nhập *
                                    </label>
                                    <input type="text" class="form-control" id="username" name="username" 
                                           value="<?php echo htmlspecialchars($username ?? ''); ?>" required>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">
                                        <i class="fas fa-envelope me-2"></i>Email *
                                    </label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="role" class="form-label">
                                    <i class="fas fa-user-tag me-2"></i>Loại tài khoản *
                                </label>
                                <select class="form-control" id="role" name="role" required>
                                    <option value="">Chọn loại tài khoản</option>
                                    <option value="student" <?php echo (($role ?? '') == 'student') ? 'selected' : ''; ?>>Sinh viên</option>
                                    <option value="teacher" <?php echo (($role ?? '') == 'teacher') ? 'selected' : ''; ?>>Giảng viên</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">
                                    <i class="fas fa-lock me-2"></i>Mật khẩu *
                                </label>
                                <input type="password" class="form-control" id="password" name="password" required>
                                <div class="password-strength">
                                    <div class="password-strength-bar" id="strengthBar"></div>
                                </div>
                                <small class="text-muted">Mật khẩu phải có ít nhất 6 ký tự</small>
                            </div>
                            
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">
                                    <i class="fas fa-lock me-2"></i>Xác nhận mật khẩu *
                                </label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>
                            
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="agree" required>
                                <label class="form-check-label" for="agree">
                                    Tôi đồng ý với <a href="#" class="text-decoration-none" data-bs-toggle="modal" data-bs-target="#termsModal">điều khoản sử dụng</a>
                                </label>
                            </div>
                            
                            <button type="submit" class="btn btn-primary btn-register w-100">
                                <i class="fas fa-user-plus me-2"></i>Đăng ký
                            </button>
                        </form>
                        
                        <div class="text-center mt-4">
                            <p class="text-muted">
                                Đã có tài khoản? 
                                <a href="login.php" class="text-decoration-none fw-bold">
                                    Đăng nhập ngay
                                </a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Điều khoản sử dụng -->
    <div class="modal fade" id="termsModal" tabindex="-1" aria-labelledby="termsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="termsModalLabel">📜 Điều Khoản Và Điều Kiện Sử Dụng Dịch Vụ 📜</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="terms-text">
                        <p><strong>Chào mừng đến với Hệ Thống Quản Lý Sinh Viên "Super STU"!</strong></p>
                        <p>Vui lòng đọc kỹ các điều khoản dưới đây trước khi tạo tài khoản. Việc bạn nhấn "Đồng ý" đồng nghĩa với việc bạn chấp nhận toàn bộ các quy định được liệt kê.</p>
                        
                        <h6>Điều 1: Định nghĩa "Người Dùng"</h6>
                        <p>"Người Dùng" là bạn, người đang đọc những dòng này, và sắp tới sẽ là một thành viên của cộng đồng chúng tôi. "Chúng tôi" là những người đã tạo ra hệ thống này và có quyền năng vô hạn (trong phạm vi hệ thống).</p>

                        <h6>Điều 2: Bảo mật tài khoản</h6>
                        <p>2.1. Bạn có trách nhiệm giữ bí mật tuyệt đối mật khẩu của mình. Không chia sẻ cho bất kỳ ai, kể cả "bạn thân" hay "người yêu". Chúng tôi không chịu trách nhiệm nếu "gấu" của bạn vào xem điểm và gây ra chiến tranh.</p>
                        <p>2.2. Nếu phát hiện tài khoản bị xâm nhập, hãy giữ bình tĩnh, pha một tách trà, và sau đó thông báo cho chúng tôi. Chúng tôi sẽ xử lý... vào một ngày đẹp trời.</p>

                        <h6>Điều 3: Quy định về nội dung</h6>
                        <p>3.1. Nghiêm cấm sử dụng hệ thống để đăng tải các nội dung vi phạm pháp luật, thuần phong mỹ tục, hoặc các meme quá "cháy".</p>
                        <p>3.2. Chúng tôi có quyền (nhưng không có nghĩa vụ) xóa bất kỳ nội dung nào mà chúng tôi cho là không phù hợp, chẳng hạn như hình ảnh dìm hàng giảng viên.</p>

                        <h6>Điều 4: Quyền sở hữu trí tuệ</h6>
                        <p>Toàn bộ mã nguồn, thiết kế, và cả những "tính năng" (bug) của hệ thống này là tài sản trí tuệ của chúng tôi. Mọi hành vi sao chép mà không ghi nguồn đều sẽ bị... nhắc nhở nhẹ nhàng.</p>
                        
                        <hr>
                        <p class="text-center fw-bold">Để hoàn tất, vui lòng xác nhận bạn đã đọc, hiểu, và sẵn sàng cho một cam kết quan trọng.</p>
                        <div class="d-grid gap-2">
                           <button class="btn btn-primary" type="button" id="reveal-button">Tôi xác nhận đã đọc và sẵn sàng cam kết</button>
                        </div>
                    </div>

                    <div id="rick-roll-container" class="text-center" style="display: none;">
                        <h6>Điều 5: Cam kết cuối cùng</h6>
                        <p>Cam kết của bạn là... không bao giờ từ bỏ điều này!</p>
                        <div class="ratio ratio-16x9">
                            <iframe src="" data-src="https://www.youtube.com/embed/dQw4w9WgXcQ?autoplay=1" title="Rick Astley - Never Gonna Give You Up" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                        </div>
                        <p class="mt-3">Chúc mừng! Bạn đã chính thức gia nhập cuộc chơi. Giờ thì quay lại đăng ký đi nhé!</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Nạp Bootstrap JS từ CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Kiểm tra độ mạnh mật khẩu khi người dùng nhập
        // Hiển thị thanh độ mạnh mật khẩu để người dùng biết mật khẩu của họ có an toàn không
        document.getElementById('password').addEventListener('input', function() {
            // Lấy giá trị mật khẩu
            const password = this.value;
            // Lấy phần tử thanh độ mạnh
            const strengthBar = document.getElementById('strengthBar');
            
            // Tính điểm độ mạnh mật khẩu (0-5 điểm)
            let strength = 0;
            if (password.length >= 6) strength++;           // Độ dài >= 6 ký tự
            if (password.match(/[a-z]/)) strength++;         // Có chữ thường
            if (password.match(/[A-Z]/)) strength++;         // Có chữ hoa
            if (password.match(/[0-9]/)) strength++;         // Có số
            if (password.match(/[^a-zA-Z0-9]/)) strength++;  // Có ký tự đặc biệt
            
            // Reset class của thanh độ mạnh
            strengthBar.className = 'password-strength-bar';
            // Xác định màu sắc và độ rộng dựa trên điểm độ mạnh
            if (strength <= 1) {
                // Yếu: màu đỏ, 25% độ rộng
                strengthBar.classList.add('strength-weak');
            } else if (strength <= 2) {
                // Trung bình: màu vàng, 50% độ rộng
                strengthBar.classList.add('strength-fair');
            } else if (strength <= 3) {
                // Khá: màu xanh dương, 75% độ rộng
                strengthBar.classList.add('strength-good');
            } else {
                // Mạnh: màu xanh lá, 100% độ rộng
                strengthBar.classList.add('strength-strong');
            }
        });
        
        // Validation form trước khi submit
        // Kiểm tra dữ liệu ở phía client để tránh submit form không hợp lệ
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            // Lấy giá trị các trường
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const agree = document.getElementById('agree').checked;
            
            // Kiểm tra mật khẩu xác nhận có khớp không
            if (password !== confirmPassword) {
                // Ngăn form submit
                e.preventDefault();
                // Hiển thị cảnh báo
                alert('Mật khẩu xác nhận không khớp');
                return false;
            }
            
            // Kiểm tra độ dài mật khẩu
            if (password.length < 6) {
                // Ngăn form submit
                e.preventDefault();
                // Hiển thị cảnh báo
                alert('Mật khẩu phải có ít nhất 6 ký tự');
                return false;
            }
            
            // Kiểm tra người dùng có đồng ý với điều khoản không
            if (!agree) {
                // Ngăn form submit
                e.preventDefault();
                // Hiển thị cảnh báo
                alert('Vui lòng đồng ý với điều khoản sử dụng');
                return false;
            }
        });
        
        // Tự động focus vào trường đầu tiên khi trang load
        document.getElementById('username').focus();

        // Xử lý modal điều khoản
        const termsModal = document.getElementById('termsModal');
        const termsText = document.getElementById('terms-text');
        const rickRollContainer = document.getElementById('rick-roll-container');
        const videoFrame = rickRollContainer.querySelector('iframe');
        const revealButton = document.getElementById('reveal-button');
        const videoSrc = videoFrame.dataset.src;

        // Khi modal được mở, reset về trạng thái ban đầu
        termsModal.addEventListener('show.bs.modal', function () {
            termsText.style.display = 'block';
            rickRollContainer.style.display = 'none';
            videoFrame.setAttribute('src', '');
        });

        // Khi người dùng nhấn nút cam kết
        revealButton.addEventListener('click', function() {
            termsText.style.display = 'none';
            rickRollContainer.style.display = 'block';
            videoFrame.setAttribute('src', videoSrc);
        });

        // Khi modal đóng, dừng video
        termsModal.addEventListener('hidden.bs.modal', function () {
            videoFrame.setAttribute('src', '');
        });
    </script>
</body>
</html>