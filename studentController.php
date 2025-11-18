<?php
// Lấy các file cấu hình cơ sở dữ liệu và các hàm tiện ích
require_once 'config/db.php';
require_once 'utils.php';

/**
 * Class StudentController
 * Xử lý tất cả các thao tác liên quan đến sinh viên
 */
class StudentController
{
    // Khai báo biến private để lưu trữ kết nối cơ sở dữ liệu
    private $conn;

    /**
     * Hàm khởi tạo StudentController
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
     * Lấy tất cả sinh viên với hỗ trợ tìm kiếm, giới hạn và phân trang
     * @param string $search Từ khóa tìm kiếm
     * @param int $limit Số lượng sinh viên tối đa cần trả về
     * @param int $offset Vị trí bắt đầu lấy dữ liệu
     * @return array Mảng chứa danh sách sinh viên
     */
    public function getAllStudents($search = '', $limit = 50, $offset = 0)
    {
        // Khai báo câu query SQL cơ bản để lấy tất cả dữ liệu từ bảng students
        $query = "SELECT * FROM students";
        // Khởi tạo mảng trống để lưu trữ các tham số bind
        $params = [];

        // Nếu có từ khóa tìm kiếm, thêm điều kiện WHERE vào câu query
        if (!empty($search)) {
            // Thêm điều kiện tìm kiếm theo fullname, msv hoặc email
            $query .= " WHERE fullname LIKE :search OR msv LIKE :search OR email LIKE :search";
            // Gán giá trị tìm kiếm với ký tự % ở hai bên để tìm kiếm bất kỳ vị trí nào
            $params[':search'] = "%$search%";
        }

        // Thêm sắp xếp theo ngày tạo (mới nhất trước) và giới hạn số lượng bản ghi
        $query .= " ORDER BY created_at DESC LIMIT :limit OFFSET :offset";

        // Chuẩn bị câu lệnh SQL
        $stmt = $this->conn->prepare($query);
        // Bind các tham số tìm kiếm nếu có
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        // Bind tham số limit và offset với kiểu dữ liệu là số nguyên
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        // Thực thi câu lệnh
        $stmt->execute();

        // Trả về tất cả kết quả dưới dạng mảng kết hợp (column_name => value)
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy thông tin một sinh viên theo ID
     * @param int $id ID của sinh viên
     * @return array|false Dữ liệu sinh viên hoặc false nếu không tìm thấy
     */
    public function getStudentById($id)
    {
        // Khai báo câu query SQL để lấy sinh viên có ID cụ thể
        $query = "SELECT * FROM students WHERE id = :id";
        // Chuẩn bị câu lệnh SQL
        $stmt = $this->conn->prepare($query);
        // Bind tham số ID vào câu query
        $stmt->bindParam(':id', $id);
        // Thực thi câu lệnh
        $stmt->execute();

        // Trả về một dòng kết quả dưới dạng mảng kết hợp hoặc false
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Thêm sinh viên mới vào cơ sở dữ liệu
     * @param array $data Dữ liệu sinh viên mới
     * @return array Mảng chỉ ra thành công/thất bại và thông điệp
     */
    public function addStudent($data)
    {
        // Khai báo câu query để kiểm tra xem mã sinh viên đã tồn tại hay chưa
        $query = "SELECT id FROM students WHERE msv = :msv";
        // Chuẩn bị câu lệnh
        $stmt = $this->conn->prepare($query);
        // Bind mã sinh viên từ dữ liệu đầu vào
        $stmt->bindParam(':msv', $data['msv']);
        // Thực thi câu lệnh
        $stmt->execute();

        // Nếu mã sinh viên đã tồn tại (rowCount > 0), trả về lỗi
        if ($stmt->rowCount() > 0) {
            return ['success' => false, 'message' => 'Mã sinh viên đã tồn tại'];
        }

        // Khai báo câu query INSERT để thêm sinh viên mới
        $query = "INSERT INTO students (msv, fullname, dob, gender, address, phone, email, avatar) 
                  VALUES (:msv, :fullname, :dob, :gender, :address, :phone, :email, :avatar)";

        // Chuẩn bị câu lệnh
        $stmt = $this->conn->prepare($query);
        // Bind tất cả các tham số từ dữ liệu đầu vào
        $stmt->bindParam(':msv', $data['msv']);
        $stmt->bindParam(':fullname', $data['fullname']);
        $stmt->bindParam(':dob', $data['dob']);
        $stmt->bindParam(':gender', $data['gender']);
        $stmt->bindParam(':address', $data['address']);
        $stmt->bindParam(':phone', $data['phone']);
        $stmt->bindParam(':email', $data['email']);
        $stmt->bindParam(':avatar', $data['avatar']);

        // Thực thi câu lệnh và kiểm tra kết quả
        if ($stmt->execute()) {
            // Nếu thành công, trả về thông báo cùng ID sinh viên vừa được thêm
            return ['success' => true, 'message' => 'Thêm sinh viên thành công', 'id' => $this->conn->lastInsertId()];
        } else {
            // Nếu thất bại, trả về thông báo lỗi
            return ['success' => false, 'message' => 'Lỗi thêm sinh viên'];
        }
    }


    /**
     * Cập nhật thông tin sinh viên hiện có
     * @param int $id ID của sinh viên cần cập nhật
     * @param array $data Dữ liệu mới cho sinh viên
     * @return array Mảng chỉ ra thành công/thất bại và thông điệp
     */
    public function updateStudent($id, $data)
    {
        // Khai báo câu query để kiểm tra xem mã sinh viên có bị trùng với sinh viên khác không
        $query = "SELECT id FROM students WHERE msv = :msv AND id != :id";
        // Chuẩn bị câu lệnh
        $stmt = $this->conn->prepare($query);
        // Bind mã sinh viên từ dữ liệu đầu vào
        $stmt->bindParam(':msv', $data['msv']);
        // Bind ID sinh viên hiện tại để loại trừ nó khỏi kiểm tra
        $stmt->bindParam(':id', $id);
        // Thực thi câu lệnh
        $stmt->execute();

        // Nếu có sinh viên khác có mã sinh viên này, trả về lỗi
        if ($stmt->rowCount() > 0) {
            return ['success' => false, 'message' => 'Mã sinh viên đã tồn tại'];
        }

        // Khai báo câu query UPDATE để cập nhật thông tin sinh viên
        $query = "UPDATE students SET msv = :msv, fullname = :fullname, dob = :dob, 
                  gender = :gender, address = :address, phone = :phone, email = :email, 
                  avatar = :avatar WHERE id = :id";

        // Chuẩn bị câu lệnh
        $stmt = $this->conn->prepare($query);
        // Bind tất cả các tham số từ dữ liệu đầu vào
        $stmt->bindParam(':msv', $data['msv']);
        $stmt->bindParam(':fullname', $data['fullname']);
        $stmt->bindParam(':dob', $data['dob']);
        $stmt->bindParam(':gender', $data['gender']);
        $stmt->bindParam(':address', $data['address']);
        $stmt->bindParam(':phone', $data['phone']);
        $stmt->bindParam(':email', $data['email']);
        $stmt->bindParam(':avatar', $data['avatar']);
        // Bind ID sinh viên để xác định bản ghi nào cần cập nhật
        $stmt->bindParam(':id', $id);

        // Thực thi câu lệnh và kiểm tra kết quả
        if ($stmt->execute()) {
            // Nếu thành công, trả về thông báo
            return ['success' => true, 'message' => 'Cập nhật sinh viên thành công'];
        } else {
            // Nếu thất bại, trả về thông báo lỗi
            return ['success' => false, 'message' => 'Lỗi cập nhật sinh viên'];
        }
    }

    /**
     * Xóa sinh viên khỏi cơ sở dữ liệu
     * @param int $id ID của sinh viên cần xóa
     * @return array Mảng chỉ ra thành công/thất bại và thông điệp
     */
    public function deleteStudent($id)
    {
        // Lấy thông tin sinh viên trước khi xóa để có thể xóa file avatar
        $student = $this->getStudentById($id);
        // Nếu sinh viên tồn tại và có avatar, xóa file avatar
        if ($student && $student['avatar']) {
            deleteFile('uploads/avatars/' . $student['avatar']);
        }

        // Khai báo câu query DELETE để xóa sinh viên
        $query = "DELETE FROM students WHERE id = :id";
        // Chuẩn bị câu lệnh
        $stmt = $this->conn->prepare($query);
        // Bind ID sinh viên cần xóa
        $stmt->bindParam(':id', $id);

        // Thực thi câu lệnh và kiểm tra kết quả
        if ($stmt->execute()) {
            // Nếu thành công, trả về thông báo
            return ['success' => true, 'message' => 'Xóa sinh viên thành công'];
        } else {
            // Nếu thất bại, trả về thông báo lỗi
            return ['success' => false, 'message' => 'Lỗi xóa sinh viên'];
        }
    }


    /**
     * Lấy tổng số sinh viên với bộ lọc tìm kiếm tùy chọn
     * @param string $search Từ khóa tìm kiếm
     * @return int Tổng số sinh viên
     */
    public function getTotalStudents($search = '')
    {
        // Khai báo câu query để đếm tổng số sinh viên
        $query = "SELECT COUNT(*) as total FROM students";
        // Khởi tạo mảng trống để lưu trữ các tham số bind
        $params = [];

        // Nếu có từ khóa tìm kiếm, thêm điều kiện WHERE vào câu query
        if (!empty($search)) {
            // Thêm điều kiện tìm kiếm theo fullname, msv hoặc email
            $query .= " WHERE fullname LIKE :search OR msv LIKE :search OR email LIKE :search";
            // Gán giá trị tìm kiếm với ký tự % ở hai bên
            $params[':search'] = "%$search%";
        }

        // Chuẩn bị câu lệnh
        $stmt = $this->conn->prepare($query);
        // Bind các tham số tìm kiếm nếu có
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        // Thực thi câu lệnh
        $stmt->execute();

        // Lấy kết quả và trả về giá trị total
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }

    /**
     * Lấy thống kê về sinh viên
     * @return array Mảng chứa các thống kê sinh viên
     */
    public function getStatistics()
    {
        // Khởi tạo mảng để lưu trữ các thống kê
        $stats = [];

        // Lấy tổng số sinh viên
        $query = "SELECT COUNT(*) as total FROM students";
        // Chuẩn bị và thực thi câu lệnh
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        // Lưu kết quả vào mảng stats
        $stats['total_students'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Lấy số sinh viên theo giới tính
        $query = "SELECT gender, COUNT(*) as count FROM students GROUP BY gender";
        // Chuẩn bị và thực thi câu lệnh
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        // Lưu kết quả vào mảng stats
        $stats['by_gender'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Lấy số sinh viên mới được thêm theo tháng trong 12 tháng gần đây
        $query = "SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count 
                  FROM students 
                  WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                  GROUP BY month 
                  ORDER BY month";
        // Chuẩn bị và thực thi câu lệnh
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        // Lưu kết quả vào mảng stats
        $stats['by_month'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Trả về mảng thống kê
        return $stats;
    }
}
