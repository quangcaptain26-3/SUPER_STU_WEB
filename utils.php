<?php
// File này chứa các hàm tiện ích được sử dụng trong toàn bộ ứng dụng.

/**
 * Làm sạch dữ liệu để ngăn chặn tấn công XSS.
 * @param mixed $data Dữ liệu cần làm sạch.
 * @return string Dữ liệu đã được làm sạch.
 */
function sanitize($data)
{
    // Loại bỏ khoảng trắng đầu và cuối, sau đó loại bỏ các thẻ HTML và chuyển đổi ký tự đặc biệt thành HTML entities
    return htmlspecialchars(strip_tags(trim($data)));
}

/**
 * Tạo token ngẫu nhiên.
 * @param int $length Độ dài của token (mặc định 32).
 * @return string Token đã được tạo.
 */
function generateToken($length = 32)
{
    // Tạo bytes ngẫu nhiên và chuyển đổi sang dạng hex (mỗi byte = 2 ký tự hex)
    return bin2hex(random_bytes($length));
}

/**
 * Băm mật khẩu bằng thuật toán bcrypt.
 * @param string $password Mật khẩu cần băm.
 * @return string Mật khẩu đã được băm.
 */
function hashPassword($password)
{
    // Sử dụng bcrypt với cost = 12 để băm mật khẩu (cost càng cao càng an toàn nhưng chậm hơn)
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
}

/**
 * Kiểm tra xem người dùng đã đăng nhập chưa.
 * @return bool True nếu người dùng đã đăng nhập, false nếu chưa.
 */
function isLoggedIn()
{
    // Kiểm tra xem session có chứa user_id hay không
    return isset($_SESSION['user_id']);
}

/**
 * Yêu cầu người dùng phải đăng nhập. Nếu chưa, chuyển hướng đến trang đăng nhập.
 */
function requireLogin()
{
    // Nếu người dùng chưa đăng nhập
    if (!isLoggedIn()) {
        // Chuyển hướng đến trang đăng nhập
        header('Location: login.php');
        // Dừng thực thi script
        exit();
    }
}

// Định nghĩa các cấp độ vai trò người dùng
define('ROLE_STUDENT', 1);      // Vai trò sinh viên (cấp độ 1)
define('ROLE_TEACHER', 2);      // Vai trò giảng viên (cấp độ 2)
define('ROLE_ADMIN', 3);        // Vai trò quản trị viên (cấp độ 3)
define('ROLE_SUPERADMIN', 4);   // Vai trò siêu quản trị (cấp độ 4)

// Định nghĩa các quyền hạn trong hệ thống
define('PERMISSION_VIEW_STUDENTS', 'view_students');        // Quyền xem danh sách sinh viên
define('PERMISSION_ADD_STUDENTS', 'add_students');          // Quyền thêm sinh viên
define('PERMISSION_EDIT_STUDENTS', 'edit_students');        // Quyền chỉnh sửa sinh viên
define('PERMISSION_DELETE_STUDENTS', 'delete_students');    // Quyền xóa sinh viên
define('PERMISSION_VIEW_SCORES', 'view_scores');             // Quyền xem điểm
define('PERMISSION_ADD_SCORES', 'add_scores');               // Quyền thêm điểm
define('PERMISSION_EDIT_SCORES', 'edit_scores');             // Quyền chỉnh sửa điểm
define('PERMISSION_DELETE_SCORES', 'delete_scores');         // Quyền xóa điểm
define('PERMISSION_VIEW_STATISTICS', 'view_statistics');     // Quyền xem thống kê
define('PERMISSION_MANAGE_USERS', 'manage_users');            // Quyền quản lý người dùng
define('PERMISSION_EXPORT_DATA', 'export_data');             // Quyền xuất dữ liệu

/**
 * Lấy danh sách quyền hạn cho một vai trò cụ thể.
 * @param string $role Vai trò cần lấy quyền hạn.
 * @return array Mảng chứa các quyền hạn.
 */
function getRolePermissions($role)
{
    // Định nghĩa quyền hạn cho từng vai trò
    $permissions = [
        // Quyền hạn của sinh viên
        'student' => [
            PERMISSION_VIEW_STUDENTS,  // Chỉ được xem danh sách sinh viên
            PERMISSION_VIEW_SCORES      // Chỉ được xem điểm
        ],
        // Quyền hạn của giảng viên
        'teacher' => [
            PERMISSION_VIEW_STUDENTS,      // Xem danh sách sinh viên
            PERMISSION_ADD_STUDENTS,       // Thêm sinh viên
            PERMISSION_EDIT_STUDENTS,      // Chỉnh sửa sinh viên
            PERMISSION_VIEW_SCORES,        // Xem điểm
            PERMISSION_ADD_SCORES,         // Thêm điểm
            PERMISSION_EDIT_SCORES,        // Chỉnh sửa điểm
            PERMISSION_VIEW_STATISTICS,    // Xem thống kê
            PERMISSION_EXPORT_DATA         // Xuất dữ liệu
        ],
        // Quyền hạn của quản trị viên
        'admin' => [
            PERMISSION_VIEW_STUDENTS,      // Xem danh sách sinh viên
            PERMISSION_ADD_STUDENTS,       // Thêm sinh viên
            PERMISSION_EDIT_STUDENTS,      // Chỉnh sửa sinh viên
            PERMISSION_DELETE_STUDENTS,    // Xóa sinh viên
            PERMISSION_VIEW_SCORES,        // Xem điểm
            PERMISSION_ADD_SCORES,         // Thêm điểm
            PERMISSION_EDIT_SCORES,        // Chỉnh sửa điểm
            PERMISSION_DELETE_SCORES,      // Xóa điểm
            PERMISSION_VIEW_STATISTICS,    // Xem thống kê
            PERMISSION_EXPORT_DATA         // Xuất dữ liệu
        ],
        // Quyền hạn của siêu quản trị (có tất cả quyền)
        'superadmin' => [
            PERMISSION_VIEW_STUDENTS,      // Xem danh sách sinh viên
            PERMISSION_ADD_STUDENTS,       // Thêm sinh viên
            PERMISSION_EDIT_STUDENTS,      // Chỉnh sửa sinh viên
            PERMISSION_DELETE_STUDENTS,    // Xóa sinh viên
            PERMISSION_VIEW_SCORES,        // Xem điểm
            PERMISSION_ADD_SCORES,         // Thêm điểm
            PERMISSION_EDIT_SCORES,        // Chỉnh sửa điểm
            PERMISSION_DELETE_SCORES,      // Xóa điểm
            PERMISSION_VIEW_STATISTICS,    // Xem thống kê
            PERMISSION_MANAGE_USERS,       // Quản lý người dùng (chỉ superadmin có)
            PERMISSION_EXPORT_DATA         // Xuất dữ liệu
        ]
    ];

    // Trả về mảng quyền hạn của vai trò, nếu không tìm thấy thì trả về mảng rỗng
    return $permissions[$role] ?? [];
}

/**
 * Kiểm tra xem người dùng hiện tại có ít nhất vai trò yêu cầu hay không.
 * @param string $requiredRole Vai trò yêu cầu.
 * @return bool True nếu người dùng có vai trò yêu cầu, false nếu không.
 */
function hasRole($requiredRole)
{
    // Nếu chưa đăng nhập thì không có quyền
    if (!isLoggedIn()) return false;

    // Định nghĩa hệ thống phân cấp vai trò (số càng lớn quyền càng cao)
    $roleHierarchy = [
        'student' => ROLE_STUDENT,        // Cấp độ 1
        'teacher' => ROLE_TEACHER,        // Cấp độ 2
        'admin' => ROLE_ADMIN,            // Cấp độ 3
        'superadmin' => ROLE_SUPERADMIN   // Cấp độ 4
    ];

    // Lấy vai trò hiện tại của người dùng từ session
    $userRole = $_SESSION['role'];
    // Lấy cấp độ của người dùng hiện tại (nếu không tìm thấy thì mặc định là 0)
    $userLevel = $roleHierarchy[$userRole] ?? 0;
    // Lấy cấp độ yêu cầu (nếu không tìm thấy thì mặc định là 0)
    $requiredLevel = $roleHierarchy[$requiredRole] ?? 0;

    // Kiểm tra xem cấp độ người dùng có >= cấp độ yêu cầu không
    return $userLevel >= $requiredLevel;
}

/**
 * Kiểm tra xem người dùng hiện tại có quyền cụ thể hay không.
 * @param string $permission Quyền cần kiểm tra.
 * @return bool True nếu người dùng có quyền, false nếu không.
 */
function hasPermission($permission)
{
    // Nếu chưa đăng nhập thì không có quyền
    if (!isLoggedIn()) return false;

    // Lấy vai trò của người dùng từ session
    $userRole = $_SESSION['role'];
    // Lấy danh sách quyền hạn của vai trò đó
    $userPermissions = getRolePermissions($userRole);

    // Kiểm tra xem quyền yêu cầu có trong danh sách quyền của người dùng không
    return in_array($permission, $userPermissions);
}

/**
 * Yêu cầu người dùng phải có vai trò cụ thể. Nếu không, chuyển hướng với thông báo lỗi.
 * @param string $role Vai trò yêu cầu.
 */
function requireRole($role)
{
    // Đảm bảo người dùng đã đăng nhập
    requireLogin();
    // Nếu người dùng không có vai trò yêu cầu
    if (!hasRole($role)) {
        // Lưu thông báo lỗi vào session
        $_SESSION['error'] = 'Bạn không có quyền truy cập trang này';
        // Chuyển hướng đến trang chủ với thông báo lỗi
        header('Location: ../public/index.php?error=access_denied');
        // Dừng thực thi script
        exit();
    }
}

/**
 * Yêu cầu người dùng phải có quyền cụ thể. Nếu không, chuyển hướng với thông báo lỗi.
 * @param string $permission Quyền yêu cầu.
 */
function requirePermission($permission)
{
    // Đảm bảo người dùng đã đăng nhập
    requireLogin();
    // Nếu người dùng không có quyền yêu cầu
    if (!hasPermission($permission)) {
        // Lưu thông báo lỗi vào session
        $_SESSION['error'] = 'Bạn không có quyền thực hiện hành động này';
        // Chuyển hướng đến trang chủ với thông báo lỗi
        header('Location: ../public/index.php?error=permission_denied');
        // Dừng thực thi script
        exit();
    }
}

/**
 * Bí danh của hàm hasPermission, kiểm tra xem người dùng có thể truy cập tính năng hay không.
 * @param string $permission Quyền cần kiểm tra.
 * @return bool True nếu người dùng có quyền, false nếu không.
 */
function canAccess($permission)
{
    // Gọi hàm hasPermission để kiểm tra quyền
    return hasPermission($permission);
}

/**
 * Lấy tên hiển thị cho một vai trò.
 * @param string $role Vai trò.
 * @return string Tên hiển thị.
 */
function getRoleDisplayName($role)
{
    // Định nghĩa tên hiển thị cho từng vai trò bằng tiếng Việt
    $roleNames = [
        'student' => 'Sinh viên',        // Hiển thị "Sinh viên"
        'teacher' => 'Giảng viên',       // Hiển thị "Giảng viên"
        'admin' => 'Quản trị viên',      // Hiển thị "Quản trị viên"
        'superadmin' => 'Siêu quản trị'  // Hiển thị "Siêu quản trị"
    ];

    // Trả về tên hiển thị, nếu không tìm thấy thì trả về "Không xác định"
    return $roleNames[$role] ?? 'Không xác định';
}

/**
 * Lấy class CSS badge cho một vai trò.
 * @param string $role Vai trò.
 * @return string Class CSS.
 */
function getRoleBadgeClass($role)
{
    // Định nghĩa class CSS badge cho từng vai trò (dùng cho Bootstrap)
    $badgeClasses = [
        'student' => 'bg-primary',      // Màu xanh dương cho sinh viên
        'teacher' => 'bg-success',      // Màu xanh lá cho giảng viên
        'admin' => 'bg-warning',        // Màu vàng cho quản trị viên
        'superadmin' => 'bg-danger'     // Màu đỏ cho siêu quản trị
    ];

    // Trả về class CSS, nếu không tìm thấy thì trả về 'bg-secondary' (màu xám)
    return $badgeClasses[$role] ?? 'bg-secondary';
}

/**
 * Định dạng chuỗi ngày tháng.
 * @param string $date Ngày cần định dạng.
 * @return string Ngày đã được định dạng.
 */
function formatDate($date)
{
    // Chuyển đổi chuỗi ngày thành timestamp rồi định dạng theo định dạng dd/mm/yyyy
    return date('d/m/Y', strtotime($date));
}

/**
 * Xử lý upload file.
 * @param array $file File từ mảng $_FILES.
 * @param string $uploadDir Thư mục để upload file (mặc định 'uploads/avatars/').
 * @return array Mảng chứa thông tin thành công/thất bại và thông báo/tên file.
 */
function uploadFile($file, $uploadDir = 'uploads/avatars/')
{
    // Kiểm tra xem thư mục upload có tồn tại không
    if (!file_exists($uploadDir)) {
        // Nếu không tồn tại thì tạo thư mục với quyền 777 (đọc, ghi, thực thi cho tất cả)
        mkdir($uploadDir, 0777, true);
    }

    // Định nghĩa các loại file được phép upload (chỉ ảnh)
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    // Định nghĩa kích thước tối đa là 5MB (5 * 1024 * 1024 bytes)
    $maxSize = 5 * 1024 * 1024; // 5MB

    // Kiểm tra loại file có nằm trong danh sách cho phép không
    if (!in_array($file['type'], $allowedTypes)) {
        // Nếu không đúng loại thì trả về lỗi
        return ['success' => false, 'message' => 'Chỉ cho phép file ảnh JPG, PNG, GIF'];
    }

    // Kiểm tra kích thước file có vượt quá giới hạn không
    if ($file['size'] > $maxSize) {
        // Nếu quá lớn thì trả về lỗi
        return ['success' => false, 'message' => 'File quá lớn (tối đa 5MB)'];
    }

    // Lấy phần mở rộng của file (ví dụ: jpg, png, gif)
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    // Tạo tên file mới bằng cách kết hợp ID duy nhất với phần mở rộng
    $filename = uniqid() . '.' . $extension;
    // Tạo đường dẫn đầy đủ đến file
    $filepath = $uploadDir . $filename;

    // Di chuyển file từ thư mục tạm lên thư mục đích
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        // Nếu thành công, trả về thông tin thành công và tên file
        return ['success' => true, 'filename' => $filename];
    } else {
        // Nếu thất bại, trả về thông báo lỗi
        return ['success' => false, 'message' => 'Lỗi upload file'];
    }
}

/**
 * Xóa một file.
 * @param string $filepath Đường dẫn đến file cần xóa.
 */
function deleteFile($filepath)
{
    // Kiểm tra xem file có tồn tại không
    if (file_exists($filepath)) {
        // Nếu tồn tại thì xóa file
        unlink($filepath);
    }
}

/**
 * Gửi email.
 * @param string $to Địa chỉ email người nhận.
 * @param string $subject Tiêu đề email.
 * @param string $message Nội dung email.
 * @return bool True nếu email được gửi thành công, false nếu không.
 */
function sendEmail($to, $subject, $message)
{
    // Lưu ý: Đây là hàm gửi email đơn giản. Trong môi trường production, nên sử dụng thư viện như PHPMailer.
    // Thiết lập header email - địa chỉ người gửi
    $headers = "From: noreply@studentmanagement.com\r\n";
    // Thiết lập header - định dạng HTML và bảng mã UTF-8
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

    // Gửi email và trả về kết quả (true/false)
    return mail($to, $subject, $message, $headers);
}

/**
 * Kiểm tra xem một sinh viên có thể xem dữ liệu của chính mình hay không.
 * @param int $studentId ID của sinh viên.
 * @return bool True nếu người dùng có thể xem dữ liệu, false nếu không.
 */
function canViewStudent($studentId)
{
    // Nếu chưa đăng nhập thì không có quyền
    if (!isLoggedIn()) return false;

    // Lấy vai trò của người dùng từ session
    $userRole = $_SESSION['role'];

    // Quản trị viên, giảng viên và siêu quản trị có thể xem tất cả dữ liệu sinh viên
    if (in_array($userRole, ['superadmin', 'admin', 'teacher'])) {
        return true;
    }

    // Một sinh viên chỉ có thể xem dữ liệu của chính mình.
    // Điều này giả định rằng tài khoản sinh viên được liên kết với student_id trong bảng users.
    // Để điều này hoạt động, cần thêm cột 'student_id' vào bảng 'users'.
    // Tạm thời cho phép sinh viên xem tất cả dữ liệu.
    return true;
}

/**
 * Tạo token CSRF (Cross-Site Request Forgery).
 * @return string Token CSRF.
 */
function generateCSRFToken()
{
    // Kiểm tra xem session đã có token CSRF chưa
    if (!isset($_SESSION['csrf_token'])) {
        // Nếu chưa có thì tạo token mới bằng cách tạo 32 bytes ngẫu nhiên và chuyển sang hex
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    // Trả về token CSRF (tạo mới hoặc đã có sẵn)
    return $_SESSION['csrf_token'];
}

/**
 * Xác minh token CSRF.
 * @param string $token Token cần xác minh.
 * @return bool True nếu token hợp lệ, false nếu không.
 */
function verifyCSRFToken($token)
{
    // Kiểm tra xem session có token CSRF không
    if (!isset($_SESSION['csrf_token'])) {
        // Nếu không có thì token không hợp lệ
        return false;
    }
    // So sánh token trong session với token được truyền vào bằng hàm hash_equals (an toàn hơn ==)
    return hash_equals($_SESSION['csrf_token'], $token);
}
