<?php
// This file contains utility functions used throughout the application.

/**
 * Sanitizes data to prevent XSS attacks.
 * @param mixed $data The data to sanitize.
 * @return string The sanitized data.
 */
function sanitize($data)
{
    return htmlspecialchars(strip_tags(trim($data)));
}

/**
 * Generates a random token.
 * @param int $length The length of the token.
 * @return string The generated token.
 */
function generateToken($length = 32)
{
    return bin2hex(random_bytes($length));
}

/**
 * Hashes a password using the bcrypt algorithm.
 * @param string $password The password to hash.
 * @return string The hashed password.
 */
function hashPassword($password)
{
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
}

/**
 * Checks if a user is logged in.
 * @return bool True if the user is logged in, false otherwise.
 */
function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}

/**
 * Requires the user to be logged in. If not, redirects to the login page.
 */
function requireLogin()
{
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

// Define user role levels
define('ROLE_STUDENT', 1);
define('ROLE_TEACHER', 2);
define('ROLE_ADMIN', 3);
define('ROLE_SUPERADMIN', 4);

// Define permissions
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

/**
 * Gets the permissions for a given role.
 * @param string $role The role to get permissions for.
 * @return array An array of permissions.
 */
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

/**
 * Checks if the current user has at least the required role.
 * @param string $requiredRole The required role.
 * @return bool True if the user has the required role, false otherwise.
 */
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

/**
 * Checks if the current user has a specific permission.
 * @param string $permission The permission to check.
 * @return bool True if the user has the permission, false otherwise.
 */
function hasPermission($permission)
{
    if (!isLoggedIn()) return false;

    $userRole = $_SESSION['role'];
    $userPermissions = getRolePermissions($userRole);

    return in_array($permission, $userPermissions);
}

/**
 * Requires the user to have a specific role. If not, redirects with an error.
 * @param string $role The required role.
 */
function requireRole($role)
{
    requireLogin();
    if (!hasRole($role)) {
        $_SESSION['error'] = 'Bạn không có quyền truy cập trang này';
        header('Location: ../public/index.php?error=access_denied');
        exit();
    }
}

/**
 * Requires the user to have a specific permission. If not, redirects with an error.
 * @param string $permission The required permission.
 */
function requirePermission($permission)
{
    requireLogin();
    if (!hasPermission($permission)) {
        $_SESSION['error'] = 'Bạn không có quyền thực hiện hành động này';
        header('Location: ../public/index.php?error=permission_denied');
        exit();
    }
}

/**
 * Alias for hasPermission, checks if the user can access a feature.
 * @param string $permission The permission to check.
 * @return bool True if the user has the permission, false otherwise.
 */
function canAccess($permission)
{
    return hasPermission($permission);
}

/**
 * Gets the display name for a role.
 * @param string $role The role.
 * @return string The display name.
 */
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

/**
 * Gets the CSS badge class for a role.
 * @param string $role The role.
 * @return string The CSS class.
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
 * Formats a date string.
 * @param string $date The date to format.
 * @return string The formatted date.
 */
function formatDate($date)
{
    return date('d/m/Y', strtotime($date));
}

/**
 * Handles file uploads.
 * @param array $file The file from the $_FILES array.
 * @param string $uploadDir The directory to upload the file to.
 * @return array An array indicating success or failure and a message/filename.
 */
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

/**
 * Deletes a file.
 * @param string $filepath The path to the file to delete.
 */
function deleteFile($filepath)
{
    if (file_exists($filepath)) {
        unlink($filepath);
    }
}

/**
 * Sends an email.
 * @param string $to The recipient's email address.
 * @param string $subject The email subject.
 * @param string $message The email message.
 * @return bool True if the email was sent successfully, false otherwise.
 */
function sendEmail($to, $subject, $message)
{
    // Note: This is a simple email function. In production, use a library like PHPMailer.
    $headers = "From: noreply@studentmanagement.com\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

    return mail($to, $subject, $message, $headers);
}

/**
 * Checks if a student can view their own data.
 * @param int $studentId The ID of the student.
 * @return bool True if the user can view the data, false otherwise.
 */
function canViewStudent($studentId)
{
    if (!isLoggedIn()) return false;

    $userRole = $_SESSION['role'];

    // Admins, teachers, and superadmins can view all student data
    if (in_array($userRole, ['superadmin', 'admin', 'teacher'])) {
        return true;
    }

    // A student can only view their own data.
    // This assumes the student account is linked to a student_id in the users table.
    // For this to work, a 'student_id' column needs to be added to the 'users' table.
    // Temporarily allowing students to view all data.
    return true;
}

/**
 * Generates a CSRF token.
 * @return string The CSRF token.
 */
function generateCSRFToken()
{
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verifies a CSRF token.
 * @param string $token The token to verify.
 * @return bool True if the token is valid, false otherwise.
 */
function verifyCSRFToken($token)
{
    if (!isset($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}
