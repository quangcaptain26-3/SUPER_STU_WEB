<?php
// Lớp `Database` được thiết kế theo mẫu Singleton (gần đúng) để quản lý một kết nối duy nhất đến cơ sở dữ liệu.
class Database {
    // --- THUỘC TÍNH KẾT NỐI ---
    // Khai báo thông tin máy chủ cơ sở dữ liệu.
    // Trên InfinityFree: thường là 'localhost' hoặc 'sqlXXX.infinityfree.com'
    // Trên local (XAMPP): 'localhost'
    private $host = 'localhost'; 
    
    // Khai báo tên của cơ sở dữ liệu cần kết nối.
    // Trên InfinityFree: format thường là 'if0_XXXXXX_student_management' (KHÔNG có dấu cách)
    // Trên local: 'student_management'
    // LƯU Ý: Loại bỏ tất cả dấu cách thừa ở đầu và cuối tên database
    private $db_name = 'student_management'; 
    
    // Khai báo tên người dùng để đăng nhập vào CSDL.
    // Trên InfinityFree: thường giống với prefix của database name (ví dụ: 'if0_XXXXXX')
    // Trên local (XAMPP): 'root'
    private $username = 'root'; 
    
    // Khai báo mật khẩu cho người dùng CSDL.
    // Trên InfinityFree: lấy từ control panel
    // Trên local (XAMPP): thường là chuỗi rỗng ''
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
            // Loại bỏ tất cả dấu cách thừa ở đầu và cuối để tránh lỗi kết nối
            $host = trim($this->host);
            $db_name = trim($this->db_name);
            $username = trim($this->username);
            $password = trim($this->password);
            
            // DSN (Data Source Name) - một chuỗi định danh nguồn dữ liệu, chỉ định driver, host và tên CSDL.
            // Sử dụng charset=utf8mb4 để hỗ trợ đầy đủ tiếng Việt
            $dsn = "mysql:host=" . $host . ";dbname=" . $db_name . ";charset=utf8mb4";
            
            // Tạo một instance mới của lớp PDO để thực hiện kết nối.
            // Các tham số bao gồm DSN, username và password.
            $this->conn = new PDO($dsn, $username, $password);
            
            // Thiết lập thuộc tính cho đối tượng PDO để khi có lỗi SQL, PDO sẽ ném ra một ngoại lệ (PDOException).
            // Điều này giúp việc xử lý lỗi trở nên dễ dàng và nhất quán hơn.
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Thiết lập chế độ fetch mặc định là FETCH_ASSOC (trả về mảng kết hợp)
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            
            // Thiết lập bộ mã ký tự (character set) cho kết nối là UTF-8.
            // Điều này cực kỳ quan trọng để đảm bảo dữ liệu tiếng Việt được lưu và truy xuất chính xác.
            $this->conn->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");

        } catch(PDOException $exception) {
            // Nếu có bất kỳ lỗi nào xảy ra trong khối `try`, nó sẽ được bắt lại ở đây.
            // Hiển thị thông báo lỗi kết nối ra màn hình để giúp debug.
            // Trong môi trường production, nên ghi lỗi này vào file log thay vì hiển thị trực tiếp.
            $error_msg = "Lỗi kết nối CSDL: " . $exception->getMessage();
            
            // Kiểm tra xem có phải lỗi do database name có dấu cách không
            if (strpos($exception->getMessage(), 'Access denied') !== false) {
                $error_msg .= "\n\n⚠️ LƯU Ý: Kiểm tra lại:\n";
                $error_msg .= "- Database name: '" . $this->db_name . "' (có thể có dấu cách thừa)\n";
                $error_msg .= "- Username: '" . $this->username . "'\n";
                $error_msg .= "- Host: '" . $this->host . "'\n";
                $error_msg .= "- Đảm bảo database đã được tạo trên hosting\n";
            }
            
            echo $error_msg;
        }
        
        // Trả về đối tượng kết nối đã được thiết lập.
        // Nếu kết nối thất bại, giá trị trả về sẽ là `null` (do đã gán ở đầu hàm).
        return $this->conn;
    }
}
?>
