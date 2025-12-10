<?php
// Nạp các file cần thiết cho hoạt động của controller.
require_once 'config/db.php'; // Chứa lớp `Database` để kết nối CSDL.
require_once 'utils.php';     // Chứa các hàm tiện ích như `hashPassword`, `generateToken`, v.v.

/**
 * Class AuthController
 * Chịu trách nhiệm xử lý tất cả các nghiệp vụ liên quan đến xác thực và quản lý người dùng,
 * bao gồm đăng nhập, đăng ký, đăng xuất, quên mật khẩu, và CRUD người dùng (cho Super Admin).
 */
class AuthController
{
    // Thuộc tính private để lưu trữ đối tượng kết nối CSDL (PDO).
    private $conn;

    /**
     * Hàm khởi tạo (constructor) của lớp AuthController.
     * Được tự động gọi khi một đối tượng mới của lớp này được tạo.
     */
    public function __construct()
    {
        // Khởi tạo một đối tượng từ lớp Database.
        $database = new Database();
        // Gọi phương thức `getConnection()` để lấy đối tượng kết nối PDO và gán vào thuộc tính `$conn`.
        $this->conn = $database->getConnection();
    }

    /**
     * Xử lý logic đăng nhập của người dùng.
     * @param string $username Tên đăng nhập do người dùng cung cấp.
     * @param string $password Mật khẩu thô do người dùng cung cấp.
     * @return array Mảng kết quả, chứa `success` (true/false) và `message`.
     */
    public function login($username, $password)
    {
        // Chuẩn bị câu lệnh SQL để truy vấn thông tin người dùng dựa trên username.
        // Lấy các cột cần thiết để xác thực và tạo session.
        $query = "SELECT id, username, password, role, email FROM users WHERE username = :username";
        
        // Chuẩn bị câu lệnh SQL để tránh SQL Injection.
        $stmt = $this->conn->prepare($query);
        // Gắn (bind) giá trị của biến `$username` vào tham số `:username` trong câu lệnh.
        $stmt->bindParam(':username', $username);
        // Thực thi câu lệnh đã chuẩn bị.
        $stmt->execute();

        // Kiểm tra xem có tìm thấy bản ghi nào không (`rowCount() > 0`).
        if ($stmt->rowCount() > 0) {
            // Nếu tìm thấy, lấy thông tin người dùng dưới dạng một mảng kết hợp.
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Băm mật khẩu mà người dùng vừa nhập bằng thuật toán SHA256.
            $hashedPassword = hash('sha256', $password);
            
            // So sánh chuỗi hash vừa tạo với chuỗi hash trong CSDL một cách an toàn.
            // `hash_equals` giúp chống lại các cuộc tấn công timing attack.
            if (hash_equals($hashedPassword, $user['password'])) {
                // Nếu mật khẩu khớp, đăng nhập thành công.
                // Thiết lập các biến session để duy trì trạng thái đăng nhập.
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['email'] = $user['email'];
                
                // Trả về kết quả thành công.
                return ['success' => true, 'message' => 'Đăng nhập thành công'];
            }
        }
        
        // Nếu username không tồn tại hoặc mật khẩu không đúng, trả về thông báo lỗi chung.
        return ['success' => false, 'message' => 'Tên đăng nhập hoặc mật khẩu không đúng'];
    }

    /**
     * Xử lý logic đăng ký người dùng mới.
     * @param string $username Tên đăng nhập mong muốn.
     * @param string $password Mật khẩu mong muốn.
     * @param string $email Email của người dùng.
     * @param string $role Vai trò được gán (mặc định là 'student').
     * @return array Mảng kết quả.
     */
    public function register($username, $password, $email, $role = 'student')
    {
        // 1. Kiểm tra xem username đã tồn tại chưa.
        $query = "SELECT id FROM users WHERE username = :username";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            return ['success' => false, 'message' => 'Tên đăng nhập đã tồn tại'];
        }

        // 2. Kiểm tra xem email đã tồn tại chưa.
        $query = "SELECT id FROM users WHERE email = :email";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            return ['success' => false, 'message' => 'Email đã được sử dụng'];
        }

        // 3. Nếu mọi thứ hợp lệ, tiến hành chèn người dùng mới vào CSDL.
        $query = "INSERT INTO users (username, password, email, role) VALUES (:username, :password, :email, :role)";
        $stmt = $this->conn->prepare($query);
        
        // Băm mật khẩu trước khi lưu vào CSDL.
        $hashedPassword = hashPassword($password); // Sử dụng hàm tiện ích từ utils.php
        
        // Gắn các giá trị vào câu lệnh INSERT.
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':password', $hashedPassword);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':role', $role);

        // Thực thi câu lệnh và trả về kết quả.
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Đăng ký thành công'];
        } else {
            return ['success' => false, 'message' => 'Lỗi xảy ra trong quá trình đăng ký'];
        }
    }

    /**
     * Xử lý logic cho chức năng "quên mật khẩu".
     * @param string $email Địa chỉ email mà người dùng khai báo.
     * @return array Kết quả xử lý.
     */
    public function forgotPassword($email)
    {
        // Kiểm tra xem email người dùng cung cấp có tồn tại trong CSDL không.
        $query = "SELECT id FROM users WHERE email = :email";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        // Nếu email có tồn tại.
        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC); // Lấy thông tin user (chủ yếu là ID).
            $token = generateToken(); // Tạo một token reset ngẫu nhiên, an toàn.

            // Thiết lập thời gian hết hạn cho token (ngắn hơn trên production).
            $hours = isDevelopmentMode() ? 24 : 12;
            $now = new DateTime('now', new DateTimeZone('Asia/Ho_Chi_Minh')); // Đảm bảo timezone nhất quán.
            $now->modify("+{$hours} hours");
            $expires = $now->format('Y-m-d H:i:s');

            // Xóa các token cũ của cùng một user để tránh rác và nhầm lẫn.
            $query = "DELETE FROM reset_tokens WHERE user_id = :user_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user['id']);
            $stmt->execute();

            // Lưu token mới vào CSDL.
            $query = "INSERT INTO reset_tokens (user_id, token, expires_at) VALUES (:user_id, :token, :expires_at)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user['id']);
            $stmt->bindParam(':token', $token);
            $stmt->bindParam(':expires_at', $expires);

            // Nếu lưu token thành công.
            if ($stmt->execute()) {
                // Tạo link đặt lại mật khẩu hoàn chỉnh.
                $resetLink = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/reset_password.php?token=" . $token;
                
                // Ở chế độ development, trả về link trực tiếp để tiện debug.
                if (isDevelopmentMode()) {
                    return [
                        'success' => true, 
                        'message' => 'Link đặt lại mật khẩu đã được tạo (Chế độ Development)',
                        'reset_link' => $resetLink
                    ];
                }
                
                // Ở chế độ production, tạo nội dung và gửi email.
                $message = "Để đặt lại mật khẩu, vui lòng nhấp vào liên kết sau: <a href='$resetLink'>$resetLink</a><br>Liên kết có hiệu lực trong {$hours} giờ.";
                if (sendEmail($email, 'Yêu cầu đặt lại mật khẩu', $message)) {
                    return ['success' => true, 'message' => 'Một email chứa liên kết đặt lại mật khẩu đã được gửi đến bạn.'];
                } else {
                    return ['success' => false, 'message' => 'Hệ thống đã gặp lỗi khi cố gắng gửi email.'];
                }
            }
        }
        
        // Nếu email không tồn tại, trả về thông báo chung để tránh lộ thông tin.
        return ['success' => false, 'message' => 'Nếu email của bạn tồn tại trong hệ thống, một liên kết sẽ được gửi đến.'];
    }

    /**
     * Xử lý việc đặt lại mật khẩu bằng token.
     * @param string $token Token từ URL mà người dùng nhấp vào.
     * @param string $newPassword Mật khẩu mới người dùng nhập.
     * @return array Kết quả xử lý.
     */
    public function resetPassword($token, $newPassword)
    {
        // Truy vấn CSDL để tìm token và kiểm tra xem nó còn hợp lệ về mặt thời gian hay không.
        $query = "SELECT user_id, expires_at FROM reset_tokens WHERE token = :token";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':token', $token);
        $stmt->execute();
        $tokenData = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Nếu không tìm thấy token trong CSDL.
        if (!$tokenData) {
            return ['success' => false, 'message' => 'Token không hợp lệ hoặc đã được sử dụng.'];
        }
        
        // So sánh thời gian hết hạn với thời gian hiện tại.
        $expiresAt = new DateTime($tokenData['expires_at'], new DateTimeZone('Asia/Ho_Chi_Minh'));
        $now = new DateTime('now', new DateTimeZone('Asia/Ho_Chi_Minh'));
        if ($now > $expiresAt) {
            // Nếu đã hết hạn, xóa token khỏi CSDL.
            $deleteQuery = "DELETE FROM reset_tokens WHERE token = :token";
            $deleteStmt = $this->conn->prepare($deleteQuery);
            $deleteStmt->bindParam(':token', $token);
            $deleteStmt->execute();
            return ['success' => false, 'message' => 'Token đã hết hạn. Vui lòng thực hiện lại yêu cầu "Quên mật khẩu".'];
        }

        // Nếu token hợp lệ.
        // Cập nhật mật khẩu mới cho user tương ứng.
        $query = "UPDATE users SET password = :password WHERE id = :user_id";
        $stmt = $this->conn->prepare($query);
        $hashedPassword = hashPassword($newPassword); // Băm mật khẩu mới.
        $stmt->bindParam(':password', $hashedPassword);
        $stmt->bindParam(':user_id', $tokenData['user_id']);

        if ($stmt->execute()) {
            // Sau khi cập nhật thành công, xóa token đã sử dụng.
            $query = "DELETE FROM reset_tokens WHERE token = :token";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':token', $token);
            $stmt->execute();

            return ['success' => true, 'message' => 'Mật khẩu của bạn đã được đặt lại thành công.'];
        }
        
        return ['success' => false, 'message' => 'Lỗi xảy ra khi cập nhật mật khẩu.'];
    }

    /**
     * Đăng xuất người dùng.
     * @return array Mảng kết quả.
     */
    public function logout()
    {
        // `session_destroy()`: Xóa tất cả dữ liệu đã đăng ký cho một phiên.
        session_destroy();
        return ['success' => true, 'message' => 'Đăng xuất thành công'];
    }

    // --- CÁC PHƯƠNG THỨC QUẢN LÝ NGƯỜI DÙNG (CHO SUPER ADMIN) ---

    /**
     * Lấy tất cả người dùng trong hệ thống.
     * @return array Mảng chứa danh sách các người dùng.
     */
    public function getAllUsers()
    {
        $query = "SELECT id, username, email, role, created_at FROM users ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy thông tin của một người dùng cụ thể bằng ID.
     * @param int $id ID của người dùng.
     * @return array|false Dữ liệu người dùng hoặc `false` nếu không tìm thấy.
     */
    public function getUserById($id)
    {
        $query = "SELECT id, username, email, role, created_at FROM users WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Xóa một người dùng khỏi hệ thống.
     * @param int $id ID của người dùng cần xóa.
     * @return array Mảng kết quả.
     */
    public function deleteUser($id)
    {
        // Một quy tắc nghiệp vụ quan trọng: không cho phép người dùng tự xóa chính mình.
        if ($id == $_SESSION['user_id']) {
            return ['success' => false, 'message' => 'Bạn không thể xóa tài khoản của chính mình.'];
        }

        $query = "DELETE FROM users WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Xóa người dùng thành công.'];
        } else {
            return ['success' => false, 'message' => 'Lỗi khi xóa người dùng.'];
        }
    }

    /**
     * Cập nhật thông tin của một người dùng (thường là vai trò).
     * @param int $id ID người dùng.
     * @param array $data Dữ liệu mới (`username`, `email`, `role`).
     * @return array Mảng kết quả.
     */
    public function updateUser($id, $data)
    {
        $query = "UPDATE users SET username = :username, email = :email, role = :role WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $data['username']);
        $stmt->bindParam(':email', $data['email']);
        $stmt->bindParam(':role', $data['role']);
        $stmt->bindParam(':id', $id);

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Cập nhật người dùng thành công.'];
        } else {
            return ['success' => false, 'message' => 'Lỗi khi cập nhật người dùng.'];
        }
    }

    /**
     * Thay đổi mật khẩu cho một người dùng (chức năng của admin).
     * @param int $id ID người dùng.
     * @param string $newPassword Mật khẩu mới.
     * @return array Mảng kết quả.
     */
    public function changePassword($id, $newPassword)
    {
        $query = "UPDATE users SET password = :password WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $hashedPassword = hashPassword($newPassword); // Băm mật khẩu mới.
        $stmt->bindParam(':password', $hashedPassword);
        $stmt->bindParam(':id', $id);

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Đổi mật khẩu thành công.'];
        } else {
            return ['success' => false, 'message' => 'Lỗi khi đổi mật khẩu.'];
        }
    }
}
