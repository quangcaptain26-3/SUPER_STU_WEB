<?php
// File `utils.php` (utilities) chứa các hàm và hằng số tiện ích, được sử dụng lặp đi lặp lại trong toàn bộ ứng dụng.
// Việc gom chúng vào một file giúp tái sử dụng code, dễ bảo trì và làm cho code ở các file khác gọn gàng hơn.

// --- CÁC HÀM BẢO MẬT VÀ TIỆN ÍCH CHUNG ---

/**
 * Làm sạch dữ liệu đầu vào để ngăn chặn tấn công XSS (Cross-Site Scripting).
 * @param mixed $data Dữ liệu thô từ người dùng (ví dụ: $_POST['username']).
 * @return string Dữ liệu đã được làm sạch và an toàn để hiển thị trên HTML.
 */
function sanitize($data)
{
    // 1. `trim($data)`: Loại bỏ các khoảng trắng thừa ở đầu và cuối chuỗi.
    // 2. `strip_tags(...)`: Loại bỏ tất cả các thẻ HTML và PHP khỏi chuỗi.
    // 3. `htmlspecialchars(...)`: Chuyển đổi các ký tự đặc biệt thành các thực thể HTML. Ví dụ: `<` thành `&lt;`.
    return htmlspecialchars(strip_tags(trim($data)));
}

/**
 * Tạo một chuỗi token ngẫu nhiên, an toàn, thường dùng cho việc reset mật khẩu hoặc CSRF.
 * @param int $length Độ dài của chuỗi byte ngẫu nhiên (mặc định là 32).
 * @return string Một chuỗi hex ngẫu nhiên có độ dài gấp đôi $length (ví dụ: 64 ký tự).
 */
function generateToken($length = 32)
{
    // `random_bytes($length)`: Tạo ra một chuỗi byte ngẫu nhiên, an toàn về mặt mật mã học.
    // `bin2hex(...)`: Chuyển đổi chuỗi byte đó sang dạng biểu diễn thập lục phân (hexadecimal).
    return bin2hex(random_bytes($length));
}

/**
 * Băm mật khẩu bằng thuật toán SHA256. Đây là một hàm băm nhanh, một chiều.
 * @param string $password Mật khẩu dạng văn bản thô.
 * @return string Chuỗi hash SHA256 có độ dài 64 ký tự.
 */
function hashPassword($password)
{
    // `hash('sha256', $password)`: Áp dụng thuật toán băm SHA256 cho mật khẩu.
    // LƯU Ý: SHA256 nhanh, nhưng trong các ứng dụng hiện đại, `password_hash()` với BCRYPT/Argon2 được khuyến khích hơn.
    return hash('sha256', $password);
}

// --- CÁC HÀM QUẢN LÝ PHIÊN ĐĂNG NHẬP ---

/**
 * Kiểm tra xem người dùng đã đăng nhập và có một phiên làm việc hợp lệ hay chưa.
 * @return bool Trả về `true` nếu đã đăng nhập, ngược lại `false`.
 */
function isLoggedIn()
{
    // `isset($_SESSION['user_id'])`: Kiểm tra sự tồn tại của biến 'user_id' trong session.
    // Đây là biến được gán sau khi người dùng đăng nhập thành công.
    return isset($_SESSION['user_id']);
}

/**
 * Một hàm "guard", yêu cầu người dùng phải đăng nhập để tiếp tục.
 * Nếu chưa đăng nhập, sẽ bị chuyển hướng ngay lập tức.
 */
function requireLogin()
{
    // Nếu `isLoggedIn()` trả về false (chưa đăng nhập).
    if (!isLoggedIn()) {
        // `header('Location: login.php')`: Gửi một HTTP header để chuyển hướng trình duyệt đến trang đăng nhập.
        header('Location: login.php');
        // `exit()`: Dừng ngay lập tức việc thực thi script để ngăn chặn code phía sau chạy.
        exit();
    }
}

// --- ĐỊNH NGHĨA VAI TRÒ VÀ QUYỀN HẠN (HỆ THỐNG RBAC) ---

// Định nghĩa các hằng số cho vai trò. Dùng số để có thể so sánh cấp bậc.
define('ROLE_STUDENT', 1);      // Vai trò sinh viên, cấp thấp nhất.
define('ROLE_TEACHER', 2);      // Vai trò giảng viên.
define('ROLE_ADMIN', 3);        // Vai trò quản trị viên.
define('ROLE_SUPERADMIN', 4);   // Vai trò siêu quản trị, cấp cao nhất.

// Định nghĩa các hằng số cho quyền hạn. Dùng chuỗi để dễ đọc và dễ quản lý.
define('PERMISSION_VIEW_STUDENTS', 'view_students');
define('PERMISSION_ADD_STUDENTS', 'add_students');
define('PERMISSION_EDIT_STUDENTS', 'edit_students');
define('PERMISSION_DELETE_STUDENTS', 'delete_students');
define('PERMISSION_VIEW_SCORES', 'view_scores');
define('PERMISSION_ADD_SCORES', 'add_scores');
define('PERMISSION_EDIT_SCORES', 'edit_scores');
define('PERMISSION_DELETE_SCORES', 'delete_scores');
define('PERMISSION_VIEW_STATISTICS', 'view_statistics');
define('PERMISSION_MANAGE_USERS', 'manage_users'); // Quyền đặc biệt chỉ Super Admin có.
define('PERMISSION_EXPORT_DATA', 'export_data');
define('PERMISSION_VIEW_SUBJECTS', 'view_subjects');
define('PERMISSION_ADD_SUBJECTS', 'add_subjects');
define('PERMISSION_EDIT_SUBJECTS', 'edit_subjects');
define('PERMISSION_DELETE_SUBJECTS', 'delete_subjects');
define('PERMISSION_VIEW_ENROLLMENTS', 'view_enrollments');
define('PERMISSION_ADD_ENROLLMENTS', 'add_enrollments');
define('PERMISSION_DELETE_ENROLLMENTS', 'delete_enrollments');

/**
 * Trái tim của hệ thống RBAC. Ánh xạ mỗi vai trò tới một danh sách các quyền hạn cụ thể.
 * @param string $role Tên vai trò (ví dụ: 'teacher').
 * @return array Mảng chứa các chuỗi quyền hạn của vai trò đó.
 */
function getRolePermissions($role)
{
    // Mảng kết hợp (associative array) định nghĩa cấu trúc phân quyền.
    $permissions = [
        'student' => [
            PERMISSION_VIEW_STUDENTS,
            PERMISSION_VIEW_SCORES
        ],
        'teacher' => [
            PERMISSION_VIEW_STUDENTS,
            PERMISSION_ADD_STUDENTS,
            PERMISSION_EDIT_STUDENTS,
            PERMISSION_VIEW_SCORES,
            PERMISSION_ADD_SCORES,
            PERMISSION_EDIT_SCORES,
            PERMISSION_VIEW_STATISTICS,
            PERMISSION_EXPORT_DATA,
            PERMISSION_VIEW_SUBJECTS,
            PERMISSION_VIEW_ENROLLMENTS,
            PERMISSION_ADD_ENROLLMENTS
        ],
        'admin' => [
            PERMISSION_VIEW_STUDENTS,
            PERMISSION_ADD_STUDENTS,
            PERMISSION_EDIT_STUDENTS,
            PERMISSION_DELETE_STUDENTS,
            PERMISSION_VIEW_SCORES,
            PERMISSION_ADD_SCORES,
            PERMISSION_EDIT_SCORES,
            PERMISSION_DELETE_SCORES,
            PERMISSION_VIEW_STATISTICS,
            PERMISSION_EXPORT_DATA,
            PERMISSION_VIEW_SUBJECTS,
            PERMISSION_ADD_SUBJECTS,
            PERMISSION_EDIT_SUBJECTS,
            PERMISSION_DELETE_SUBJECTS,
            PERMISSION_VIEW_ENROLLMENTS,
            PERMISSION_ADD_ENROLLMENTS,
            PERMISSION_DELETE_ENROLLMENTS
        ],
        'superadmin' => [
            PERMISSION_VIEW_STUDENTS,
            PERMISSION_ADD_STUDENTS,
            PERMISSION_EDIT_STUDENTS,
            PERMISSION_DELETE_STUDENTS,
            PERMISSION_VIEW_SCORES,
            PERMISSION_ADD_SCORES,
            PERMISSION_EDIT_SCORES,
            PERMISSION_DELETE_SCORES,
            PERMISSION_VIEW_STATISTICS,
            PERMISSION_MANAGE_USERS, // Quyền quản lý người dùng.
            PERMISSION_EXPORT_DATA,
            PERMISSION_VIEW_SUBJECTS,
            PERMISSION_ADD_SUBJECTS,
            PERMISSION_EDIT_SUBJECTS,
            PERMISSION_DELETE_SUBJECTS,
            PERMISSION_VIEW_ENROLLMENTS,
            PERMISSION_ADD_ENROLLMENTS,
            PERMISSION_DELETE_ENROLLMENTS
        ]
    ];

    // `?? []`: Toán tử Null Coalescing. Nếu vai trò không tồn tại trong mảng, trả về một mảng rỗng để tránh lỗi.
    return $permissions[$role] ?? [];
}

// --- CÁC HÀM KIỂM TRA QUYỀN ---

/**
 * Kiểm tra xem người dùng hiện tại có vai trò từ một cấp bậc nào đó trở lên hay không.
 * @param string $requiredRole Vai trò tối thiểu yêu cầu.
 * @return bool True nếu người dùng có vai trò bằng hoặc cao hơn, ngược lại false.
 */
function hasRole($requiredRole)
{
    if (!isLoggedIn()) return false;

    // Mảng ánh xạ vai trò (chuỗi) sang cấp bậc (số).
    $roleHierarchy = [
        'student' => ROLE_STUDENT,
        'teacher' => ROLE_TEACHER,
        'admin' => ROLE_ADMIN,
        'superadmin' => ROLE_SUPERADMIN
    ];

    $userRole = $_SESSION['role'];
    $userLevel = $roleHierarchy[$userRole] ?? 0;
    $requiredLevel = $roleHierarchy[$requiredRole] ?? 0;

    // So sánh cấp bậc số, cho phép phân cấp quyền. Ví dụ: Admin (3) có quyền của Teacher (2).
    return $userLevel >= $requiredLevel;
}

/**
 * Kiểm tra xem người dùng hiện tại có một quyền hạn cụ thể hay không.
 * @param string $permission Chuỗi quyền hạn cần kiểm tra (ví dụ: 'delete_students').
 * @return bool True nếu người dùng có quyền, ngược lại false.
 */
function hasPermission($permission)
{
    if (!isLoggedIn()) return false;

    // Lấy vai trò của người dùng từ phiên làm việc hiện tại.
    $userRole = $_SESSION['role'];
    // Dùng hàm getRolePermissions để lấy danh sách các quyền của vai trò đó.
    $userPermissions = getRolePermissions($userRole);

    // `in_array()`: Kiểm tra xem quyền hạn yêu cầu có nằm trong mảng quyền của người dùng không.
    return in_array($permission, $userPermissions);
}

/**
 * Hàm "guard" yêu cầu vai trò cụ thể. Chuyển hướng nếu không đáp ứng.
 * @param string $role Vai trò yêu cầu.
 */
function requireRole($role)
{
    requireLogin();
    if (!hasRole($role)) {
        $_SESSION['error'] = 'Bạn không có quyền truy cập trang này';
        header('Location: index.php?error=access_denied');
        exit();
    }
}

/**
 * Hàm "guard" yêu cầu quyền hạn cụ thể. Chuyển hướng nếu không đáp ứng.
 * Đây là hàm bảo vệ chính được dùng ở đầu các trang/file xử lý nghiệp vụ.
 * @param string $permission Quyền hạn yêu cầu.
 */
function requirePermission($permission)
{
    requireLogin();
    if (!hasPermission($permission)) {
        $_SESSION['error'] = 'Bạn không có quyền thực hiện hành động này';
        header('Location: index.php?error=permission_denied');
        exit();
    }
}

/**
 * Một tên gọi khác (alias) cho `hasPermission`, thường dùng trong template để code dễ đọc hơn.
 * @param string $permission Quyền cần kiểm tra.
 * @return bool
 */
function canAccess($permission)
{
    return hasPermission($permission);
}

// --- CÁC HÀM TIỆN ÍCH CHO GIAO DIỆN (UI HELPERS) ---

/**
 * Lấy tên hiển thị tiếng Việt cho một vai trò.
 * @param string $role Tên vai trò (VD: 'superadmin').
 * @return string Tên hiển thị (VD: 'Siêu quản trị').
 */
function getRoleDisplayName($role)
{
    $roleNames = [
        'student' => 'Nhân viên',
        'teacher' => 'Giảng viên',
        'admin' => 'Quản trị viên',
        'superadmin' => 'Siêu quản trị'
    ];
    return $roleNames[$role] ?? 'Không xác định';
}

/**
 * Lấy class CSS của Bootstrap để hiển thị badge màu cho vai trò.
 * @param string $role Tên vai trò.
 * @return string Class CSS (VD: 'bg-danger').
 */
function getRoleBadgeClass($role)
{
    $badgeClasses = [
        'student' => 'bg-primary',
        'teacher' => 'bg-success',
        'admin' => 'bg-warning',
        'superadmin' => 'bg-danger'
    ];
    return $badgeClasses[$role] ?? 'bg-secondary';
}

/**
 * Định dạng một chuỗi ngày tháng (VD: '2025-12-10') sang định dạng thân thiện hơn (VD: '10/12/2025').
 * @param string $date Chuỗi ngày tháng đầu vào.
 * @return string Ngày đã được định dạng.
 */
function formatDate($date)
{
    // `strtotime($date)`: Chuyển đổi chuỗi ngày tháng sang một Unix timestamp (số giây).
    // `date('d/m/Y', ...)`: Định dạng lại timestamp đó theo cấu trúc Ngày/Tháng/Năm.
    return date('d/m/Y', strtotime($date));
}

// --- CÁC HÀM XỬ LÝ FILE ---

/**
 * Xử lý việc tải file lên một cách an toàn.
 * @param array $file Dữ liệu file từ biến toàn cục `$_FILES`.
 * @param string $uploadDir Thư mục đích để lưu file.
 * @return array Mảng kết quả chứa `success` (true/false) và `message` hoặc `filename`.
 */
function uploadFile($file, $uploadDir = 'uploads/avatars/')
{
    // Nếu thư mục đích không tồn tại.
    if (!file_exists($uploadDir)) {
        // Tạo thư mục đó. `0777` là quyền truy cập (rất rộng, cần cẩn trọng trên server production). `true` cho phép tạo lồng nhau.
        mkdir($uploadDir, 0777, true);
    }

    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif']; // Chỉ cho phép các loại file ảnh phổ biến.
    $maxSize = 5 * 1024 * 1024; // Giới hạn kích thước tối đa là 5MB.

    // Kiểm tra xem kiểu MIME của file có nằm trong danh sách cho phép không.
    if (!in_array($file['type'], $allowedTypes)) {
        return ['success' => false, 'message' => 'Chỉ cho phép file ảnh JPG, PNG, GIF'];
    }

    // Kiểm tra kích thước file (tính bằng byte).
    if ($file['size'] > $maxSize) {
        return ['success' => false, 'message' => 'File quá lớn (tối đa 5MB)'];
    }

    // Lấy phần mở rộng của file gốc (VD: 'png').
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    // Tạo một tên file mới hoàn toàn duy nhất bằng `uniqid()` để tránh ghi đè lên file đã có.
    $filename = uniqid() . '.' . $extension;
    // Tạo đường dẫn đầy đủ đến file mới.
    $filepath = $uploadDir . $filename;

    // Di chuyển file đã tải lên từ thư mục tạm của server đến thư mục đích.
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return ['success' => true, 'filename' => $filename]; // Trả về tên file mới để lưu vào CSDL.
    } else {
        return ['success' => false, 'message' => 'Lỗi khi di chuyển file'];
    }
}

/**
 * Xóa một file khỏi hệ thống.
 * @param string $filepath Đường dẫn đầy đủ đến file cần xóa.
 */
function deleteFile($filepath)
{
    // `file_exists()`: Kiểm tra xem file có thực sự tồn tại không để tránh lỗi.
    if (file_exists($filepath)) {
        // `unlink()`: Xóa file.
        unlink($filepath);
    }
}

// --- CÁC HÀM TIỆN ÍCH KHÁC ---

/**
 * Kiểm tra xem ứng dụng đang chạy ở môi trường phát triển (localhost) hay không.
 * @return bool True nếu là môi trường phát triển.
 */
function isDevelopmentMode()
{
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $serverName = $_SERVER['SERVER_NAME'] ?? '';
    
    // Kiểm tra nếu host/server name là 'localhost' hoặc địa chỉ IP loopback '127.0.0.1'.
    return (
        strpos($host, 'localhost') !== false ||
        strpos($host, '127.0.0.1') !== false ||
        strpos($serverName, 'localhost') !== false ||
        strpos($serverName, '127.0.0.1') !== false ||
        $host === '127.0.0.1' ||
        $serverName === 'localhost'
    );
}

/**
 * Gửi email (dạng đơn giản).
 * @param string $to Địa chỉ email người nhận.
 * @param string $subject Tiêu đề email.
 * @param string $message Nội dung email (có thể chứa HTML).
 * @return bool True nếu hàm `mail()` được gọi thành công.
 */
function sendEmail($to, $subject, $message)
{
    // Trong môi trường dev, giả lập việc gửi email thành công để không cần cấu hình mail server.
    if (isDevelopmentMode()) {
        return true; 
    }
    
    // Thiết lập các header cần thiết cho email.
    $headers = "From: noreply@studentmanagement.com\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n"; // Cho phép gửi nội dung dạng HTML và hỗ trợ UTF-8.

    // Sử dụng hàm `mail()` có sẵn của PHP.
    return mail($to, $subject, $message, $headers);
}

/**
 * Kiểm tra quyền sở hữu tài nguyên (chưa được triển khai đầy đủ).
 * @param int $studentId ID của sinh viên (tài nguyên).
 * @return bool
 */
function canViewStudent($studentId)
{
    if (!isLoggedIn()) return false;
    $userRole = $_SESSION['role'];

    // Admin, Teacher, Superadmin có thể xem mọi thứ.
    if (in_array($userRole, ['superadmin', 'admin', 'teacher'])) {
        return true;
    }

    // Logic cho sinh viên chỉ xem của chính mình cần được phát triển thêm.
    // Hiện tại đang cho phép xem tất cả để đơn giản hóa.
    return true;
}

// --- CÁC HÀM BẢO MẬT CSRF ---

/**
 * Tạo và lưu một token CSRF vào session nếu nó chưa tồn tại.
 * @return string Token CSRF.
 */
function generateCSRFToken()
{
    // Nếu trong session chưa có token.
    if (!isset($_SESSION['csrf_token'])) {
        // Tạo một token ngẫu nhiên và lưu vào session. Token này sẽ tồn tại suốt phiên làm việc.
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    // Trả về token đã lưu.
    return $_SESSION['csrf_token'];
}

/**
 * Xác minh một token được gửi từ form có khớp với token trong session hay không.
 * @param string $token Token từ `$_POST` hoặc `$_GET`.
 * @return bool True nếu token hợp lệ, ngược lại false.
 */
function verifyCSRFToken($token)
{
    // Nếu trong session không có token để so sánh thì chắc chắn là không hợp lệ.
    if (!isset($_SESSION['csrf_token'])) {
        return false;
    }
    // `hash_equals()`: Hàm so sánh chuỗi an toàn, chống lại timing attack.
    // Nó luôn mất một khoảng thời gian như nhau để so sánh, dù các chuỗi khác nhau ở vị trí nào.
    return hash_equals($_SESSION['csrf_token'], $token);
}
