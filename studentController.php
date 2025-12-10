<?php
// Nạp các file cần thiết.
require_once 'config/db.php'; // Chứa lớp `Database` để kết nối CSDL.
require_once 'utils.php';     // Chứa các hàm tiện ích.

/**
 * Class StudentController
 * Chịu trách nhiệm xử lý tất cả các nghiệp vụ logic liên quan đến đối tượng Sinh viên.
 * Bao gồm các thao tác CRUD (Create, Read, Update, Delete) và các nghiệp vụ thống kê.
 */
class StudentController
{
    // Thuộc tính private để lưu trữ đối tượng kết nối CSDL (PDO).
    private $conn;

    /**
     * Hàm khởi tạo của lớp StudentController.
     */
    public function __construct()
    {
        // Khởi tạo một đối tượng từ lớp Database.
        $database = new Database();
        // Lấy đối tượng kết nối PDO và gán vào thuộc tính của controller.
        $this->conn = $database->getConnection();
    }

    /**
     * Lấy danh sách sinh viên, hỗ trợ tìm kiếm và phân trang.
     * @param string $search Chuỗi tìm kiếm (có thể rỗng).
     * @param int $limit Số lượng bản ghi tối đa trên một trang.
     * @param int $offset Vị trí bắt đầu lấy bản ghi (dùng cho phân trang).
     * @return array Mảng các sinh viên, mỗi sinh viên là một mảng kết hợp.
     */
    public function getAllStudents($search = '', $limit = 50, $offset = 0)
    {
        // Bắt đầu xây dựng câu lệnh SQL.
        $query = "SELECT * FROM students";
        // Khởi tạo mảng để chứa các tham số cho câu lệnh prepared statement.
        $params = [];

        // Nếu người dùng có nhập từ khóa tìm kiếm.
        if (!empty($search)) {
            // Nối thêm điều kiện WHERE vào câu query.
            // Tìm kiếm trên các cột: fullname, msv, email. `LIKE` được dùng để tìm kiếm một phần của chuỗi.
            $query .= " WHERE fullname LIKE :search OR msv LIKE :search OR email LIKE :search";
            // Gán giá trị cho tham số `:search`. Dấu `%` đại diện cho bất kỳ chuỗi ký tự nào.
            $params[':search'] = "%$search%";
        }

        // Nối thêm phần sắp xếp và phân trang.
        // `ORDER BY created_at DESC`: Sắp xếp các sinh viên mới nhất lên đầu.
        // `LIMIT :limit OFFSET :offset`: Giới hạn số lượng kết quả và chỉ định điểm bắt đầu.
        $query .= " ORDER BY created_at DESC LIMIT :limit OFFSET :offset";

        // Chuẩn bị câu lệnh SQL để thực thi.
        $stmt = $this->conn->prepare($query);
        
        // Gắn các giá trị từ mảng `$params` vào câu lệnh.
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        // Gắn các giá trị cho limit và offset, chỉ định rõ kiểu dữ liệu là số nguyên (INT).
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        
        // Thực thi câu lệnh.
        $stmt->execute();

        // Lấy tất cả các dòng kết quả và trả về dưới dạng một mảng các mảng kết hợp.
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy thông tin chi tiết của một sinh viên dựa vào ID.
     * @param int $id ID của sinh viên.
     * @return array|false Mảng chứa thông tin sinh viên, hoặc `false` nếu không tìm thấy.
     */
    public function getStudentById($id)
    {
        // Câu lệnh SQL để chọn một sinh viên có ID cụ thể.
        $query = "SELECT * FROM students WHERE id = :id";
        // Chuẩn bị câu lệnh.
        $stmt = $this->conn->prepare($query);
        // Gắn ID vào tham số `:id`.
        $stmt->bindParam(':id', $id);
        // Thực thi.
        $stmt->execute();

        // `fetch()`: Lấy một bản ghi duy nhất và trả về.
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Thêm một sinh viên mới vào cơ sở dữ liệu.
     * @param array $data Dữ liệu của sinh viên từ form.
     * @return array Mảng kết quả chứa `success`, `message`, và `id` của sinh viên mới.
     */
    public function addStudent($data)
    {
        // Quy tắc nghiệp vụ: Mã sinh viên (msv) phải là duy nhất.
        // Trước khi thêm, kiểm tra xem MSV đã tồn tại chưa.
        $query = "SELECT id FROM students WHERE msv = :msv";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':msv', $data['msv']);
        $stmt->execute();

        // Nếu `rowCount` > 0, nghĩa là đã tìm thấy sinh viên có cùng MSV.
        if ($stmt->rowCount() > 0) {
            return ['success' => false, 'message' => 'Mã sinh viên này đã tồn tại trong hệ thống.'];
        }

        // Nếu MSV hợp lệ, chuẩn bị câu lệnh INSERT.
        $query = "INSERT INTO students (msv, fullname, dob, gender, address, phone, email, avatar) 
                  VALUES (:msv, :fullname, :dob, :gender, :address, :phone, :email, :avatar)";

        $stmt = $this->conn->prepare($query);
        
        // Gắn tất cả các giá trị từ mảng `$data` vào các tham số tương ứng.
        $stmt->bindParam(':msv', $data['msv']);
        $stmt->bindParam(':fullname', $data['fullname']);
        $stmt->bindParam(':dob', $data['dob']);
        $stmt->bindParam(':gender', $data['gender']);
        $stmt->bindParam(':address', $data['address']);
        $stmt->bindParam(':phone', $data['phone']);
        $stmt->bindParam(':email', $data['email']);
        $stmt->bindParam(':avatar', $data['avatar']); // Tên file avatar đã được xử lý ở bước trước.

        // Thực thi câu lệnh INSERT và kiểm tra kết quả.
        if ($stmt->execute()) {
            // `lastInsertId()`: Lấy ID của bản ghi vừa được chèn vào.
            return ['success' => true, 'message' => 'Thêm sinh viên thành công!', 'id' => $this->conn->lastInsertId()];
        } else {
            return ['success' => false, 'message' => 'Đã xảy ra lỗi khi thêm sinh viên.'];
        }
    }

    /**
     * Cập nhật thông tin của một sinh viên đã có.
     * @param int $id ID của sinh viên cần cập nhật.
     * @param array $data Dữ liệu mới của sinh viên.
     * @return array Mảng kết quả.
     */
    public function updateStudent($id, $data)
    {
        // Quy tắc nghiệp vụ: Khi cập nhật, MSV mới không được trùng với MSV của một sinh viên *khác*.
        $query = "SELECT id FROM students WHERE msv = :msv AND id != :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':msv', $data['msv']);
        $stmt->bindParam(':id', $id); // Loại trừ chính sinh viên đang được sửa.
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            return ['success' => false, 'message' => 'Mã sinh viên này đã tồn tại trong hệ thống.'];
        }

        // Chuẩn bị câu lệnh UPDATE.
        $query = "UPDATE students SET msv = :msv, fullname = :fullname, dob = :dob, 
                  gender = :gender, address = :address, phone = :phone, email = :email, 
                  avatar = :avatar WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        
        // Gắn các giá trị mới.
        $stmt->bindParam(':msv', $data['msv']);
        $stmt->bindParam(':fullname', $data['fullname']);
        $stmt->bindParam(':dob', $data['dob']);
        $stmt->bindParam(':gender', $data['gender']);
        $stmt->bindParam(':address', $data['address']);
        $stmt->bindParam(':phone', $data['phone']);
        $stmt->bindParam(':email', $data['email']);
        $stmt->bindParam(':avatar', $data['avatar']);
        $stmt->bindParam(':id', $id); // Gắn ID để xác định bản ghi cần cập nhật.

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Cập nhật thông tin sinh viên thành công.'];
        } else {
            return ['success' => false, 'message' => 'Đã xảy ra lỗi khi cập nhật.'];
        }
    }

    /**
     * Xóa một sinh viên khỏi cơ sở dữ liệu và xóa file avatar liên quan.
     * @param int $id ID của sinh viên cần xóa.
     * @return array Mảng kết quả.
     */
    public function deleteStudent($id)
    {
        // Lấy thông tin sinh viên để biết tên file avatar cần xóa.
        $student = $this->getStudentById($id);
        
        // Nếu sinh viên tồn tại và có thông tin avatar.
        if ($student && !empty($student['avatar'])) {
            // Gọi hàm tiện ích để xóa file vật lý trên server.
            deleteFile('uploads/avatars/' . $student['avatar']);
        }

        // Chuẩn bị câu lệnh DELETE.
        $query = "DELETE FROM students WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Xóa sinh viên thành công.'];
        } else {
            return ['success' => false, 'message' => 'Đã xảy ra lỗi khi xóa sinh viên.'];
        }
    }

    /**
     * Đếm tổng số sinh viên, có áp dụng bộ lọc tìm kiếm.
     * Dùng cho việc tính toán phân trang.
     * @param string $search Từ khóa tìm kiếm.
     * @return int Tổng số sinh viên tìm thấy.
     */
    public function getTotalStudents($search = '')
    {
        // Bắt đầu câu lệnh đếm.
        $query = "SELECT COUNT(*) as total FROM students";
        $params = [];

        // Nếu có tìm kiếm, thêm điều kiện WHERE tương tự như `getAllStudents`.
        if (!empty($search)) {
            $query .= " WHERE fullname LIKE :search OR msv LIKE :search OR email LIKE :search";
            $params[':search'] = "%$search%";
        }

        $stmt = $this->conn->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();

        // Lấy kết quả đếm từ cột 'total'.
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)$result['total']; // Ép kiểu về số nguyên.
    }

    /**
     * Lấy các dữ liệu thống kê liên quan đến sinh viên.
     * Được sử dụng bởi API cho dashboard.
     * @return array Mảng chứa các thông tin thống kê.
     */
    public function getStatistics()
    {
        $stats = [];

        // 1. Đếm tổng số sinh viên.
        $query1 = "SELECT COUNT(*) as total FROM students";
        $stmt1 = $this->conn->prepare($query1);
        $stmt1->execute();
        $stats['total_students'] = $stmt1->fetch(PDO::FETCH_ASSOC)['total'];

        // 2. Đếm số sinh viên theo từng giới tính.
        $query2 = "SELECT gender, COUNT(*) as count FROM students GROUP BY gender";
        $stmt2 = $this->conn->prepare($query2);
        $stmt2->execute();
        $stats['by_gender'] = $stmt2->fetchAll(PDO::FETCH_ASSOC);

        // 3. Đếm số sinh viên mới được tạo trong 12 tháng gần nhất, nhóm theo tháng.
        $query3 = "SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count 
                   FROM students 
                   WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                   GROUP BY month 
                   ORDER BY month";
        $stmt3 = $this->conn->prepare($query3);
        $stmt3->execute();
        $stats['by_month'] = $stmt3->fetchAll(PDO::FETCH_ASSOC);

        // Trả về mảng chứa tất cả các kết quả thống kê.
        return $stats;
    }
}
