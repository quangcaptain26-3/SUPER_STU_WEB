<?php
// Lấy các file cấu hình cơ sở dữ liệu và các hàm tiện ích
require_once 'config/db.php';
require_once 'utils.php';

/**
 * Class AuthController
 * Xử lý các tác vụ xác thực như đăng nhập, đăng ký, quản lý mật khẩu
 */
class AuthController
{
    // Khai báo biến private để lưu trữ kết nối cơ sở dữ liệu
    private $conn;

    /**
     * Hàm khởi tạo AuthController
     * Khởi tạo kết nối cơ sở dữ liệu
     */
    public function __construct()
    {
        // Tạo đối tượng Database mới
        $database = new Database();
        // Gán kết nối cơ sở dữ liệu vào biến $conn
        $this->conn = $database->getConnection();
    }

    /**
     * Đăng nhập người dùng
     * @param string $username Tên đăng nhập
     * @param string $password Mật khẩu
     * @return array Mảng chỉ ra thành công/thất bại và thông điệp
     */
    public function login($username, $password)
    {
        // Khai báo câu query để lấy thông tin người dùng theo tên đăng nhập
        $query = "SELECT id, username, password, role, email FROM users WHERE username = :username";
        // Chuẩn bị câu lệnh
        $stmt = $this->conn->prepare($query);
        // Bind tham số tên đăng nhập
        $stmt->bindParam(':username', $username);
        // Thực thi câu lệnh
        $stmt->execute();

        // Nếu người dùng tồn tại
        if ($stmt->rowCount() > 0) {
            // Lấy dữ liệu người dùng
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Xác minh mật khẩu bằng SHA256
            $hashedPassword = hash('sha256', $password);
            // Sử dụng hash_equals để so sánh an toàn (tránh timing attack)
            if (hash_equals($hashedPassword, $user['password'])) {
                // Đặt các biến session khi đăng nhập thành công
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['email'] = $user['email'];
                // Trả về thông báo thành công
                return ['success' => true, 'message' => 'Đăng nhập thành công'];
            }
        }
        // Trả về lỗi nếu đăng nhập không thành công
        return ['success' => false, 'message' => 'Tên đăng nhập hoặc mật khẩu không đúng'];
    }

    /**
     * Đăng ký người dùng mới
     * @param string $username Tên đăng nhập
     * @param string $password Mật khẩu
     * @param string $email Địa chỉ email
     * @param string $role Vai trò người dùng (mặc định là 'student')
     * @return array Mảng chỉ ra thành công/thất bại và thông điệp
     */
    public function register($username, $password, $email, $role = 'student')
    {
        // Kiểm tra xem tên đăng nhập đã tồn tại chưa
        $query = "SELECT id FROM users WHERE username = :username";
        // Chuẩn bị câu lệnh
        $stmt = $this->conn->prepare($query);
        // Bind tên đăng nhập
        $stmt->bindParam(':username', $username);
        // Thực thi câu lệnh
        $stmt->execute();

        // Nếu tên đăng nhập đã tồn tại, trả về lỗi
        if ($stmt->rowCount() > 0) {
            return ['success' => false, 'message' => 'Tên đăng nhập đã tồn tại'];
        }

        // Kiểm tra xem email đã tồn tại chưa
        $query = "SELECT id FROM users WHERE email = :email";
        // Chuẩn bị câu lệnh
        $stmt = $this->conn->prepare($query);
        // Bind email
        $stmt->bindParam(':email', $email);
        // Thực thi câu lệnh
        $stmt->execute();

        // Nếu email đã tồn tại, trả về lỗi
        if ($stmt->rowCount() > 0) {
            return ['success' => false, 'message' => 'Email đã được sử dụng'];
        }

        // Chèn người dùng mới vào cơ sở dữ liệu
        $query = "INSERT INTO users (username, password, email, role) VALUES (:username, :password, :email, :role)";
        // Chuẩn bị câu lệnh
        $stmt = $this->conn->prepare($query);
        // Hash mật khẩu bằng SHA256
        $hashedPassword = hashPassword($password);
        // Bind tất cả các tham số
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':password', $hashedPassword);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':role', $role);

        // Thực thi câu lệnh và kiểm tra kết quả
        if ($stmt->execute()) {
            // Trả về thông báo thành công nếu đăng ký thành công
            return ['success' => true, 'message' => 'Đăng ký thành công'];
        } else {
            // Trả về lỗi nếu đăng ký thất bại
            return ['success' => false, 'message' => 'Lỗi đăng ký'];
        }
    }

    /**
     * Xử lý yêu cầu "quên mật khẩu"
     * @param string $email Địa chỉ email của người dùng
     * @return array Mảng chỉ ra thành công/thất bại và thông điệp
     */
    public function forgotPassword($email)
    {
        // Kiểm tra xem email có tồn tại trong hệ thống không
        $query = "SELECT id FROM users WHERE email = :email";
        // Chuẩn bị câu lệnh
        $stmt = $this->conn->prepare($query);
        // Bind email
        $stmt->bindParam(':email', $email);
        // Thực thi câu lệnh
        $stmt->execute();

        // Nếu email tồn tại
        if ($stmt->rowCount() > 0) {
            // Lấy dữ liệu người dùng
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            // Tạo token ngẫu nhiên để đặt lại mật khẩu
            $token = generateToken();
            // Đặt thời gian hết hạn của token
            // Development mode: 24 giờ, Production: 12 giờ
            $hours = isDevelopmentMode() ? 24 : 12;
            // Sử dụng DateTime để đảm bảo timezone nhất quán
            $now = new DateTime('now', new DateTimeZone('Asia/Ho_Chi_Minh'));
            $now->modify("+{$hours} hours");
            $expires = $now->format('Y-m-d H:i:s');

            // Xóa tất cả các token cũ cho người dùng này
            $query = "DELETE FROM reset_tokens WHERE user_id = :user_id";
            // Chuẩn bị câu lệnh
            $stmt = $this->conn->prepare($query);
            // Bind ID người dùng
            $stmt->bindParam(':user_id', $user['id']);
            // Thực thi câu lệnh
            $stmt->execute();

            // Chèn token đặt lại mật khẩu mới
            $query = "INSERT INTO reset_tokens (user_id, token, expires_at) VALUES (:user_id, :token, :expires_at)";
            // Chuẩn bị câu lệnh
            $stmt = $this->conn->prepare($query);
            // Bind ID người dùng
            $stmt->bindParam(':user_id', $user['id']);
            // Bind token
            $stmt->bindParam(':token', $token);
            // Bind thời gian hết hạn
            $stmt->bindParam(':expires_at', $expires);

            // Thực thi câu lệnh
            if ($stmt->execute()) {
                // Tạo link đặt lại mật khẩu
                $resetLink = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/reset_password.php?token=" . $token;
                
                // Nếu là development mode, trả về link reset để hiển thị trực tiếp
                if (isDevelopmentMode()) {
                    return [
                        'success' => true, 
                        'message' => 'Link đặt lại mật khẩu đã được tạo (Development Mode)',
                        'reset_link' => $resetLink,
                        'token' => $token
                    ];
                }
                
                // Tạo nội dung email
                $hours = isDevelopmentMode() ? 24 : 12;
                $message = "Để đặt lại mật khẩu, vui lòng click vào link sau: <a href='$resetLink'>$resetLink</a><br>Link có hiệu lực trong {$hours} giờ.";

                // Gửi email đặt lại mật khẩu
                if (sendEmail($email, 'Đặt lại mật khẩu', $message)) {
                    // Trả về thông báo thành công
                    return ['success' => true, 'message' => 'Email đặt lại mật khẩu đã được gửi'];
                } else {
                    // Trả về lỗi gửi email
                    return ['success' => false, 'message' => 'Lỗi gửi email'];
                }
            }
        }
        // Trả về lỗi nếu email không tồn tại
        return ['success' => false, 'message' => 'Email không tồn tại trong hệ thống'];
    }

    /**
     * Đặt lại mật khẩu người dùng bằng token
     * @param string $token Token đặt lại mật khẩu
     * @param string $newPassword Mật khẩu mới
     * @return array Mảng chỉ ra thành công/thất bại và thông điệp
     */
    public function resetPassword($token, $newPassword)
    {
        // Xác minh token và kiểm tra xem nó đã hết hạn chưa
        // Sử dụng UTC_TIMESTAMP() để đảm bảo timezone nhất quán với PHP
        // Hoặc convert NOW() sang cùng timezone với expires_at
        $query = "SELECT user_id, expires_at FROM reset_tokens WHERE token = :token";
        // Chuẩn bị câu lệnh
        $stmt = $this->conn->prepare($query);
        // Bind token
        $stmt->bindParam(':token', $token);
        // Thực thi câu lệnh
        $stmt->execute();

        // Lấy dữ liệu token
        $tokenData = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Kiểm tra token có tồn tại không
        if (!$tokenData) {
            return ['success' => false, 'message' => 'Token không hợp lệ'];
        }
        
        // Kiểm tra token đã hết hạn chưa bằng PHP để tránh timezone mismatch
        $expiresAt = new DateTime($tokenData['expires_at'], new DateTimeZone('Asia/Ho_Chi_Minh'));
        $now = new DateTime('now', new DateTimeZone('Asia/Ho_Chi_Minh'));
        
        if ($now > $expiresAt) {
            // Token đã hết hạn, xóa token cũ
            $deleteQuery = "DELETE FROM reset_tokens WHERE token = :token";
            $deleteStmt = $this->conn->prepare($deleteQuery);
            $deleteStmt->bindParam(':token', $token);
            $deleteStmt->execute();
            
            return ['success' => false, 'message' => 'Token đã hết hạn. Vui lòng yêu cầu link reset mật khẩu mới.'];
        }

        // Nếu token hợp lệ và chưa hết hạn
        if ($tokenData) {
            // Cập nhật mật khẩu của người dùng
            $query = "UPDATE users SET password = :password WHERE id = :user_id";
            // Chuẩn bị câu lệnh
            $stmt = $this->conn->prepare($query);
            // Hash mật khẩu mới bằng SHA256
            $hashedPassword = hashPassword($newPassword);
            // Bind mật khẩu mới đã hash
            $stmt->bindParam(':password', $hashedPassword);
            // Bind ID người dùng
            $stmt->bindParam(':user_id', $tokenData['user_id']);

            // Thực thi câu lệnh
            if ($stmt->execute()) {
                // Xóa token đã sử dụng
                $query = "DELETE FROM reset_tokens WHERE token = :token";
                // Chuẩn bị câu lệnh
                $stmt = $this->conn->prepare($query);
                // Bind token
                $stmt->bindParam(':token', $token);
                // Thực thi câu lệnh
                $stmt->execute();

                // Trả về thông báo thành công
                return ['success' => true, 'message' => 'Mật khẩu đã được đặt lại thành công'];
            } else {
                return ['success' => false, 'message' => 'Lỗi cập nhật mật khẩu'];
            }
        }
        
        // Fallback: Trả về lỗi nếu token không hợp lệ
        return ['success' => false, 'message' => 'Token không hợp lệ hoặc đã hết hạn'];
    }

    /**
     * Đăng xuất người dùng bằng cách hủy phiên
     * @return array Mảng chỉ ra thành công và thông điệp
     */
    public function logout()
    {
        // Hủy phiên người dùng
        session_destroy();
        // Trả về thông báo thành công
        return ['success' => true, 'message' => 'Đăng xuất thành công'];
    }

    /**
     * Lấy tất cả người dùng từ cơ sở dữ liệu
     * @return array Mảng chứa tất cả người dùng
     */
    public function getAllUsers()
    {
        // Khai báo câu query để lấy tất cả người dùng
        $query = "SELECT id, username, email, role, created_at FROM users ORDER BY created_at DESC";
        // Chuẩn bị câu lệnh
        $stmt = $this->conn->prepare($query);
        // Thực thi câu lệnh
        $stmt->execute();

        // Trả về tất cả kết quả
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy thông tin một người dùng theo ID
     * @param int $id ID của người dùng
     * @return array|false Dữ liệu người dùng hoặc false nếu không tìm thấy
     */
    public function getUserById($id)
    {
        // Khai báo câu query để lấy người dùng có ID cụ thể
        $query = "SELECT id, username, email, role, created_at FROM users WHERE id = :id";
        // Chuẩn bị câu lệnh
        $stmt = $this->conn->prepare($query);
        // Bind ID người dùng
        $stmt->bindParam(':id', $id);
        // Thực thi câu lệnh
        $stmt->execute();

        // Trả về một dòng kết quả hoặc false
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Xóa người dùng theo ID
     * @param int $id ID của người dùng cần xóa
     * @return array Mảng chỉ ra thành công/thất bại và thông điệp
     */
    public function deleteUser($id)
    {
        // Ngăn chặn người dùng xóa tài khoản của chính mình
        if ($id == $_SESSION['user_id']) {
            return ['success' => false, 'message' => 'Không thể xóa tài khoản của chính mình'];
        }

        // Khai báo câu query để xóa người dùng
        $query = "DELETE FROM users WHERE id = :id";
        // Chuẩn bị câu lệnh
        $stmt = $this->conn->prepare($query);
        // Bind ID người dùng
        $stmt->bindParam(':id', $id);

        // Thực thi câu lệnh và kiểm tra kết quả
        if ($stmt->execute()) {
            // Trả về thông báo thành công
            return ['success' => true, 'message' => 'Xóa người dùng thành công'];
        } else {
            // Trả về lỗi
            return ['success' => false, 'message' => 'Lỗi xóa người dùng'];
        }
    }

    /**
     * Cập nhật thông tin người dùng
     * @param int $id ID của người dùng cần cập nhật
     * @param array $data Dữ liệu mới cho người dùng
     * @return array Mảng chỉ ra thành công/thất bại và thông điệp
     */
    public function updateUser($id, $data)
    {
        // Khai báo câu query UPDATE
        $query = "UPDATE users SET username = :username, email = :email, role = :role WHERE id = :id";
        // Chuẩn bị câu lệnh
        $stmt = $this->conn->prepare($query);
        // Bind tất cả các tham số
        $stmt->bindParam(':username', $data['username']);
        $stmt->bindParam(':email', $data['email']);
        $stmt->bindParam(':role', $data['role']);
        $stmt->bindParam(':id', $id);

        // Thực thi câu lệnh và kiểm tra kết quả
        if ($stmt->execute()) {
            // Trả về thông báo thành công
            return ['success' => true, 'message' => 'Cập nhật người dùng thành công'];
        } else {
            // Trả về lỗi
            return ['success' => false, 'message' => 'Lỗi cập nhật người dùng'];
        }
    }

    /**
     * Thay đổi mật khẩu của người dùng
     * @param int $id ID của người dùng
     * @param string $newPassword Mật khẩu mới
     * @return array Mảng chỉ ra thành công/thất bại và thông điệp
     */
    public function changePassword($id, $newPassword)
    {
        // Khai báo câu query UPDATE để cập nhật mật khẩu
        $query = "UPDATE users SET password = :password WHERE id = :id";
        // Chuẩn bị câu lệnh
        $stmt = $this->conn->prepare($query);
        // Hash mật khẩu mới bằng SHA256
        $hashedPassword = hashPassword($newPassword);
        // Bind mật khẩu mới đã hash
        $stmt->bindParam(':password', $hashedPassword);
        // Bind ID người dùng
        $stmt->bindParam(':id', $id);

        // Thực thi câu lệnh và kiểm tra kết quả
        if ($stmt->execute()) {
            // Trả về thông báo thành công
            return ['success' => true, 'message' => 'Đổi mật khẩu thành công'];
        } else {
            // Trả về lỗi
            return ['success' => false, 'message' => 'Lỗi đổi mật khẩu'];
        }
    }
}
