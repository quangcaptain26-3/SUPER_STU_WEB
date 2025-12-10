<?php
// Lớp `Database` được thiết kế theo mẫu Singleton (gần đúng) để quản lý một kết nối duy nhất đến cơ sở dữ liệu.
class Database {
    // --- THUỘC TÍNH KẾT NỐI ---
    // Khai báo thông tin máy chủ cơ sở dữ liệu. 'localhost' nghĩa là CSDL đang chạy trên cùng máy chủ với web server.
    private $host = 'localhost'; 
    // Khai báo tên của cơ sở dữ liệu cần kết nối.
    private $db_name = 'student_management'; 
    // Khai báo tên người dùng để đăng nhập vào CSDL. 'root' là người dùng mặc định có quyền cao nhất trong MySQL.
    private $username = 'root'; 
    // Khai báo mật khẩu cho người dùng CSDL. Mặc định của XAMPP là chuỗi rỗng.
    private $password = ''; 
    // Thuộc tính này sẽ lưu giữ đối tượng kết nối PDO sau khi đã kết nối thành công.
    private $conn; 

    /**
     * Phương thức `getConnection()` chịu trách nhiệm tạo và trả về một đối tượng kết nối PDO.
     * @return PDO|null Trả về đối tượng PDO nếu kết nối thành công, hoặc null nếu thất bại.
     */
    public function getConnection() {
        // Gán lại thuộc tính $conn bằng null để đảm bảo không sử dụng lại kết nối cũ nếu có lỗi xảy ra trước đó.
        $this->conn = null;
        
        // Sử dụng khối try...catch để bắt các ngoại lệ (lỗi) có thể xảy ra trong quá trình kết nối CSDL.
        try {
            // DSN (Data Source Name) - một chuỗi định danh nguồn dữ liệu, chỉ định driver, host và tên CSDL.
            $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name;
            
            // Tạo một instance mới của lớp PDO để thực hiện kết nối.
            // Các tham số bao gồm DSN, username và password.
            $this->conn = new PDO($dsn, $this->username, $this->password);
            
            // Thiết lập thuộc tính cho đối tượng PDO để khi có lỗi SQL, PDO sẽ ném ra một ngoại lệ (PDOException).
            // Điều này giúp việc xử lý lỗi trở nên dễ dàng và nhất quán hơn.
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Thiết lập bộ mã ký tự (character set) cho kết nối là UTF-8.
            // Điều này cực kỳ quan trọng để đảm bảo dữ liệu tiếng Việt được lưu và truy xuất chính xác.
            $this->conn->exec("set names utf8");

        } catch(PDOException $exception) {
            // Nếu có bất kỳ lỗi nào xảy ra trong khối `try`, nó sẽ được bắt lại ở đây.
            // Hiển thị thông báo lỗi kết nối ra màn hình để giúp debug.
            // Trong môi trường production, nên ghi lỗi này vào file log thay vì hiển thị trực tiếp.
            echo "Lỗi kết nối CSDL: " . $exception->getMessage();
        }
        
        // Trả về đối tượng kết nối đã được thiết lập.
        // Nếu kết nối thất bại, giá trị trả về sẽ là `null` (do đã gán ở đầu hàm).
        return $this->conn;
    }
}
?>
