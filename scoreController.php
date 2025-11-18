<?php
// Lấy các file cấu hình cơ sở dữ liệu và các hàm tiện ích
require_once 'config/db.php';
require_once 'utils.php';

/**
 * Class ScoreController
 * Xử lý tất cả các thao tác liên quan đến điểm sinh viên
 */
class ScoreController
{
    // Khai báo biến private để lưu trữ kết nối cơ sở dữ liệu
    private $conn;

    /**
     * Hàm khởi tạo ScoreController
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
     * Lấy tất cả điểm với bộ lọc tùy chọn theo ID sinh viên và học kỳ
     * @param int|null $studentId ID sinh viên để lọc
     * @param string|null $semester Học kỳ để lọc
     * @return array Mảng chứa danh sách điểm
     */
    public function getAllScores($studentId = null, $semester = null)
    {
        // Khai báo câu query SELECT để lấy điểm kèm thông tin sinh viên
        $query = "SELECT s.*, st.fullname, st.msv 
                  FROM scores s 
                  LEFT JOIN students st ON s.student_id = st.id";
        // Khởi tạo mảng trống để lưu trữ các tham số bind
        $params = [];

        // Khởi tạo mảng để lưu trữ các điều kiện WHERE
        $conditions = [];
        // Nếu có ID sinh viên, thêm điều kiện lọc theo sinh viên
        if ($studentId) {
            $conditions[] = "s.student_id = :student_id";
            $params[':student_id'] = $studentId;
        }
        // Nếu có học kỳ, thêm điều kiện lọc theo học kỳ
        if ($semester) {
            $conditions[] = "s.semester = :semester";
            $params[':semester'] = $semester;
        }

        // Nếu có điều kiện, thêm WHERE vào câu query
        if (!empty($conditions)) {
            $query .= " WHERE " . implode(" AND ", $conditions);
        }

        // Thêm sắp xếp theo học kỳ (mới nhất trước) và tên sinh viên
        $query .= " ORDER BY s.semester DESC, st.fullname";

        // Chuẩn bị câu lệnh SQL
        $stmt = $this->conn->prepare($query);
        // Bind các tham số lọc nếu có
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        // Thực thi câu lệnh
        $stmt->execute();

        // Trả về tất cả kết quả dưới dạng mảng kết hợp
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy thông tin một điểm theo ID
     * @param int $id ID của điểm
     * @return array|false Dữ liệu điểm hoặc false nếu không tìm thấy
     */
    public function getScoreById($id)
    {
        // Khai báo câu query SQL để lấy điểm kèm thông tin sinh viên
        $query = "SELECT s.*, st.fullname, st.msv 
                  FROM scores s 
                  LEFT JOIN students st ON s.student_id = st.id 
                  WHERE s.id = :id";
        // Chuẩn bị câu lệnh SQL
        $stmt = $this->conn->prepare($query);
        // Bind tham số ID
        $stmt->bindParam(':id', $id);
        // Thực thi câu lệnh
        $stmt->execute();

        // Trả về một dòng kết quả hoặc false
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Thêm điểm mới vào cơ sở dữ liệu
     * @param array $data Dữ liệu điểm mới
     * @return array Mảng chỉ ra thành công/thất bại và thông điệp
     */
    public function addScore($data)
    {
        // Khai báo câu query INSERT để thêm điểm mới
        $query = "INSERT INTO scores (student_id, subject, score, semester) 
                  VALUES (:student_id, :subject, :score, :semester)";

        // Chuẩn bị câu lệnh
        $stmt = $this->conn->prepare($query);
        // Bind tất cả các tham số từ dữ liệu đầu vào
        $stmt->bindParam(':student_id', $data['student_id']);
        $stmt->bindParam(':subject', $data['subject']);
        $stmt->bindParam(':score', $data['score']);
        $stmt->bindParam(':semester', $data['semester']);

        // Thực thi câu lệnh và kiểm tra kết quả
        if ($stmt->execute()) {
            // Nếu thành công, trả về thông báo cùng ID điểm vừa được thêm
            return ['success' => true, 'message' => 'Thêm điểm thành công', 'id' => $this->conn->lastInsertId()];
        } else {
            // Nếu thất bại, trả về thông báo lỗi
            return ['success' => false, 'message' => 'Lỗi thêm điểm'];
        }
    }

    /**
     * Cập nhật thông tin điểm hiện có
     * @param int $id ID của điểm cần cập nhật
     * @param array $data Dữ liệu mới cho điểm
     * @return array Mảng chỉ ra thành công/thất bại và thông điệp
     */
    public function updateScore($id, $data)
    {
        // Khai báo câu query UPDATE để cập nhật thông tin điểm
        $query = "UPDATE scores SET student_id = :student_id, subject = :subject, 
                  score = :score, semester = :semester WHERE id = :id";

        // Chuẩn bị câu lệnh
        $stmt = $this->conn->prepare($query);
        // Bind tất cả các tham số từ dữ liệu đầu vào
        $stmt->bindParam(':student_id', $data['student_id']);
        $stmt->bindParam(':subject', $data['subject']);
        $stmt->bindParam(':score', $data['score']);
        $stmt->bindParam(':semester', $data['semester']);
        // Bind ID điểm để xác định bản ghi nào cần cập nhật
        $stmt->bindParam(':id', $id);

        // Thực thi câu lệnh và kiểm tra kết quả
        if ($stmt->execute()) {
            // Nếu thành công, trả về thông báo
            return ['success' => true, 'message' => 'Cập nhật điểm thành công'];
        } else {
            // Nếu thất bại, trả về thông báo lỗi
            return ['success' => false, 'message' => 'Lỗi cập nhật điểm'];
        }
    }

    /**
     * Xóa điểm khỏi cơ sở dữ liệu
     * @param int $id ID của điểm cần xóa
     * @return array Mảng chỉ ra thành công/thất bại và thông điệp
     */
    public function deleteScore($id)
    {
        // Khai báo câu query DELETE để xóa điểm
        $query = "DELETE FROM scores WHERE id = :id";
        // Chuẩn bị câu lệnh
        $stmt = $this->conn->prepare($query);
        // Bind ID điểm cần xóa
        $stmt->bindParam(':id', $id);

        // Thực thi câu lệnh và kiểm tra kết quả
        if ($stmt->execute()) {
            // Nếu thành công, trả về thông báo
            return ['success' => true, 'message' => 'Xóa điểm thành công'];
        } else {
            // Nếu thất bại, trả về thông báo lỗi
            return ['success' => false, 'message' => 'Lỗi xóa điểm'];
        }
    }

    /**
     * Lấy thống kê về điểm
     * @return array Mảng chứa các thống kê điểm
     */
    public function getScoreStatistics()
    {
        // Khởi tạo mảng để lưu trữ các thống kê
        $stats = [];

        // Tính điểm trung bình theo môn học
        $query = "SELECT subject, AVG(score) as avg_score, COUNT(*) as count 
                  FROM scores 
                  GROUP BY subject 
                  ORDER BY avg_score DESC";
        // Chuẩn bị và thực thi câu lệnh
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        // Lưu kết quả vào mảng stats
        $stats['by_subject'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Tính điểm trung bình theo học kỳ
        $query = "SELECT semester, AVG(score) as avg_score, COUNT(*) as count 
                  FROM scores 
                  GROUP BY semester 
                  ORDER BY semester DESC";
        // Chuẩn bị và thực thi câu lệnh
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        // Lưu kết quả vào mảng stats
        $stats['by_semester'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Lấy phân bố điểm theo loại bảng xếp (A+, A, B+, B, C, D)
        $query = "SELECT 
                    CASE 
                        WHEN score >= 9 THEN 'A+'
                        WHEN score >= 8 THEN 'A'
                        WHEN score >= 7 THEN 'B+'
                        WHEN score >= 6 THEN 'B'
                        WHEN score >= 5 THEN 'C'
                        ELSE 'D'
                    END as grade,
                    COUNT(*) as count
                  FROM scores 
                  GROUP BY grade 
                  ORDER BY grade";
        // Chuẩn bị và thực thi câu lệnh
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        // Lưu kết quả vào mảng stats
        $stats['grade_distribution'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Trả về mảng thống kê
        return $stats;
    }

    /**
     * Lấy tất cả điểm của một sinh viên cụ thể
     * @param int $studentId ID của sinh viên
     * @return array Mảng chứa tất cả điểm của sinh viên
     */
    public function getStudentScores($studentId)
    {
        // Khai báo câu query để lấy tất cả điểm của sinh viên
        $query = "SELECT * FROM scores WHERE student_id = :student_id ORDER BY semester DESC, subject";
        // Chuẩn bị câu lệnh
        $stmt = $this->conn->prepare($query);
        // Bind ID sinh viên
        $stmt->bindParam(':student_id', $studentId);
        // Thực thi câu lệnh
        $stmt->execute();

        // Trả về tất cả kết quả
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy điểm trung bình của một sinh viên cụ thể
     * @param int $studentId ID của sinh viên
     * @return float Điểm trung bình của sinh viên
     */
    public function getStudentAverageScore($studentId)
    {
        // Khai báo câu query để tính điểm trung bình
        $query = "SELECT AVG(score) as avg_score FROM scores WHERE student_id = :student_id";
        // Chuẩn bị câu lệnh
        $stmt = $this->conn->prepare($query);
        // Bind ID sinh viên
        $stmt->bindParam(':student_id', $studentId);
        // Thực thi câu lệnh
        $stmt->execute();

        // Lấy kết quả và trả về giá trị avg_score làm tròn 2 chữ số thập phân
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return round($result['avg_score'], 2);
    }
}
