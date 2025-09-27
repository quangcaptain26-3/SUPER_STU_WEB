<?php
/**
 * Middleware cho hệ thống phân quyền
 * Sử dụng để kiểm tra quyền truy cập trước khi thực hiện các hành động
 */

require_once 'utils.php';

class PermissionMiddleware {
    
    /**
     * Kiểm tra quyền truy cập trang
     */
    public static function checkPageAccess($permission) {
        if (!hasPermission($permission)) {
            $_SESSION['error'] = 'Bạn không có quyền truy cập trang này';
            header('Location: ../public/index.php?error=access_denied');
            exit();
        }
    }
    
    /**
     * Kiểm tra quyền thực hiện hành động
     */
    public static function checkActionPermission($permission) {
        if (!hasPermission($permission)) {
            return [
                'success' => false,
                'message' => 'Bạn không có quyền thực hiện hành động này'
            ];
        }
        return ['success' => true];
    }
    
    /**
     * Kiểm tra quyền dựa trên vai trò
     */
    public static function checkRoleAccess($requiredRole) {
        if (!hasRole($requiredRole)) {
            $_SESSION['error'] = 'Bạn không có quyền truy cập với vai trò hiện tại';
            header('Location: ../public/index.php?error=role_denied');
            exit();
        }
    }
    
    /**
     * Kiểm tra quyền sở hữu tài nguyên
     */
    public static function checkResourceOwnership($resourceUserId) {
        $currentUserId = $_SESSION['user_id'] ?? 0;
        $userRole = $_SESSION['role'] ?? 'student';
        
        // Super admin và admin có thể truy cập tất cả
        if (in_array($userRole, ['superadmin', 'admin'])) {
            return true;
        }
        
        // Kiểm tra quyền sở hữu
        return $currentUserId == $resourceUserId;
    }
    
    /**
     * Lấy danh sách quyền của người dùng hiện tại
     */
    public static function getCurrentUserPermissions() {
        if (!isLoggedIn()) {
            return [];
        }
        
        $userRole = $_SESSION['role'];
        return getRolePermissions($userRole);
    }
    
    /**
     * Kiểm tra xem người dùng có thể thực hiện hành động không
     */
    public static function canPerformAction($action) {
        $permissions = self::getCurrentUserPermissions();
        return in_array($action, $permissions);
    }
    
    /**
     * Lấy thông tin vai trò hiện tại
     */
    public static function getCurrentUserRole() {
        if (!isLoggedIn()) {
            return null;
        }
        
        return [
            'role' => $_SESSION['role'],
            'display_name' => getRoleDisplayName($_SESSION['role']),
            'badge_class' => getRoleBadgeClass($_SESSION['role']),
            'permissions' => self::getCurrentUserPermissions()
        ];
    }
    
    /**
     * Kiểm tra quyền xuất dữ liệu
     */
    public static function checkExportPermission() {
        return self::checkActionPermission(PERMISSION_EXPORT_DATA);
    }
    
    /**
     * Kiểm tra quyền quản lý người dùng
     */
    public static function checkUserManagementPermission() {
        return self::checkActionPermission(PERMISSION_MANAGE_USERS);
    }
    
    /**
     * Kiểm tra quyền xem thống kê
     */
    public static function checkStatisticsPermission() {
        return self::checkActionPermission(PERMISSION_VIEW_STATISTICS);
    }
    
    /**
     * Kiểm tra quyền CRUD sinh viên
     */
    public static function checkStudentPermissions($action) {
        $permissions = [
            'view' => PERMISSION_VIEW_STUDENTS,
            'add' => PERMISSION_ADD_STUDENTS,
            'edit' => PERMISSION_EDIT_STUDENTS,
            'delete' => PERMISSION_DELETE_STUDENTS
        ];
        
        if (!isset($permissions[$action])) {
            return ['success' => false, 'message' => 'Hành động không hợp lệ'];
        }
        
        return self::checkActionPermission($permissions[$action]);
    }
    
    /**
     * Kiểm tra quyền CRUD điểm số
     */
    public static function checkScorePermissions($action) {
        $permissions = [
            'view' => PERMISSION_VIEW_SCORES,
            'add' => PERMISSION_ADD_SCORES,
            'edit' => PERMISSION_EDIT_SCORES,
            'delete' => PERMISSION_DELETE_SCORES
        ];
        
        if (!isset($permissions[$action])) {
            return ['success' => false, 'message' => 'Hành động không hợp lệ'];
        }
        
        return self::checkActionPermission($permissions[$action]);
    }
    
    /**
     * Tạo response JSON cho API
     */
    public static function jsonResponse($success, $message, $data = []) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => $success,
            'message' => $message,
            'data' => $data
        ]);
        exit();
    }
    
    /**
     * Kiểm tra và trả về response lỗi nếu không có quyền
     */
    public static function requirePermissionOrFail($permission) {
        $result = self::checkActionPermission($permission);
        if (!$result['success']) {
            self::jsonResponse(false, $result['message']);
        }
    }
}
?>
