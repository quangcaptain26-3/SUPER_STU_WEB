<?php
require_once 'config/db.php';
require_once 'utils.php';

class AuthController {
    private $conn;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    public function login($username, $password) {
        $query = "SELECT id, username, password, role, email FROM users WHERE username = :username";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if (hashPassword($password) === $user['password']) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['email'] = $user['email'];
                return ['success' => true, 'message' => 'Đăng nhập thành công'];
            }
        }
        return ['success' => false, 'message' => 'Tên đăng nhập hoặc mật khẩu không đúng'];
    }
    
    public function register($username, $password, $email, $role = 'student') {
        // Check if username exists
        $query = "SELECT id FROM users WHERE username = :username";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            return ['success' => false, 'message' => 'Tên đăng nhập đã tồn tại'];
        }
        
        // Check if email exists
        $query = "SELECT id FROM users WHERE email = :email";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            return ['success' => false, 'message' => 'Email đã được sử dụng'];
        }
        
        // Insert new user
        $query = "INSERT INTO users (username, password, email, role) VALUES (:username, :password, :email, :role)";
        $stmt = $this->conn->prepare($query);
        $hashedPassword = hashPassword($password);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':password', $hashedPassword);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':role', $role);
        
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Đăng ký thành công'];
        } else {
            return ['success' => false, 'message' => 'Lỗi đăng ký'];
        }
    }
    
    public function forgotPassword($email) {
        $query = "SELECT id FROM users WHERE email = :email";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            $token = generateToken();
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            // Delete old tokens
            $query = "DELETE FROM reset_tokens WHERE user_id = :user_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user['id']);
            $stmt->execute();
            
            // Insert new token
            $query = "INSERT INTO reset_tokens (user_id, token, expires_at) VALUES (:user_id, :token, :expires_at)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user['id']);
            $stmt->bindParam(':token', $token);
            $stmt->bindParam(':expires_at', $expires);
            
            if ($stmt->execute()) {
                $resetLink = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/reset_password.php?token=" . $token;
                $message = "Để đặt lại mật khẩu, vui lòng click vào link sau: <a href='$resetLink'>$resetLink</a><br>Link có hiệu lực trong 1 giờ.";
                
                if (sendEmail($email, 'Đặt lại mật khẩu', $message)) {
                    return ['success' => true, 'message' => 'Email đặt lại mật khẩu đã được gửi'];
                } else {
                    return ['success' => false, 'message' => 'Lỗi gửi email'];
                }
            }
        }
        return ['success' => false, 'message' => 'Email không tồn tại trong hệ thống'];
    }
    
    public function resetPassword($token, $newPassword) {
        $query = "SELECT user_id FROM reset_tokens WHERE token = :token AND expires_at > NOW()";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':token', $token);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $tokenData = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Update password
            $query = "UPDATE users SET password = :password WHERE id = :user_id";
            $stmt = $this->conn->prepare($query);
            $hashedPassword = hashPassword($newPassword);
            $stmt->bindParam(':password', $hashedPassword);
            $stmt->bindParam(':user_id', $tokenData['user_id']);
            
            if ($stmt->execute()) {
                // Delete token
                $query = "DELETE FROM reset_tokens WHERE token = :token";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':token', $token);
                $stmt->execute();
                
                return ['success' => true, 'message' => 'Mật khẩu đã được đặt lại thành công'];
            }
        }
        return ['success' => false, 'message' => 'Token không hợp lệ hoặc đã hết hạn'];
    }
    
    public function logout() {
        session_destroy();
        return ['success' => true, 'message' => 'Đăng xuất thành công'];
    }
    
    public function getAllUsers() {
        $query = "SELECT id, username, email, role, created_at FROM users ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getUserById($id) {
        $query = "SELECT id, username, email, role, created_at FROM users WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function deleteUser($id) {
        // Không cho phép xóa chính mình
        if ($id == $_SESSION['user_id']) {
            return ['success' => false, 'message' => 'Không thể xóa tài khoản của chính mình'];
        }
        
        $query = "DELETE FROM users WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Xóa người dùng thành công'];
        } else {
            return ['success' => false, 'message' => 'Lỗi xóa người dùng'];
        }
    }
    
    public function updateUser($id, $data) {
        $query = "UPDATE users SET username = :username, email = :email, role = :role WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $data['username']);
        $stmt->bindParam(':email', $data['email']);
        $stmt->bindParam(':role', $data['role']);
        $stmt->bindParam(':id', $id);
        
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Cập nhật người dùng thành công'];
        } else {
            return ['success' => false, 'message' => 'Lỗi cập nhật người dùng'];
        }
    }
    
    public function changePassword($id, $newPassword) {
        $query = "UPDATE users SET password = :password WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $hashedPassword = hashPassword($newPassword);
        $stmt->bindParam(':password', $hashedPassword);
        $stmt->bindParam(':id', $id);
        
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Đổi mật khẩu thành công'];
        } else {
            return ['success' => false, 'message' => 'Lỗi đổi mật khẩu'];
        }
    }
}
?>
