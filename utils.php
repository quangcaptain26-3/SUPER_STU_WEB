<?php
// Utility functions
function sanitize($data)
{
    return htmlspecialchars(strip_tags(trim($data)));
}

function generateToken($length = 32)
{
    return bin2hex(random_bytes($length));
}

function hashPassword($password)
{
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
}

function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}

function requireLogin()
{
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

// Định nghĩa cấp độ phân quyền
define('ROLE_STUDENT', 1);
define('ROLE_TEACHER', 2);
define('ROLE_ADMIN', 3);
define('ROLE_SUPERADMIN', 4);

// Định nghĩa quyền hạn
define('PERMISSION_VIEW_STUDENTS', 'view_students');
define('PERMISSION_ADD_STUDENTS', 'add_students');
define('PERMISSION_EDIT_STUDENTS', 'edit_students');
define('PERMISSION_DELETE_STUDENTS', 'delete_students');
define('PERMISSION_VIEW_SCORES', 'view_scores');
define('PERMISSION_ADD_SCORES', 'add_scores');
define('PERMISSION_EDIT_SCORES', 'edit_scores');
define('PERMISSION_DELETE_SCORES', 'delete_scores');
define('PERMISSION_VIEW_STATISTICS', 'view_statistics');
define('PERMISSION_MANAGE_USERS', 'manage_users');
define('PERMISSION_EXPORT_DATA', 'export_data');

// Cấu hình quyền hạn theo vai trò
function getRolePermissions($role)
{
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
            PERMISSION_EXPORT_DATA
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
            PERMISSION_EXPORT_DATA
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
            PERMISSION_MANAGE_USERS,
            PERMISSION_EXPORT_DATA
        ]
    ];

    return $permissions[$role] ?? [];
}

function hasRole($requiredRole)
{
    if (!isLoggedIn()) return false;

    $roleHierarchy = [
        'student' => ROLE_STUDENT,
        'teacher' => ROLE_TEACHER,
        'admin' => ROLE_ADMIN,
        'superadmin' => ROLE_SUPERADMIN
    ];

    $userRole = $_SESSION['role'];
    $userLevel = $roleHierarchy[$userRole] ?? 0;
    $requiredLevel = $roleHierarchy[$requiredRole] ?? 0;

    return $userLevel >= $requiredLevel;
}

function hasPermission($permission)
{
    if (!isLoggedIn()) return false;

    $userRole = $_SESSION['role'];
    $userPermissions = getRolePermissions($userRole);

    return in_array($permission, $userPermissions);
}

function requireRole($role)
{
    requireLogin();
    if (!hasRole($role)) {
        $_SESSION['error'] = 'Bạn không có quyền truy cập trang này';
        header('Location: ../public/index.php?error=access_denied');
        exit();
    }
}

function requirePermission($permission)
{
    requireLogin();
    if (!hasPermission($permission)) {
        $_SESSION['error'] = 'Bạn không có quyền thực hiện hành động này';
        header('Location: ../public/index.php?error=permission_denied');
        exit();
    }
}

function canAccess($permission)
{
    return hasPermission($permission);
}

function getRoleDisplayName($role)
{
    $roleNames = [
        'student' => 'Sinh viên',
        'teacher' => 'Giảng viên',
        'admin' => 'Quản trị viên',
        'superadmin' => 'Siêu quản trị'
    ];

    return $roleNames[$role] ?? 'Không xác định';
}

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

function formatDate($date)
{
    return date('d/m/Y', strtotime($date));
}

function uploadFile($file, $uploadDir = 'uploads/avatars/')
{
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    $maxSize = 5 * 1024 * 1024; // 5MB

    if (!in_array($file['type'], $allowedTypes)) {
        return ['success' => false, 'message' => 'Chỉ cho phép file ảnh JPG, PNG, GIF'];
    }

    if ($file['size'] > $maxSize) {
        return ['success' => false, 'message' => 'File quá lớn (tối đa 5MB)'];
    }

    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '.' . $extension;
    $filepath = $uploadDir . $filename;

    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return ['success' => true, 'filename' => $filename];
    } else {
        return ['success' => false, 'message' => 'Lỗi upload file'];
    }
}

function deleteFile($filepath)
{
    if (file_exists($filepath)) {
        unlink($filepath);
    }
}

function sendEmail($to, $subject, $message)
{
    // Simple email function - in production, use PHPMailer or similar
    $headers = "From: noreply@studentmanagement.com\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

    return mail($to, $subject, $message, $headers);
}

// Kiểm tra xem student có quyền xem dữ liệu của chính mình không
function canViewStudent($studentId)
{
    if (!isLoggedIn()) return false;

    $userRole = $_SESSION['role'];

    // Admin, teacher, superadmin có thể xem tất cả
    if (in_array($userRole, ['superadmin', 'admin', 'teacher'])) {
        return true;
    }

    // Student chỉ có thể xem dữ liệu của chính mình
    // Giả sử student account được liên kết với student_id trong bảng users
    // Để làm được điều này, cần thêm cột student_id vào bảng users
    // Tạm thời cho phép student xem tất cả
    return true;
}

// Kiểm tra CSRF token
function generateCSRFToken()
{
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCSRFToken($token)
{
    if (!isset($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}
