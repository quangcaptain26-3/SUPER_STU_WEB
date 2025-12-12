<?php
/**
 * File này chứa lớp "Middleware" cho hệ thống phân quyền.
 * LƯU Ý: Tên gọi "Middleware" ở đây mang tính khái niệm, không giống với middleware trong các framework hiện đại (như Laravel hay Express.js)
 * vốn hoạt động bằng cách "bọc" quanh một request.
 *
 * Thay vào đó, đây là một lớp Helper tĩnh (`PermissionMiddleware`), cung cấp một tập hợp các phương thức tiện ích
 * để kiểm tra quyền hạn một cách có tổ chức và tái sử dụng trong toàn bộ ứng dụng.
 */

// Nạp file utils.php vì các hàm middleware này phụ thuộc vào các hàm kiểm tra quyền cơ bản như `hasPermission`.
require_once 'utils.php';

/**
 * Class PermissionMiddleware
 * Cung cấp các phương thức tĩnh để kiểm tra quyền truy cập và quyền thực hiện hành động.
 * Việc sử dụng phương thức tĩnh cho phép gọi trực tiếp mà không cần tạo đối tượng: `PermissionMiddleware::checkPageAccess(...)`.
 */
class PermissionMiddleware {
    
    /**
     * Hàm "guard" chính cho các trang. Kiểm tra quyền truy cập một trang.
     * Nếu không có quyền, người dùng sẽ bị chuyển hướng và script sẽ dừng lại.
     * @param string $permission Quyền hạn cần có để truy cập trang (VD: PERMISSION_VIEW_STUDENTS).
     */
    public static function checkPageAccess($permission) {
        // Sử dụng hàm `hasPermission` từ `utils.php` để kiểm tra.
        if (!hasPermission($permission)) {
            // Nếu không có quyền, đặt một thông báo lỗi vào session để có thể hiển thị ở trang chủ.
            $_SESSION['error'] = 'Bạn không có quyền truy cập trang này.';
            // Gửi header chuyển hướng người dùng về trang chủ.
            header('Location: ../public/index.php?error=access_denied');
            // Dừng script ngay lập tức.
            exit();
        }
    }
    
    /**
     * Kiểm tra xem người dùng có quyền thực hiện một hành động cụ thể hay không (thường dùng cho API hoặc xử lý form).
     * @param string $permission Quyền cần có để thực hiện hành động.
     * @return array Mảng kết quả `['success' => bool, 'message' => string]`.
     */
    public static function checkActionPermission($permission) {
        if (!hasPermission($permission)) {
            // Nếu không có quyền, trả về kết quả thất bại cùng thông báo.
            return [
                'success' => false,
                'message' => 'Bạn không có quyền thực hiện hành động này.'
            ];
        }
        // Nếu có quyền, trả về thành công.
        return ['success' => true];
    }
    
    /**
     * Hàm "guard" kiểm tra vai trò của người dùng.
     * @param string $requiredRole Vai trò yêu cầu (VD: 'admin').
     */
    public static function checkRoleAccess($requiredRole) {
        // Sử dụng hàm `hasRole` từ `utils.php`.
        if (!hasRole($requiredRole)) {
            $_SESSION['error'] = 'Vai trò của bạn không đủ để truy cập trang này.';
            header('Location: ../public/index.php?error=role_denied');
            exit();
        }
    }
    
    /**
     * Kiểm tra xem người dùng hiện tại có phải là chủ sở hữu của một tài nguyên hay không.
     * Rất hữu ích khi muốn giới hạn "student" chỉ xem được thông tin của chính mình.
     * @param int $resourceUserId ID của người dùng sở hữu tài nguyên (VD: `student.user_id`).
     * @return bool True nếu là chủ sở hữu hoặc là admin/superadmin, ngược lại false.
     */
    public static function checkResourceOwnership($resourceUserId) {
        // Lấy thông tin người dùng đang đăng nhập từ session.
        $currentUserId = $_SESSION['user_id'] ?? 0;
        $userRole = $_SESSION['role'] ?? 'student';
        
        // Superadmin và admin luôn có quyền truy cập tất cả tài nguyên.
        if (in_array($userRole, ['superadmin', 'admin'])) {
            return true;
        }
        
        // Người dùng thông thường chỉ có quyền nếu ID của họ khớp với ID chủ sở hữu tài nguyên.
        return $currentUserId == $resourceUserId;
    }
    
    /**
     * Lấy danh sách các chuỗi quyền của người dùng đang đăng nhập.
     * @return array Mảng các quyền.
     */
    public static function getCurrentUserPermissions() {
        if (!isLoggedIn()) {
            return [];
        }
        $userRole = $_SESSION['role'];
        // Gọi hàm từ `utils.php` để lấy danh sách quyền dựa trên vai trò.
        return getRolePermissions($userRole);
    }
    
    /**
     * Một cách gọi khác của `hasPermission`, dùng để kiểm tra một hành động cụ thể.
     * @param string $action Chuỗi hành động (trùng với chuỗi quyền).
     * @return bool True nếu có thể thực hiện, ngược lại false.
     */
    public static function canPerformAction($action) {
        $permissions = self::getCurrentUserPermissions();
        return in_array($action, $permissions);
    }
    
    // --- CÁC PHƯƠNG THỨC TIỆN ÍCH CHO VIỆC KIỂM TRA QUYỀN CỤ THỂ ---
    // Các hàm này giúp code ở các nơi khác dễ đọc hơn.
    // Ví dụ: `PermissionMiddleware::checkExportPermission()` thay vì `PermissionMiddleware::checkActionPermission(PERMISSION_EXPORT_DATA)`
    
    public static function checkExportPermission() {
        return self::checkActionPermission(PERMISSION_EXPORT_DATA);
    }
    
    public static function checkUserManagementPermission() {
        return self::checkActionPermission(PERMISSION_MANAGE_USERS);
    }
    
    public static function checkStatisticsPermission() {
        return self::checkActionPermission(PERMISSION_VIEW_STATISTICS);
    }
    
    /**
     * Kiểm tra quyền CRUD trên module Sinh viên.
     * @param string $action Hành động: 'view', 'add', 'edit', 'delete'.
     * @return array Kết quả từ `checkActionPermission`.
     */
    public static function checkStudentPermissions($action) {
        $permissions = [
            'view'   => PERMISSION_VIEW_STUDENTS,
            'add'    => PERMISSION_ADD_STUDENTS,
            'edit'   => PERMISSION_EDIT_STUDENTS,
            'delete' => PERMISSION_DELETE_STUDENTS
        ];
        
        if (!isset($permissions[$action])) {
            return ['success' => false, 'message' => 'Hành động không hợp lệ.'];
        }
        
        return self::checkActionPermission($permissions[$action]);
    }

    /**
     * Kiểm tra quyền CRUD trên module Điểm số.
     * @param string $action Hành động: 'view', 'add', 'edit', 'delete'.
     * @return array Kết quả từ `checkActionPermission`.
     */
    public static function checkScorePermissions($action) {
        $permissions = [
            'view'   => PERMISSION_VIEW_SCORES,
            'add'    => PERMISSION_ADD_SCORES,
            'edit'   => PERMISSION_EDIT_SCORES,
            'delete' => PERMISSION_DELETE_SCORES
        ];
        
        if (!isset($permissions[$action])) {
            return ['success' => false, 'message' => 'Hành động không hợp lệ.'];
        }
        
        return self::checkActionPermission($permissions[$action]);
    }
    
    /**
     * Hàm tiện ích để tạo và trả về một response JSON chuẩn hóa.
     * Thường được dùng trong các file API.
     * @param bool $success Trạng thái thành công/thất bại.
     * @param string $message Thông điệp phản hồi.
     * @param array $data Dữ liệu tùy chọn gửi kèm.
     */
    public static function jsonResponse($success, $message, $data = []) {
        // Thiết lập header để trình duyệt hiểu đây là JSON.
        header('Content-Type: application/json');
        // Mã hóa mảng thành chuỗi JSON và in ra.
        echo json_encode([
            'success' => $success,
            'message' => $message,
            'data' => $data
        ]);
        // Dừng script.
        exit();
    }
    
    /**
     * Hàm "guard" cho API. Kiểm tra quyền, nếu không có thì trả về lỗi JSON và dừng script.
     * @param string $permission Quyền hạn bắt buộc.
     */
    public static function requirePermissionOrFail($permission) {
        $result = self::checkActionPermission($permission);
        if (!$result['success']) {
            // Gọi hàm `jsonResponse` để trả về lỗi 403 (Forbidden) một cách thân thiện.
            self::jsonResponse(false, $result['message']);
        }
    }

    /**
     * Lấy thông tin chi tiết về vai trò và quyền hạn của người dùng hiện tại.
     * @return array Một mảng chứa vai trò ('role'), tên hiển thị ('display_name'), class CSS cho badge ('badge_class') và danh sách quyền hạn ('permissions').
     */
    public static function getCurrentUserRole() {
        if (!isLoggedIn()) {
            return [
                'role' => 'guest',
                'display_name' => 'Khách',
                'badge_class' => 'bg-secondary',
                'permissions' => []
            ];
        }
        $userRole = $_SESSION['role'];
        return [
            'role' => $userRole,
            'display_name' => getRoleDisplayName($userRole),
            'badge_class' => getRoleBadgeClass($userRole),
            'permissions' => getRolePermissions($userRole)
        ];
    }
}
?>