<?php
/**
 * Middleware for the permission system.
 * Used to check access rights before performing actions.
 */

require_once 'utils.php';

/**
 * Class PermissionMiddleware
 * Provides static methods to handle permission checks throughout the application.
 */
class PermissionMiddleware {
    
    /**
     * Checks if the current user has permission to access a specific page.
     * If not, it redirects to the main page with an error message.
     * @param string $permission The required permission.
     */
    public static function checkPageAccess($permission) {
        if (!hasPermission($permission)) {
            $_SESSION['error'] = 'Bạn không có quyền truy cập trang này';
            header('Location: ../public/index.php?error=access_denied');
            exit();
        }
    }
    
    /**
     * Checks if the current user has permission to perform a specific action.
     * @param string $permission The required permission.
     * @return array An array indicating success or failure.
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
     * Checks if the current user has a specific role.
     * If not, it redirects to the main page with an error message.
     * @param string $requiredRole The required role.
     */
    public static function checkRoleAccess($requiredRole) {
        if (!hasRole($requiredRole)) {
            $_SESSION['error'] = 'Bạn không có quyền truy cập với vai trò hiện tại';
            header('Location: ../public/index.php?error=role_denied');
            exit();
        }
    }
    
    /**
     * Checks if the current user owns a specific resource.
     * Admins and superadmins have access to all resources.
     * @param int $resourceUserId The user ID associated with the resource.
     * @return bool True if the user has ownership or is an admin, false otherwise.
     */
    public static function checkResourceOwnership($resourceUserId) {
        $currentUserId = $_SESSION['user_id'] ?? 0;
        $userRole = $_SESSION['role'] ?? 'student';
        
        // Superadmin and admin can access all resources
        if (in_array($userRole, ['superadmin', 'admin'])) {
            return true;
        }
        
        // Check for ownership
        return $currentUserId == $resourceUserId;
    }
    
    /**
     * Gets the list of permissions for the current user.
     * @return array An array of permissions.
     */
    public static function getCurrentUserPermissions() {
        if (!isLoggedIn()) {
            return [];
        }
        
        $userRole = $_SESSION['role'];
        return getRolePermissions($userRole);
    }
    
    /**
     * Checks if the current user can perform a specific action.
     * @param string $action The action to check.
     * @return bool True if the user can perform the action, false otherwise.
     */
    public static function canPerformAction($action) {
        $permissions = self::getCurrentUserPermissions();
        return in_array($action, $permissions);
    }
    
    /**
     * Gets information about the current user's role.
     * @return array|null An array with role information or null if not logged in.
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
     * Checks if the user has permission to export data.
     * @return array An array indicating success or failure.
     */
    public static function checkExportPermission() {
        return self::checkActionPermission(PERMISSION_EXPORT_DATA);
    }
    
    /**
     * Checks if the user has permission to manage users.
     * @return array An array indicating success or failure.
     */
    public static function checkUserManagementPermission() {
        return self::checkActionPermission(PERMISSION_MANAGE_USERS);
    }
    
    /**
     * Checks if the user has permission to view statistics.
     * @return array An array indicating success or failure.
     */
    public static function checkStatisticsPermission() {
        return self::checkActionPermission(PERMISSION_VIEW_STATISTICS);
    }
    
    /**
     * Checks permissions for CRUD actions on students.
     * @param string $action The action to check ('view', 'add', 'edit', 'delete').
     * @return array An array indicating success or failure.
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
     * Checks permissions for CRUD actions on scores.
     * @param string $action The action to check ('view', 'add', 'edit', 'delete').
     * @return array An array indicating success or failure.
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
     * Creates a JSON response for API calls.
     * @param bool $success Indicates if the operation was successful.
     * @param string $message The response message.
     * @param array $data Optional data to include in the response.
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
     * Checks for a required permission and returns a JSON error response if it's not met.
     * @param string $permission The required permission.
     */
    public static function requirePermissionOrFail($permission) {
        $result = self::checkActionPermission($permission);
        if (!$result['success']) {
            self::jsonResponse(false, $result['message']);
        }
    }
}
?>
