<?php
// Lớp Database để quản lý kết nối đến cơ sở dữ liệu
class Database {
    // Thông tin kết nối CSDL
    private $host = 'localhost'; // Host của CSDL
    private $db_name = 'student_management'; // Tên CSDL
    private $username = 'root'; // Tên người dùng CSDL
    private $password = ''; // Mật khẩu CSDL
    private $conn; // Biến lưu trữ đối tượng kết nối PDO

    // Phương thức để lấy kết nối đến CSDL
    public function getConnection() {
        // Gán lại conn bằng null để đảm bảo kết nối mới được tạo
        $this->conn = null;
        
        try {
            // Tạo một đối tượng PDO mới để kết nối
            // DSN (Data Source Name) chứa thông tin về driver, host và tên CSDL
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, 
                                $this->username, $this->password);
            // Thiết lập chế độ báo lỗi của PDO thành Exception để dễ dàng bắt lỗi
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            // Thiết lập bộ mã ký tự là UTF-8 để hỗ trợ tiếng Việt
            $this->conn->exec("set names utf8");
        } catch(PDOException $exception) {
            // Nếu có lỗi kết nối, hiển thị thông báo lỗi
            echo "Connection error: " . $exception->getMessage();
        }
        
        // Trả về đối tượng kết nối
        return $this->conn;
    }
}
?>
