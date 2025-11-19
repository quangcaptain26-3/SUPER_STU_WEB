<?php
/**
 * Middleware cho hệ thống phân quyền.
 * Được sử dụng để kiểm tra quyền truy cập trước khi thực hiện các hành động.
 */

require_once 'utils.php';

/**
 * Class PermissionMiddleware
 * Cung cấp các phương thức tĩnh để xử lý kiểm tra quyền trong toàn bộ ứng dụng.
 */
class PermissionMiddleware {
    
    /**
     * Kiểm tra xem người dùng hiện tại có quyền truy cập trang cụ thể hay không.
     * Nếu không có quyền, chuyển hướng về trang chính với thông báo lỗi.
     * @param string $permission Quyền cần thiết.
     */
    public static function checkPageAccess($permission) {
        if (!hasPermission($permission)) {
            // Đặt thông báo lỗi vào session
            $_SESSION['error'] = 'Bạn không có quyền truy cập trang này';
            // Chuyển hướng về trang chính với mã lỗi
            header('Location: ../public/index.php?error=access_denied');
            exit();
        }
    }
    
    /**
     * Kiểm tra xem người dùng hiện tại có quyền thực hiện hành động cụ thể hay không.
     * @param string $permission Quyền cần thiết.
     * @return array Mảng kết quả gồm trạng thái thành công hoặc thất bại và thông điệp.
     */
    public static function checkActionPermission($permission) {
        if (!hasPermission($permission)) {
            // Trả về kết quả lỗi khi không có quyền
            return [
                'success' => false,
                'message' => 'Bạn không có quyền thực hiện hành động này'
            ];
        }
        // Trả về thành công nếu có quyền
        return ['success' => true];
    }
    
    /**
     * Kiểm tra xem người dùng hiện tại có vai trò cụ thể hay không.
     * Nếu không, chuyển hướng về trang chính với thông báo lỗi.
     * @param string $requiredRole Vai trò yêu cầu.
     */
    public static function checkRoleAccess($requiredRole) {
        if (!hasRole($requiredRole)) {
            // Đặt thông báo lỗi vào session
            $_SESSION['error'] = 'Bạn không có quyền truy cập với vai trò hiện tại';
            // Chuyển hướng về trang chính với mã lỗi vai trò
            header('Location: ../public/index.php?error=role_denied');
            exit();
        }
    }
    
    /**
     * Kiểm tra xem người dùng hiện tại có phải chủ sở hữu của một tài nguyên cụ thể hay không.
     * Vai trò admin và superadmin có thể truy cập tất cả tài nguyên.
     * @param int $resourceUserId ID người dùng liên quan đến tài nguyên.
     * @return bool True nếu người dùng là chủ sở hữu hoặc là admin, ngược lại false.
     */
    public static function checkResourceOwnership($resourceUserId) {
        // Lấy ID người dùng hiện tại từ session, mặc định 0 nếu chưa đăng nhập
        $currentUserId = $_SESSION['user_id'] ?? 0;
        // Lấy vai trò người dùng hiện tại
        $userRole = $_SESSION['role'] ?? 'student';
        
        // Vai trò superadmin và admin có quyền truy cập tất cả tài nguyên
        if (in_array($userRole, ['superadmin', 'admin'])) {
            return true;
        }
        
        // Kiểm tra xem người dùng có phải chủ sở hữu tài nguyên hay không
        return $currentUserId == $resourceUserId;
    }
    
    /**
     * Lấy danh sách các quyền của người dùng hiện tại.
     * @return array Mảng các quyền.
     */
    public static function getCurrentUserPermissions() {
        // Nếu chưa đăng nhập, trả về mảng rỗng
        if (!isLoggedIn()) {
            return [];
        }
        
        // Lấy vai trò hiện tại
        $userRole = $_SESSION['role'];
        // Trả về các quyền tương ứng với vai trò
        return getRolePermissions($userRole);
    }
    
    /**
     * Kiểm tra xem người dùng hiện tại có thể thực hiện một hành động cụ thể hay không.
     * @param string $action Hành động cần kiểm tra.
     * @return bool True nếu có quyền thực hiện hành động, false nếu không.
     */
    public static function canPerformAction($action) {
        $permissions = self::getCurrentUserPermissions();
        // Kiểm tra xem hành động có nằm trong danh sách quyền hiện tại hay không
        return in_array($action, $permissions);
    }
    
    /**
     * Lấy thông tin về vai trò của người dùng hiện tại.
     * @return array|null Mảng thông tin vai trò hoặc null nếu chưa đăng nhập.
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
     * Kiểm tra quyền xuất dữ liệu của người dùng.
     * @return array Mảng kết quả kiểm tra quyền.
     */
    public static function checkExportPermission() {
        return self::checkActionPermission(PERMISSION_EXPORT_DATA);
    }
    
    /**
     * Kiểm tra quyền quản lý người dùng.
     * @return array Mảng kết quả kiểm tra quyền.
     */
    public static function checkUserManagementPermission() {
        return self::checkActionPermission(PERMISSION_MANAGE_USERS);
    }
    
    /**
     * Kiểm tra quyền xem thống kê.
     * @return array Mảng kết quả kiểm tra quyền.
     */
    public static function checkStatisticsPermission() {
        return self::checkActionPermission(PERMISSION_VIEW_STATISTICS);
    }
    
    /**
     * Kiểm tra quyền CRUD của người dùng với sinh viên.
     * @param string $action Hành động cần kiểm tra ('view', 'add', 'edit', 'delete').
     * @return array Mảng kết quả kiểm tra quyền.
     */
    public static function checkStudentPermissions($action) {
        // Định nghĩa các quyền tương ứng với hành động trên sinh viên
        $permissions = [
            'view' => PERMISSION_VIEW_STUDENTS,
            'add' => PERMISSION_ADD_STUDENTS,
            'edit' => PERMISSION_EDIT_STUDENTS,
            'delete' => PERMISSION_DELETE_STUDENTS
        ];
        
        // Kiểm tra hành động có hợp lệ không
        if (!isset($permissions[$action])) {
            return ['success' => false, 'message' => 'Hành động không hợp lệ'];
        }
        
        // Kiểm tra quyền tương ứng
        return self::checkActionPermission($permissions[$action]);
    }
    
    /**
     * Kiểm tra quyền CRUD của người dùng với điểm số.
     * @param string $action Hành động cần kiểm tra ('view', 'add', 'edit', 'delete').
     * @return array Mảng kết quả kiểm tra quyền.
     */
    public static function checkScorePermissions($action) {
        // Định nghĩa các quyền tương ứng với hành động trên điểm số
        $permissions = [
            'view' => PERMISSION_VIEW_SCORES,
            'add' => PERMISSION_ADD_SCORES,
            'edit' => PERMISSION_EDIT_SCORES,
            'delete' => PERMISSION_DELETE_SCORES
        ];
        
        // Kiểm tra hành động có hợp lệ không
        if (!isset($permissions[$action])) {
            return ['success' => false, 'message' => 'Hành động không hợp lệ'];
        }
        
        // Kiểm tra quyền tương ứng
        return self::checkActionPermission($permissions[$action]);
    }
    
    /**
     * Tạo phản hồi JSON cho các cuộc gọi API.
     * @param bool $success Trạng thái thành công hoặc thất bại.
     * @param string $message Thông điệp phản hồi.
     * @param array $data Dữ liệu tùy chọn gửi kèm.
     */
    public static function jsonResponse($success, $message, $data = []) {
        // Đặt header là JSON
        header('Content-Type: application/json');
        // Trả về dữ liệu dưới dạng JSON
        echo json_encode([
            'success' => $success,
            'message' => $message,
            'data' => $data
        ]);
        exit();
    }
    
    /**
     * Kiểm tra quyền bắt buộc và trả về lỗi dạng JSON nếu không đủ quyền.
     * @param string $permission Quyền bắt buộc.
     */
    public static function requirePermissionOrFail($permission) {
        $result = self::checkActionPermission($permission);
        if (!$result['success']) {
            self::jsonResponse(false, $result['message']);
        }
    }
}
?>